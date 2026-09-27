<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// InjectMenuBadges runs on every authenticated request and counts pending
// budgets/invoices to show sidebar badges. Neither table had an index
// covering `status`, so every page view did a full table scan on both —
// scoped by the tenant's school_id first, since that's always in the WHERE
// clause via the BelongsToSchool global scope.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            if (!$this->indexExists('budgets', 'budgets_school_id_status_current_step_index')) {
                $table->index(['school_id', 'status', 'current_step'], 'budgets_school_id_status_current_step_index');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (!$this->indexExists('invoices', 'invoices_school_id_status_index')) {
                $table->index(['school_id', 'status'], 'invoices_school_id_status_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropIndex('budgets_school_id_status_current_step_index');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_school_id_status_index');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return collect(Schema::getIndexes($table))->pluck('name')->contains($indexName);
    }
};
