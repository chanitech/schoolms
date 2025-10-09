<?php

namespace App\Exports;

use App\Models\Leave;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeavesExport implements FromCollection, WithHeadings
{
    protected $staffId;
    protected $filters;

    public function __construct($staffId, $filters = [])
    {
        $this->staffId = $staffId;
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Leave::with('requester')->where('requested_to', $this->staffId);

        if (!empty($this->filters['staff_name'])) {
            $query->whereHas('requester', fn($q) => 
                $q->where('first_name', 'like', '%'.$this->filters['staff_name'].'%')
                  ->orWhere('last_name', 'like', '%'.$this->filters['staff_name'].'%')
            );
        }
        if (!empty($this->filters['start_date_from'])) $query->where('start_date', '>=', $this->filters['start_date_from']);
        if (!empty($this->filters['start_date_to'])) $query->where('start_date', '<=', $this->filters['start_date_to']);
        if (!empty($this->filters['status'])) $query->where('status', $this->filters['status']);

        return $query->get()->map(function($leave) {
            return [
                'Staff' => $leave->requester->name,
                'Start Date' => $leave->start_date->format('Y-m-d'),
                'End Date' => $leave->end_date->format('Y-m-d'),
                'Type' => ucfirst($leave->type),
                'Status' => ucfirst($leave->status),
                'Reason' => $leave->reason,
            ];
        });
    }

    public function headings(): array
    {
        return ['Staff', 'Start Date', 'End Date', 'Type', 'Status', 'Reason'];
    }
}
