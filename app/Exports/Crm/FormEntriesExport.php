<?php

namespace App\Exports\Crm;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FormEntriesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private Builder $query)
    {
    }

    public function query(): Builder
    {
        return $this->query;
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return ['ID', 'Form', 'Status', 'Submitted At', 'Preview'];
    }

    /** @return array<int, mixed> */
    public function map($entry): array
    {
        $data = is_array($entry->data) ? $entry->data : [];
        $preview = collect($data)->take(3)->map(fn ($v, $k) => $k.': '.$v)->implode(' | ');

        return [
            $entry->id,
            $entry->form?->name ?? '—',
            $entry->status,
            optional($entry->submitted_at)->format('Y-m-d H:i'),
            $preview,
        ];
    }
}
