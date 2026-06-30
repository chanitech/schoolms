<?php

namespace App\Notifications;

use App\Models\Leave;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRequestNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Leave $leave,
        public string $action,   // 'submitted' | 'approved' | 'rejected'
        public string $actorName
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $staffName = $this->leave->staff->full_name ?? $this->leave->staff->name ?? 'A staff member';
        $leaveType = $this->leave->leave_type ?? 'Leave';

        return match ($this->action) {
            'submitted' => [
                'type'    => 'leave_request',
                'icon'    => 'fas fa-file-signature',
                'color'   => 'info',
                'title'   => 'New Leave Request',
                'message' => "{$staffName} submitted a {$leaveType} request.",
                'url'     => route('leaves.received'),
            ],
            'approved' => [
                'type'    => 'leave_approved',
                'icon'    => 'fas fa-check-circle',
                'color'   => 'success',
                'title'   => 'Leave Approved',
                'message' => "Your {$leaveType} request has been approved by {$this->actorName}.",
                'url'     => route('leaves.index'),
            ],
            'rejected' => [
                'type'    => 'leave_rejected',
                'icon'    => 'fas fa-times-circle',
                'color'   => 'danger',
                'title'   => 'Leave Rejected',
                'message' => "Your {$leaveType} request was rejected by {$this->actorName}.",
                'url'     => route('leaves.index'),
            ],
            default => [
                'type'    => 'leave_update',
                'icon'    => 'fas fa-plane-departure',
                'color'   => 'secondary',
                'title'   => 'Leave Update',
                'message' => "Your leave request status has changed.",
                'url'     => route('leaves.index'),
            ],
        };
    }
}
