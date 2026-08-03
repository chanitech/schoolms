<?php

namespace App\Console\Commands;

use App\Services\SmsService;
use Illuminate\Console\Command;

class SmsTest extends Command
{
    protected $signature = 'sms:test {phone : Tanzanian phone number, e.g. 0712345678} {message=Test message from ShulePRO}';

    protected $description = 'Send a single test SMS via Beem Africa to verify BEEM_API_KEY/BEEM_SECRET_KEY are working.';

    public function handle(SmsService $sms): int
    {
        $normalized = $sms->normalizePhone($this->argument('phone'));
        if (! $normalized) {
            $this->error('Not a valid Tanzanian phone number: ' . $this->argument('phone'));
            return self::FAILURE;
        }

        $this->info("Sending to {$normalized}...");
        $result = $sms->sendSingle($this->argument('phone'), $this->argument('message'), 'custom');

        if ($result['sent'] > 0) {
            $this->info('Sent successfully. Check sms_logs table or the phone for delivery.');
            return self::SUCCESS;
        }

        $this->error('Failed: ' . ($result['error'] ?? 'unknown error'));
        return self::FAILURE;
    }
}
