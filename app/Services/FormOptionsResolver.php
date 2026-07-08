<?php

namespace App\Services;

use App\Models\Country;
use App\Models\FormField;
use App\Models\Group;

class FormOptionsResolver
{
    public function resolve(?string $source): array
    {
        if (! $source) {
            return [];
        }

        $static = config('form_options.'.$source);
        if (is_array($static)) {
            return array_map(fn ($item) => is_array($item) ? $item : ['value' => $item, 'label' => $item], $static);
        }

        return match ($source) {
            'countries' => Country::query()->orderBy('name')->pluck('name')->map(fn ($name) => ['value' => $name, 'label' => $name])->values()->all(),
            'debit_groups' => Group::query()->where('status', 1)->orderBy('serial')->get(['title'])->map(fn ($row) => ['value' => $row->title, 'label' => $row->title])->values()->all(),
            default => [],
        };
    }

    public function resolveField(FormField $field): array
    {
        $data = $field->toArray();

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
}
