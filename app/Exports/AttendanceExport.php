<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendanceExport implements FromCollection, WithHeadings
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Attendance::with('staff')->orderBy('date', 'desc');

        if (!empty($this->filters['date_from']) && !empty($this->filters['date_to'])) {
            $query->whereBetween('date', [$this->filters['date_from'], $this->filters['date_to']]);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['staff_name'])) {
            $query->whereHas('staff', function ($q) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$this->filters['staff_name']}%"]);
            });
        }

        return $query->get()->map(function ($item) {
            return [
                'Staff Name' => $item->staff->name,
                'Date' => $item->date->format('Y-m-d'),
                'Status' => ucfirst($item->status),
            ];
        });
    }

    public function headings(): array
    {
        return ['Staff Name', 'Date', 'Status'];
    }
}
