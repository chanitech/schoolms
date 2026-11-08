<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

// Route guards such as role:Admin check the role itself, not permissions.
return new class extends Migration
{
    public function up(): void
    {
        User::withoutGlobalScopes()->where('is_super_admin', true)->get()
            ->each(fn (User $user) => $user->assignRole('Admin'));
    }

    public function down(): void
    {
    }
};
