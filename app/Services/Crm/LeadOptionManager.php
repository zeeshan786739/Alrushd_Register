<?php

namespace App\Services\Crm;

use App\Enums\LeadStatus;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadCategory;
use App\Models\Crm\LeadStatusDefinition;
use App\Models\Organization;
use App\Support\LeadCategoryUi;
use App\Support\LeadFormOptions;
use App\Support\LeadPipeline;
use App\Support\LeadSettings;
use App\Support\LeadStatusCatalog;
use App\Support\OrganizationContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Adds, renames, reorders and removes the options in the Lead Settings dropdown lists,
 * keeping the leads, quick buttons and old links that use each option in step.
 */
final class LeadOptionManager
{
    public const STATUS = 'lead_status';

    public const CATEGORY = 'lead_category_id';

    public const INTAKE = 'admission_intake';

    public const CONTACT_METHOD = 'contact_method';

    private const MAX_LENGTH = [self::INTAKE => 30, self::CONTACT_METHOD => 100];

    private const SETTINGS_KEY = [self::INTAKE => 'admission_intakes', self::CONTACT_METHOD => 'contact_methods'];

    /** Removal choice meaning "take this value off the leads that have it". */
    public const CLEAR = '__clear__';

    public static function exists(string $list): bool
    {
        return in_array($list, [self::STATUS, self::CATEGORY, self::INTAKE, self::CONTACT_METHOD], true)
            || self::customKey($list) !== null;
    }

    private static function customKey(string $list): ?string
    {
        if (! str_starts_with($list, LeadSettings::CUSTOM_PARAM_PREFIX)) {
            return null;
        }
        $key = substr($list, strlen(LeadSettings::CUSTOM_PARAM_PREFIX));

        return LeadSettings::customFilter($key) ? $key : null;
    }

    /**
     * @return list<array{value: string, label: string, count: int, tone: ?string, locked: bool, unlisted: bool}>
     */
    public function options(string $list): array
    {
        return match (true) {
            $list === self::STATUS => $this->statusOptions(),
            $list === self::CATEGORY => $this->categoryOptions(),
            default => $this->textOptions($list),
        };
    }

    public function add(string $list, string $name, ?string $tone = null): void
    {
        $name = $this->cleanName($list, $name);
        $this->assertUnique($list, $name);

        match (true) {
            $list === self::STATUS => $this->addStatus($name, $tone),
            $list === self::CATEGORY => LeadCategory::create([
                'organization_id' => OrganizationContext::idOrFail(),
                'name' => $name,
                'icon' => LeadCategoryUi::DEFAULT_ICON,
                'tone' => LeadCategoryUi::sanitizeTone($tone),
                'is_active' => true,
                'sort_order' => (int) LeadCategory::forCurrentOrganization()->max('sort_order') + 1,
            ]),
            default => $this->saveTextValues($list, function (array $values) use ($list, $name) {
                $values[] = $name;
                $this->forgetAlias($list, $name);

                return $values;
            }),
        };
    }

    /** @return int Leads changed by the rename. */
    public function rename(string $list, string $value, string $name): int
    {
        $name = $this->cleanName($list, $name);
        if ($name === $value) {
            return 0;
        }
        $this->assertKnown($list, $value);
        $this->assertUnique($list, $name, $value);

        if ($list === self::STATUS) {
            $this->renameStatus($value, $name);

            return 0;
        }
        if ($list === self::CATEGORY) {
            LeadCategory::forCurrentOrganization()->whereKey($value)->update(['name' => $name]);

            return 0;
        }

        return DB::transaction(function () use ($list, $value, $name) {
            $changed = $this->moveLeads($list, $value, $name);
            $this->saveTextValues($list, fn (array $values) => array_map(fn ($item) => $item === $value ? $name : $item, $values));
            $this->rememberRename($list, $value, $name);

            return $changed;
        });
    }

