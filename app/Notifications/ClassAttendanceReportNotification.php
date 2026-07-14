<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClassAttendanceReportNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $classLabel,
        public string $periodLabel,
        public string $summary,      // e.g. "42 attended · 3 late · 1 absent"
        public string $senderName,
        public string $url
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'class_attendance_report',
            'icon'    => 'fas fa-clipboard-check',
            'color'   => 'info',
            'title'   => 'Class Attendance Report',
            'message' => "{$this->classLabel} — {$this->periodLabel}: {$this->summary}. Sent by {$this->senderName}.",
            'url'     => $this->url,
        ];
    }
}
