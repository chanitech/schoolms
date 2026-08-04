<?php

namespace App\Notifications;

use App\Models\Suggestion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SuggestionSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public Suggestion $suggestion) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $from = $this->suggestion->is_anonymous
            ? 'Anonymous ' . $this->suggestion->submitter_role
            : ($this->suggestion->submitter?->name ?? $this->suggestion->submitter_role);

        return [
            'type'    => 'suggestion_submitted',
            'icon'    => 'fas fa-comment-dots',
            'color'   => 'info',
            'title'   => 'New ' . ucfirst($this->suggestion->category),
            'message' => "\"{$this->suggestion->subject}\" from {$from}.",
            'url'     => route('suggestions.manage'),
        ];
    }
}
