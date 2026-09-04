<?php

namespace App\Services\Crm\LeadImport;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv as CsvReader;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use PhpOffice\PhpSpreadsheet\Reader\Xls as XlsReader;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Reader\Xml as XmlReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;

class LeadImportFileReader
{
    /**
     * @return array{
     *     format: string,
     *     sheets: array<int, array{name: string, row_count: int, has_data: bool}>,
     *     selected_sheet: string,
     *     header_row: int,
     *     data_start_row: int,
     *     headers: array<int, array{key: string, label: string, index: int}>,
     *     rows: array<int, array{row_number: int, values: array<string, mixed>}>,
     *     sample_values: array<string, array<int, string>>
     * }
     */
    public function read(string $path, ?string $sheetName = null, ?int $headerRow = null, ?int $maxRows = null): array
    {
        $maxRows ??= (int) config('lead_import.max_rows', 10000);
        $format = $this->detectFormat($path);
        $reader = $this->makeReader($format);
        $reader->setReadDataOnly(true);

        if ($reader instanceof CsvReader) {
            $this->configureCsv($reader, $path);
        }

        $spreadsheet = $reader->load($path);

        try {
            $sheet = $this->selectSheet($spreadsheet, $sheetName);
            $sheets = $this->describeSheets($spreadsheet);
            $highestColumn = $sheet->getHighestDataColumn();
            $highestRow = (int) $sheet->getHighestDataRow();
            $columnCount = Coordinate::columnIndexFromString($highestColumn);
            $detectedHeaderRow = $headerRow ?? $this->detectHeaderRow($sheet, $columnCount, $highestRow);
            $headers = $this->readHeaders($sheet, $detectedHeaderRow, $columnCount);

            if ($headers === []) {
                throw new RuntimeException('No header row could be detected in the selected sheet.');
            }

            $rows = [];
            $sampleValues = [];
            $scanLastRow = min($highestRow, $detectedHeaderRow + $maxRows);
            $dataStartRow = $this->detectDataStartRow($sheet, $headers, $detectedHeaderRow, $scanLastRow);
            $lastRow = min($highestRow, $dataStartRow + $maxRows - 1);

            for ($row = $dataStartRow; $row <= $lastRow; $row++) {
                $values = [];
                $empty = true;
                foreach ($headers as $header) {
                    $cell = $sheet->getCell(Coordinate::stringFromColumnIndex($header['index'] + 1).$row);
                    $raw = $this->cellValue($cell);
                    $values[$header['key']] = $raw;
                    if ($this->hasContent($raw)) {
                        $empty = false;
                    }
                    if (count($sampleValues[$header['key']] ?? []) < 3 && $this->hasContent($raw)) {
                        $sampleValues[$header['key']][] = $this->sampleString($raw);
                    }
                }

                if ($empty) {
                    continue;
                }

                // Some administration sheets use an otherwise unnamed first column
                // for date-only section dividers. They are layout, not lead records.
                if ($this->isDateSectionDivider($headers, $values)) {
                    continue;
                }

                $rows[] = [
                    'row_number' => $row,
                    'values' => $values,
                ];
            }

            if (count($rows) > $maxRows) {
                throw new RuntimeException('This file exceeds the maximum of '.$maxRows.' data rows.');
            }

            return [
                'format' => $format,
                'sheets' => $sheets,
                'selected_sheet' => $sheet->getTitle(),
                'header_row' => $detectedHeaderRow,
                'data_start_row' => $dataStartRow,
                'headers' => $headers,
                'rows' => $rows,
                'sample_values' => $sampleValues,
            ];
        } finally {
            $spreadsheet->disconnectWorksheets();
        }
    }

