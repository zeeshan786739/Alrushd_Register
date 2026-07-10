<?php

namespace App\Services;

use App\Models\AdmissionDate;
use App\Models\Country;
use App\Models\FormField;
use App\Models\Gender;
use App\Models\Group;
use App\Models\Nationality;
use App\Models\PaymentCountry;
use App\Models\RelationShip;
use App\Models\School;
use App\Models\StudentGroup;
use App\Models\StudentLanguage;
use App\Models\StudentPackage;
use App\Models\StudentSubject;
use App\Models\StudentYear;

class FormOptionsResolver
{
    public function resolve(?string $source): array
    {
        if (! $source) {
            return [];
        }

        $crm = $this->resolveCrm($source);
        if ($crm !== null) {
            return $crm;
        }

        $static = config('form_options.'.$source);
        if (is_array($static) && ! isset($static['label'])) {
            return array_map(
                fn ($item) => is_array($item) ? $item : ['value' => $item, 'label' => $item],
                $static
            );
        }

        return [];
    }

    public function resolveField(FormField $field): array
    {
        $data = $field->toArray();

        if ($field->type === 'section') {
            $data['options'] = [];

            return $data;
        }

        if ($field->options_source) {
            $data['options'] = $this->resolve($field->options_source);
        } elseif (is_array($field->options) && array_is_list($field->options) === false) {
            $data['options'] = collect($field->options)->map(fn ($label, $value) => ['value' => (string) $value, 'label' => (string) $label])->values()->all();
        } elseif (is_array($field->options)) {
            $data['options'] = array_map(fn ($item) => ['value' => $item, 'label' => $item], $field->options);
        }

        if ($field->type === 'repeater' && isset($field->settings['fields'])) {
            $data['settings']['fields'] = collect($field->settings['fields'])->map(function ($child) {
                if (! empty($child['options_source'])) {
                    $child['options'] = $this->resolve($child['options_source']);
                }

                return $child;
            })->all();
        }

        return $data;
    }

    public static function sourceLabels(): array
    {
        return config('form_options.option_sources', []);
    }

    private function resolveCrm(string $source): ?array
    {
        return match ($source) {
            'nationalities' => $this->resolveNationalities(),
            'genders' => $this->pluckName(Gender::class),
            'relationships' => $this->pluckName(RelationShip::class),
            'payment_countries' => $this->pluckName(PaymentCountry::class),
            'admission_dates' => $this->resolveAdmissionDates(),
            'schools' => $this->pluckName(School::class),
            'student_groups' => $this->pluckName(StudentGroup::class),
            'student_years' => $this->pluckName(StudentYear::class),
            'student_packages' => $this->pluckName(StudentPackage::class),
            'student_languages' => $this->pluckName(StudentLanguage::class),
            'student_subjects' => $this->pluckName(StudentSubject::class),
            'countries' => Country::query()->orderBy('name')->pluck('name')->map(fn ($name) => ['value' => $name, 'label' => $name])->values()->all(),
            'debit_groups' => Group::query()->where('status', 1)->orderBy('serial')->get(['title'])->map(fn ($row) => ['value' => $row->title, 'label' => $row->title])->values()->all(),
            default => null,
        };
    }

    private function resolveNationalities(): array
    {
        $rows = Nationality::query()->where('status', 1)->orderBy('name')->pluck('name');
        if ($rows->isEmpty()) {
            $rows = Country::query()->orderBy('name')->pluck('name');
        }

        return $rows->map(fn ($name) => ['value' => $name, 'label' => $name])->values()->all();
    }

    private function resolveAdmissionDates(): array
    {
        return AdmissionDate::query()
            ->where('status', 1)
            ->orderBy('date')
            ->get(['date'])
            ->map(fn ($row) => ['value' => (string) $row->date, 'label' => (string) $row->date])
            ->values()
            ->all();
    }

    private function pluckName(string $modelClass): array
    {
        return $modelClass::query()
            ->where('status', 1)
            ->orderBy('name')
            ->pluck('name')
            ->map(fn ($name) => ['value' => $name, 'label' => $name])
            ->values()
            ->all();
    }
}
