<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\LoanCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanCategoryController extends Controller
{
    public function index()
    {
        $categories = LoanCategory::with('creator')->get();
        return view('treasurer.loan-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('treasurer.loan-categories.create');
    }

    public function store(Request $request)
    {
        // Convert JSON strings to arrays (empty string becomes empty array)
        $request->merge([
            'eligibility_criteria' => json_decode($request->eligibility_criteria, true) ?? [],
            'restrictions'          => json_decode($request->restrictions, true) ?? [],
        ]);

        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'min_amount'           => 'required|numeric|min:0',
            'max_amount'           => 'required|numeric|gt:min_amount',
            'max_installments'     => 'required|integer|min:1',
            'interest_rate'        => 'required|numeric|min:0',
            'eligibility_criteria' => 'nullable|array',
            'restrictions'         => 'nullable|array',
            'is_active'            => 'boolean',
        ]);

        $validated['created_by_treasurer_id'] = Auth::user()->id;
        LoanCategory::create($validated);

        return redirect()->route('treasurer.loan-categories.index')
                         ->with('success', 'Loan category created.');
    }

    public function edit(LoanCategory $loanCategory)
    {
        return view('treasurer.loan-categories.edit', compact('loanCategory'));
    }

    public function update(Request $request, LoanCategory $loanCategory)
    {
        $request->merge([
            'eligibility_criteria' => json_decode($request->eligibility_criteria, true) ?? [],
            'restrictions'          => json_decode($request->restrictions, true) ?? [],
        ]);

        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'min_amount'           => 'required|numeric|min:0',
            'max_amount'           => 'required|numeric|gt:min_amount',
            'max_installments'     => 'required|integer|min:1',
            'interest_rate'        => 'required|numeric|min:0',
            'eligibility_criteria' => 'nullable|array',
            'restrictions'         => 'nullable|array',
            'is_active'            => 'boolean',
        ]);

        $loanCategory->update($validated);

        return redirect()->route('treasurer.loan-categories.index')
                         ->with('success', 'Loan category updated.');
    }

    public function destroy(LoanCategory $loanCategory)
    {
        if ($loanCategory->loans()->exists()) {
            return back()->with('error', 'Cannot delete category with existing loans.');
        }
        $loanCategory->delete();
        return redirect()->route('treasurer.loan-categories.index')
                         ->with('success', 'Loan category deleted.');
    }
}