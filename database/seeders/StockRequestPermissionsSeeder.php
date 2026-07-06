<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Introduces the Storekeeper -> Procurement Officer -> Treasurer workflow:
 * Storekeeper flags a stock need to the Procurement Officer, who is the one
 * who actually requests a purchase from the Treasurer. Previously both
 * roles held 'create procurement requests', letting Storekeeper skip the
 * Procurement Officer entirely — a weak separation of duties for anything
 * touching money.
 */
class StockRequestPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'create stock requests']);
        Permission::firstOrCreate(['name' => 'review stock requests']);

        if ($storekeeper = Role::where('name', 'storekeeper')->first()) {
            $storekeeper->givePermissionTo('create stock requests');

            // The one-time correction plain givePermissionTo elsewhere can't
            // do: Storekeeper no longer requests purchases directly.
            if ($storekeeper->hasPermissionTo('create procurement requests')) {
                $storekeeper->revokePermissionTo('create procurement requests');
            }
        }

        if ($procurementOfficer = Role::where('name', 'procurement_officer')->first()) {
            $procurementOfficer->givePermissionTo('review stock requests');
        }

        if ($treasurer = Role::where('name', 'treasurer')->first()) {
            // Oversight only — Treasurer isn't the one flagging stock needs.
            $treasurer->givePermissionTo('review stock requests');
        }

        // Admin's "always has every permission" grant lives in
        // SyncAllPermissionsToAdminSeeder — these two are added to its list
        // too, so re-running that seeder keeps Admin in sync.
    }
}
