# ZKTeco K40 → SchoolMS Attendance Relay

Connects a ZKTeco K40 fingerprint terminal to SchoolMS. SchoolMS is hosted
remotely and can't reach a device sitting on a school's local network, so
this script does the opposite: it runs on a PC **at the school**, reads scan
logs off the K40 locally, and pushes them out to SchoolMS over HTTPS
(outbound calls need no special network/firewall setup).

The K40 itself keeps up to ~80,000 scan records on its own storage, so
nothing is lost if this PC is turned off for a while — the next run just
picks up wherever it left off.

## What you need

- A PC on the same local network as the K40 (Windows or Linux — the office
  computer is fine to start with; see "Choosing a machine" below).
- Python 3.8 or newer.
- The K40's IP address (see "Finding the device's IP address").
- From SchoolMS, logged in as Admin: **Settings > Biometric Devices** — copy
  the **School Slug** and **Device Key** shown there. You'll also need the
  shared **Public API Key**, which the SchoolMS admin (Chani Technologies)
  can provide.
- Each staff member's fingerprint must already be **enrolled directly on the
  K40** (via its own menu), and their **Biometric ID** (the number the K40
  assigned) entered on their staff profile in SchoolMS (Staff > Edit >
  Biometric ID field).

## Setup

1. Install dependencies:
   ```bash
   pip install -r requirements.txt
   ```
2. Copy the config template and fill in your values:
   ```bash
   cp config.ini.example config.ini
   ```
   Edit `config.ini`:
   - `[device] ip` — the K40's IP address.
   - `[server] school_slug` — from Settings > Biometric Devices.
   - `[server] api_key` — the shared Public API Key.
   - `[server] device_key` — the Device Key from Settings > Biometric Devices.
3. Test it manually first:
   ```bash
   python relay.py
   ```
   Check `relay.log` in this folder for what happened. On the first run it
   sends every scan currently stored on the device; after that, only new
   scans are sent.
4. Once a manual run works, schedule it to repeat every few minutes (below).

## Finding the device's IP address

On the K40 itself: **Menu > Comm > Ethernet**. Give it a static IP (or a
DHCP reservation on the school's router) so it doesn't change later and
silently break the relay.

## Scheduling

Run `relay.py` every 5 minutes or so — it's a single-shot script, not a
long-running process, so let the OS scheduler handle repetition.

### Windows Task Scheduler

1. Open **Task Scheduler** > **Create Basic Task**.
2. Trigger: **Daily**, then edit it afterwards to repeat every 5 minutes
   (in the task's Properties > Triggers > Edit > "Repeat task every: 5
   minutes, for a duration of: Indefinitely").
3. Action: **Start a program**.
   - Program/script: full path to `python.exe` (e.g.
     `C:\Python311\python.exe`).
   - Add arguments: `relay.py`
   - Start in: the full path to this `zkteco-relay` folder.
4. Save. Run it once manually from Task Scheduler to confirm it works, then
   check `relay.log`.

### Linux / Raspberry Pi (cron)

```bash
crontab -e
```
Add:
```
*/5 * * * * cd /path/to/zkteco-relay && /usr/bin/python3 relay.py
```

(A `systemd` timer works too if you prefer it to cron — same idea, run
`relay.py` on an interval from this directory.)

## Choosing a machine

Start with whatever computer at the school is already usually switched on
during school hours (the front office PC, for example) — this costs
nothing and is enough to begin with, since the K40 holds its own history
and nothing is lost if that PC is occasionally off. If you later want
"set and forget" reliability that doesn't depend on someone remembering to
leave the office PC on, a small always-on Raspberry Pi (roughly $50–80) is
the natural upgrade — same script, same setup, just running on a dedicated
low-power device instead.

## Troubleshooting

- **Can't connect to the device**: check `relay.log`. Confirm the IP in
  `config.ini` matches the device (Menu > Comm > Ethernet on the K40), that
  this PC can reach it (`ping <device-ip>`), and that nothing blocks TCP
  port 4370 between them (a firewall, a different VLAN, etc.).
- **401/403 from the server**: the `api_key` or `device_key` in `config.ini`
  is wrong, or the Device Key was regenerated in Settings > Biometric
  Devices since this file was last updated — copy the new one in.
- **Staff show up as "Unmatched"**: their `biometric_id` isn't set on their
  SchoolMS staff profile yet, or doesn't match the K40's enrollment number.
  Fix it either on the Staff edit page, or from Settings > Biometric
  Devices > Unmatched Scans (which also backfills their past attendance
  once mapped).
- **Nothing in `relay.log` at all**: the scheduled task/cron job likely
  isn't running — check Task Scheduler's history, or run
  `crontab -l` and confirm the entry, or just re-run `python relay.py`
  manually from this folder to see errors directly in the terminal.

## Security notes

- `config.ini` holds real secrets (API key, device key) — it's already
  listed in `.gitignore` in this folder; never commit it.
- The Device Key is specific to this one school. If it's ever exposed,
  regenerate it from Settings > Biometric Devices — the old one stops
  working immediately, and you just need to update `config.ini` here with
  the new value.
