<?php

namespace App\Services;

use App\Models\Timetable;
use App\Models\TimetablePeriod;
use Illuminate\Support\Facades\DB;

class TimetableGeneratorService
{
    private array $warnings = [];

    private function currentSchoolId(): ?int
    {
        return app()->bound('currentSchool') ? (int) app('currentSchool')->id : null;
    }

    // ── Class Timetable ───────────────────────────────────────────────────

    public function generateClassTimetable(Timetable $timetable): array
    {
        $this->warnings = [];
        $settings        = $timetable->settings ?? [];
        $classIds        = $timetable->class_ids;
        $days            = $settings['days'] ?? [1, 2, 3, 4, 5];
        $periodsPerWeek  = $settings['periods_per_week'] ?? [];
        $defaultPpw      = (int)($settings['default_periods_per_week'] ?? 5);
        $sessionDuration = (int)($settings['session_duration'] ?? 40);
        $schoolStart     = $settings['school_start_time'] ?? '07:30';

        // Load ALL periods (incl. breaks) sorted for time computation
        $allPeriods = TimetablePeriod::where('is_active', true)->orderBy('order_no')->get();
        if ($allPeriods->isEmpty()) {
            return ['entries' => [], 'warnings' => ['No active time periods found. Please configure timetable periods.']];
        }

        // Special sessions that apply every configured teaching day — used to push period start times.
        $specialSessions = $settings['special_sessions'] ?? [];
        $allDaySessions  = array_values(array_filter($specialSessions, function ($ss) use ($days) {
            $ssDays = array_map('intval', $ss['days'] ?? [1, 2, 3, 4, 5]);
            return empty(array_diff($days, $ssDays));
        }));

        // Compute period start/end times — manual overrides auto
        if (($settings['timing_mode'] ?? 'auto') === 'manual' && !empty($settings['period_times'])) {
            $periodTimings = [];
            foreach ($allPeriods as $p) {
                $mt = $settings['period_times'][$p->id] ?? null;
                $periodTimings[$p->id] = $mt
                    ? ['start' => $mt['start'], 'end' => $mt['end']]
                    : ['start' => '00:00',       'end' => '00:00'];
            }
        } else {
            $periodTimings = $this->computePeriodTimings($schoolStart, $sessionDuration, $allPeriods, $allDaySessions);
        }

        // Teaching period IDs in order (no breaks)
        $teachingPeriods = $allPeriods->where('is_break', false)->pluck('id')->toArray();

        // Build consecutive pairs for double-session placement
        $consecutivePairs = [];
        for ($i = 0; $i < count($teachingPeriods) - 1; $i++) {
            $consecutivePairs[] = [$teachingPeriods[$i], $teachingPeriods[$i + 1]];
        }

        // Keys of subjects that must be placed as back-to-back consecutive pairs only
        $doubleKeys = $settings['double_periods'] ?? [];

        // Load subject assignments. subject_class.teacher_id is a foreign
        // key to staff.id, but timetable_entries.teacher_id (where this
        // ultimately gets written) is a foreign key to users.id — join
        // through staff to translate it, or every insert with a non-null
        // teacher fails its FK constraint.
        $assignments = DB::table('subject_class')
            ->join('subjects', 'subjects.id', '=', 'subject_class.subject_id')
            ->leftJoin('staff', 'staff.id', '=', 'subject_class.teacher_id')
            ->whereIn('subject_class.class_id', $classIds)
            ->select('subject_class.class_id', 'subject_class.subject_id',
                     'staff.user_id as teacher_id', 'subjects.name as subject_name')
            ->get();

        // Sort hardest-to-place first (most periods needed)
        $assignments = $assignments->sortByDesc(function ($a) use ($periodsPerWeek, $defaultPpw) {
            return (int)($periodsPerWeek[$a->class_id . '_' . $a->subject_id] ?? $defaultPpw);
        })->values();

        $available = count($teachingPeriods) * count($days);

        // ── PRE-CHECK: capacity and teacher load ──────────────────────────
        $this->checkCapacity($assignments, $periodsPerWeek, $defaultPpw, $available, $days, $teachingPeriods);

        $classSlots   = [];
        $teacherSlots = [];
        $entries      = [];
        $missed       = [];
        $comboPlaced  = [];

        // ── SEED teacher slots from other non-draft timetables of the same session ──
        // Prevents this timetable from double-booking a teacher already committed elsewhere.
        // academic_session_id is just an auto-increment PK — without the school_id
        // filter, two schools sharing an id would seed teacher slots from each
        // other's timetables entirely.
        $crossEntries = DB::table('timetable_entries')
            ->join('timetables', 'timetables.id', '=', 'timetable_entries.timetable_id')
            ->where('timetables.academic_session_id', $timetable->academic_session_id)
            ->where('timetables.type', 'class')
            ->where('timetables.id', '!=', $timetable->id)
            ->when($this->currentSchoolId(), fn ($q, $id) => $q->where('timetables.school_id', $id))
            ->whereIn('timetables.status', ['published', 'pending_review', 'approved'])
            ->whereNotNull('timetable_entries.teacher_id')
            ->whereNotNull('timetable_entries.day_of_week')
            ->whereNotNull('timetable_entries.period_id')
            ->select('timetable_entries.teacher_id',
                     'timetable_entries.day_of_week',
                     'timetable_entries.period_id')
            ->get();
        foreach ($crossEntries as $ce) {
            $teacherSlots[$ce->teacher_id][$ce->day_of_week][$ce->period_id] = true;
        }

        // ── PRE-PLACEMENT: combination/optional subject groups ─────────────
        // Subjects in a combo group share N "simultaneous" slots (students pick one).
        // They are placed together FIRST; solo periods (needed - shared) follow in the main loop.
        $combinations = $settings['combinations'] ?? [];
        foreach ($combinations as $combo) {
            $comboSubjectIds = $combo['subjects'] ?? [];
            $sharedCount     = max(1, (int)($combo['shared'] ?? 2));
            // Class restriction: if combo specifies class_ids, only apply to those classes
            $comboClassLimit = !empty($combo['class_ids'])
                ? array_map('intval', $combo['class_ids'])
                : null;
            if (count($comboSubjectIds) < 2) continue;

            // Process per class
            foreach ($classIds as $classId) {
                // Skip if this combo is restricted to specific classes and this isn't one of them
                if ($comboClassLimit !== null && !in_array((int)$classId, $comboClassLimit)) continue;

                // Find which combo subjects are actually assigned to this class
                $comboAsgns = $assignments->filter(
                    fn($a) => $a->class_id == $classId && in_array($a->subject_id, $comboSubjectIds)
                )->values();

                if ($comboAsgns->count() < 2) continue;

                $placed = 0;

                // Helper: claim one combo slot (all combo subjects + all their teachers)
                $claimComboSlot = function ($day, $pId) use (
                    $classId, $comboAsgns, &$classSlots, &$teacherSlots, &$entries, &$comboPlaced,
                    $timetable, $periodTimings
                ) {
                    $classSlots[$classId][$day][$pId] = true;
                    foreach ($comboAsgns as $ca) {
                        if ($ca->teacher_id) $teacherSlots[$ca->teacher_id][$day][$pId] = true;
                        $entries[] = $this->buildClassEntry($timetable, $ca, $day, $pId, $periodTimings);
                        $comboPlaced[$classId . '_' . $ca->subject_id] =
                            ($comboPlaced[$classId . '_' . $ca->subject_id] ?? 0) + 1;
                    }
                };

                // Helper: is a single slot free for the class AND all combo teachers?
                $comboSlotFree = function ($day, $pId) use ($classId, $comboAsgns, &$classSlots, &$teacherSlots) {
                    if (!empty($classSlots[$classId][$day][$pId])) return false;
                    foreach ($comboAsgns as $ca) {
                        if ($ca->teacher_id && !empty($teacherSlots[$ca->teacher_id][$day][$pId])) return false;
                    }
                    return true;
                };

                // ── Phase 1: period-first consecutive pairs ───────────────────
                // Try P1-P2 across all days first, then P2-P3, etc.
                $doublesNeeded = (int)floor($sharedCount / 2);
                if ($doublesNeeded > 0 && !empty($consecutivePairs)) {
                    $doublesPlaced = 0;
                    foreach ($consecutivePairs as [$p1, $p2]) {
                        if ($doublesPlaced >= $doublesNeeded) break;
                        $dayOrder = $days; shuffle($dayOrder);
                        foreach ($dayOrder as $day) {
                            if (!$comboSlotFree($day, $p1) || !$comboSlotFree($day, $p2)) continue;
                            $claimComboSlot($day, $p1);
                            $claimComboSlot($day, $p2);
                            $placed        += 2;
                            $doublesPlaced++;
                            break;
                        }
                    }
                }

                // ── Phase 2: period-first singles for remainder ───────────────
                if ($placed < $sharedCount) {
                    $allSlots = [];
                    foreach ($teachingPeriods as $pId) {
                        $dayOrder = $days; shuffle($dayOrder);
                        foreach ($dayOrder as $day) {
                            $allSlots[] = [$day, $pId];
                        }
                    }
                    foreach ($allSlots as [$day, $pId]) {
                        if ($placed >= $sharedCount) break;
                        if (!$comboSlotFree($day, $pId)) continue;
                        $claimComboSlot($day, $pId);
                        $placed++;
                    }
                }
            }
        }

        // ── FAIR-SHARE CAP: prevents high-PPW subjects from starving low-PPW ones ──
        // floor(slots / subjects) = minimum guaranteed periods per subject per class.
        $classFairCap = [];
        foreach ($classIds as $classId) {
            $subjectCount = $assignments->filter(function ($a) use ($classId, $periodsPerWeek, $defaultPpw) {
                if ($a->class_id != $classId) return false;
                $key = $a->class_id . '_' . $a->subject_id;
                return (array_key_exists($key, $periodsPerWeek) ? (int)$periodsPerWeek[$key] : $defaultPpw) > 0;
            })->count();
            $classFairCap[$classId] = $subjectCount > 0 ? max(1, (int)floor($available / $subjectCount)) : PHP_INT_MAX;
        }

        $mainPlaced = []; // total periods placed by the main loop, per key

        // Three-pass round-robin prevents any subject from being starved:
        //   Pass 1 – guaranteed 1 slot per subject (class is least-full, teacher conflicts minimal)
        //   Pass 2 – up to fairShareCap per subject (cumulative)
        //   Pass 3 – full requested quota (cumulative, extras distributed to higher-priority subjects)
        for ($loopPass = 1; $loopPass <= 3; $loopPass++) {
            foreach ($assignments as $asgn) {
                $key      = $asgn->class_id . '_' . $asgn->subject_id;
                $fullNeed = array_key_exists($key, $periodsPerWeek)
                    ? (int)$periodsPerWeek[$key]
                    : $defaultPpw;
                if ($fullNeed === 0) continue;
                $fullNeed = max(0, $fullNeed - ($comboPlaced[$key] ?? 0));

                $alreadyPlaced = $mainPlaced[$key] ?? 0;

                $cap = match ($loopPass) {
                    1       => 1,                                      // round 1: 1 slot each
                    2       => $classFairCap[$asgn->class_id],         // round 2: up to fairCap
                    default => PHP_INT_MAX,                             // round 3: full quota
                };
                $needed = min($fullNeed, $cap) - $alreadyPlaced;

                if ($needed <= 0) continue;
                $placed = 0;

                // Is this subject marked as "must be back-to-back doubles only"?
                $mustDouble = in_array($key, $doubleKeys);

                // Double subjects need ≥2 remaining slots to place a pair.
                // Skip early passes (cap=1) so we don't accidentally place a lone single.
                // They will be picked up in pass 3 when the full quota is available at once.
                if ($mustDouble && $needed < 2) continue;

                // ── PHASE 1: Period-first double placement ────────────────────
                // Try P1-P2 across all days first, then P2-P3, etc.
                // Guarantees early periods fill before later ones.
                $doublesNeeded = (int)floor($needed / 2);

                if ($doublesNeeded > 0 && !empty($consecutivePairs)) {
                    $doublesPlaced = 0;

                    foreach ($consecutivePairs as [$p1, $p2]) {
                        if ($doublesPlaced >= $doublesNeeded) break;
                        $dayOrder = $days;
                        shuffle($dayOrder);

                        foreach ($dayOrder as $day) {
                            if (!empty($classSlots[$asgn->class_id][$day][$p1])) continue;
                            if (!empty($classSlots[$asgn->class_id][$day][$p2])) continue;
                            if ($asgn->teacher_id && !empty($teacherSlots[$asgn->teacher_id][$day][$p1])) continue;
                            if ($asgn->teacher_id && !empty($teacherSlots[$asgn->teacher_id][$day][$p2])) continue;

                            foreach ([$p1, $p2] as $pId) {
                                $classSlots[$asgn->class_id][$day][$pId] = true;
                                if ($asgn->teacher_id) {
                                    $teacherSlots[$asgn->teacher_id][$day][$pId] = true;
                                }
                                $entries[] = $this->buildClassEntry($timetable, $asgn, $day, $pId, $periodTimings);
                                $placed++;
                            }
                            $doublesPlaced++;
                            break;
                        }
                    }
                }

                // ── PHASE 2: Singles ─────────────────────────────────────────────
                // Double subjects: allow at most 1 single for an odd-count remainder
                //   (e.g. 3 periods → 1 pair + 1 single; 2 or 4 → pairs only).
                // Normal subjects: fill all remaining needed slots as singles.
                $singlesQuota = $mustDouble ? ($needed % 2) : ($needed - $placed);

                if ($singlesQuota > 0 && $placed < $needed) {
                    // Period-first: P1 across all days before P2, etc.
                    $allSlots = [];
                    foreach ($teachingPeriods as $periodId) {
                        $dayOrder = $days;
                        shuffle($dayOrder);
                        foreach ($dayOrder as $day) {
                            $allSlots[] = [$day, $periodId];
                        }
                    }

                    $singlesPlaced = 0;
                    foreach ($allSlots as [$day, $periodId]) {
                        if ($placed >= $needed || $singlesPlaced >= $singlesQuota) break;
                        if (!empty($classSlots[$asgn->class_id][$day][$periodId])) continue;
                        if ($asgn->teacher_id && !empty($teacherSlots[$asgn->teacher_id][$day][$periodId])) continue;

                        $classSlots[$asgn->class_id][$day][$periodId] = true;
                        if ($asgn->teacher_id) {
                            $teacherSlots[$asgn->teacher_id][$day][$periodId] = true;
                        }
                        $entries[] = $this->buildClassEntry($timetable, $asgn, $day, $periodId, $periodTimings);
                        $placed++;
                        $singlesPlaced++;
                    }
                }

                $mainPlaced[$key] = $alreadyPlaced + $placed;

                // Track missed only after pass 3 (final picture)
                if ($loopPass === 3 && $mainPlaced[$key] < $fullNeed) {
                    $name = $asgn->subject_name;
                    if (!isset($missed[$name])) {
                        $missed[$name] = ['placed' => 0, 'needed' => 0, 'classes' => 0, 'is_double' => false];
                    }
                    $missed[$name]['placed']    += $mainPlaced[$key];
                    $missed[$name]['needed']    += $fullNeed;
                    $missed[$name]['classes']   += 1;
                    if ($mustDouble) $missed[$name]['is_double'] = true;
                }
            }
        }

        // Aggregate missed warnings — subjects that could not reach their exact quota.
        // Empty slots are intentionally left blank; NO subject is placed beyond its quota.
        foreach ($missed as $subj => $m) {
            $hint = $m['is_double']
                ? ' (marked Double — no free consecutive pair found; add more teaching periods or uncheck Double)'
                : ' — teacher conflicts or insufficient free slots';
            $this->warnings[] = "⚠ {$subj}: only {$m['placed']}/{$m['needed']} periods placed across {$m['classes']} class(es){$hint}.";
        }

        return ['entries' => $entries, 'warnings' => $this->warnings];
    }

