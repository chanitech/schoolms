<?php

namespace App\Http\Controllers;

use App\Models\InterestInventory;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InterestInventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // List / index (with optional filters)
    public function index(Request $request)
    {
        $query = InterestInventory::with(['student', 'creator']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $records = $query->latest()->paginate(15);

        $students = Student::orderBy('first_name')->get();

        return view('counseling.interest_inventory.index', compact('records', 'students'));
    }

    // show create form
    public function create()
    {
        $students = Student::orderBy('first_name')->get();
        return view('counseling.interest_inventory.create', compact('students'));
    }

    // store
    public function store(Request $request)
    {
        $rules = [
            'student_id' => 'required|exists:students,id',
            'date' => 'nullable|date',
        ];
        for ($i = 1; $i <= 17; $i++) {
            $rules["q{$i}"] = 'nullable|string';
        }
        $request->validate($rules);

        $data = $request->only(array_merge(['student_id','date'], array_map(fn($i)=>"q{$i}", range(1,17))));
        $data['created_by'] = Auth::id();

        InterestInventory::create($data);

        return redirect()->route('interest-inventories.index')
            ->with('success', 'Interest Inventory saved successfully.');
    }

    // show single
    public function show(InterestInventory $interestInventory)
    {
        return view('counseling.interest_inventory.show', compact('interestInventory'));
    }

    // edit
    public function edit(InterestInventory $interestInventory)
    {
        $students = Student::orderBy('first_name')->get();
        return view('counseling.interest_inventory.edit', compact('interestInventory', 'students'));
    }

    // update
    public function update(Request $request, InterestInventory $interestInventory)
    {
        $rules = [
            'student_id' => 'required|exists:students,id',
            'date' => 'nullable|date',
        ];
        for ($i = 1; $i <= 17; $i++) {
            $rules["q{$i}"] = 'nullable|string';
        }
        $request->validate($rules);

        $data = $request->only(array_merge(['student_id','date'], array_map(fn($i)=>"q{$i}", range(1,17))));
        $interestInventory->update($data);

        return redirect()->route('interest-inventories.index')
            ->with('success', 'Interest Inventory updated successfully.');
    }

    // destroy
    public function destroy(InterestInventory $interestInventory)
    {
        $interestInventory->delete();
        return redirect()->route('interest-inventories.index')->with('success', 'Record deleted.');
    }

    // OPTIONAL: export single record to PDF (requires barryvdh/laravel-dompdf or similar)
    public function exportPdf(InterestInventory $interestInventory)
    {
        // if you install barryvdh/laravel-dompdf, you can uncomment and use:
        // $pdf = \PDF::loadView('counseling.interest_inventory.pdf', compact('interestInventory'));
        // return $pdf->download("interest_inventory_{$interestInventory->id}.pdf");

        abort(501, 'PDF export not implemented. Install dompdf and uncomment code in controller.');
    }
}
