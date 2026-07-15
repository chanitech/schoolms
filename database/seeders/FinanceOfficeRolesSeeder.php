<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FinanceOfficeRolesSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'record payments',
            'verify payments',
            'flag payments',
            'manage stock',
            'create procurement requests',
            'approve procurement requests',
            'disburse payments',
            'record expenses',
            'manage job descriptions',
            'manage tasks',
            'submit task justification',
            'review task justification',
            'view finance dashboard',
        ];

        // Pre-existing permissions referenced by BudgetController/InvoiceController/
        // the sidebar's 'can' checks but never created — same dead-gate pattern as
        // the treasurer/accountant roles below. Needed so the Treasurer dashboard's
        // links to Pending Budgets/Invoices actually work instead of 403ing.
        $legacyApprovalPermissions = [
            'approve loans',
            'view pending approvals',
            'approve invoices',
        ];

        foreach ([...$permissions, ...$legacyApprovalPermissions] as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Admin already gets Permission::all() in RolesAndPermissionsSeeder,
        // but that ran before these permissions existed — grant explicitly
        // so Admin (this app's super-admin role) can access every Finance
        // Office screen without needing a separate Finance Office role.
        if ($admin = Role::where('name', 'Admin')->first()) {
            $admin->givePermissionTo([...$permissions, ...$legacyApprovalPermissions]);
        }

        // Fixes previously-dead role gates in routes/web.php (treasurer.* group)
        // and Loan::approveBy() — these role names were referenced in code but
        // never existed as rows, making those routes unreachable by anyone.
        $treasurer = Role::firstOrCreate(['name' => 'treasurer']);
        $treasurer->givePermissionTo([...$permissions, ...$legacyApprovalPermissions]);

        $chiefAccountant = Role::firstOrCreate(['name' => 'chief-accountant']);
        $chiefAccountant->givePermissionTo([
            'view payments', 'view fee reports',
            // Senior accounting oversight
            'view finance dashboard', 'verify payments', 'flag payments',
            'manage tasks', 'submit task justification',
        ]);

        $accountant = Role::firstOrCreate(['name' => 'accountant']);
        $accountant->givePermissionTo(['view payments', 'view fee reports', 'manage tasks', 'submit task justification']);

        $classAccountant = Role::firstOrCreate(['name' => 'class_accountant']);
        $classAccountant->givePermissionTo(['view payments', 'verify payments', 'flag payments']);

        $procurementOfficer = Role::firstOrCreate(['name' => 'procurement_officer']);
        $procurementOfficer->givePermissionTo(['create procurement requests', 'manage tasks', 'submit task justification']);

        $cashier = Role::firstOrCreate(['name' => 'cashier']);
        $cashier->givePermissionTo(['record payments', 'disburse payments', 'manage tasks', 'submit task justification']);

        $storekeeper = Role::firstOrCreate(['name' => 'storekeeper']);
        $storekeeper->givePermissionTo(['manage stock', 'create procurement requests', 'manage tasks', 'submit task justification']);
    }
}