    // ── Public: full capacity analysis for the show view ─────────────────

    public function getCapacityAnalysis(Timetable $timetable): array
    {
        if ($timetable->type !== 'class') return [];

        $settings       = $timetable->settings ?? [];
        $days           = $settings['days'] ?? [1, 2, 3, 4, 5];
        $periodsPerWeek = $settings['periods_per_week'] ?? [];
        $defaultPpw     = (int)($settings['default_periods_per_week'] ?? 5);
        $classIds       = $timetable->class_ids;

        $allPeriods      = TimetablePeriod::where('is_active', true)->orderBy('order_no')->get();
        $teachingCount   = $allPeriods->where('is_break', false)->count();
        $available       = $teachingCount * count($days);
        $teacherSlots    = $teachingCount * count($days);

        // Load assignments with subject names and class names
        $assignments = DB::table('subject_class')
            ->join('subjects',      'subjects.id',      '=', 'subject_class.subject_id')
            ->join('school_classes','school_classes.id', '=', 'subject_class.class_id')
            ->whereIn('subject_class.class_id', $classIds)
            ->select(
                'subject_class.class_id',
                'school_classes.name as class_name',
                'subject_class.subject_id',
                'subjects.name as subject_name',
                'subject_class.teacher_id'
            )
            ->get();

        // Load teacher names — subject_class.teacher_id is a foreign key to
        // staff.id, not users.id.
        $teacherIds = $assignments->pluck('teacher_id')->filter()->unique()->toArray();
        $teachers   = DB::table('staff')->whereIn('id', $teacherIds)
            ->select('id', 'first_name', 'last_name')
            ->get()->keyBy('id');

        // Build per-class and per-teacher metrics
        $classData    = [];
        $teacherLoad  = [];

        foreach ($assignments as $asgn) {
            $key    = $asgn->class_id . '_' . $asgn->subject_id;
            $needed = array_key_exists($key, $periodsPerWeek)
                ? (int)$periodsPerWeek[$key]
                : $defaultPpw;
            if ($needed === 0) continue; // explicitly excluded from scheduling

            if (!isset($classData[$asgn->class_id])) {
                $classData[$asgn->class_id] = [
                    'class_id'   => $asgn->class_id,
                    'class_name' => $asgn->class_name,
                    'needed'     => 0,
                    'subjects'   => [],
                ];
            }
            $classData[$asgn->class_id]['needed'] += $needed;
            $classData[$asgn->class_id]['subjects'][] = [
                'name'    => $asgn->subject_name,
                'periods' => $needed,
            ];

            if ($asgn->teacher_id) {
                if (!isset($teacherLoad[$asgn->teacher_id])) {
                    $t = $teachers[$asgn->teacher_id] ?? null;
                    $teacherLoad[$asgn->teacher_id] = [
                        'teacher_id'   => $asgn->teacher_id,
                        'teacher_name' => $t ? trim($t->first_name . ' ' . $t->last_name) : "Teacher #{$asgn->teacher_id}",
                        'load'         => 0,
                        'classes'      => 0,
                    ];
                }
                $teacherLoad[$asgn->teacher_id]['load']    += $needed;
                $teacherLoad[$asgn->teacher_id]['classes'] += 1;
            }
        }

        // Annotate each class with capacity metrics and suggestions
        $overCapacity = [];
        foreach ($classData as $clsId => &$cls) {
            $numSubj = count($cls['subjects']);
            $deficit = $cls['needed'] - $available;
            $pct     = $available > 0 ? (int)round($cls['needed'] / $available * 100) : 0;

            if ($deficit <= 0) continue; // class is fine

            // Sort subjects desc to suggest which to reduce
            usort($cls['subjects'], fn($a, $b) => $b['periods'] - $a['periods']);
            $topSubjects = array_slice($cls['subjects'], 0, 5);

            // How to fill the deficit: which subjects to cut and by how much
            $cuts          = [];
            $tempDeficit   = $deficit;
            foreach ($cls['subjects'] as $s) {
                if ($tempDeficit <= 0) break;
                if ($s['periods'] > 1) {
                    $cut = min($s['periods'] - 1, $tempDeficit);
                    $cuts[] = "{$s['name']}: {$s['periods']}→" . ($s['periods'] - $cut);
                    $tempDeficit -= $cut;
                }
            }

            $extraPeriodsPerDay = (int)ceil($deficit / count($days));
            $uniformMax         = $numSubj > 0 ? (int)floor($available / $numSubj) : 0;

            $overCapacity[$clsId] = [
                'class_id'           => $clsId,
                'class_name'         => $cls['class_name'],
                'needed'             => $cls['needed'],
                'available'          => $available,
                'deficit'            => $deficit,
                'pct'                => $pct,
                'num_subjects'       => $numSubj,
                'uniform_max'        => $uniformMax,
                'extra_periods_day'  => $extraPeriodsPerDay,
                'top_subjects'       => $topSubjects,
                'suggested_cuts'     => $cuts,
            ];
        }
        unset($cls);

        // $teacherSlots is 0 when no active, non-break TimetablePeriod rows
        // exist yet — nothing to compare load against, so there's no
        // meaningful "overload" and dividing by it would crash.
        $teacherOverload = $teacherSlots > 0
            ? array_filter($teacherLoad, fn($t) => $t['load'] > $teacherSlots)
            : [];
        foreach ($teacherOverload as &$t) {
            $t['available'] = $teacherSlots;
            $t['overload']  = $t['load'] - $teacherSlots;
            $t['pct']       = (int)round($t['load'] / $teacherSlots * 100);
        }
        unset($t);

        return [
            'available'       => $available,
            'teaching_slots'  => $teachingCount,
            'days'            => count($days),
            'over_capacity'   => array_values($overCapacity),
            'teacher_overload'=> array_values($teacherOverload),
        ];
    }

