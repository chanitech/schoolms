<?php
// app/Http/Controllers/Treasurer/BankStatementController.php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\BankStatement;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class BankStatementController extends Controller
{
    public function index()
    {
        $statements = BankStatement::with('staff', 'uploader')->latest()->get();
        return view('treasurer.bank-statements.index', compact('statements'));
    }

    public function create()
    {
        $staffList = Staff::all();
        return view('treasurer.bank-statements.create', compact('staffList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'statement_month' => 'required|date_format:Y-m-d',
            'file' => 'required|file|mimes:pdf,jpg,png|max:5120',
        ]);

        // Check for duplicate month
        $exists = BankStatement::where('staff_id', $request->staff_id)
                               ->where('statement_month', $request->statement_month)
                               ->exists();
        if ($exists) {
            return back()->with('error', 'Statement for this month already uploaded.');
        }

        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : 'unassigned';
        $file = $request->file('file');
        $path = $file->store("schools/{$schoolId}/bank_statements", 'public');
        
        BankStatement::create([
            'staff_id' => $request->staff_id,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'statement_month' => $request->statement_month,
            'uploaded_by' => Auth::user()->id,
        ]);

        return redirect()->route('treasurer.bank-statements.index')->with('success', 'Bank statement uploaded.');
    }

    public function destroy(BankStatement $bankStatement)
    {
        Storage::disk('public')->delete($bankStatement->file_path);
        $bankStatement->delete();
        return back()->with('success', 'Bank statement deleted.');
    }
}