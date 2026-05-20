<?php
// app/Console/Commands/MarkOverdueRepayments.php

namespace App\Console\Commands;

use App\Models\LoanRepayment;
use Illuminate\Console\Command;

class MarkOverdueRepayments extends Command
{
    protected $signature = 'loans:mark-overdue';
    protected $description = 'Mark overdue loan repayments as overdue';

    public function handle()
    {
        $count = LoanRepayment::where('status', 'pending')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);

        $this->info("Marked {$count} repayments as overdue.");
    }
}