    // ── Pre-generation capacity check (used during generation) ───────────

    private function checkCapacity(
        $assignments, array $periodsPerWeek, int $defaultPpw,
        int $available, array $days, array $teachingPeriods
    ): void {
        $classNeeded   = [];
        $classSubjects = [];
        $teacherLoad   = [];

        foreach ($assignments as $asgn) {
            $key    = $asgn->class_id . '_' . $asgn->subject_id;
            $needed = array_key_exists($key, $periodsPerWeek)
                ? (int)$periodsPerWeek[$key]
                : $defaultPpw;
            if ($needed === 0) continue; // explicitly excluded
            $classNeeded[$asgn->class_id]   = ($classNeeded[$asgn->class_id]   ?? 0) + $needed;
            $classSubjects[$asgn->class_id] = ($classSubjects[$asgn->class_id] ?? 0) + 1;
            if ($asgn->teacher_id) {
                $teacherLoad[$asgn->teacher_id] = ($teacherLoad[$asgn->teacher_id] ?? 0) + $needed;
            }
        }

        $overCount = 0;
        foreach ($classNeeded as $clsId => $total) {
            if ($total > $available) $overCount++;
        }

        if ($overCount > 0) {
            $numDays    = count($days);
            $first      = null;
            foreach ($classNeeded as $clsId => $total) {
                if ($total > $available && $first === null) {
                    $numSubj   = $classSubjects[$clsId] ?? 1;
                    $deficit   = $total - $available;
                    $uniformMax = (int)floor($available / $numSubj);
                    $extraDay   = (int)ceil($deficit / $numDays);
                    $first = "e.g. Class #{$clsId} needs {$total}/{$available} slots — reduce default to ≤{$uniformMax}/week OR add {$extraDay} teaching period(s)/day in Period Settings.";
                }
            }
            $this->warnings[] = "⚠ OVER-CAPACITY: {$overCount} class(es) cannot fit all subjects. {$first} See the Capacity Analysis panel on this page for full details.";
        }

        // Teacher over-load
        $teacherAvailable = count($teachingPeriods) * count($days);
        $overTeachers = 0;
        foreach ($teacherLoad as $load) {
            if ($load > $teacherAvailable) $overTeachers++;
        }
        if ($overTeachers > 0) {
            $this->warnings[] = "⚠ TEACHER OVERLOAD: {$overTeachers} teacher(s) assigned more periods than available. Reassign some subjects to other teachers.";
        }
    }

