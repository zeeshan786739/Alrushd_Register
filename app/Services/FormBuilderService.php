<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormStep;
use App\Models\Organization;
use Illuminate\Support\Facades\Schema;

class FormBuilderService
{
    /**
     * @param  array{prune?: bool}  $options  When prune=false (Form Center setup), existing
     *                                        custom steps/fields are preserved and matched
     *                                        by id/key/sort_order instead of being deleted.
     */
    public function syncForm(array $payload, ?Form $form = null, array $options = []): Form
    {
        $prune = (bool) ($options['prune'] ?? true);
        $form = $form ?? new Form();

        $slug = $payload['slug'];
        $successRoute = $payload['success_route'] ?? null;
        if (! $successRoute && ($payload['handler'] ?? 'dynamic') === 'dynamic') {
            $successRoute = '/forms/'.$slug.'/success';
        }

        $form->fill([
            'name' => $payload['name'],
            'slug' => $slug,
            'description' => $payload['description'] ?? null,
            'legacy_route' => $payload['legacy_route'] ?? null,
            'legacy_table' => $payload['legacy_table'] ?? null,
            'success_route' => $successRoute,
            'submit_method' => $payload['submit_method'] ?? 'urlencoded',
            'is_active' => (bool) ($payload['is_active'] ?? true),
            'show_on_landing' => (bool) ($payload['show_on_landing'] ?? false),
            'hero_label' => $payload['hero_label'] ?? $payload['name'],
            'hero_variant' => $payload['hero_variant'] ?? 'outline',
            'handler' => $payload['handler'] ?? 'dynamic',
            'settings' => $this->resolveSettings($form, $payload),
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
        ]);

        if (Schema::hasColumn('forms', 'organization_id') && empty($form->organization_id)) {
            $form->organization_id = Organization::default()->id;
        }

        if (array_key_exists('placements', $payload)) {
            $form->setPlacements($payload['placements'] ?? []);
        } elseif (array_key_exists('show_on_landing', $payload) && ! array_key_exists('settings', $payload)) {
            $form->setPlacements(
                $payload['show_on_landing']
                    ? array_values(array_unique([...$form->placements(), 'landing']))
                    : array_values(array_filter($form->placements(), fn ($p) => $p !== 'landing'))
            );
        }

        $form->save();

        $stepIds = [];
        $stepsMeta = [];
        foreach ($payload['steps'] ?? [] as $index => $stepData) {
            $step = $this->resolveStep($form->id, $stepData, $index + 1);

            $step->fill([
                'title' => $stepData['title'],
                'sort_order' => $index + 1,
            ]);
            $step->save();
            $stepIds[] = $step->id;
            $stepsMeta[(string) $step->id] = [
                'description' => $stepData['description'] ?? '',
                'transition' => $stepData['transition'] ?? 'slide',
            ];

            $fieldIds = [];
            foreach ($stepData['fields'] ?? [] as $fieldIndex => $fieldData) {
                $field = $this->resolveField($form->id, $fieldData, $step->id);

                $fieldKey = $this->uniqueFieldKey(
                    $form->id,
                    $fieldData['key'],
                    $field->exists ? $field->id : null,
                );

                $field->fill([
                    'form_step_id' => $step->id,
                    'key' => $fieldKey,
                    'label' => $fieldData['label'],
                    'type' => $fieldData['type'] ?? 'text',
                    'placeholder' => $fieldData['placeholder'] ?? null,
                    'required' => (bool) ($fieldData['required'] ?? false),
                    'options' => $fieldData['options'] ?? null,
                    'options_source' => $fieldData['options_source'] ?? null,
                    'validation' => $fieldData['validation'] ?? null,
                    'col_span' => (int) ($fieldData['col_span'] ?? 1),
                    'sort_order' => $fieldIndex + 1,
                    'settings' => $fieldData['settings'] ?? null,
                ]);
                $field->save();
                $fieldIds[] = $field->id;
            }

            if ($prune) {
                FormField::where('form_step_id', $step->id)
                    ->whereNotIn('id', $fieldIds)
                    ->delete();
            }
        }

        if ($prune) {
            FormStep::where('form_id', $form->id)->whereNotIn('id', $stepIds)->delete();
            FormField::where('form_id', $form->id)->whereNull('form_step_id')->delete();
        }

        $settings = $form->settings ?? [];
        $settings['steps_meta'] = array_replace($settings['steps_meta'] ?? [], $stepsMeta);
        $form->update(['settings' => $settings]);

        return $form->fresh(['steps.fields']);
    }