    public function recolor(string $list, string $value, string $tone): void
    {
        $tone = LeadCategoryUi::sanitizeTone($tone);

        if ($list === self::CATEGORY) {
            $this->assertKnown($list, $value);
            LeadCategory::forCurrentOrganization()->whereKey($value)->update(['tone' => $tone]);

            return;
        }

        if ($list === self::STATUS && LeadStatus::tryFrom($value) === null) {
            $this->assertKnown($list, $value);
            LeadStatusDefinition::query()
                ->where('organization_id', OrganizationContext::idOrFail())
                ->where('slug', $value)
                ->update(['tone' => $tone]);

            return;
        }

        throw $this->invalid('This option has a fixed colour.');
    }

    /** @param  list<string>  $order */
    public function reorder(string $list, array $order): void
    {
        if ($list === self::STATUS) {
            LeadPipeline::saveOrder(Organization::query()->findOrFail(OrganizationContext::idOrFail()), $order);

            return;
        }

        if ($list === self::CATEGORY) {
            DB::transaction(function () use ($order) {
                foreach (array_values($order) as $position => $id) {
                    LeadCategory::forCurrentOrganization()->whereKey($id)->update(['sort_order' => $position + 1]);
                }
            });

            return;
        }

        $this->saveTextValues($list, function (array $values) use ($order) {
            $sorted = array_values(array_intersect($order, $values));

            return array_values(array_unique(array_merge($sorted, $values)));
        });
    }

    /**
     * @param  string|null  $moveTo  Another option, self::CLEAR, or null to leave leads as they are.
     * @return int Leads changed.
     */
    public function remove(string $list, string $value, ?string $moveTo): int
    {
        $this->assertKnown($list, $value);
        $moveTo = $moveTo === '' ? null : $moveTo;

        if ($moveTo !== null && $moveTo !== self::CLEAR) {
            if ($moveTo === $value || ! collect($this->options($list))->where('unlisted', false)->pluck('value')->contains($moveTo)) {
                throw $this->invalid('Choose another option to move these leads to.');
            }
        }

        return DB::transaction(function () use ($list, $value, $moveTo) {
            $changed = match (true) {
                $list === self::STATUS => $this->removeStatus($value, $moveTo),
                $list === self::CATEGORY => $this->removeCategory($value, $moveTo),
                default => $this->removeText($list, $value, $moveTo),
            };

            $this->dropQuickFilters($list, $value);

            return $changed;
        });
    }

    public function countLeads(string $list, string $value): int
    {
        return $this->leadsWith($list, $value)->count();
    }

    private function statusOptions(): array
    {
        $counts = Lead::forCurrentOrganization()->selectRaw('lead_status, COUNT(*) as total')->groupBy('lead_status')->pluck('total', 'lead_status');

        return array_map(fn ($status) => [
            'value' => $status->value,
            'label' => $status->label(),
            'count' => (int) ($counts[$status->value] ?? 0),
            'tone' => $status->tone,
            'locked' => ! $status->isCustom,
            'unlisted' => false,
        ], LeadStatusCatalog::ordered());
    }

    private function categoryOptions(): array
    {
        $counts = Lead::forCurrentOrganization()->whereNotNull('lead_category_id')->selectRaw('lead_category_id, COUNT(*) as total')->groupBy('lead_category_id')->pluck('total', 'lead_category_id');

        return LeadCategory::forCurrentOrganization()->active()->orderBy('sort_order')->orderBy('name')->get()
            ->map(fn (LeadCategory $category) => [
                'value' => (string) $category->id,
                'label' => $category->name,
                'count' => (int) ($counts[$category->id] ?? 0),
                'tone' => $category->displayTone(),
                'locked' => false,
                'unlisted' => false,
            ])->all();
    }

    private function textOptions(string $list): array
    {
        $values = $this->textValues($list);
        $row = fn (string $value, int $count, bool $unlisted) => [
            'value' => $value, 'label' => $value, 'count' => $count, 'tone' => null, 'locked' => false, 'unlisted' => $unlisted,
        ];

        if (($key = self::customKey($list)) !== null) {
            return array_map(fn ($value) => $row($value, $this->countLeads($list, $value), false), $values);
        }

        $counts = Lead::forCurrentOrganization()
            ->whereNotNull($list)->where($list, '!=', '')
            ->selectRaw($list.' as option_value, COUNT(*) as total')
            ->groupBy($list)->pluck('total', 'option_value')->all();

        $rows = [];
        foreach ($values as $value) {
            $rows[] = $row($value, (int) ($counts[$value] ?? 0), false);
            unset($counts[$value]);
        }
        foreach ($counts as $value => $count) {
            $rows[] = $row((string) $value, (int) $count, true);
        }

        return $rows;
    }

