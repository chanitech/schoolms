<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EvaluationExport implements FromView
{
    protected $evaluations;

    public function __construct($evaluations)
    {
        $this->evaluations = $evaluations;
    }

    public function view(): View
    {
        return view('hr-reports.evaluation-excel', [
            'evaluations' => $this->evaluations
        ]);
    }
}
