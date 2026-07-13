<?php

namespace App\Services;

use App\Models\Debit;
use App\Models\Enquire;
use App\Models\Form;
use App\Models\FormEntry;
use App\Models\JobApplication;
use App\Models\Metting;
use App\Models\Referral;
use App\Models\StaffAdmissionForm;
use Illuminate\Support\Carbon;

class LegacyFormDataMigrator
{
    public function migrateAll(): array
    {
        $counts = [];

        $counts['job_applications'] = $this->migrateModel(JobApplication::class, 'job-applications', 'job_applications', function ($row) {
            $data = $row->toArray();
            unset($data['id'], $data['created_at'], $data['updated_at']);

            return [
                'entry_id' => $row->id,
                'data' => $data,
                'status' => $this->normalizeStatus($row->status ?? 'pending'),
                'submitted_at' => $row->date_of_submission ?? $row->created_at,
            ];
        });

        $counts['staff_admission_forms'] = $this->migrateModel(StaffAdmissionForm::class, 'staff-application', 'staff_admission_forms', function ($row) {
            $data = $row->toArray();
            unset($data['id'], $data['created_at'], $data['updated_at']);

            return [
                'entry_id' => $row->id,
                'data' => $data,
                'status' => $this->normalizeStatus($row->status ?? 'pending'),
                'submitted_at' => $row->created_at,
            ];
        });

        $counts['enquires'] = $this->migrateModel(Enquire::class, 'enquire-now', 'enquires', function ($row) {
            $data = $row->toArray();
            unset($data['id'], $data['created_at'], $data['updated_at']);

            return [
                'entry_id' => $row->entry_id ?? $row->id,
                'data' => $data,
                'status' => 'pending',
                'submitted_at' => $this->parseDate($row->submission_date) ?? $row->created_at,
            ];
        });

        $counts['referrals'] = $this->migrateModel(Referral::class, 'referral', 'referrals', function ($row) {
            $data = $row->toArray();
            unset($data['id'], $data['created_at'], $data['updated_at']);

            return [
                'entry_id' => $row->entry_id ?? $row->id,
                'data' => $data,
                'status' => 'pending',
                'submitted_at' => $this->parseDate($row->submission_date) ?? $row->created_at,
            ];
        });

        $counts['debits'] = $this->migrateModel(Debit::class, 'debit-form', 'debits', function ($row) {
            $data = $row->toArray();
            unset($data['id'], $data['created_at'], $data['updated_at']);

            $students = $row->debitstudent()->get()->map(fn ($s) => [
                'name' => $s->student_name,
                'group' => $s->student_group,
            ])->all();

            if ($students) {
                $data['students'] = $students;
            }

            return [
                'entry_id' => $row->id,
                'data' => $data,
                'status' => $this->normalizeStatus($row->status ?? 'pending'),
                'submitted_at' => $row->created_at,
            ];
        });

        $counts['mettings'] = $this->migrateModel(Metting::class, 'meeting-form', 'mettings', function ($row) {
            $data = $row->toArray();
            unset($data['id'], $data['created_at'], $data['updated_at']);

            return [
                'entry_id' => $row->id,
                'data' => $data,
                'status' => 'pending',
                'submitted_at' => $this->parseDate($row->date) ?? $row->created_at,
            ];
        });

        return $counts;
    }

    private function migrateModel(string $modelClass, string $formSlug, string $legacySource, callable $mapper): int
    {
        $form = Form::where('slug', $formSlug)->first();
        if (! $form) {
            return 0;
        }

        $form->update(['legacy_table' => $legacySource]);

        $count = 0;
        $modelClass::query()->orderBy('id')->chunk(100, function ($rows) use ($form, $legacySource, $mapper, &$count) {
            foreach ($rows as $row) {
                $mapped = $mapper($row);

                FormEntry::updateOrCreate(
                    [
                        'form_id' => $form->id,
                        'legacy_source' => $legacySource,
                        'legacy_record_id' => $row->id,
                    ],
                    [
                        'entry_id' => $mapped['entry_id'],
                        'data' => $mapped['data'],
                        'status' => $mapped['status'],
                        'submitted_at' => $mapped['submitted_at'],
                    ]
                );

                $count++;
            }
        });

        return $count;
    }

    private function normalizeStatus(mixed $status): string
    {
        $value = strtolower((string) $status);

        return match (true) {
            in_array($value, ['1', 'approved', 'approve'], true) => 'approved',
            in_array($value, ['2', 'rejected', 'reject'], true) => 'rejected',
            default => 'pending',
        };
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
