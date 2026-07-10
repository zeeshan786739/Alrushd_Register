<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormEntry;
use App\Services\FormBuilderService;
use App\Services\FormOptionsResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DynamicFormController extends Controller
{
    public function __construct(
        private FormBuilderService $builder,
        private FormOptionsResolver $options,
    ) {}

    public function index(): JsonResponse
    {
        $forms = Form::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'legacy_route', 'hero_label', 'hero_variant', 'handler', 'sort_order']);

        return response()->json([
            'forms' => $forms->map(fn (Form $form) => [
                'id' => $form->id,
                'name' => $form->name,
                'slug' => $form->slug,
                'legacy_route' => $form->legacy_route,
                'href' => $form->routePath(),
                'hero_label' => $form->hero_label ?: $form->name,
                'hero_variant' => $form->hero_variant,
                'handler' => $form->handler,
            ]),
        ]);
    }

    public function landingButtons(): JsonResponse
    {
        $forms = Form::query()
            ->where('is_active', true)
            ->where('show_on_landing', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'hero_buttons' => $forms->map(fn (Form $form) => [
                'label' => $form->hero_label ?: $form->name,
                'href' => $form->routePath(),
                'variant' => $form->hero_variant ?: 'outline',
                'slug' => $form->slug,
                'handler' => $form->handler,
            ]),
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $form = Form::query()
            ->where('is_active', true)
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->orWhere('legacy_route', $slug)
                    ->orWhere('legacy_route', ltrim($slug, '/'));
            })
            ->firstOrFail();
        $form->load(['steps.fields']);

        $schema = $this->builder->toSchema($form);
        $schema['success_route'] = $form->successPath();
        $fieldModels = $form->fields->keyBy('key');
        $schema['steps'] = collect($schema['steps'])->map(function ($step) use ($fieldModels) {
            $step['fields'] = collect($step['fields'])->map(function ($field) use ($fieldModels) {
                $model = $fieldModels->get($field['key']);

                return $model ? $this->options->resolveField($model) : $field;
            })->values();

            return $step;
        })->values();

        return response()->json($schema);
    }

    public function submit(Request $request, string $slug): JsonResponse
    {
        $form = Form::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $form->load(['steps.fields', 'fields']);

        $data = [];
        foreach ($form->fields as $field) {
            $field->setRelation('form', $form);
            $value = $this->extractFieldValue($request, $field);
            if ($field->required && ($value === null || $value === '' || $value === [])) {
                return response()->json(['message' => "{$field->label} is required."], 422);
            }
            if ($value !== null) {
                $data[$field->key] = $value;
            }
        }

        $entryNumber = (FormEntry::where('form_id', $form->id)->max('entry_id') ?? 0) + 1;

        $entry = FormEntry::create([
            'form_id' => $form->id,
            'entry_id' => $entryNumber,
            'data' => $data,
            'status' => 'pending',
            'ip_address' => $request->ip(),
            'submitted_at' => now(),
        ]);

        return response()->json([
            'message' => 'Form submitted successfully.',
            'entry_id' => $entry->entry_id,
            'success_route' => $form->successPath(),
        ]);
    }

    private function extractFieldValue(Request $request, $field): mixed
    {
        return match ($field->type) {
            'file' => $this->storeUploadedFiles($request, $field),
            'repeater' => $this->extractRepeater($request, $field),
            'checkbox' => $request->boolean($field->key),
            default => $request->input($field->key),
        };
    }

    private function storeUploadedFiles(Request $request, $field): mixed
    {
        if ($field->settings['multiple'] ?? false) {
            $paths = [];
            foreach ($request->file($field->key, []) ?: [] as $file) {
                if ($file) {
                    $paths[] = $file->store('form-uploads/'.Str::slug($field->form->slug), 'public');
                }
            }

            return $paths;
        }

        if (! $request->hasFile($field->key)) {
            return null;
        }

        return $request->file($field->key)->store('form-uploads/'.Str::slug($field->form->slug), 'public');
    }

    private function extractRepeater(Request $request, $field): array
    {
        $rows = $request->input($field->key, []);
        if (! is_array($rows)) {
            return [];
        }

        $result = [];
        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }
            $entry = [];
            foreach ($field->settings['fields'] ?? [] as $child) {
                $childKey = $child['key'];
                if (($child['type'] ?? '') === 'file' && $request->hasFile("{$field->key}.{$index}.{$childKey}")) {
                    $entry[$childKey] = $request->file("{$field->key}.{$index}.{$childKey}")
                        ->store('form-uploads/'.Str::slug($field->form->slug), 'public');
                } else {
                    $entry[$childKey] = $row[$childKey] ?? null;
                }
            }
            if (array_filter($entry)) {
                $result[] = $entry;
            }
        }

        return $result;
    }
}