    // Compute actual start/end times for every period from school start + session duration + break durations.
    // $allDaySessions: special sessions that apply every teaching day — used to push period starts past them.
    private function computePeriodTimings(string $schoolStart, int $sessionMin, $allPeriods, array $allDaySessions = []): array
    {
        [$h, $m] = explode(':', $schoolStart . ':00');
        $cur     = (int)$h * 60 + (int)$m;
        $timings = [];

        foreach ($allPeriods as $period) {
            // Only push TEACHING periods past all-day special sessions.
            // Break periods must not be pushed — their stored duration defines the gap itself.
            if (!$period->is_break) {
                $cur = $this->pushPastSessions($cur, $allDaySessions);
            }

            if ($period->is_break) {
                [$bsh, $bsm] = explode(':', substr($period->start_time, 0, 5));
                [$beh, $bem] = explode(':', substr($period->end_time,   0, 5));
                $dur = ((int)$beh * 60 + (int)$bem) - ((int)$bsh * 60 + (int)$bsm);
                $dur = max($dur, 5);
            } else {
                $dur = $sessionMin;
            }

            $timings[$period->id] = [
                'start' => sprintf('%02d:%02d', intdiv($cur, 60), $cur % 60),
                'end'   => sprintf('%02d:%02d', intdiv($cur + $dur, 60), ($cur + $dur) % 60),
            ];
            $cur += $dur;
        }

        return $timings;
    }

