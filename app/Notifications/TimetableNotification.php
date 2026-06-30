<?php

namespace App\Notifications;

use App\Models\Timetable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TimetableNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Timetable $timetable,
        public string    $action,     // 'submitted' | 'approved' | 'rejected' | 'published' | 'unpublished'
        public string    $actorName
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $name = $this->timetable->name ?? ('Timetable #' . $this->timetable->id);

        return match ($this->action) {
            'submitted' => [
                'type'    => 'timetable_review',
                'icon'    => 'fas fa-calendar-check',
                'color'   => 'info',
                'title'   => 'Timetable Needs Review',
                'message' => "\"{$name}\" was submitted for review by {$this->actorName}.",
                'url'     => route('timetables.show', $this->timetable->id),
            ],
            'approved' => [
                'type'    => 'timetable_approved',
                'icon'    => 'fas fa-check-circle',
                'color'   => 'success',
                'title'   => 'Timetable Approved',
                'message' => "\"{$name}\" was approved by {$this->actorName}.",
                'url'     => route('timetables.show', $this->timetable->id),
            ],
            'rejected' => [
                'type'    => 'timetable_rejected',
                'icon'    => 'fas fa-times-circle',
                'color'   => 'danger',
                'title'   => 'Timetable Rejected',
                'message' => "\"{$name}\" was rejected by {$this->actorName}. Please fix and resubmit.",
                'url'     => route('timetables.show', $this->timetable->id),
            ],
            'published' => [
                'type'    => 'timetable_published',
                'icon'    => 'fas fa-calendar-week',
                'color'   => 'success',
                'title'   => 'Timetable Published',
                'message' => "\"{$name}\" is now live. Check your schedule.",
                'url'     => route('timetables.my-sessions'),
            ],
            'unpublished' => [
                'type'    => 'timetable_unpublished',
                'icon'    => 'fas fa-calendar-times',
                'color'   => 'warning',
                'title'   => 'Timetable Withdrawn',
                'message' => "\"{$name}\" has been unpublished by {$this->actorName}.",
                'url'     => route('timetables.index'),
            ],
            default => [
                'type'    => 'timetable_update',
                'icon'    => 'fas fa-calendar-alt',
                'color'   => 'secondary',
                'title'   => 'Timetable Updated',
                'message' => "\"{$name}\" was updated.",
                'url'     => route('timetables.index'),
            ],
        };
    }
}
