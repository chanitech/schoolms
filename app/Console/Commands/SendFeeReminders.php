<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Services\ParentSms;
use Illuminate\Console\Command;

class SendFeeReminders extends Command
{
    protected $signature = 'sms:fee-reminders {--school= : Only this school id}';

    protected $description = 'Text parents about fee balances that are due soon or due today (schools with fee reminders switched on).';

    public function handle(ParentSms $parentSms): int
    {
        $schools = School::query()
            ->when($this->option('school'), fn ($q, $id) => $q->whereKey($id))
            ->whereIn('subscription_status', ['active', 'trial'])
            ->get()
            ->filter(fn (School $s) => ParentSms::settings($s)['fee_reminder']);

        foreach ($schools as $school) {
            // Commands have no request, so bind the tenant for the scoped queries and SMS logs.
            app()->instance('currentSchool', $school);
            try {
                $r = $parentSms->feeReminders($school);
                $this->info("{$school->name}: {$r['sent']} sent of {$r['checked']} bills due");
            } finally {
                app()->forgetInstance('currentSchool');
            }
        }

        if ($schools->isEmpty()) $this->info('No school has fee reminders switched on.');
        return self::SUCCESS;
    }
}