    private function textValues(string $list): array
    {
        if (($key = self::customKey($list)) !== null) {
            return LeadSettings::customFilter($key)['options'];
        }

        return $list === self::INTAKE ? LeadSettings::admissionIntakes() : LeadSettings::contactMethods();
    }

    private function saveTextValues(string $list, callable $change): void
    {
        $values = array_values($change($this->textValues($list)));
        $key = self::customKey($list);

        LeadSettings::update(function (array $settings) use ($list, $key, $values) {
            if ($key === null) {
                $settings[self::SETTINGS_KEY[$list]] = $values;

                return $settings;
            }
            foreach ($settings['custom_filters'] as &$filter) {
                if ($filter['key'] === $key) {
                    $filter['options'] = $values;
                }
            }

            return $settings;
        });
    }

    private function leadsWith(string $list, string $value)
    {
        return ($key = self::customKey($list)) !== null
            ? Lead::forCurrentOrganization()->where('custom_fields->'.$key, $value)
            : Lead::forCurrentOrganization()->where($list, $value);
    }

    /** Includes archived leads, so restoring one never brings back a removed option. */
    private function moveLeads(string $list, string $from, ?string $to): int
    {
        $query = Lead::withTrashed()->where('organization_id', OrganizationContext::idOrFail());
        $key = self::customKey($list);
        $key !== null ? $query->where('custom_fields->'.$key, $from) : $query->where($list, $from);
        $column = $key !== null ? 'custom_fields->'.$key : $list;

        return $query->toBase()->update([$column => $to]);
    }

    private function removeText(string $list, string $value, ?string $moveTo): int
    {
        $changed = 0;
        if ($moveTo === self::CLEAR) {
            $changed = $this->moveLeads($list, $value, null);
        } elseif ($moveTo !== null) {
            $changed = $this->moveLeads($list, $value, $moveTo);
            $this->rememberRename($list, $value, $moveTo);
        }

        $this->saveTextValues($list, fn (array $values) => array_values(array_filter($values, fn ($item) => $item !== $value)));

        return $changed;
    }

    private function addStatus(string $name, ?string $tone): void
    {
        $organizationId = OrganizationContext::idOrFail();
        $slug = Str::slug($name, '_');
        if ($slug === '' || LeadStatus::tryFrom($slug) !== null || isset(LeadStatus::LEGACY_MAP[$slug])) {
            $slug = 'status_'.($slug ?: Str::lower(Str::random(6)));
        }

        $slug = Str::limit($slug, 50, '');
        $rows = LeadStatusDefinition::query()->where('organization_id', $organizationId);

        // Older data can hold retired rows: reuse one with this name instead of clashing with it.
        $row = (clone $rows)->where('is_active', false)->where(fn ($query) => $query->where('name', $name)->orWhere('slug', $slug))->first();
        if ($row === null) {
            $base = $slug;
            for ($suffix = 2; (clone $rows)->where('slug', $slug)->exists(); $suffix++) {
                $slug = $base.'_'.$suffix;
            }
            $row = new LeadStatusDefinition(['organization_id' => $organizationId, 'slug' => $slug]);
        }

        $row->fill([
            'name' => $name,
            'tone' => LeadCategoryUi::sanitizeTone($tone),
            'is_active' => true,
            'sort_order' => (int) (clone $rows)->max('sort_order') + 1,
        ])->save();
    }

    private function renameStatus(string $value, string $name): void
    {
        $status = LeadStatus::tryFrom($value);
        if ($status === null) {
            LeadStatusDefinition::query()
                ->where('organization_id', OrganizationContext::idOrFail())
                ->where('slug', $value)
                ->update(['name' => $name]);

            return;
        }

        LeadSettings::update(function (array $settings) use ($status, $name) {
            if ($name === $status->label()) {
                unset($settings['status_labels'][$status->value]);
            } else {
                $settings['status_labels'][$status->value] = $name;
            }

            return $settings;
        });
    }

