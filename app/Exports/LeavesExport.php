<?php
namespace App\Exports;

use App\Models\Leave;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeavesExport implements FromCollection, WithHeadings
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Leave::with(['requester.department']);

        if (!empty($this->filters['staff_name'])) {
            $query->whereHas('requester', fn($q) => $q->where('name', 'like', '%'.$this->filters['staff_name'].'%'));
        }
        if (!empty($this->filters['department_id'])) {
            $query->whereHas('requester', fn($q) => $q->where('department_id', $this->filters['department_id']));
        }
        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('start_date', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('end_date', '<=', $this->filters['date_to']);
        }

        return $query->get()->map(function ($leave) {
            return [
                'Staff Name' => $leave->requester->name,
                'Department' => $leave->requester->department->name ?? 'N/A',
                'Leave Type' => $leave->type,
                'Start Date' => $leave->start_date,
                'End Date' => $leave->end_date,
                'Total Days' => $leave->total_days,
                'Status' => ucfirst($leave->status),
                'Reason' => $leave->reason,
            ];
        });
    }

    public function headings(): array
    {
        return ['Staff Name', 'Department', 'Leave Type', 'Start Date', 'End Date', 'Total Days', 'Status', 'Reason'];
    }
}