    private function resolveStep(int $formId, array $stepData, int $sortOrder): FormStep
    {
        if (! empty($stepData['id'])) {
            $step = FormStep::query()
                ->where('form_id', $formId)
                ->whereKey($stepData['id'])
                ->first();

            if ($step) {
                return $step;
            }
        }

        $step = FormStep::query()
            ->where('form_id', $formId)
            ->where('sort_order', $sortOrder)
            ->first();

        if ($step) {
            return $step;
        }

        if (! empty($stepData['title'])) {
            $step = FormStep::query()
                ->where('form_id', $formId)
                ->where('title', $stepData['title'])
                ->first();

            if ($step) {
                return $step;
            }
        }

        return new FormStep(['form_id' => $formId]);
    }

    private function resolveField(int $formId, array $fieldData, int $stepId): FormField
    {
        if (! empty($fieldData['id'])) {
            $field = FormField::query()
                ->where('form_id', $formId)
                ->whereKey($fieldData['id'])
                ->first();

            if ($field) {
                return $field;
            }
        }

        if (! empty($fieldData['key'])) {
            $field = FormField::query()
                ->where('form_id', $formId)
                ->where('key', $fieldData['key'])
                ->first();

            if ($field) {
                return $field;
            }
        }

        return new FormField([
            'form_id' => $formId,
            'form_step_id' => $stepId,
        ]);
    }

    private function uniqueFieldKey(int $formId, string $key, ?int $ignoreId = null): string
    {
        $key = trim($key) !== '' ? trim($key) : 'field';
        $candidate = $key;
        $suffix = 1;

        while (
            FormField::query()
                ->where('form_id', $formId)
                ->where('key', $candidate)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = $key.'_'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function resolveSettings(Form $form, array $payload): array
    {
        if (array_key_exists('settings', $payload)) {
            return $payload['settings'] ?? [];
        }

        return $form->exists ? ($form->settings ?? []) : [];
    }

    public function toSchema(Form $form): array
    {
        $form->load(['steps.fields']);

        return [
            'id' => $form->id,
            'name' => $form->name,
            'slug' => $form->slug,
            'description' => $form->description,
            'legacy_route' => $form->legacy_route,
            'success_route' => $form->success_route,
            'submit_method' => $form->submit_method,
            'is_active' => $form->is_active,
            'show_on_landing' => $form->show_on_landing,
            'hero_label' => $form->hero_label,
            'hero_variant' => $form->hero_variant,
            'handler' => $form->handler,
            'sort_order' => $form->sort_order,
            'settings' => $form->settings ?? [],
            'steps' => $form->steps->map(function (FormStep $step) use ($form) {
                $meta = ($form->settings['steps_meta'] ?? [])[(string) $step->id] ?? [];

                return [
                'id' => $step->id,
                'key' => 'step_'.$step->sort_order,
                'title' => $step->title,
                'description' => $meta['description'] ?? '',
                'transition' => $meta['transition'] ?? 'slide',
                'sort_order' => $step->sort_order,
                'fields' => $step->fields->map(fn (FormField $field) => [
                    'id' => $field->id,
                    'key' => $field->key,
                    'label' => $field->label,
                    'type' => $field->type,
                    'placeholder' => $field->placeholder,
                    'required' => $field->required,
                    'options' => $field->options,
                    'options_source' => $field->options_source,
                    'col_span' => $field->col_span,
                    'settings' => $field->settings,
                ])->values(),
            ];
            })->values(),
        ];
    }
}
