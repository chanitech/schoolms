<?php

namespace App\Services;

use App\Models\SmsLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SmsService
{
    private const ENDPOINT = 'https://apisms.beem.africa/v1/send';

    /**
     * Send one message to many recipients via Beem Africa, logging every
     * recipient (sent/failed/invalid) for an audit trail. Beem's send
     * response is a single aggregate result for the whole batch, not
     * per-number — so every valid number is logged with the same outcome.
     *
     * @param  array  $recipients  [['phone' => '0712345678', 'name' => 'Jane Doe'], ...]
     * @return array{batch_id: string, sent: int, invalid: int, failed: int, error: ?string}
     */
    public function sendBulk(array $recipients, string $message, string $category): array
    {
        $batchId = (string) Str::uuid();
        $senderId = Auth::id();

        $valid = [];
        $invalidCount = 0;

        foreach ($recipients as $r) {
            $normalized = $this->normalizePhone($r['phone'] ?? '');
            if (! $normalized) {
                SmsLog::create([
                    'batch_id' => $batchId,
                    'category' => $category,
                    'recipient_name' => $r['name'] ?? null,
                    'recipient_phone' => $r['phone'] ?? '',
                    'message' => $message,
                    'status' => 'invalid',
                    'error_message' => 'Not a valid Tanzanian phone number',
                    'sent_by' => $senderId,
                ]);
                $invalidCount++;
                continue;
            }
            $valid[] = ['normalized' => $normalized, 'name' => $r['name'] ?? null, 'raw' => $r['phone']];
        }

        if (empty($valid)) {
            return ['batch_id' => $batchId, 'sent' => 0, 'invalid' => $invalidCount, 'failed' => 0, 'error' => null];
        }

        [$apiKey, $secretKey, $sourceAddr] = [
            config('services.beem.api_key'),
            config('services.beem.secret_key'),
            config('services.beem.sender_id'),
        ];

        if (! $apiKey || ! $secretKey) {
            foreach ($valid as $v) {
                $this->logResult($batchId, $category, $v, $message, 'failed', null, 'SMS gateway not configured (missing BEEM_API_KEY/BEEM_SECRET_KEY)', $senderId);
            }
            return ['batch_id' => $batchId, 'sent' => 0, 'invalid' => $invalidCount, 'failed' => count($valid), 'error' => 'SMS gateway not configured'];
        }

        try {
            $response = Http::withBasicAuth($apiKey, $secretKey)
                ->timeout(20)
                ->post(self::ENDPOINT, [
                    'source_addr' => $sourceAddr,
                    'schedule_time' => '',
                    'encoding' => 0,
                    'message' => $message,
                    'recipients' => collect($valid)->values()->map(fn($v, $i) => [
                        'recipient_id' => $i + 1,
                        'dest_addr' => $v['normalized'],
                    ])->all(),
                ]);

            $body = $response->json();
            $success = $response->successful() && (($body['successful'] ?? false) === true);
            $status = $success ? 'sent' : 'failed';
            $error = $success ? null : ($body['message'] ?? 'Beem API request failed (HTTP ' . $response->status() . ')');
            $requestId = $body['request_id'] ?? null;

            foreach ($valid as $v) {
                $this->logResult($batchId, $category, $v, $message, $status, $requestId, $error, $senderId);
            }

            return [
                'batch_id' => $batchId,
                'sent' => $success ? count($valid) : 0,
                'invalid' => $invalidCount,
                'failed' => $success ? 0 : count($valid),
                'error' => $error,
            ];
        } catch (\Throwable $e) {
            foreach ($valid as $v) {
                $this->logResult($batchId, $category, $v, $message, 'failed', null, $e->getMessage(), $senderId);
            }
            return ['batch_id' => $batchId, 'sent' => 0, 'invalid' => $invalidCount, 'failed' => count($valid), 'error' => $e->getMessage()];
        }
    }

    public function sendSingle(string $phone, string $message, string $category = 'custom', ?string $name = null): array
    {
        return $this->sendBulk([['phone' => $phone, 'name' => $name]], $message, $category);
    }

    private function logResult(string $batchId, string $category, array $v, string $message, string $status, ?string $requestId, ?string $error, ?int $senderId): void
    {
        SmsLog::create([
            'batch_id' => $batchId,
            'category' => $category,
            'recipient_name' => $v['name'],
            'recipient_phone' => $v['normalized'],
            'message' => $message,
            'status' => $status,
            'provider_request_id' => $requestId,
            'error_message' => $error,
            'sent_by' => $senderId,
        ]);
    }

    /**
     * Tanzanian numbers only, normalized to Beem's expected 255XXXXXXXXX
     * format. Returns null for anything unrecognizable — test/seed data in
     * this app includes garbage values ("3343", "76r7r7") that must never
     * reach the paid API.
     */
    public function normalizePhone(?string $raw): ?string
    {
        if (! $raw) return null;

        $digits = preg_replace('/\D+/', '', $raw);

        if (str_starts_with($digits, '255') && strlen($digits) === 12) {
            $number = $digits;
        } elseif (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $number = '255' . substr($digits, 1);
        } elseif (strlen($digits) === 9) {
            $number = '255' . $digits;
        } else {
            return null;
        }

        // Valid Tanzanian mobile prefixes after the 255 country code
        return preg_match('/^255(6|7)\d{8}$/', $number) ? $number : null;
    }
}
