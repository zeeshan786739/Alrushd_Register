<?php

namespace App\Exports\Crm;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeadsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private Builder $query) {}

    public function query()
    {
        return $this->query->with('assignedAdmin');
    }

    public function headings(): array
    {
        return [
            'ID', 'Name', 'Email', 'Phone', 'Source', 'Lead Source', 'Status', 'Priority',
            'Assigned To', 'Estimated Value', 'Follow Up Date', 'Converted', 'Created At',
        ];
    }

    public function map($lead): array
    {
        return [
            $lead->id,
            $lead->full_name,
            $lead->email,
            $lead->phone,
            $lead->source,
            $lead->lead_source,
            $lead->lead_status,
            $lead->priority,
            $lead->assignedAdmin?->name,
            $lead->estimated_value,
            $lead->next_follow_up_date,
            $lead->is_converted ? 'Yes' : 'No',
            $lead->created_at,
        ];
    }
}
