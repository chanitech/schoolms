<?php

namespace Tests\Feature\Api;

use App\Models\Attendance;
use App\Models\BiometricScanLog;
use App\Models\School;
use App\Models\Staff;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * routes/api.php isn't covered by ResolveTenant, so BiometricScanController
 * resolves its school explicitly and every query is manually scoped — these
 * tests exercise that path directly, plus the dedup/aggregation logic that
 * turns raw scans into check-in/check-out attendance.
 */
class BiometricScanControllerTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private Staff $staff;
    private string $apiKey = 'test-shared-key';
    private string $deviceKey;

    protected function setUp(): void
    {
        parent::setUp();

        app()->forgetInstance('currentSchool');
        config(['services.public_api.key' => $this->apiKey]);

        $this->school = School::create([
            'name' => 'Test School',
            'slug' => 'test-school',
            'subscription_status' => 'active',
            'plan' => 'pro',
        ]);
        $this->deviceKey = $this->school->regenerateBiometricApiKey();

        $this->staff = Staff::create([
            'school_id' => $this->school->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@test.local',
            'biometric_id' => '101',
        ]);
    }

    private function endpoint(): string
    {
        return "/api/public/biometric-scans/{$this->school->slug}";
    }

    private function headers(array $overrides = []): array
    {
        return array_merge([
            'X-API-Key' => $this->apiKey,
            'X-Device-Key' => $this->deviceKey,
        ], $overrides);
    }

    public function test_valid_batch_creates_attendance_with_first_in_last_out(): void
    {
        $response = $this->postJson($this->endpoint(), [
            'scans' => [
                ['device_user_id' => '101', 'scanned_at' => '2026-07-27T07:55:00'],
                ['device_user_id' => '101', 'scanned_at' => '2026-07-27T16:10:00'],
            ],
        ], $this->headers());

        $response->assertOk();
        $response->assertJson(['accepted' => 2, 'skipped' => 0]);

        // whereDate(), not where('date', ...): the 'date' cast writes a full
        // "Y-m-d H:i:s" string, so plain equality against a bare "Y-m-d"
        // string doesn't reliably match (see recomputeAttendance() in the
        // controller for the same issue on the write side).
        $attendance = Attendance::where('staff_id', $this->staff->id)->whereDate('date', '2026-07-27')->first();

        $this->assertNotNull($attendance);
        $this->assertEquals('present', $attendance->status);
        $this->assertEquals('biometric', $attendance->source);
        $this->assertEquals('2026-07-27 07:55:00', $attendance->check_in_at->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-07-27 16:10:00', $attendance->check_out_at->format('Y-m-d H:i:s'));
    }

    public function test_single_scan_sets_check_in_only(): void
    {
        $this->postJson($this->endpoint(), [
            'scans' => [
                ['device_user_id' => '101', 'scanned_at' => '2026-07-27T07:55:00'],
            ],
        ], $this->headers())->assertOk();

        $attendance = Attendance::where('staff_id', $this->staff->id)->first();

        $this->assertNotNull($attendance->check_in_at);
        $this->assertNull($attendance->check_out_at);
    }

    public function test_scan_overrides_manually_set_leave_status(): void
    {
        Attendance::create([
            'school_id' => $this->school->id,
            'staff_id' => $this->staff->id,
            'date' => '2026-07-27',
            'status' => 'leave',
            'source' => 'manual',
        ]);

        $this->postJson($this->endpoint(), [
            'scans' => [
                ['device_user_id' => '101', 'scanned_at' => '2026-07-27T07:55:00'],
            ],
        ], $this->headers())->assertOk();

        $attendance = Attendance::where('staff_id', $this->staff->id)->first();

        $this->assertEquals('present', $attendance->status);
        $this->assertEquals('biometric', $attendance->source);
    }

    public function test_resending_identical_batch_is_a_noop(): void
    {
        $payload = [
            'scans' => [
                ['device_user_id' => '101', 'scanned_at' => '2026-07-27T07:55:00'],
                ['device_user_id' => '101', 'scanned_at' => '2026-07-27T16:10:00'],
            ],
        ];

        $this->postJson($this->endpoint(), $payload, $this->headers())->assertOk();
        $this->postJson($this->endpoint(), $payload, $this->headers())->assertOk();

        $this->assertEquals(2, BiometricScanLog::count());
        $this->assertEquals(1, Attendance::count());
    }

    public function test_out_of_order_batch_yields_correct_min_max(): void
    {
        // Later scan listed first in the payload.
        $this->postJson($this->endpoint(), [
            'scans' => [
                ['device_user_id' => '101', 'scanned_at' => '2026-07-27T16:10:00'],
                ['device_user_id' => '101', 'scanned_at' => '2026-07-27T07:55:00'],
                ['device_user_id' => '101', 'scanned_at' => '2026-07-27T12:00:00'],
            ],
        ], $this->headers())->assertOk();

        $attendance = Attendance::where('staff_id', $this->staff->id)->first();

        $this->assertEquals('2026-07-27 07:55:00', $attendance->check_in_at->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-07-27 16:10:00', $attendance->check_out_at->format('Y-m-d H:i:s'));
    }

    public function test_unmatched_device_id_is_logged_without_attendance(): void
    {
        $response = $this->postJson($this->endpoint(), [
            'scans' => [
                ['device_user_id' => '999', 'scanned_at' => '2026-07-27T07:55:00'],
            ],
        ], $this->headers());

        $response->assertOk();
        $response->assertJson(['unmatched_device_ids' => ['999']]);

        $this->assertEquals(0, Attendance::count());
        $this->assertEquals(1, BiometricScanLog::count());
        $this->assertNull(BiometricScanLog::first()->staff_id);
    }

    public function test_wrong_device_key_is_rejected(): void
    {
        $response = $this->postJson($this->endpoint(), [
            'scans' => [['device_user_id' => '101', 'scanned_at' => '2026-07-27T07:55:00']],
        ], $this->headers(['X-Device-Key' => 'wrong-key']));

        $response->assertStatus(401);
        $this->assertEquals(0, BiometricScanLog::count());
    }

    public function test_wrong_outer_api_key_is_rejected_before_controller_runs(): void
    {
        $response = $this->postJson($this->endpoint(), [
            'scans' => [['device_user_id' => '101', 'scanned_at' => '2026-07-27T07:55:00']],
        ], $this->headers(['X-API-Key' => 'wrong-shared-key']));

        $response->assertStatus(401);
        $this->assertEquals(0, BiometricScanLog::count());
    }

    public function test_implausible_timestamp_is_skipped_not_fatal(): void
    {
        $response = $this->postJson($this->endpoint(), [
            'scans' => [
                ['device_user_id' => '101', 'scanned_at' => '2000-01-01T00:00:00'], // dead-battery reset
                ['device_user_id' => '101', 'scanned_at' => '2026-07-27T07:55:00'],
            ],
        ], $this->headers());

        $response->assertOk();
        $response->assertJson(['accepted' => 1, 'skipped' => 1]);
        $this->assertEquals(1, BiometricScanLog::count());
    }

    public function test_biometric_id_uniqueness_is_scoped_per_school(): void
    {
        $otherSchool = School::create([
            'name' => 'Other School',
            'slug' => 'other-school',
            'subscription_status' => 'active',
            'plan' => 'pro',
        ]);

        // Same biometric_id ('101') on a different school must be allowed.
        $otherStaff = Staff::create([
            'school_id' => $otherSchool->id,
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john@test.local',
            'biometric_id' => '101',
        ]);

        $this->assertNotNull($otherStaff->id);

        // Same biometric_id within the SAME school must be rejected by the DB constraint.
        $this->expectException(QueryException::class);

        Staff::create([
            'school_id' => $this->school->id,
            'first_name' => 'Dup',
            'last_name' => 'Licate',
            'email' => 'dup@test.local',
            'biometric_id' => '101',
        ]);
    }
}
