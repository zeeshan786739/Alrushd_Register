<?php

namespace App\Support;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class LeadSmartSearch
{
    /** @param  Collection<int, \App\Models\Form>  $forms
     * @param  Collection<int, \App\Models\Crm\LeadCategory>  $categories
     * @return array{filters: array<string, string>, search: ?string, label: string, confidence: string}
     */
    public static function parse(string $query, Collection $forms, Collection $categories, int $currentAdminId): array
    {
        $original = trim($query);
        $normalized = Str::lower($original);
        $filters = [];
        $confidence = 'low';

        if ($normalized === '') {
            return ['filters' => [], 'search' => null, 'label' => '', 'confidence' => 'none'];
        }

        $phrases = [
            'assigned_to' => [
                'me' => ['assigned to me', 'my leads', 'my queue', 'my pipeline', 'mine'],
                'unassigned' => ['unassigned', 'no assignee', 'not assigned', 'without assignee'],
            ],
            'follow_up' => [
                'today' => ['due today', 'follow up today', 'follow-up today', 'followups today'],
                'overdue' => ['overdue follow', 'overdue', 'past due', 'late follow'],
            ],
            'source' => [
                'form_submission' => ['form submission', 'form submissions', 'from forms', 'form leads', 'form intake', 'website form'],
                'facebook_lead_ads' => ['facebook', 'meta leads', 'instagram leads'],
                'tiktok_lead_ads' => ['tiktok', 'tik tok'],
                'file_import' => ['import', 'file import', 'spreadsheet', 'imported leads'],
                'manual' => ['manual entry', 'manual leads', 'added manually'],
            ],
            'priority' => [
                'urgent' => ['urgent priority', 'urgent leads', 'urgent'],
                'high' => ['high priority', 'high leads'],
                'high_urgent' => ['high and urgent', 'high & urgent', 'high urgent'],
                'medium' => ['medium priority', 'medium leads'],
                'low' => ['low priority', 'low leads'],
            ],
        ];

        foreach ($phrases as $field => $map) {
            foreach ($map as $value => $needles) {
                foreach ($needles as $needle) {
                    if (Str::contains($normalized, $needle)) {
                        $filters[$field] = $value;
                        $normalized = trim(str_replace($needle, ' ', $normalized));
                        $confidence = 'high';
                    }
                }
            }
        }

        foreach (LeadStatus::cases() as $status) {
            $label = Str::lower($status->label());
            $value = $status->value;
            if (Str::contains($normalized, $label) || Str::contains($normalized, str_replace('_', ' ', $value))) {
                $filters['lead_status'] = $value;
                $normalized = trim(str_replace([$label, str_replace('_', ' ', $value)], ' ', $normalized));
                $confidence = 'high';
            }
        }

        if (Str::contains($normalized, 'new lead') || preg_match('/\bnew\b/', $normalized)) {
            $filters['lead_status'] = $filters['lead_status'] ?? LeadStatus::New->value;
            $normalized = trim(preg_replace('/\b(new leads?|new)\b/', ' ', $normalized) ?? $normalized);
            $confidence = 'high';
        }

        foreach ($forms as $form) {
            $name = Str::lower($form->name);
            if ($name !== '' && Str::contains($normalized, $name)) {
                $filters['form_id'] = (string) $form->id;
                $filters['source'] = 'form_submission';
                $normalized = trim(str_replace($name, ' ', $normalized));
                $confidence = 'high';
            }
        }

        foreach ($categories as $category) {
            $name = Str::lower($category->name);
            if ($name !== '' && Str::contains($normalized, $name)) {
                $filters['lead_category_id'] = (string) $category->id;
                $normalized = trim(str_replace($name, ' ', $normalized));
                $confidence = 'high';
            }
        }

        if (Str::contains($normalized, 'needs action') || Str::contains($normalized, 'needs attention')) {
            $filters['follow_up'] = $filters['follow_up'] ?? 'overdue';
            $normalized = trim(str_replace(['needs action', 'needs attention'], ' ', $normalized));
            $confidence = 'high';
        }

        $normalized = trim(preg_replace('/\s+/', ' ', $normalized) ?? '');

        $search = $normalized !== '' ? $normalized : null;
        if ($search && empty($filters)) {
            $confidence = 'medium';
        }

        return [
            'filters' => $filters,
            'search' => $search,
            'label' => self::describe($filters, $search, $forms, $categories),
            'confidence' => $confidence,
        ];
    }

    /** @return list<array{label: string, query: string, description: string}> */
    public static function suggestions(Collection $forms, Collection $categories): array
    {
        $items = [
            ['label' => 'My queue', 'query' => 'assigned to me', 'description' => 'Leads assigned to you'],
            ['label' => 'Needs action', 'query' => 'overdue follow ups', 'description' => 'Overdue follow-ups'],
            ['label' => 'Form intake', 'query' => 'form submissions', 'description' => 'Leads and pending forms'],
            ['label' => 'Unassigned new', 'query' => 'unassigned new leads', 'description' => 'New leads with no owner'],
            ['label' => 'Urgent', 'query' => 'urgent leads', 'description' => 'Highest priority leads'],
        ];

        $form = $forms->first();
        if ($form) {
            $items[] = [
                'label' => Str::limit($form->name, 22),
                'query' => Str::lower($form->name),
                'description' => 'Leads from this form',
            ];
        }

        $category = $categories->first();
        if ($category) {
            $items[] = [
                'label' => $category->name,
                'query' => Str::lower($category->name),
                'description' => 'Leads in this category',
            ];
        }

        return $items;
    }

    /** @param  array<string, string>  $filters */
    public static function describe(array $filters, ?string $search, Collection $forms, Collection $categories): string
    {
        $parts = [];

        if (isset($filters['assigned_to'])) {
            $parts[] = match ($filters['assigned_to']) {
                'me' => 'Assigned to me',
                'unassigned' => 'Unassigned',
                default => 'Assigned',
            };
        }

        if (isset($filters['follow_up'])) {
            $parts[] = match ($filters['follow_up']) {
                'today' => 'Due today',
                'overdue' => 'Overdue follow-up',
                default => 'Follow-up',
            };
        }

        if (isset($filters['lead_status'])) {
            $parts[] = LeadStatus::tryFrom($filters['lead_status'])?->label() ?? 'Status';
        }

        if (isset($filters['priority'])) {
            $parts[] = match ($filters['priority']) {
                'high_urgent' => 'High & urgent',
                default => LeadPriority::tryFrom($filters['priority'])?->label() ?? 'Priority',
            };
        }

        if (isset($filters['source'])) {
            $parts[] = LeadSourceOptions::label($filters['source']);
        }

        if (isset($filters['form_id'])) {
            $parts[] = $forms->firstWhere('id', (int) $filters['form_id'])?->name ?? 'Form';
        }

        if (isset($filters['lead_category_id'])) {
            $parts[] = $categories->firstWhere('id', (int) $filters['lead_category_id'])?->name ?? 'Category';
        }

        if ($search) {
            $parts[] = '"'.$search.'"';
        }

        return $parts ? implode(' · ', $parts) : 'All leads';
    }
}
