<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormStep;

class FormBuilderService
{
    public function syncForm(array $payload, ?Form $form = null): Form
    {
        $form = $form ?? new Form();

        $form->fill([
            'name' => $payload['name'],
            'slug' => $payload['slug'],
            'description' => $payload['description'] ?? null,
            'legacy_route' => $payload['legacy_route'] ?? null,
            'legacy_table' => $payload['legacy_table'] ?? null,
            'success_route' => $payload['success_route'] ?? null,
            'submit_method' => $payload['submit_method'] ?? 'urlencoded',
            'is_active' => (bool) ($payload['is_active'] ?? true),
            'show_on_landing' => (bool) ($payload['show_on_landing'] ?? false),
            'hero_label' => $payload['hero_label'] ?? $payload['name'],
            'hero_variant' => $payload['hero_variant'] ?? 'outline',
            'handler' => $payload['handler'] ?? 'dynamic',
            'settings' => $payload['settings'] ?? [],
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
        ]);

        $form->save();

        $stepIds = [];
        foreach ($payload['steps'] ?? [] as $index => $stepData) {
            $step = isset($stepData['id'])
                ? FormStep::where('form_id', $form->id)->findOrFail($stepData['id'])
                : new FormStep(['form_id' => $form->id]);

            $step->fill([
                'title' => $stepData['title'],
                'sort_order' => $index + 1,
            ]);
            $step->save();
            $stepIds[] = $step->id;

            $fieldIds = [];
            foreach ($stepData['fields'] ?? [] as $fieldIndex => $fieldData) {
                $field = isset($fieldData['id'])
                    ? FormField::where('form_id', $form->id)->findOrFail($fieldData['id'])
                    : new FormField(['form_id' => $form->id]);

                $field->fill([
                    'form_step_id' => $step->id,
                    'key' => $fieldData['key'],
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

            FormField::where('form_step_id', $step->id)
                ->whereNotIn('id', $fieldIds)
                ->delete();
        }

        FormStep::where('form_id', $form->id)->whereNotIn('id', $stepIds)->delete();
        FormField::where('form_id', $form->id)->whereNull('form_step_id')->delete();

        return $form->fresh(['steps.fields']);
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
            'steps' => $form->steps->map(fn (FormStep $step) => [
                'id' => $step->id,
                'title' => $step->title,
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
            ])->values(),
        ];
    }
}