    // Push $cur past any special session whose time range covers it (repeat until stable).
    private function pushPastSessions(int $cur, array $sessions): int
    {
        $changed = true;
        while ($changed) {
            $changed = false;
            foreach ($sessions as $ss) {
                [$sh, $sm] = explode(':', substr($ss['start_time'], 0, 5));
                [$eh, $em] = explode(':', substr($ss['end_time'],   0, 5));
                $ssStart = (int)$sh * 60 + (int)$sm;
                $ssEnd   = (int)$eh * 60 + (int)$em;
                if ($ssStart <= $cur && $ssEnd > $cur) {
                    $cur     = $ssEnd;
                    $changed = true;
                }
            }
        }
        return $cur;
    }

    private function buildClassEntry(Timetable $timetable, object $asgn, int $day, int $periodId, array $timings): array
    {
        return [
            // saveEntries() inserts via raw DB::table(), which skips the
            // BelongsToSchool auto-fill — without this, every generated
            // entry gets school_id = NULL and becomes invisible under the
            // scoped queries used everywhere else (TimetableEntry::with(...)).
            'school_id'    => $timetable->school_id,
            'timetable_id' => $timetable->id,
            'class_id'     => $asgn->class_id,
            'subject_id'   => $asgn->subject_id,
            'teacher_id'   => $asgn->teacher_id,
            'day_of_week'  => $day,
            'period_id'    => $periodId,
            'start_time'   => $timings[$periodId]['start'] ?? null,
            'end_time'     => $timings[$periodId]['end']   ?? null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ];
    }

