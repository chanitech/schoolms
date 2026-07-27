#!/usr/bin/env python3
"""
ZKTeco K40 -> SchoolMS attendance relay.

Run this on a PC on the same local network as the K40 (via Windows Task
Scheduler or cron/systemd), every few minutes. It pulls scan logs off the
device, tracks a local watermark so only new scans are sent, and POSTs them
to the SchoolMS biometric-scans API endpoint. See README.md for setup.

The K40 itself retains up to ~80,000 scan records, so nothing is lost if
this machine is off for a while -- the next run picks up wherever it left off.
"""
import configparser
import json
import logging
import os
import sys
import time
from datetime import datetime

import requests
from zk import ZK

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
CONFIG_PATH = os.path.join(SCRIPT_DIR, "config.ini")
LOG_PATH = os.path.join(SCRIPT_DIR, "relay.log")

logging.basicConfig(
    filename=LOG_PATH,
    level=logging.INFO,
    format="%(asctime)s %(levelname)s %(message)s",
)
log = logging.getLogger("zk_relay")


def load_config():
    if not os.path.exists(CONFIG_PATH):
        log.error("Missing config.ini -- copy config.ini.example to config.ini and fill it in.")
        sys.exit(1)
    cfg = configparser.ConfigParser()
    cfg.read(CONFIG_PATH)
    return cfg


def acquire_lock(lock_path, max_age_minutes):
    if os.path.exists(lock_path):
        age = time.time() - os.path.getmtime(lock_path)
        if age < max_age_minutes * 60:
            log.warning(
                "Lock file present and recent (%.0fs old) -- a previous run may still be "
                "active. Skipping this run.", age,
            )
            return False
        log.warning("Stale lock file (%.0fs old) -- previous run likely crashed. Proceeding.", age)
    with open(lock_path, "w") as f:
        f.write(str(os.getpid()))
    return True


def release_lock(lock_path):
    try:
        os.remove(lock_path)
    except OSError:
        pass


def load_watermark(state_path):
    if not os.path.exists(state_path):
        return None
    with open(state_path) as f:
        data = json.load(f)
    ts = data.get("last_synced_at")
    return datetime.fromisoformat(ts) if ts else None


def save_watermark(state_path, when):
    with open(state_path, "w") as f:
        json.dump({"last_synced_at": when.isoformat()}, f)


def fetch_scans(cfg):
    ip = cfg.get("device", "ip")
    port = cfg.getint("device", "port", fallback=4370)
    password = cfg.getint("device", "password", fallback=0)
    timeout = cfg.getint("device", "timeout", fallback=5)

    zk = ZK(ip, port=port, timeout=timeout, password=password, force_udp=False, ommit_ping=False)
    conn = zk.connect()
    try:
        # Best practice per pyzk docs: pause the device's own clock/UI while
        # reading, so a scan mid-read can't corrupt the transfer.
        conn.disable_device()
        return conn.get_attendance()
    finally:
        conn.enable_device()
        conn.disconnect()


def post_batch(cfg, scans):
    base_url = cfg.get("server", "base_url").rstrip("/")
    school_slug = cfg.get("server", "school_slug")
    url = f"{base_url}/api/public/biometric-scans/{school_slug}"

    headers = {
        "X-API-Key": cfg.get("server", "api_key"),
        "X-Device-Key": cfg.get("server", "device_key"),
    }

    max_retries = cfg.getint("relay", "max_retries", fallback=3)
    backoff = cfg.getint("relay", "retry_backoff_seconds", fallback=5)

    for attempt in range(1, max_retries + 1):
        try:
            resp = requests.post(url, json={"scans": scans}, headers=headers, timeout=30)
            if resp.status_code == 200:
                return resp.json()
            log.error("Server returned %s: %s", resp.status_code, resp.text[:500])
        except requests.RequestException as exc:
            log.error("Request failed (attempt %d/%d): %s", attempt, max_retries, exc)

        if attempt < max_retries:
            time.sleep(backoff * attempt)

    return None


def chunked(items, size):
    for i in range(0, len(items), size):
        yield items[i:i + size]


def main():
    cfg = load_config()

    lock_path = os.path.join(SCRIPT_DIR, cfg.get("relay", "lock_file", fallback="relay.lock"))
    state_path = os.path.join(SCRIPT_DIR, cfg.get("relay", "state_file", fallback="state.json"))
    batch_size = cfg.getint("relay", "batch_size", fallback=500)
    lock_max_age = cfg.getint("relay", "lock_max_age_minutes", fallback=10)

    if not acquire_lock(lock_path, lock_max_age):
        sys.exit(0)

    try:
        watermark = load_watermark(state_path)
        log.info("Connecting to device, watermark=%s", watermark)

        try:
            records = fetch_scans(cfg)
        except Exception as exc:
            log.error("Could not connect to device: %s", exc)
            sys.exit(1)

        new_records = [r for r in records if watermark is None or r.timestamp > watermark]
        new_records.sort(key=lambda r: r.timestamp)

        if not new_records:
            log.info("No new scans since last run.")
            return

        log.info("%d new scan(s) to send.", len(new_records))

        scans_payload = [
            {"device_user_id": str(r.user_id), "scanned_at": r.timestamp.isoformat()}
            for r in new_records
        ]

        for batch in chunked(scans_payload, batch_size):
            result = post_batch(cfg, batch)
            if result is None:
                log.error("Batch failed after retries -- stopping here, will retry from this point next run.")
                break

            log.info(
                "Batch accepted=%s skipped=%s unmatched=%s",
                result.get("accepted"), result.get("skipped"), result.get("unmatched_device_ids"),
            )

            # Advance the watermark only past what the server actually
            # accepted, so a failed later batch doesn't lose earlier scans.
            batch_max_ts = max(datetime.fromisoformat(s["scanned_at"]) for s in batch)
            save_watermark(state_path, batch_max_ts)

    finally:
        release_lock(lock_path)


if __name__ == "__main__":
    main()
