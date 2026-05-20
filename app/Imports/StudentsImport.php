<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Hash;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation
{
    private $skipDuplicates;
    private $successCount = 0;
    private $errors = [];

    public function __construct($skipDuplicates = true)
    {
        $this->skipDuplicates = $skipDuplicates;
    }

    public function model(array $row)
    {
        // Check for duplicate admission number
        if ($this->skipDuplicates && Student::where('admission_no', $row['admission_no'])->exists()) {
            $this->errors[] = "Skipped duplicate admission_no: {$row['admission_no']}";
            return null;
        }

        $this->successCount++;
        return new Student([
            'admission_no'         => $row['admission_no'],
            'first_name'           => $row['first_name'],
            'last_name'            => $row['last_name'],
            'gender'               => $row['gender'] ?? null,
            'date_of_birth'        => $row['date_of_birth'] ?? null,
            'guardian_id'          => $row['guardian_id'] ?? null,
            'class_id'             => $row['class_id'] ?? null,
            'dormitory_id'         => $row['dormitory_id'] ?? null,
            'academic_session_id'  => $row['academic_session_id'] ?? null,
            // You may add other fields like 'password' if needed
        ]);
    }

    public function rules(): array
    {
        return [
            'admission_no' => 'required|unique:students,admission_no',
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'gender'       => 'nullable|in:male,female',
            'date_of_birth'=> 'nullable|date',
            'guardian_id'  => 'nullable|exists:guardians,id',
            'class_id'     => 'nullable|exists:school_classes,id',
            'dormitory_id' => 'nullable|exists:dormitories,id',
            'academic_session_id' => 'nullable|exists:academic_sessions,id',
        ];
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}