    // ── Exam Timetable ────────────────────────────────────────────────────

    public function generateExamTimetable(Timetable $timetable): array
    {
        $this->warnings = [];
        $settings          = $timetable->settings ?? [];
        $classIds          = $timetable->class_ids;
        $dates             = $settings['exam_dates'] ?? [];
        $slots             = $settings['exam_slots'] ?? [
            ['start' => '08:00', 'end' => '10:30', 'label' => 'Morning'],
            ['start' => '14:00', 'end' => '16:30', 'label' => 'Afternoon'],
        ];
        $invigilatorsPerSlot = (int)($settings['invigilators_per_slot'] ?? 2);

        if (empty($dates)) {
            return ['entries' => [], 'warnings' => ['No exam dates configured.']];
        }

        // Load all subjects for the selected classes. subject_class.teacher_id
        // is a foreign key to staff.id, but timetable_entries.teacher_id is a
        // foreign key to users.id — translate via staff, same as the class
        // timetable generator above.
        $subjectsByClass = [];
        $rows = DB::table('subject_class')
            ->join('subjects', 'subjects.id', '=', 'subject_class.subject_id')
            ->leftJoin('staff', 'staff.id', '=', 'subject_class.teacher_id')
            ->whereIn('class_id', $classIds)
            ->select('subject_class.class_id', 'subject_class.subject_id',
                     'staff.user_id as teacher_id', 'subjects.name as subject_name')
            ->get();

        foreach ($rows as $row) {
            $subjectsByClass[$row->class_id][] = [
                'subject_id'   => $row->subject_id,
                'teacher_id'   => $row->teacher_id,
                'subject_name' => $row->subject_name,
            ];
        }

        // Load all staff available to invigilate (Teachers + HODs)
        // Without the school_id filter this pulled teachers from EVERY school
        // as invigilator candidates for this school's exams.
        $allInvigilators = DB::table('users')
            ->join('model_has_roles', function ($j) {
                $j->on('users.id', '=', 'model_has_roles.model_id')
                  ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->whereIn('roles.name', ['Teacher', 'HOD', 'Academic'])
            ->when($this->currentSchoolId(), fn ($q, $id) => $q->where('users.school_id', $id))
            ->distinct()
            ->pluck('users.id')
            ->toArray();

        shuffle($allInvigilators);

        // Track: classDateSlot[class_id][date][slot_index] = true
        // Track: invigSlot[invig_id][date][slot_index] = true
        $classDateSlot = [];
        $invigSlot     = [];
        $entries       = [];

        foreach ($subjectsByClass as $classId => $subjects) {
            foreach ($subjects as $sub) {
                $placed = false;
                foreach ($dates as $date) {
                    if ($placed) break;
                    foreach ($slots as $si => $slot) {
                        if (!empty($classDateSlot[$classId][$date][$si])) continue;

                        // Pick invigilators: exclude the subject teacher, avoid double-booking
                        $assigned = $this->pickInvigilators(
                            $allInvigilators,
                            $invigSlot,
                            $date,
                            $si,
                            $invigilatorsPerSlot,
                            $sub['teacher_id']
                        );

                        if (count($assigned) < $invigilatorsPerSlot) {
                            $this->warnings[] = "Only " . count($assigned) . "/{$invigilatorsPerSlot} invigilators could be assigned for {$sub['subject_name']} (class #{$classId}) on {$date}.";
                        }

                        // Mark invigilator slots occupied
                        foreach ($assigned as $invigId) {
                            $invigSlot[$invigId][$date][$si] = true;
                        }

                        $classDateSlot[$classId][$date][$si] = true;

                        $entries[] = [
                            'school_id'       => $timetable->school_id,
                            'timetable_id'    => $timetable->id,
                            'class_id'        => $classId,
                            'subject_id'      => $sub['subject_id'],
                            'teacher_id'      => $sub['teacher_id'],
                            'invigilator_ids' => json_encode($assigned),
                            'exam_date'       => $date,
                            'start_time'      => $slot['start'],
                            'end_time'        => $slot['end'],
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ];
                        $placed = true;
                        break;
                    }
                }
                if (!$placed) {
                    $this->warnings[] = "Could not schedule exam for {$sub['subject_name']} in class #{$classId} — no available slots. Add more exam dates.";
                }
            }
        }

        return ['entries' => $entries, 'warnings' => $this->warnings];
    }

    private function pickInvigilators(
        array $allInvigilators,
        array &$invigSlot,
        string $date,
        int $slotIndex,
        int $needed,
        ?int $excludeTeacherId
    ): array {
        $assigned = [];
        foreach ($allInvigilators as $id) {
            if (count($assigned) >= $needed) break;
            if ($id === $excludeTeacherId) continue;                   // no self-invigilation
            if (!empty($invigSlot[$id][$date][$slotIndex])) continue;  // already busy
            $assigned[] = $id;
        }
        return $assigned;
    }

    // ── Save entries ──────────────────────────────────────────────────────

    public function saveEntries(Timetable $timetable, array $entries): void
    {
        // Remove previous entries and insert fresh batch
        $timetable->entries()->delete();

        foreach (array_chunk($entries, 200) as $chunk) {
            DB::table('timetable_entries')->insert($chunk);
        }
    }

    // ── Collision check (used after manual edits) ─────────────────────────

    public function getCollisions(Timetable $timetable): array
    {
        $collisions = [];

        if ($timetable->type === 'class') {
            // Build a set of (subject_id, subject_id) pairs that are intentional combos
            $comboPairs = [];
            foreach ($timetable->settings['combinations'] ?? [] as $combo) {
                $sids = $combo['subjects'] ?? [];
                for ($i = 0; $i < count($sids); $i++) {
                    for ($j = $i + 1; $j < count($sids); $j++) {
                        $pair = [$sids[$i], $sids[$j]];
                        sort($pair);
                        $comboPairs[implode('_', $pair)] = true;
                    }
                }
            }

            // Class double-booking — skip slots that are intentional combo placements
            $dups = DB::table('timetable_entries')
                ->where('timetable_id', $timetable->id)
                ->whereNotNull('day_of_week')
                ->groupBy('class_id', 'day_of_week', 'period_id')
                ->havingRaw('COUNT(*) > 1')
                ->select('class_id', 'day_of_week', 'period_id', DB::raw('COUNT(*) as cnt'))
                ->get();

            foreach ($dups as $d) {
                // Fetch the subjects in this duplicate slot
                $slotSubjects = DB::table('timetable_entries')
                    ->where('timetable_id', $timetable->id)
                    ->where('class_id', $d->class_id)
                    ->where('day_of_week', $d->day_of_week)
                    ->where('period_id', $d->period_id)
                    ->pluck('subject_id')->toArray();

                // If every unique pair of subjects in this slot is a known combo pair, skip
                sort($slotSubjects);
                $isCombo = true;
                for ($i = 0; $i < count($slotSubjects) && $isCombo; $i++) {
                    for ($j = $i + 1; $j < count($slotSubjects) && $isCombo; $j++) {
                        $pair = [$slotSubjects[$i], $slotSubjects[$j]];
                        sort($pair);
                        if (!isset($comboPairs[implode('_', $pair)])) $isCombo = false;
                    }
                }
                if (!$isCombo) {
                    $collisions[] = "Class #{$d->class_id}: duplicate on day {$d->day_of_week}, period #{$d->period_id}";
                }
            }

            // ── Teacher collision: period-based AND time-range overlap ────────
            $teacherEntries = DB::table('timetable_entries')
                ->where('timetable_id', $timetable->id)
                ->whereNotNull('teacher_id')
                ->whereNotNull('day_of_week')
                ->select('teacher_id', 'day_of_week', 'period_id',
                         'start_time', 'end_time', 'class_id', 'subject_id')
                ->get();

            // Resolve teacher names once
            $teacherNameMap = DB::table('users')
                ->whereIn('id', $teacherEntries->pluck('teacher_id')->unique()->toArray())
                ->select('id', 'name', 'first_name', 'last_name')->get()
                ->mapWithKeys(fn($u) => [$u->id => $u->name ?: trim($u->first_name . ' ' . $u->last_name)]);

            $dayLabels = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'];

            // Group entries: teacher → day → list
            $byTeacherDay = [];
            foreach ($teacherEntries as $te) {
                $byTeacherDay[$te->teacher_id][$te->day_of_week][] = $te;
            }

            foreach ($byTeacherDay as $tId => $dayMap) {
                $tName = $teacherNameMap[$tId] ?? "Teacher #{$tId}";
                foreach ($dayMap as $day => $slots) {
                    if (count($slots) < 2) continue;
                    $dayLabel = $dayLabels[$day] ?? "Day {$day}";

                    // 1) Period-based duplicates (same period_id, same day)
                    $byPeriod = [];
                    foreach ($slots as $s) {
                        if ($s->period_id) $byPeriod[$s->period_id][] = $s;
                    }
                    foreach ($byPeriod as $pId => $pSlots) {
                        if (count($pSlots) < 2) continue;
                        $subjIds = array_column($pSlots, 'subject_id');
                        $isCombo = true;
                        for ($i = 0; $i < count($subjIds) && $isCombo; $i++) {
                            for ($j = $i + 1; $j < count($subjIds) && $isCombo; $j++) {
                                $pair = [$subjIds[$i], $subjIds[$j]]; sort($pair);
                                if (!isset($comboPairs[implode('_', $pair)])) $isCombo = false;
                            }
                        }
                        if (!$isCombo) {
                            $collisions[] = "{$tName} is double-booked on {$dayLabel} (period #{$pId})";
                        }
                    }

                    // 2) Time-range overlap check (different period_ids but overlapping times)
                    $sorted = collect($slots)
                        ->filter(fn($s) => $s->start_time && $s->end_time && $s->period_id)
                        ->unique('period_id')
                        ->sortBy('start_time')->values();
                    for ($i = 0; $i < $sorted->count() - 1; $i++) {
                        $a = $sorted[$i]; $b = $sorted[$i + 1];
                        if ($a->period_id !== $b->period_id && $a->end_time > $b->start_time) {
                            $collisions[] = "{$tName}: overlapping sessions on {$dayLabel} ({$a->start_time}–{$a->end_time} and {$b->start_time}–{$b->end_time})";
                        }
                    }
                }
            }

            // 3) Cross-timetable teacher conflicts (against other published timetables)
            $crossEntries = DB::table('timetable_entries')
                ->join('timetables', 'timetables.id', '=', 'timetable_entries.timetable_id')
                ->where('timetables.academic_session_id', $timetable->academic_session_id)
                ->where('timetables.type', 'class')
                ->where('timetables.id', '!=', $timetable->id)
                ->when($this->currentSchoolId(), fn ($q, $id) => $q->where('timetables.school_id', $id))
                ->where('timetables.status', 'published')
                ->whereNotNull('timetable_entries.teacher_id')
                ->whereNotNull('timetable_entries.day_of_week')
                ->select('timetable_entries.teacher_id', 'timetable_entries.day_of_week',
                         'timetable_entries.start_time', 'timetable_entries.end_time',
                         'timetables.title as other_title')
                ->get();

            if ($crossEntries->isNotEmpty()) {
                $crossMap = [];
                foreach ($crossEntries as $ct) {
                    $crossMap[$ct->teacher_id][$ct->day_of_week][] = $ct;
                }
                foreach ($teacherEntries as $te) {
                    if (empty($crossMap[$te->teacher_id][$te->day_of_week])) continue;
                    foreach ($crossMap[$te->teacher_id][$te->day_of_week] as $other) {
                        if ($te->start_time && $te->end_time && $other->start_time && $other->end_time
                            && $te->start_time < $other->end_time && $te->end_time > $other->start_time) {
                            $tName    = $teacherNameMap[$te->teacher_id] ?? "Teacher #{$te->teacher_id}";
                            $dayLabel = $dayLabels[$te->day_of_week] ?? "Day {$te->day_of_week}";
                            $collisions[] = "{$tName}: also in '{$other->other_title}' on {$dayLabel} at {$te->start_time}–{$te->end_time}";
                        }
                    }
                }
            }
        }

        if ($timetable->type === 'exam') {
            // Invigilator double-booking: load in PHP since invigilator_ids is JSON
            $entries = DB::table('timetable_entries')
                ->where('timetable_id', $timetable->id)
                ->whereNotNull('exam_date')
                ->get(['class_id', 'exam_date', 'start_time', 'invigilator_ids']);

            $invigSlots = [];
            foreach ($entries as $e) {
                $ids = json_decode($e->invigilator_ids ?? '[]', true) ?: [];
                $key = $e->exam_date . '_' . $e->start_time;
                foreach ($ids as $invigId) {
                    if (isset($invigSlots[$invigId][$key])) {
                        $collisions[] = "Invigilator #{$invigId} is double-booked on {$e->exam_date} at {$e->start_time}";
                    }
                    $invigSlots[$invigId][$key] = true;
                }
            }
        }

        return array_unique($collisions);
    }
}
