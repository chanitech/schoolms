<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentsTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['ADM001', 'John', 'Doe', 'male', '2010-05-15', '1', '1', '1', '1'],
            ['ADM002', 'Jane', 'Smith', 'female', '2010-08-22', '2', '2', '', '1'],
        ];
    }

    public function headings(): array
    {
        return [
            'admission_no',
            'first_name',
            'last_name',
            'gender',
            'date_of_birth',
            'guardian_id',
            'class_id',
            'dormitory_id',
            'academic_session_id'
        ];
    }
}