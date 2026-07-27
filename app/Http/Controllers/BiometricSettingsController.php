<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BiometricScanLog;
use App\Models\Staff;
use Illuminate\Http\Request;

class BiometricSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage biometric devices');
    }

    public function index()
    {
        $school = app('currentSchool');

        $recentScans = BiometricScanLog::with('staff')
            ->orderByDesc('scanned_at')
            ->limit(50)
            ->get();

        $unmatchedDeviceIds = BiometricScanLog::whereNull('staff_id')
            ->select('device_user_id')
            ->distinct()
            ->orderBy('device_user_id')
            ->pluck('device_user_id');

        $staff = Staff::orderBy('first_name')->get();

        $endpointUrl = url("/api/public/biometric-scans/{$school->slug}");

        return view('settings.biometric-devices', compact('school', 'recentScans', 'unmatchedDeviceIds', 'staff', 'endpointUrl'));
    }

    public function regenerateKey()
    {
        $school = app('currentSchool');
        $school->regenerateBiometricApiKey();

        return redirect()->route('settings.biometric-devices.index')
            ->with('success', 'Biometric device key regenerated. Update the relay script config with the new key — the old key stops working immediately.');
    }

    public function mapUnmatched(Request $request)
    {
        $request->validate([
            'device_user_id' => 'required|string|max:50',
            'staff_id' => 'required|exists:staff,id',
        ]);

        $staff = Staff::findOrFail($request->staff_id);

        $alreadyMapped = Staff::where('biometric_id', $request->device_user_id)
            ->where('id', '!=', $staff->id)
            ->exists();

        if ($alreadyMapped) {
            return back()->withErrors(['device_user_id' => 'This device ID is already mapped to another staff member.']);
        }

        $staff->update(['biometric_id' => $request->device_user_id]);

        // Backfill: this device ID's historical scans had no staff match —
        // link them now and recompute the attendance they should have produced,
        // otherwise the admin would be confused why mapping doesn't fix the past.
        $unmatchedLogs = BiometricScanLog::where('device_user_id', $request->device_user_id)
            ->whereNull('staff_id')
            ->get();

        BiometricScanLog::whereIn('id', $unmatchedLogs->pluck('id'))->update(['staff_id' => $staff->id]);

        $dates = $unmatchedLogs->map(fn ($log) => $log->scanned_at->toDateString())->unique();

        foreach ($dates as $date) {
            $agg = BiometricScanLog::where('staff_id', $staff->id)
                ->whereDate('scanned_at', $date)
                ->selectRaw('MIN(scanned_at) as first_scan, MAX(scanned_at) as last_scan, COUNT(*) as scan_count')
                ->first();

            // See BiometricScanController::recomputeAttendance() for why this
            // isn't Attendance::updateOrCreate() — the 'date' cast's string
            // format makes a plain equality lookup unreliable.
            $attendance = Attendance::where('staff_id', $staff->id)->whereDate('date', $date)->first();

            $values = [
                'status' => 'present',
                'source' => 'biometric',
                'check_in_at' => $agg->first_scan,
                'check_out_at' => $agg->scan_count > 1 ? $agg->last_scan : null,
            ];

            if ($attendance) {
                $attendance->update($values);
            } else {
                Attendance::create($values + ['staff_id' => $staff->id, 'date' => $date]);
            }
        }

        return redirect()->route('settings.biometric-devices.index')
            ->with('success', "Mapped device ID {$request->device_user_id} to {$staff->name} and backfilled {$unmatchedLogs->count()} historical scan(s).");
    }
}
