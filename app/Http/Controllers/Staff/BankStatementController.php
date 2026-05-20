<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\BankStatement;
use Illuminate\Support\Facades\Auth;

class BankStatementController extends Controller
{
    public function index()
    {
        $staff = Auth::user()->staff;
        $statements = BankStatement::where('staff_id', $staff->id)->latest()->get();
        return view('staff.bank-statements.index', compact('statements'));
    }
}