<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\Crm\LeadOptionManager;
use App\Support\LeadCategorySchema;
use App\Support\LeadCategoryUi;
use App\Support\LeadFormOptions;
use App\Support\LeadSettings;
use App\Support\LeadStatusCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LeadSettingsController extends Controller
{
    private const MAX_CUSTOM_FILTERS = 12;

    private const MAX_QUICK_FILTERS = 12;

    public function __construct(private LeadOptionManager $options)
    {
        $this->middleware('permission:manage lead settings');
    }

    public function edit(): View
    {
        return view('admin.crm.settings.leads', ['state' => $this->state()]);
    }

    public function updateLayout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'filters' => ['sometimes', 'array', 'max:40'],
            'filters.*.key' => ['required', 'string'],
            'filters.*.placement' => ['required', Rule::in([LeadSettings::PLACEMENT_MAIN, LeadSettings::PLACEMENT_MORE, LeadSettings::PLACEMENT_HIDDEN])],
            'filters.*.label' => ['nullable', 'string', 'max:40'],
            'quick_filters' => ['sometimes', 'array', 'max:'.self::MAX_QUICK_FILTERS],
            'quick_filters.*.id' => ['nullable', 'string', 'max:40'],
            'quick_filters.*.label' => ['required', 'string', 'max:40'],
            'quick_filters.*.param' => ['required', 'string', 'max:60'],
            'quick_filters.*.value' => ['required', 'string', 'max:100'],
            'show_sources' => ['sometimes', 'boolean'],
            'show_date_picker' => ['sometimes', 'boolean'],
        ], [
            'quick_filters.max' => 'You can have up to '.self::MAX_QUICK_FILTERS.' quick buttons.',
            'filters.*.label.max' => 'Please keep filter names under 40 characters.',
            'quick_filters.*.label.max' => 'Please keep button names under 40 characters.',
        ]);

        $known = collect(LeadSettings::filters())->keyBy('key');
        $choices = $this->quickChoices();

        $filters = isset($validated['filters'])
            ? collect($validated['filters'])
                ->filter(fn ($row) => $known->has($row['key']))
                ->unique('key')
                ->map(fn ($row) => [
                    'key' => $row['key'],
                    'placement' => $row['placement'],
                    // Blank or unchanged means "use the standard name".
                    'label' => trim((string) ($row['label'] ?? '')) === $known[$row['key']]['default_label'] ? null : (trim((string) ($row['label'] ?? '')) ?: null),
                ])->values()->all()
            : null;

        $quick = null;
        if (isset($validated['quick_filters'])) {
            $quick = [];
            foreach ($validated['quick_filters'] as $row) {
                $allowed = collect($choices[$row['param']]['options'] ?? [])->pluck('value')->map(fn ($value) => (string) $value);
                if (! $allowed->contains($row['value'])) {
                    throw ValidationException::withMessages(['quick_filters' => 'That quick button points at an option that no longer exists.']);
                }
                $quick[] = [
                    'id' => $row['id'] ?: Str::lower(Str::random(10)),
                    'label' => trim($row['label']),
                    'param' => $row['param'],
                    'value' => $row['value'],
                ];
            }
        }

        LeadSettings::update(function (array $settings) use ($filters, $quick, $validated) {
            if ($filters !== null) {
                $settings['filters'] = $filters;
            }
            if ($quick !== null) {
                $settings['quick_filters'] = $quick;
            }
            foreach (['show_sources', 'show_date_picker'] as $flag) {
                if (array_key_exists($flag, $validated)) {
                    $settings[$flag] = (bool) $validated[$flag];
                }
            }

            return $settings;
        });

        return $this->saved('Saved');
    }

    /** Back to the standard layout. Dropdown lists are left alone: they hold real lead data. */
    public function restore(Request $request): JsonResponse
    {
        $section = $request->validate(['section' => ['required', Rule::in(['filters', 'quick'])]])['section'];

        LeadSettings::update(function (array $settings) use ($section) {
            if ($section === 'filters') {
                unset($settings['filters']);
            } else {
                unset($settings['quick_filters'], $settings['show_sources'], $settings['show_date_picker']);
            }

            return $settings;
        });

        return $this->saved($section === 'filters' ? 'Filters are back to the standard layout' : 'Quick buttons are back to the standard set');
    }

    public function storeCustomFilter(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:40'],
            'options' => ['required', 'array', 'min:1', 'max:50'],
            'options.*' => ['required', 'string', 'max:60'],
        ], [
            'label.required' => 'Please give the filter a name.',
            'options.required' => 'Please add at least one option.',
            'options.min' => 'Please add at least one option.',
            'options.*.max' => 'Please keep options under 60 characters.',
        ]);

        $label = trim($validated['label']);
        foreach (LeadSettings::filters() as $filter) {
            if (mb_strtolower($filter['label']) === mb_strtolower($label)) {
                throw ValidationException::withMessages(['label' => "There is already a filter called “{$filter['label']}”."]);
            }
        }
        if (count(LeadSettings::customFilters()) >= self::MAX_CUSTOM_FILTERS) {
            throw ValidationException::withMessages(['label' => 'You can have up to '.self::MAX_CUSTOM_FILTERS.' custom filters.']);
        }

        $options = collect($validated['options'])->map(fn ($option) => trim($option))->filter()
            ->unique(fn ($option) => mb_strtolower($option))->values()->all();

        $used = array_column(LeadSettings::customFilters(), 'key');
        $base = Str::limit(Str::slug($label, '_') ?: 'filter', 30, '');
        $key = $base;
        for ($suffix = 2; in_array($key, $used, true); $suffix++) {
            $key = $base.'_'.$suffix;
        }

        LeadSettings::update(function (array $settings) use ($key, $label, $options) {
            $settings['custom_filters'][] = ['key' => $key, 'label' => $label, 'options' => $options];

            return $settings;
        });

        return $this->saved('“'.$label.'” filter added. It now shows on the Leads page and lead forms.');
    }

    public function updateCustomFilter(Request $request, string $key): JsonResponse
    {
        abort_if(LeadSettings::customFilter($key) === null, 404);
        $label = trim($request->validate(['label' => ['required', 'string', 'max:40']])['label']);

        foreach (LeadSettings::filters() as $filter) {
            if ($filter['key'] !== LeadSettings::CUSTOM_PARAM_PREFIX.$key && mb_strtolower($filter['label']) === mb_strtolower($label)) {
                throw ValidationException::withMessages(['label' => "There is already a filter called “{$filter['label']}”."]);
            }
        }

        LeadSettings::update(function (array $settings) use ($key, $label) {
            foreach ($settings['custom_filters'] as &$filter) {
                if ($filter['key'] === $key) {
                    $filter['label'] = $label;
                }
            }

            return $settings;
        });

        return $this->saved('Renamed');
    }

    public function destroyCustomFilter(string $key): JsonResponse
    {
        $filter = LeadSettings::customFilter($key);
        abort_if($filter === null, 404);
        $param = LeadSettings::CUSTOM_PARAM_PREFIX.$key;

        LeadSettings::update(function (array $settings) use ($key, $param) {
            $settings['custom_filters'] = array_values(array_filter($settings['custom_filters'], fn ($row) => $row['key'] !== $key));
            $settings['filters'] = array_values(array_filter($settings['filters'] ?? [], fn ($row) => $row['key'] !== $param));
            $settings['quick_filters'] = array_values(array_filter(LeadSettings::quickFilters(), fn ($row) => $row['param'] !== $param));

            return $settings;
        });

        return $this->saved('“'.$filter['label'].'” filter removed');
    }

    public function addOption(Request $request, string $list): JsonResponse
    {
        $this->ensureList($list);
        $validated = $request->validate(['name' => ['required', 'string', 'max:120'], 'tone' => ['nullable', 'string', 'max:20']], ['name.required' => 'Please enter a name.']);

        $this->options->add($list, $validated['name'], $validated['tone'] ?? LeadCategoryUi::DEFAULT_TONE);

        return $this->saved('“'.trim($validated['name']).'” added');
    }

    public function updateOption(Request $request, string $list): JsonResponse
    {
        $this->ensureList($list);
        $validated = $request->validate([
            'value' => ['required', 'string'],
            'name' => ['nullable', 'string', 'max:120'],
            'tone' => ['nullable', 'string', 'max:20'],
        ]);

        if (! empty($validated['tone'])) {
            $this->options->recolor($list, $validated['value'], $validated['tone']);
        }

        $message = 'Saved';
        if (isset($validated['name'])) {
            $changed = $this->options->rename($list, $validated['value'], $validated['name']);
            $message = $changed > 0 ? 'Renamed, and updated '.number_format($changed).' '.Str::plural('lead', $changed) : 'Renamed';
        }

        return $this->saved($message);
    }

    public function reorderOptions(Request $request, string $list): JsonResponse
    {
        $this->ensureList($list);
        $validated = $request->validate(['values' => ['required', 'array', 'max:200'], 'values.*' => ['required', 'string']]);

        $this->options->reorder($list, $validated['values']);

        return $this->saved('Order saved');
    }

    public function removeOption(Request $request, string $list): JsonResponse
    {
        $this->ensureList($list);
        $validated = $request->validate(['value' => ['required', 'string'], 'move_to' => ['nullable', 'string']]);

        $changed = $this->options->remove($list, $validated['value'], $validated['move_to'] ?? null);

        return $this->saved($changed > 0 ? 'Removed, and updated '.number_format($changed).' '.Str::plural('lead', $changed) : 'Removed');
    }

    private function ensureList(string $list): void
    {
        abort_unless(LeadOptionManager::exists($list), 404);
    }

    private function saved(string $message): JsonResponse
    {
        return response()->json(['message' => $message, 'state' => $this->state()]);
    }

    /** Everything the settings page shows; each save returns it so the page stays in step. */
    private function state(): array
    {
        $lists = [[
            'key' => LeadOptionManager::STATUS,
            'title' => LeadSettings::filterLabel('lead_status'),
            'icon' => 'solar:checklist-minimalistic-linear',
            'hint' => 'The stages of your pipeline, in the order they appear on the board.',
            'colors' => true,
            'on_remove' => 'required',
        ]];
        if (LeadCategorySchema::ready()) {
            $lists[] = [
                'key' => LeadOptionManager::CATEGORY,
                'title' => LeadSettings::filterLabel('lead_category_id'),
                'icon' => 'solar:folder-linear',
                'hint' => 'Group leads your way, for example Teachers or Open Day.',
                'colors' => true,
                'on_remove' => 'category',
            ];
        }
        $lists[] = [
            'key' => LeadOptionManager::INTAKE,
            'title' => LeadSettings::filterLabel('admission_intake'),
            'icon' => 'solar:calendar-mark-linear',
            'hint' => 'The admission intakes your team can tag a lead with.',
            'colors' => false,
            'on_remove' => 'optional',
        ];
        $lists[] = [
            'key' => LeadOptionManager::CONTACT_METHOD,
            'title' => LeadSettings::filterLabel('contact_method'),
            'icon' => 'solar:phone-linear',
            'hint' => 'How a family prefers to be contacted.',
            'colors' => false,
            'on_remove' => 'optional',
        ];
        foreach (LeadSettings::customFilters() as $filter) {
            $lists[] = [
                'key' => LeadSettings::CUSTOM_PARAM_PREFIX.$filter['key'],
                'title' => $filter['label'],
                'icon' => 'solar:filter-linear',
                'hint' => 'Your own filter. It also appears as a field on the lead forms.',
                'colors' => false,
                'on_remove' => 'optional',
                'custom_key' => $filter['key'],
            ];
        }
        foreach ($lists as &$list) {
            $list['options'] = $this->options->options($list['key']);
        }
        unset($list);

        return [
            'filters' => LeadSettings::filters(),
            'quick_filters' => array_map(fn (array $quick) => $quick + [
                'icon' => LeadSettings::quickFilterIcon($quick['param'], $quick['value']),
            ], LeadSettings::quickFilters()),
            'quick_choices' => $this->quickChoices(),
            'show_sources' => LeadSettings::showSources(),
            'show_date_picker' => LeadSettings::showDatePicker(),
            'lists' => $lists,
            'tones' => collect(LeadCategoryUi::colors())->map(fn ($color, $tone) => ['value' => $tone, 'label' => $color['label']])->values()->all(),
            'year_groups' => [
                'title' => LeadSettings::filterLabel('year_group'),
                'options' => LeadFormOptions::yearGroups(),
                'manage_url' => auth('admin')->user()?->can('view year') ? route('admin.student-years.index') : null,
            ],
            'clear_value' => LeadOptionManager::CLEAR,
        ];
    }

    /** What a quick button can point at: each filter and the values it accepts. */
    private function quickChoices(): array
    {
        $pair = fn (array $values) => array_map(fn ($value) => ['value' => (string) $value, 'label' => (string) $value], $values);
        $keyed = fn (array $values) => array_map(fn ($value, $label) => ['value' => (string) $value, 'label' => $label], array_keys($values), $values);

        $admins = Admin::forCurrentOrganization()->assignable()->orderBy('name')->get(['id', 'name']);

        $options = [
            'lead_status' => $keyed(LeadStatusCatalog::options()),
            'year_group' => $pair(LeadFormOptions::yearGroups()),
            'admission_intake' => $pair(LeadFormOptions::admissionIntakes()),
            'assigned_to' => array_merge(
                $keyed(['me' => 'Me', 'unassigned' => 'Unassigned']),
                $admins->map(fn ($admin) => ['value' => (string) $admin->id, 'label' => $admin->name])->all()
            ),
            'lead_category_id' => LeadCategorySchema::ready() ? $this->options->options(LeadOptionManager::CATEGORY) : [],
            'follow_up' => $keyed(LeadSettings::FOLLOW_UP_OPTIONS),
            'contact_method' => $pair(LeadFormOptions::contactMethods()),
            'contact_details' => $keyed(LeadSettings::CONTACT_DETAIL_OPTIONS),
            'children' => $keyed(LeadSettings::CHILDREN_OPTIONS),
            'converted' => $keyed(LeadSettings::CONVERTED_OPTIONS),
        ];
        foreach (LeadSettings::customFilters() as $filter) {
            $options[LeadSettings::CUSTOM_PARAM_PREFIX.$filter['key']] = $pair($filter['options']);
        }

        $choices = [];
        foreach (LeadSettings::filters() as $filter) {
            if (! empty($options[$filter['param']])) {
                $choices[$filter['param']] = [
                    'label' => $filter['label'],
                    'options' => array_map(fn ($option) => ['value' => $option['value'], 'label' => $option['label']], $options[$filter['param']]),
                ];
            }
        }

        return $choices;
    }
}
