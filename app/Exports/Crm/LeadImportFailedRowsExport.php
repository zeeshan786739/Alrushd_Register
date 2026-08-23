<?php

namespace App\Exports\Crm;

use App\Models\Crm\LeadImport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeadImportFailedRowsExport implements FromCollection, WithHeadings
{
    public function __construct(private LeadImport $import, private Collection $rows) {}

    public function headings(): array
    {
        return ['Row', 'Status', 'Errors', 'Warnings'];
    }

    public function collection(): Collection
    {
        return $this->rows->map(fn ($row) => [
            $row->row_number,
            $row->status,
            implode('; ', $row->errors ?? []),
            implode('; ', $row->warnings ?? []),
        ]);
    }
}
