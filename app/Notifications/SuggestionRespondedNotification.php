<?php

namespace App\Notifications;

use App\Models\Suggestion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SuggestionRespondedNotification extends Notification
{
    use Queueable;

    public function __construct(public Suggestion $suggestion) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $isGuardian = $notifiable->hasRole('guardian');

        return [
            'type'    => 'suggestion_responded',
            'icon'    => 'fas fa-reply',
            'color'   => 'success',
            'title'   => 'Response to Your ' . ucfirst($this->suggestion->category),
            'message' => "The administration replied to \"{$this->suggestion->subject}\".",
            'url'     => route(($isGuardian ? 'guardian.' : '') . 'suggestions.index'),
        ];
    }
}
