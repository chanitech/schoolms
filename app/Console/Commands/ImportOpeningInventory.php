<?php

namespace App\Console\Commands;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\School;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportOpeningInventory extends Command
{
    protected $signature = 'inventory:import-opening
        {file : Path to the import CSV (category,name,unit,quantity,unit_cost,notes)}
        {--date=2026-06-30 : Opening balance date recorded on the stock transactions}
        {--school= : School slug (defaults to the first school)}
        {--dry-run : Parse and report without saving anything}';

    protected $description = 'Import opening inventory balances from a CSV prepared from the school\'s Excel inventory workbook. Idempotent — re-running updates items instead of duplicating them.';

    private const CATEGORY_ICONS = [
        'Stationery'  => 'fas fa-pen',
        'Food Store'  => 'fas fa-utensils',
        'School Shop' => 'fas fa-store',
    ];

    public function handle(): int
    {
        $path = $this->argument('file');
        if (! is_readable($path)) {
            $this->error("Cannot read file: {$path}");
            return self::FAILURE;
        }

        $school = $this->option('school')
            ? School::withoutGlobalScopes()->where('slug', $this->option('school'))->first()
            : School::withoutGlobalScopes()->orderBy('id')->first();
        if (! $school) {
            $this->error('No school found.');
            return self::FAILURE;
        }
        app()->instance('currentSchool', $school);

        $recorder = User::role('Admin')->first();

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $expected = ['category', 'name', 'unit', 'quantity', 'unit_cost', 'notes'];
        if ($header !== $expected) {
            $this->error('Unexpected CSV header: ' . implode(',', $header ?: []));
            $this->line('Expected: ' . implode(',', $expected));
            return self::FAILURE;
        }

        $rows = [];
        while (($r = fgetcsv($handle)) !== false) {
            if (count($r) < 6 || trim($r[1]) === '') continue;
            $rows[] = array_combine($expected, $r);
        }
        fclose($handle);

        $created = $updated = $transactions = 0;

        DB::beginTransaction();
        try {
            $categories = [];
            foreach (array_unique(array_column($rows, 'category')) as $catName) {
                $categories[$catName] = InventoryCategory::firstOrCreate(
                    ['name' => $catName, 'school_id' => $school->id],
                    ['icon' => self::CATEGORY_ICONS[$catName] ?? 'fas fa-box']
                );
            }

            foreach ($rows as $row) {
                $item = InventoryItem::firstOrNew([
                    'school_id'   => $school->id,
                    'category_id' => $categories[$row['category']]->id,
                    'name'        => $row['name'],
                    'unit'        => $row['unit'],
                ]);
                $item->exists ? $updated++ : $created++;

                $item->fill([
                    'quantity_in_stock' => (int) $row['quantity'],
                    'unit_cost'         => (float) $row['unit_cost'],
                    'minimum_stock'     => $item->minimum_stock ?? 0,
                    'condition'         => $item->condition ?? 'good',
                    'notes'             => $row['notes'],
                ])->save();

                // One opening transaction per stocked item, never duplicated
                if ((int) $row['quantity'] > 0) {
                    $exists = InventoryTransaction::where('item_id', $item->id)
                        ->where('remarks', 'like', 'Opening balance import%')->exists();
                    if (! $exists) {
                        InventoryTransaction::create([
                            'item_id'          => $item->id,
                            'type'             => 'purchase',
                            'quantity'         => (int) $row['quantity'],
                            'balance_after'    => (int) $row['quantity'],
                            'remarks'          => 'Opening balance import — Excel inventory workbook (closing 30.06.2026)',
                            'user_id'          => $recorder?->id,
                            'transaction_date' => $this->option('date'),
                        ]);
                        $transactions++;
                    }
                }
            }

            if ($this->option('dry-run')) {
                DB::rollBack();
                $this->warn('DRY RUN — nothing saved.');
            } else {
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Import failed, nothing saved: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('Import ' . ($this->option('dry-run') ? 'simulated' : 'complete') . " for {$school->name}:");
        $this->table(['Metric', 'Count'], [
            ['CSV rows', count($rows)],
            ['Items created', $created],
            ['Items updated (re-run)', $updated],
            ['Opening stock transactions', $transactions],
        ]);

        return self::SUCCESS;
    }
}
