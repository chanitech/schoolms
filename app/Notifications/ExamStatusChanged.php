<?php

namespace App\Notifications;

use App\Models\Exam;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ExamStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Exam $exam,
        public string $action,   // 'submitted_for_review' | 'published' | 'rejected' | 'unpublished'
        public string $actorName
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return match ($this->action) {
            'submitted_for_review' => [
                'type'    => 'exam_review',
                'icon'    => 'fas fa-search',
                'color'   => 'warning',
                'title'   => 'Exam Submitted for Review',
                'message' => "\"{$this->exam->name}\" was submitted for review by {$this->actorName}.",
                'url'     => route('exams.index'),
            ],
            'published' => [
                'type'    => 'exam_published',
                'icon'    => 'fas fa-globe',
                'color'   => 'success',
                'title'   => 'Results Published',
                'message' => "Results for \"{$this->exam->name}\" are now published and visible to parents.",
                'url'     => route('exams.index'),
            ],
            'rejected' => [
                'type'    => 'exam_rejected',
                'icon'    => 'fas fa-undo',
                'color'   => 'danger',
                'title'   => 'Review Rejected',
                'message' => "\"{$this->exam->name}\" was sent back to draft by {$this->actorName}.",
                'url'     => route('exams.index'),
            ],
            'unpublished' => [
                'type'    => 'exam_unpublished',
                'icon'    => 'fas fa-eye-slash',
                'color'   => 'secondary',
                'title'   => 'Results Unpublished',
                'message' => "Results for \"{$this->exam->name}\" have been unpublished by {$this->actorName}.",
                'url'     => route('exams.index'),
            ],
            default => [
                'type'    => 'exam_update',
                'icon'    => 'fas fa-file-alt',
                'color'   => 'info',
                'title'   => 'Exam Updated',
                'message' => "\"{$this->exam->name}\" status changed.",
                'url'     => route('exams.index'),
            ],
        };
    }
}
