<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MigrateStaffToUsers extends Command
{
    protected $signature = 'migrate:staff-to-users';
    protected $description = 'Copy all staff records into users table for login';

    public function handle(): int
    {
        $staffMembers = Staff::all();

        if ($staffMembers->isEmpty()) {
            $this->info('No staff records found.');
            return 0;
        }

        foreach ($staffMembers as $staff) {
            // Check if user already exists
            $existing = User::where('email', $staff->email)->first();
            if ($existing) {
                $this->warn("User already exists: {$staff->email}");
                continue;
            }

            User::create([
                'name' => $staff->first_name . ' ' . $staff->last_name,
                'first_name' => $staff->first_name,
                'last_name' => $staff->last_name,
                'email' => $staff->email,
                'password' => $staff->password, // If already hashed, keep it
                'phone' => $staff->phone,
                'department_id' => $staff->department_id,
                'position' => $staff->position,
                'photo' => $staff->photo,
                'role' => $staff->role ?? 'Staff',
            ]);

            $this->info("Migrated staff: {$staff->email}");
        }

        $this->info('Staff migration completed!');
        return 0;
    }
}
