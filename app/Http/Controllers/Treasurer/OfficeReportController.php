<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\ExpenseLog;
use App\Models\Invoice;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\ProcurementRequest;
use App\Models\StockRequest;
use App\Models\User;
use App\Services\DocumentSignatureService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class OfficeReportController extends Controller
{
    public function __construct(private DocumentSignatureService $signatures)
    {
        $this->middleware('permission:view payments')->only('payments');
        $this->middleware('permission:view invoices')->only('invoices');
        $this->middleware('permission:view budgets')->only('budgets');
        // loans / procurement / stock / expenses are gated by the treasurer
        // route group's role middleware (see routes/web.php)
    }

    /**
     * Signed PDF export for a treasurer-office module. Every report row
     * carries its full approval trail (who did what, when); the document
     * footer carries the digital signature block with a public
     * verification code.
     */
    private function export(Request $request, string $docType, string $title, array $columns, $rows, string $summary)
    {
        $rowsArr   = array_values($rows->toArray());
        $signature = $this->signatures->sign($docType, $title, $summary, $rowsArr);

        $pdf = Pdf::loadView('treasurer.reports.pdf', [
            'title'     => $title,
            'columns'   => $columns,
            'rows'      => $rowsArr,
            'summary'   => $summary,
            'signature' => $signature,
        ])->setPaper('a4', 'landscape');

        return $pdf->download(str_replace(' ', '-', strtolower($title)) . '-' . now()->format('Ymd-His') . '.pdf');
    }

    /** Resolve user names in one query for approval-trail columns. */
    private function names(array $ids): array
    {
        return User::whereIn('id', array_filter(array_unique($ids)))->pluck('name', 'id')->all();
    }

    private function dateRange(Request $request, $query, string $column): array
    {
        $from = $request->input('from');
        $to   = $request->input('to');
        if ($from) $query->whereDate($column, '>=', $from);
        if ($to)   $query->whereDate($column, '<=', $to);

        return [$from, $to];
    }

    private function rangeLabel(?string $from, ?string $to): string
    {
        if (!$from && !$to) return 'All time';

        return ($from ?: 'start') . ' to ' . ($to ?: 'today');
    }

    public function loans(Request $request)
    {
        $query = Loan::with(['staff', 'category'])->latest('application_date');
        if ($request->filled('status')) $query->where('status', $request->status);
        [$from, $to] = $this->dateRange($request, $query, 'application_date');
        $loans = $query->get();

        $names = $this->names($loans->flatMap(fn($l) => [
            $l->chief_accountant_approved_by, $l->accountant_approved_by, $l->treasurer_approved_by,
        ])->all());

        $sign = fn($id, $at) => $id
            ? ($names[$id] ?? '#' . $id) . ' — ' . optional($at)->format('d.m.Y H:i')
            : '—';

        $rows = $loans->map(fn($l) => [
            trim(($l->staff->first_name ?? '') . ' ' . ($l->staff->last_name ?? '')),
            $l->category->name ?? '—',
            number_format($l->amount_applied) . ' / ' . number_format($l->amount_approved ?? 0),
            $l->application_date?->format('d.m.Y'),
            ucfirst($l->status),
            $sign($l->chief_accountant_approved_by, $l->chief_accountant_approved_at),
            $sign($l->accountant_approved_by, $l->accountant_approved_at),
            $sign($l->treasurer_approved_by, $l->treasurer_approved_at),
        ]);

        return $this->export($request, 'loans', 'Staff Loans Report',
            ['Staff', 'Category', 'Applied / Approved (TZS)', 'Applied On', 'Status',
             'Chief Accountant', 'Accountant', 'Treasurer'],
            $rows,
            $loans->count() . ' loans, ' . $this->rangeLabel($from, $to)
                . ($request->filled('status') ? ', status: ' . $request->status : ''));
    }

    public function procurement(Request $request)
    {
        $query = ProcurementRequest::with('requestedBy')->latest();
        if ($request->filled('status')) $query->where('status', $request->status);
        [$from, $to] = $this->dateRange($request, $query, 'created_at');
        $items = $query->get();

        $names = $this->names($items->flatMap(fn($p) => [
            $p->approved_by, $p->headmaster_approved_by, $p->disbursed_by, $p->returned_by,
        ])->all());

        $sign = fn($id, $at) => $id
            ? ($names[$id] ?? '#' . $id) . ' — ' . optional($at)->format('d.m.Y H:i')
            : '—';

        $rows = $items->map(fn($p) => [
            $p->item,
            $p->quantity,
            number_format($p->estimated_cost ?? 0) . ' / ' . number_format($p->actual_cost ?? 0),
            $p->requestedBy->name ?? '—',
            ucfirst(str_replace('_', ' ', $p->status)),
            $sign($p->approved_by, $p->approved_at),
            $sign($p->headmaster_approved_by, $p->headmaster_approved_at),
            $sign($p->disbursed_by, $p->disbursed_at),
        ]);

        return $this->export($request, 'procurement', 'Procurement Requests Report',
            ['Item', 'Qty', 'Estimated / Actual (TZS)', 'Requested By', 'Status',
             'Treasurer Approval', 'Head Master Approval', 'Disbursed By (Cashier)'],
            $rows,
            $items->count() . ' requests, ' . $this->rangeLabel($from, $to));
    }

    public function stockRequests(Request $request)
    {
        $query = StockRequest::with('requestedBy')->latest();
        if ($request->filled('status')) $query->where('status', $request->status);
        [$from, $to] = $this->dateRange($request, $query, 'created_at');
        $items = $query->get();

        $names = $this->names($items->pluck('reviewed_by')->all());

        $rows = $items->map(fn($s) => [
            $s->item,
            $s->quantity,
            \Illuminate\Support\Str::limit($s->reason ?? '', 60),
            $s->requestedBy->name ?? '—',
            $s->created_at->format('d.m.Y'),
            ucfirst($s->status),
            $s->reviewed_by ? ($names[$s->reviewed_by] ?? '—') . ' — ' . optional($s->reviewed_at)->format('d.m.Y H:i') : '—',
        ]);

        return $this->export($request, 'stock_requests', 'Stock Requests Report',
            ['Item', 'Qty', 'Reason', 'Requested By (Storekeeper)', 'Date', 'Status', 'Reviewed By (Procurement)'],
            $rows,
            $items->count() . ' requests, ' . $this->rangeLabel($from, $to));
    }

    public function expenses(Request $request)
    {
        $query = ExpenseLog::with('procurementRequest')->latest();
        if ($request->filled('category')) $query->where('category', $request->category);
        [$from, $to] = $this->dateRange($request, $query, 'created_at');
        $items = $query->get();

        $names = $this->names($items->pluck('recorded_by')->all());

        $rows = $items->map(fn($e) => [
            $e->created_at->format('d.m.Y'),
            $e->category,
            number_format($e->amount),
            $e->procurementRequest->item ?? '—',
            \Illuminate\Support\Str::limit($e->notes ?? '', 60) ?: '—',
            (($e->recorded_by ? $names[$e->recorded_by] ?? '—' : '—')) . ' — ' . $e->created_at->format('d.m.Y H:i'),
        ]);

        return $this->export($request, 'expenses', 'Expense Log Report',
            ['Date', 'Category', 'Amount (TZS)', 'Linked Procurement', 'Notes', 'Recorded By (Cashier)'],
            $rows,
            $items->count() . ' expenses totalling ' . number_format($items->sum('amount')) . ' TZS, '
                . $this->rangeLabel($from, $to));
    }

    public function payments(Request $request)
    {
        $query = Payment::with('student')->latest('payment_date');
        if ($request->filled('status')) $query->where('status', $request->status);
        [$from, $to] = $this->dateRange($request, $query, 'payment_date');
        $items = $query->get();

        $names = $this->names($items->flatMap(fn($p) => [$p->recorded_by, $p->verified_by])->all());

        $rows = $items->map(fn($p) => [
            $p->payment_date?->format('d.m.Y'),
            trim(($p->student->first_name ?? '') . ' ' . ($p->student->last_name ?? '')),
            number_format($p->amount),
            strtoupper($p->method ?? '—'),
            $p->reference ?: '—',
            ucfirst($p->status),
            ($p->recorded_by ? $names[$p->recorded_by] ?? '—' : '—'),
            $p->verified_by ? ($names[$p->verified_by] ?? '—') : '—',
        ]);

        return $this->export($request, 'payments', 'Payments Reconciliation Report',
            ['Date', 'Student', 'Amount (TZS)', 'Method', 'Reference', 'Status', 'Recorded By', 'Verified By'],
            $rows,
            $items->count() . ' payments totalling ' . number_format($items->sum('amount')) . ' TZS, '
                . $this->rangeLabel($from, $to));
    }

    public function invoices(Request $request)
    {
        $query = Invoice::with(['budgetItem.budget.department', 'approvedBy', 'paidBy'])->latest();
        if ($request->filled('status')) $query->where('status', $request->status);
        [$from, $to] = $this->dateRange($request, $query, 'created_at');
        $items = $query->get();

        $rows = $items->map(fn($i) => [
            '#' . str_pad($i->id, 5, '0', STR_PAD_LEFT),
            $i->budgetItem->item ?? '—',
            $i->budgetItem->budget->department->name ?? '—',
            number_format($i->amount),
            ucfirst(str_replace('_', ' ', $i->status)),
            $i->approved_by_do_id ? ($i->approvedBy->name ?? '—') : '—',
            $i->paid_by_finance_id ? ($i->paidBy->name ?? '—') . ' — ' . optional($i->payment_date)->format('d.m.Y') : '—',
            \Illuminate\Support\Str::limit($i->notes ?? '', 50) ?: '—',
        ]);

        return $this->export($request, 'invoices', 'Invoices Report',
            ['Invoice', 'Budget Item', 'Department', 'Amount (TZS)', 'Status',
             'Approved By (Head Master)', 'Paid By', 'Notes'],
            $rows,
            $items->count() . ' invoices totalling ' . number_format($items->sum('amount')) . ' TZS, '
                . $this->rangeLabel($from, $to));
    }

    public function budgets(Request $request)
    {
        $query = Budget::with(['department', 'staff', 'items.approvedBy'])->latest();
        if ($request->filled('status')) $query->where('status', $request->status);
        [$from, $to] = $this->dateRange($request, $query, 'created_at');
        $budgets = $query->get();

        $rows = $budgets->flatMap(fn($b) => $b->items->map(fn($item) => [
            '#' . $b->id . ' ' . ($b->department->name ?? '—') . ' (' . $b->month . ' ' . $b->year . ')',
            $item->item,
            number_format($item->price),
            ucfirst($item->status),
            $item->approved_by ? ($item->approvedBy->name ?? '—') : '—',
            \Illuminate\Support\Str::limit($item->note ?? '', 50) ?: '—',
        ]));

        return $this->export($request, 'budgets', 'Budgets Report',
            ['Budget (Department, Period)', 'Item', 'Price (TZS)', 'Item Status',
             'Decided By (Head Master)', 'Decision Note'],
            $rows,
            $budgets->count() . ' budgets / ' . $rows->count() . ' items, ' . $this->rangeLabel($from, $to));
    }
}
