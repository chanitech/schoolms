<?php
// app/Notifications/LoanStatusNotification.php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LoanStatusNotification extends Notification
{
    use Queueable;

    protected $loan;
    protected $message;

    public function __construct(Loan $loan, $message)
    {
        $this->loan = $loan;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Loan Status Update')
            ->greeting('Hello ' . $this->loan->staff->name)
            ->line($this->message)
            ->line('Loan Amount: TZS ' . number_format($this->loan->amount_approved ?? $this->loan->amount_applied, 2))
            ->action('View Loan Details', url(route('staff.loans.show', $this->loan)))
            ->line('Thank you for using our loan management system.');
    }
}