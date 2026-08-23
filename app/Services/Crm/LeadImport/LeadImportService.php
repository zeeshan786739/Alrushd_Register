<?php

namespace App\Services\Crm\LeadImport;

use App\Enums\LeadImportRowStatus;
use App\Enums\LeadImportStatus;
use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Models\Admin;
use App\Models\Crm\LeadImport;
use App\Models\Crm\LeadImportProfile;
use App\Models\Crm\LeadImportRow;
use App\Support\LeadImportFields;
use App\Support\OrganizationContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class LeadImportService
{
    public function __construct(
        private LeadImportFileReader $reader,
        private LeadImportHeaderNormalizer $normalizer,
        private LeadImportMappingService $mappingService,
        private LeadImportValueNormalizer $valueNormalizer,
        private LeadImportValidator $validator,
        private LeadImportDuplicateDetector $duplicateDetector,
        private LeadImportProcessor $processor,
    ) {}

    public function createFromUpload(UploadedFile $file, Admin $admin): LeadImport
    {
        $organizationId = OrganizationContext::idOrFail();
        $maxBytes = (int) config('lead_import.max_bytes', 10485760);
        if ($file->getSize() > $maxBytes) {
            throw new RuntimeException('The file exceeds the maximum upload size.');
        }

        $disk = (string) config('lead_import.disk', 'local');
        $hash = hash_file('sha256', $file->getRealPath());
        $path = 'lead-imports/'.$organizationId.'/'.Str::uuid()->toString().'.bin';
        Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()) ?: '');

        $absolute = Storage::disk($disk)->path($path);
        $parsed = $this->reader->read($absolute);

        $import = LeadImport::create([
            'organization_id' => $organizationId,
            'uploaded_by' => $admin->id,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'file_hash' => $hash,
            'detected_format' => $parsed['format'],
            'selected_sheet' => $parsed['selected_sheet'],
            'header_row' => $parsed['header_row'],
            'detected_headers' => $parsed['headers'],
            'mapping' => $this->defaultMapping($parsed['headers'], $parsed['sample_values']),
            'import_options' => $this->defaultOptions(),
            'status' => LeadImportStatus::Uploaded->value,
            'total_rows' => count($parsed['rows']),
        ]);

        $signature = $this->normalizer->signature($parsed['headers']);
        $profile = LeadImportProfile::query()
            ->where('organization_id', $organizationId)
            ->where('header_signature', $signature)
            ->first();

        if ($profile && is_array($profile->mapping)) {
            $import->update([
                'mapping' => $this->mappingService->sanitize($profile->mapping),
                'import_options' => array_merge($import->import_options ?? [], $profile->options ?? []),
            ]);
            $profile->update(['last_used_at' => now()]);
        }

        return $import->fresh();
    }

    public function parsedWorkbook(LeadImport $import, ?string $sheet = null, ?int $headerRow = null): array
    {
        $disk = (string) config('lead_import.disk', 'local');
        $absolute = Storage::disk($disk)->path($import->stored_path);

        return $this->reader->read(
            $absolute,
            $sheet ?? $import->selected_sheet,
            $headerRow ?? $import->header_row,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function saveMapping(LeadImport $import, array $payload): LeadImport
    {
        $parsed = $this->parsedWorkbook(
            $import,
            $payload['selected_sheet'] ?? $import->selected_sheet,
            isset($payload['header_row']) ? (int) $payload['header_row'] : $import->header_row,
        );

        $mapping = $this->mappingService->sanitize($payload['mapping'] ?? []);
        foreach ($parsed['headers'] as $header) {
            if (! array_key_exists($header['key'], $mapping)) {
                $mapping[$header['key']] = LeadImportFields::CUSTOM;
            }
        }

        $options = array_merge($this->defaultOptions(), $import->import_options ?? [], $payload['options'] ?? []);

        $import->update([
            'selected_sheet' => $parsed['selected_sheet'],
            'header_row' => $parsed['header_row'],
            'detected_headers' => $parsed['headers'],
            'mapping' => $mapping,
            'import_options' => $options,
            'status' => LeadImportStatus::Mapped->value,
            'total_rows' => count($parsed['rows']),
        ]);

        $this->buildPreview($import->fresh(), $parsed);

        $this->rememberProfile($import->fresh(), $options);

        return $import->fresh();
    }

    public function confirm(LeadImport $import): LeadImport
    {
        if ($import->rows()->count() === 0) {
            $this->buildPreview($import, $this->parsedWorkbook($import));
            $import = $import->fresh();
        }

        return $this->processor->process($import);
    }

    public function mappingSuggestions(LeadImport $import): array
    {
        $parsed = $this->parsedWorkbook($import);

        return [
            'parsed' => $parsed,
            'suggestions' => $this->mappingService->suggest($parsed['headers'], $parsed['sample_values']),
        ];
    }

    /**
     * @param  array<int, array{key: string, label: string, index: int}>  $headers
     * @param  array<string, array<int, string>>  $samples
     * @return array<string, string>
     */
    private function defaultMapping(array $headers, array $samples): array
    {
        $suggestions = $this->mappingService->suggest($headers, $samples);
        $mapping = [];
        foreach ($headers as $header) {
            $mapping[$header['key']] = $suggestions[$header['key']]['field'] ?? LeadImportFields::CUSTOM;
        }

        return $mapping;
    }

    /** @return array<string, mixed> */
    private function defaultOptions(): array
    {
        return [
            'duplicate_behavior' => 'skip',
            'default_status' => LeadStatus::New->value,
            'default_priority' => LeadPriority::Medium->value,
            'default_assigned_to' => null,
            'source_label' => null,
            'default_calling_code' => null,
            'date_format' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $parsed
     */
    private function buildPreview(LeadImport $import, array $parsed): void
    {
        $import->rows()->delete();

        $prepared = [];
        foreach ($parsed['rows'] as $row) {
            $normalized = $this->valueNormalizer->normalize(
                $parsed['headers'],
                $import->mapping ?? [],
                $row['values'],
                $import->import_options ?? [],
            );
            $validated = $this->validator->validate($normalized['fields'], $normalized['warnings']);
            $rowHash = hash('sha256', json_encode($row['values'], JSON_UNESCAPED_UNICODE) ?: '');
            $prepared[] = [
                'row_number' => $row['row_number'],
                'email' => $normalized['fields']['email'] ?? null,
                'phone' => $normalized['fields']['phone'] ?? null,
                'row_hash' => $rowHash,
                'raw' => $this->jsonSafe($row['values']),
                'normalized' => [
                    'fields' => $this->jsonSafe($normalized['fields']),
                    'custom' => $normalized['custom'],
                ],
                'status' => $validated['status'],
                'warnings' => $validated['warnings'],
                'errors' => $validated['errors'],
            ];
        }

        $dupes = $this->duplicateDetector->detect($import, $prepared);
        $counts = [
            'ready' => 0,
            'warning' => 0,
            'duplicate' => 0,
            'invalid' => 0,
        ];

        foreach ($prepared as $index => $row) {
            $status = $row['status'];
            $warnings = $row['warnings'];
            $errors = $row['errors'];
            if (isset($dupes[$index]) && $status !== LeadImportRowStatus::Invalid->value) {
                $status = LeadImportRowStatus::Duplicate->value;
                $warnings[] = $dupes[$index]['reason'];
            }

            match ($status) {
                LeadImportRowStatus::Ready->value => $counts['ready']++,
                LeadImportRowStatus::Warning->value => $counts['warning']++,
                LeadImportRowStatus::Duplicate->value => $counts['duplicate']++,
                LeadImportRowStatus::Invalid->value => $counts['invalid']++,
                default => null,
            };

            LeadImportRow::create([
                'organization_id' => $import->organization_id,
                'lead_import_id' => $import->id,
                'row_number' => $row['row_number'],
                'row_hash' => $row['row_hash'],
                'raw_data' => $row['raw'],
                'normalized_data' => $row['normalized'],
                'status' => $status,
                'warnings' => $warnings ?: null,
                'errors' => $errors ?: null,
            ]);
        }

        $import->update([
            'status' => LeadImportStatus::Previewed->value,
            'total_rows' => count($prepared),
            'ready_rows' => $counts['ready'] + $counts['warning'],
            'warning_rows' => $counts['warning'],
            'duplicate_rows' => $counts['duplicate'],
            'failed_rows' => $counts['invalid'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function rememberProfile(LeadImport $import, array $options): void
    {
        $headers = $import->detected_headers ?? [];
        if ($headers === []) {
            return;
        }

        LeadImportProfile::updateOrCreate(
            [
                'organization_id' => $import->organization_id,
                'header_signature' => $this->normalizer->signature($headers),
            ],
            [
                'name' => $import->original_filename,
                'mapping' => $import->mapping,
                'options' => $options,
                'last_used_at' => now(),
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function jsonSafe(array $values): array
    {
        $safe = [];
        foreach ($values as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $safe[$key] = $value->format('c');
            } elseif (is_scalar($value) || $value === null) {
                $safe[$key] = $value;
            } else {
                $safe[$key] = (string) json_encode($value);
            }
        }

        return $safe;
    }
}
