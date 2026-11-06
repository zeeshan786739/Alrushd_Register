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

    public function query(): Builder
    {
        return $this->query->with('assignedAdmin');
    }

    public function headings(): array
    {
        return [
            'ID', 'Name', 'Email', 'Phone', 'Year Group', 'Admission Intake', 'Contact Method', 'Source', 'Lead Source', 'Status',
            'Assigned To', 'Estimated Value', 'Follow Up Date', 'Converted', 'Created At',
            ...array_column(\App\Support\LeadSettings::customFilters(), 'label'),
        ];
    }

    public function map($lead): array
    {
        return [
            $lead->id,
            $lead->full_name,
            $lead->email,
            $lead->phone,
            implode(', ', $lead->childYearGroups()),
            $lead->admission_intake,
            $lead->contact_method,
            $lead->source,
            $lead->lead_source,
            \App\Support\LeadStatusCatalog::label($lead->lead_status),
            $lead->assignedAdmin?->name,
            $lead->estimated_value,
            $lead->next_follow_up_date,
            $lead->is_converted ? 'Yes' : 'No',
            $lead->created_at,
            ...array_map(fn (array $filter) => $lead->custom_fields[$filter['key']] ?? null, \App\Support\LeadSettings::customFilters()),
        ];
    }
}
