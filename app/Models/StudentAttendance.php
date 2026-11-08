<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class StudentAttendance extends Model
{
    use BelongsToSchool;

    public const STATUSES = ['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'excused' => 'Excused'];

    protected $fillable = ['school_id', 'student_id', 'class_id', 'academic_session_id', 'date', 'status', 'recorded_by'];

    protected $casts = ['date' => 'date'];

    /**
     * Attendance per student. Late still counts as attending; excused days
     * don't count against the student, so they leave the total.
     */
    public static function summaries(Collection $studentIds, int|string $sessionId, $upTo): Collection
    {
        return static::whereIn('student_id', $studentIds)
            ->where('academic_session_id', $sessionId)
            ->whereDate('date', '<=', $upTo)
            ->selectRaw('student_id, status, count(*) as total')
            ->groupBy('student_id', 'status')
            ->get()
            ->groupBy('student_id')
            ->map(function ($rows) {
                $count    = $rows->pluck('total', 'status');
                $late     = (int) $count->get('late', 0);
                $attended = (int) $count->get('present', 0) + $late;
                $absent   = (int) $count->get('absent', 0);
                $days     = $attended + $absent;

                return [
                    'rate'     => $days ? round($attended / $days * 100, 1) : 0,
                    'attended' => $attended,
                    'days'     => $days,
                    'absent'   => $absent,
                    'late'     => $late,
                    'excused'  => (int) $count->get('excused', 0),
                ];
            });
    }
}