    public function detectFormat(string $path): string
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Unable to read the uploaded file.');
        }

        $head = (string) fread($handle, 2048);
        fclose($handle);

        $trimmed = ltrim($head);
        if (str_starts_with($head, 'PK')) {
            return 'xlsx';
        }

        if (str_starts_with($trimmed, '<?xml') || str_contains($head, 'urn:schemas-microsoft-com:office:spreadsheet') || str_contains($head, 'SpreadsheetML')) {
            return 'spreadsheetml';
        }

        $ole = strtoupper(bin2hex(substr($head, 0, 8)));
        if (str_starts_with($ole, 'D0CF11E0A1B11AE1')) {
            return 'xls';
        }

        return 'csv';
    }

    private function makeReader(string $format): IReader
    {
        return match ($format) {
            'xlsx' => new XlsxReader,
            'xls' => new XlsReader,
            'spreadsheetml' => new XmlReader,
            'csv' => new CsvReader,
            default => IOFactory::createReader('Csv'),
        };
    }

    private function configureCsv(CsvReader $reader, string $path): void
    {
        $reader->setInputEncoding('UTF-8');
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return;
        }
        $line = (string) fgets($handle);
        fclose($handle);
        $line = preg_replace('/^\xEF\xBB\xBF/', '', $line) ?? $line;

        $counts = [
            ',' => substr_count($line, ','),
            ';' => substr_count($line, ';'),
            "\t" => substr_count($line, "\t"),
            '|' => substr_count($line, '|'),
        ];
        arsort($counts);
        $delimiter = (string) array_key_first($counts);
        $reader->setDelimiter(($counts[$delimiter] ?? 0) > 0 ? $delimiter : ',');
    }

    /**
     * @return array<int, array{name: string, row_count: int, has_data: bool}>
     */
    private function describeSheets(Spreadsheet $spreadsheet): array
    {
        $sheets = [];
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $rows = (int) $sheet->getHighestDataRow();
            $sheets[] = [
                'name' => $sheet->getTitle(),
                'row_count' => $rows,
                'has_data' => $rows > 1,
            ];
        }

        return $sheets;
    }

    private function selectSheet(Spreadsheet $spreadsheet, ?string $sheetName): Worksheet
    {
        if ($sheetName) {
            $named = $spreadsheet->getSheetByName($sheetName);
            if ($named) {
                return $named;
            }
        }

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            if ((int) $sheet->getHighestDataRow() > 1) {
                return $sheet;
            }
        }

        return $spreadsheet->getActiveSheet();
    }

    private function detectHeaderRow(Worksheet $sheet, int $columnCount, int $highestRow): int
    {
        $limit = min($highestRow, 20);
        $bestRow = 1;
        $bestScore = -1;

        for ($row = 1; $row <= $limit; $row++) {
            $nonEmpty = 0;
            $textish = 0;
            for ($col = 1; $col <= min($columnCount, 40); $col++) {
                $value = $this->cellValue($sheet->getCell(Coordinate::stringFromColumnIndex($col).$row));
                if (! $this->hasContent($value)) {
                    continue;
                }
                $nonEmpty++;
                if (is_string($value) && ! is_numeric($value)) {
                    $textish++;
                }
            }
            $score = $nonEmpty * 2 + $textish;
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestRow = $row;
            }
        }

        return $bestRow;
    }

    /**
     * @return array<int, array{key: string, label: string, index: int}>
     */
    private function readHeaders(Worksheet $sheet, int $headerRow, int $columnCount): array
    {
        $headers = [];
        $labels = [];
        $lastUsed = 0;
        for ($col = 1; $col <= $columnCount; $col++) {
            $label = $this->cellValue($sheet->getCell(Coordinate::stringFromColumnIndex($col).$headerRow));
            $label = is_scalar($label) ? trim((string) $label) : '';
            $labels[$col] = $label;
            if ($label !== '') {
                $lastUsed = $col;
            }
        }

        // The administration workbook intentionally leaves A1 blank although
        // column A contains the assigned team member. Infer that header only
        // when the neighbouring identity columns make the layout unambiguous.
        if (($labels[1] ?? '') === ''
            && $this->headerLooksLike($labels[2] ?? '', ['contact_no', 'contact_number', 'phone'])
            && $this->headerLooksLike($labels[3] ?? '', ['email', 'email_address'])
            && $this->headerLooksLike($labels[4] ?? '', ['student_name', 'name'])) {
            $labels[1] = 'Assigned team member';
        }

        for ($col = 1; $col <= $lastUsed; $col++) {
            $label = $labels[$col] ?? '';
            if ($label === '') {
                $label = 'Column '.$col;
            }
            $headers[] = [
                'key' => 'col_'.($col - 1),
                'label' => $label,
                'index' => $col - 1,
            ];
        }

        return $headers;
    }

    /**
     * Find the first genuine table row while preserving sparse rows once the
     * data region has begun. This excludes legends placed between the header
     * and the records without requiring users to alter their workbook.
     *
     * @param  array<int, array{key: string, label: string, index: int}>  $headers
     */
    private function detectDataStartRow(Worksheet $sheet, array $headers, int $headerRow, int $lastRow): int
    {
        $minimumCells = min(3, max(1, (int) ceil(count($headers) * 0.15)));
        $scanUntil = min($lastRow, $headerRow + 100);

        for ($row = $headerRow + 1; $row <= $scanUntil; $row++) {
            $populated = 0;
            foreach ($headers as $header) {
                $value = $this->cellValue($sheet->getCell(
                    Coordinate::stringFromColumnIndex($header['index'] + 1).$row
                ));
                if ($this->hasContent($value)) {
                    $populated++;
                }
            }

            if ($populated >= $minimumCells) {
                return $row;
            }
        }

        return $headerRow + 1;
    }

    /**
     * @param  array<int, array{key: string, label: string, index: int}>  $headers
     * @param  array<string, mixed>  $values
     */
    private function isDateSectionDivider(array $headers, array $values): bool
    {
        $populated = array_filter($values, fn (mixed $value): bool => $this->hasContent($value));
        if (count($populated) !== 1) {
            return false;
        }

        $key = (string) array_key_first($populated);
        $header = null;
        foreach ($headers as $candidate) {
            if ($candidate['key'] === $key) {
                $header = $candidate;
                break;
            }
        }
        if (! is_array($header) || $header['index'] !== 0) {
            return false;
        }

        $value = $populated[$key];

        return is_numeric($value) && (float) $value > 20000 && (float) $value < 80000;
    }

    /** @param array<int, string> $expected */
    private function headerLooksLike(string $label, array $expected): bool
    {
        $normalized = mb_strtolower(trim($label));
        $normalized = preg_replace('/[^\pL\pN]+/u', '_', $normalized) ?? $normalized;
        $normalized = trim($normalized, '_');

        return in_array($normalized, $expected, true);
    }

    private function cellValue(Cell $cell): mixed
    {
        $value = $cell->getValue();
        if (is_string($value)) {
            return $value;
        }

        if (is_float($value) || is_int($value)) {
            if (is_float($value) && abs($value - round($value)) < 0.0000001 && $value >= 1e8) {
                return (string) (int) round($value);
            }
        }

        return $value;
    }

    private function hasContent(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }
        if (is_string($value)) {
            return trim($value) !== '';
        }

        return true;
    }

    private function sampleString(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_scalar($value)) {
            return mb_substr((string) $value, 0, 120);
        }

        return '';
    }
}
