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
        {file : Path to the import CSV (category,name,unit,opening_qty,received_qty,issued_qty,quantity,unit_cost,notes)}
        {--period-start=2026-06-01 : Date for the opening-stock transactions}
        {--period-end=2026-06-30 : Date for the issued/closing transactions}
        {--school= : School slug (defaults to the first school)}
        {--dry-run : Parse and report without saving anything}';

    protected $description = 'Import inventory history from a CSV prepared from the school\'s Excel inventory workbook: items plus a full ledger (opening stock, goods received, issued/sold) per period. Idempotent — re-running updates items and never duplicates transactions.';

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
        $expected = ['category', 'name', 'unit', 'opening_qty', 'received_qty', 'issued_qty', 'quantity', 'unit_cost', 'notes'];
        if ($header !== $expected) {
            $this->error('Unexpected CSV header: ' . implode(',', $header ?: []));
            $this->line('Expected: ' . implode(',', $expected));
            return self::FAILURE;
        }

        $rows = [];
        while (($r = fgetcsv($handle)) !== false) {
            if (count($r) < 9 || trim($r[1]) === '') continue;
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

                // Full period ledger, never duplicated: opening stock at the
                // period start, goods received during the period, and
                // issued/sold at the period end — running balance intact.
                $alreadyImported = InventoryTransaction::where('item_id', $item->id)
                    ->where('remarks', 'like', 'Excel import:%')->exists();

                if (! $alreadyImported) {
                    $opening  = (int) $row['opening_qty'];
                    $received = (int) $row['received_qty'];
                    $issued   = (int) $row['issued_qty'];
                    $running  = 0;

                    $movements = [
                        [$opening,  'purchase', $this->option('period-start'), 'Excel import: opening stock as at ' . $this->option('period-start')],
                        [$received, 'purchase', $this->option('period-end'),   'Excel import: goods received during the period (aggregated)'],
                    ];
                    // A negative "issued" in the workbook is stock coming back
                    // (a return/correction), not a sale.
                    $movements[] = $issued >= 0
                        ? [$issued,       'issue',  $this->option('period-end'), 'Excel import: issued/sold during the period (aggregated)']
                        : [abs($issued),  'return', $this->option('period-end'), 'Excel import: stock returned during the period (negative issue in workbook)'];

                    foreach ($movements as [$qty, $type, $date, $remarks]) {
                        if ($qty <= 0) continue;
                        $running += $type === 'issue' ? -$qty : $qty;
                        InventoryTransaction::create([
                            'item_id'          => $item->id,
                            'type'             => $type,
                            'quantity'         => $qty,
                            'balance_after'    => $running,
                            'remarks'          => $remarks,
                            'user_id'          => $recorder?->id,
                            'transaction_date' => $date,
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
