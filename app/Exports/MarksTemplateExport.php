<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MarksTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [170, '2024-001', 78],
            [171, '2024-002', 92],
            [172, '2024-003', 65],
        ];
    }

    public function headings(): array
    {
        return ['student_id', 'admission_no', 'mark'];
    }
}