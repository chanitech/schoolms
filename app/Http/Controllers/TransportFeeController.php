<?php

namespace App\Http\Controllers;

use App\Models\TransportFee;
use App\Models\TransportPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransportFeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view transport')->only(['index']);
        $this->middleware('permission:record transport payments')->only(['pay']);
    }

    public function index(Request $request)
    {
        $query = TransportFee::with(['student', 'route', 'payments']);

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('route_id')) $query->where('route_id', $request->route_id);
        if ($request->filled('month') && $request->filled('year')) {
            $query->where('month', $request->month)->where('year', $request->year);
        }

        $fees = $query->orderByDesc('year')->orderByDesc('month')->paginate(25)->withQueryString();
        $routes = \App\Models\BusRoute::orderBy('name')->get(['id', 'name']);

        return view('transport.fees.index', compact('fees', 'routes'));
    }

    public function pay(Request $request, TransportFee $fee)
    {
        $data = $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:40',
            'reference'      => 'nullable|string|max:100',
            'payment_date'   => 'required|date',
            'note'           => 'nullable|string|max:500',
        ]);

        if ($data['amount'] > $fee->balance) {
            return back()->withInput()->with('error', 'Payment amount cannot exceed the outstanding balance (' . number_format($fee->balance) . ').');
        }

        DB::transaction(function () use ($fee, $data) {
            TransportPayment::create([
                'transport_fee_id' => $fee->id,
                'amount'           => $data['amount'],
                'payment_method'   => $data['payment_method'],
                'reference'        => $data['reference'] ?? null,
                'payment_date'     => $data['payment_date'],
                'recorded_by'      => Auth::id(),
                'note'             => $data['note'] ?? null,
            ]);

            $fee->refresh();
            $newPaid = $fee->amount_paid + $data['amount'];
            $newBalance = $fee->amount - $newPaid;

            $fee->update([
                'amount_paid' => $newPaid,
                'balance'     => $newBalance,
                'status'      => $newBalance <= 0 ? 'paid' : ($newPaid > 0 ? 'partial' : 'unpaid'),
            ]);
        });

        return back()->with('success', 'Payment recorded.');
    }
}
