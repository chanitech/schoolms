<?php

namespace App\Exports;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EventsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Event::with('department')->get()->map(function($event) {
            return [
                'Title' => $event->title,
                'Department' => $event->department?->name ?? 'All',
                'Type' => ucfirst($event->type),
                'Start Date' => $event->start_date->format('Y-m-d'),
                'End Date' => $event->end_date->format('Y-m-d'),
                'Description' => $event->description,
            ];
        });
    }

    public function headings(): array
    {
        return ['Title', 'Department', 'Type', 'Start Date', 'End Date', 'Description'];
    }
}