    private function removeStatus(string $value, ?string $moveTo): int
    {
        if (LeadStatus::tryFrom($value) !== null) {
            throw $this->invalid('Built-in statuses keep your pipeline and reports working, so they can be renamed but not removed.');
        }

        $used = Lead::withTrashed()->where('organization_id', OrganizationContext::idOrFail())->where('lead_status', $value)->exists();
        if ($used && ($moveTo === null || $moveTo === self::CLEAR)) {
            throw $this->invalid('Every lead needs a status. Choose where to move these leads.');
        }

        $changed = $used ? $this->moveLeads(self::STATUS, $value, $moveTo) : 0;
        LeadStatusDefinition::query()
            ->where('organization_id', OrganizationContext::idOrFail())
            ->where('slug', $value)
            ->delete();

        return $changed;
    }

    private function removeCategory(string $value, ?string $moveTo): int
    {
        $target = $moveTo === null || $moveTo === self::CLEAR ? null : (int) $moveTo;
        $changed = Lead::withTrashed()
            ->where('organization_id', OrganizationContext::idOrFail())
            ->where('lead_category_id', (int) $value)
            ->toBase()
            ->update(['lead_category_id' => $target]);

        LeadCategory::forCurrentOrganization()->whereKey($value)->delete();

        return $changed;
    }

    /** Quick buttons, bookmarks, saved filters and future imports follow the new name. */
    private function rememberRename(string $list, string $from, string $to): void
    {
        LeadSettings::update(function (array $settings) use ($list, $from, $to) {
            $aliases = $settings['aliases'][$list] ?? [];
            foreach ($aliases as $old => $new) {
                if ($new === $from) {
                    $aliases[$old] = $to;
                }
            }
            $aliases[$from] = $to;
            unset($aliases[$to]);
            $settings['aliases'][$list] = $aliases;

            $settings['quick_filters'] = array_map(function (array $quick) use ($list, $from, $to) {
                if ($quick['param'] === $list && $quick['value'] === $from) {
                    $quick['value'] = $to;
                    if (str_contains($quick['label'], $from)) {
                        $quick['label'] = str_replace($from, $to, $quick['label']);
                    }
                }

                return $quick;
            }, LeadSettings::quickFilters());

            return $settings;
        });
    }

    private function forgetAlias(string $list, string $value): void
    {
        LeadSettings::update(function (array $settings) use ($list, $value) {
            unset($settings['aliases'][$list][$value]);

            return $settings;
        });
    }

    private function dropQuickFilters(string $list, string $value): void
    {
        $remaining = array_values(array_filter(
            LeadSettings::quickFilters(),
            fn (array $quick) => ! ($quick['param'] === $list && $quick['value'] === $value)
        ));

        if (count($remaining) !== count(LeadSettings::quickFilters())) {
            LeadSettings::update(function (array $settings) use ($remaining) {
                $settings['quick_filters'] = $remaining;

                return $settings;
            });
        }
    }

    private function cleanName(string $list, string $name): string
    {
        $name = trim((string) preg_replace('/\s+/u', ' ', $name));
        $max = self::MAX_LENGTH[$list] ?? 60;

        if ($name === '') {
            throw $this->invalid('Please enter a name.');
        }
        if (mb_strlen($name) > $max) {
            throw $this->invalid("Please keep this under {$max} characters.");
        }
        if ($name === LeadFormOptions::FILTER_NOT_SET) {
            throw $this->invalid('Please choose a different name.');
        }

        return $name;
    }

    private function assertUnique(string $list, string $name, ?string $except = null): void
    {
        foreach ($this->options($list) as $option) {
            if ($option['value'] === $except || $option['unlisted']) {
                continue;
            }
            if (mb_strtolower($option['label']) === mb_strtolower($name)) {
                throw $this->invalid("“{$option['label']}” is already in this list.");
            }
        }
    }

    private function assertKnown(string $list, string $value): void
    {
        if (! collect($this->options($list))->pluck('value')->contains($value)) {
            throw $this->invalid('That option no longer exists. Please refresh the page.');
        }
    }

    private function invalid(string $message): ValidationException
    {
        return ValidationException::withMessages(['value' => $message]);
    }
}
