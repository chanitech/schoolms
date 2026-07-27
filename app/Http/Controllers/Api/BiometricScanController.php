<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\BiometricScanLog;
use App\Models\School;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Ingests fingerprint scan batches from a school's local ZKTeco relay script.
 * ResolveTenant doesn't run on routes/api.php, so the school is resolved
 * explicitly here and every query is scoped manually — same pattern as
 * GuardianRegistrationController / StudentDirectoryController.
 */
class BiometricScanController extends Controller
{
    public function store(Request $request, string $schoolSlug): JsonResponse
    {
        $school = School::resolveBySlug($schoolSlug);

        if (! $school->biometric_api_key || ! hash_equals($school->biometric_api_key, (string) $request->header('X-Device-Key'))) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Capped at 500: protects shared cPanel hosting from a misconfigured
        // relay dumping all ~80,000 on-device records in a single request.
        $validated = $request->validate([
            'scans' => 'required|array|min:1|max:500',
            'scans.*.device_user_id' => 'required|string|max:50',
            'scans.*.scanned_at' => 'required|date',
            'device_serial' => 'nullable|string|max:100',
        ]);

        // A dead device battery resets the K40's clock to a factory default
        // (often year 2000); it will then happily log scans with garbage
        // dates until someone notices and resets it. Skip those rows rather
        // than writing implausible attendance dates or failing the batch.
        $minPlausible = Carbon::create(2020, 1, 1);
        $maxPlausible = now()->addDay();

        $accepted = 0;
        $skipped = 0;
        $rows = [];
        $now = now();

        foreach ($validated['scans'] as $scan) {
            $scannedAt = Carbon::parse($scan['scanned_at']);

            if ($scannedAt->lt($minPlausible) || $scannedAt->gt($maxPlausible)) {
                $skipped++;
                continue;
            }

            $rows[] = [
                'device_user_id' => $scan['device_user_id'],
                'scanned_at' => $scannedAt,
            ];
            $accepted++;
        }

        $unmatchedDeviceIds = [];

        if (! empty($rows)) {
            // Resolve device_user_id -> staff_id in one query (avoids N+1).
            $deviceUserIds = array_unique(array_column($rows, 'device_user_id'));
            $staffByDeviceId = Staff::withoutSchoolScope()
                ->where('school_id', $school->id)
                ->whereIn('biometric_id', $deviceUserIds)
                ->get()
                ->keyBy('biometric_id');

            $insertRows = [];
            foreach ($rows as $row) {
                $staff = $staffByDeviceId->get($row['device_user_id']);

                if (! $staff) {
                    $unmatchedDeviceIds[] = $row['device_user_id'];
                }

                $insertRows[] = [
                    'school_id' => $school->id,
                    'staff_id' => $staff?->id,
                    'device_user_id' => $row['device_user_id'],
                    'device_serial' => $validated['device_serial'] ?? null,
                    'scanned_at' => $row['scanned_at'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Atomic + race-safe: naturally dedups via the unique key on
            // (school_id, device_user_id, scanned_at), so a resent batch
            // (e.g. after a relay timeout) is a no-op rather than an error.
            BiometricScanLog::insertOrIgnore($insertRows);

            $this->recomputeAttendance($school->id, $insertRows);
        }

        Log::info('Biometric scan batch ingested', [
            'school' => $schoolSlug,
            'accepted' => $accepted,
            'skipped' => $skipped,
            'unmatched' => count(array_unique($unmatchedDeviceIds)),
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'accepted' => $accepted,
            'skipped' => $skipped,
            'unmatched_device_ids' => array_values(array_unique($unmatchedDeviceIds)),
        ]);
    }

    /**
     * Recomputes check-in/out times from biometric_scan_logs (rather than
     * merging min/max incrementally into `attendances`), so retries,
     * out-of-order batches, and >2 punches/day (e.g. a lunch break) all
     * resolve correctly to first-in/last-out. A scan is authoritative: it
     * overwrites any manually-set status for that day, since it reflects
     * physical reality.
     */
    private function recomputeAttendance(int $schoolId, array $insertedRows): void
    {
        $touched = collect($insertedRows)
            ->filter(fn ($row) => $row['staff_id'] !== null)
            ->map(fn ($row) => [
                'staff_id' => $row['staff_id'],
                'date' => Carbon::parse($row['scanned_at'])->toDateString(),
            ])
            ->unique(fn ($pair) => $pair['staff_id'].'|'.$pair['date']);

        foreach ($touched as $pair) {
            $agg = BiometricScanLog::withoutSchoolScope()
                ->where('school_id', $schoolId)
                ->where('staff_id', $pair['staff_id'])
                ->whereDate('scanned_at', $pair['date'])
                ->selectRaw('MIN(scanned_at) as first_scan, MAX(scanned_at) as last_scan, COUNT(*) as scan_count')
                ->first();

            // Deliberately not Attendance::updateOrCreate(): its lookup does a
            // plain string-equality match on 'date', but the column's 'date'
            // cast writes a full "Y-m-d H:i:s" string on save. MySQL's DATE
            // column type silently truncates that back to "Y-m-d" on storage
            // (masking the mismatch there), but nothing guarantees that
            // truncation — whereDate() compares by calendar day explicitly
            // instead of relying on it.
            $attendance = Attendance::withoutSchoolScope()
                ->where('school_id', $schoolId)
                ->where('staff_id', $pair['staff_id'])
                ->whereDate('date', $pair['date'])
                ->first();

            $values = [
                'status' => 'present',
                'source' => 'biometric',
                'check_in_at' => $agg->first_scan,
                'check_out_at' => $agg->scan_count > 1 ? $agg->last_scan : null,
            ];

            if ($attendance) {
                $attendance->update($values);
            } else {
                Attendance::create($values + [
                    'school_id' => $schoolId,
                    'staff_id' => $pair['staff_id'],
                    'date' => $pair['date'],
                ]);
            }
        }
    }
}
