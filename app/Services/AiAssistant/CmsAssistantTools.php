<?php

namespace App\Services\AiAssistant;

use App\Models\Admin;
use App\Models\AiAssistantChange;
use App\Models\WebsiteCms;
use App\Models\WebsitePage;
use App\Services\HeadlessWebsiteService;
use App\Services\WebsiteCmsService;
use App\Support\WebsiteCmsValidator;
use App\Support\WebsitePageDrafts;

/**
 * The AI assistant's tools, built from docs/ai-assistant-map.md. Every write goes through the
 * CMS's own save path: WebsiteCmsValidator + WebsiteCmsService::saveDraft() for site settings,
 * WebsitePage::normaliseDraft() + WebsitePageDrafts::problem() for pages. Nothing is published.
 */
class CmsAssistantTools
{
    /**
     * Sections the assistant may read and change: where the data lives, the permission needed to
     * change it, and each editable field's type (text | bool | days | color | form | choice:a,b).
     */
    public const SECTIONS = [
        'nav_menu' => [
            'label' => 'Navigation menu', 'path' => 'nav_menu', 'permission' => 'edit setting',
            'purpose' => 'Header links and dropdowns (use the menu tools) and the header button.',
            'fields' => ['cta_text' => 'text', 'cta_url' => 'text'],
        ],
        'footer_menu' => [
            'label' => 'Footer', 'path' => 'footer_menu', 'permission' => 'edit setting',
            'purpose' => 'Footer heading ("First line|Second line"), text and link columns (use the menu tools for links).',
            'fields' => ['heading' => 'text', 'description' => 'text'],
        ],
        'site_forms' => [
            'label' => 'Website forms', 'path' => 'website.forms', 'permission' => 'edit setting',
            'purpose' => 'Which Form Center form receives each website form (value: a form slug).',
            'fields' => ['apply' => 'form', 'contact' => 'form', 'review' => 'form', 'newsletter' => 'form'],
        ],
        'website' => [
            'label' => 'Announcement & portal', 'path' => 'website', 'permission' => 'edit setting',
            'purpose' => 'Announcement bar above the header and the parent/student portal login button.',
            'fields' => [
                'announcement' => 'text', 'announcement_enabled' => 'bool', 'announcement_link_text' => 'text',
                'announcement_url' => 'text', 'announcement_style' => 'choice:gold,navy,cream',
                'announcement_starts_at' => 'text', 'announcement_ends_at' => 'text',
                'portal_url' => 'text', 'portal_button_text' => 'text',
            ],
        ],
        'contact' => [
            'label' => 'Contact details', 'path' => 'contact', 'permission' => 'edit setting',
            'purpose' => 'Phone, email, address, WhatsApp and office hours used across the site.',
            'fields' => [
                'phone' => 'text', 'email' => 'text', 'address' => 'text',
                'whatsapp_enabled' => 'bool', 'whatsapp_same_as_phone' => 'bool', 'whatsapp_number' => 'text', 'whatsapp_message' => 'text',
                'opening_days' => 'days', 'opening_from' => 'text', 'opening_until' => 'text', 'opening_timezone' => 'text',
            ],
        ],
        'social' => [
            'label' => 'Social media', 'path' => 'social', 'permission' => 'edit setting',
            'purpose' => 'Social profile links and which icons are shown (enabled.<network>).',
            'fields' => [
                'facebook' => 'text', 'instagram' => 'text', 'linkedin' => 'text', 'twitter' => 'text',
                'youtube' => 'text', 'tiktok' => 'text', 'whatsapp' => 'text', 'threads' => 'text',
                'enabled.facebook' => 'bool', 'enabled.instagram' => 'bool', 'enabled.linkedin' => 'bool', 'enabled.twitter' => 'bool',
                'enabled.youtube' => 'bool', 'enabled.tiktok' => 'bool', 'enabled.whatsapp' => 'bool', 'enabled.threads' => 'bool',
                'whatsapp_use_contact' => 'bool',
            ],
        ],
        'branding' => [
            'label' => 'Branding', 'path' => 'branding', 'permission' => 'edit setting',
            'purpose' => 'School name, short name, tagline, description and copyright line.',
            'fields' => [
                'company_name' => 'text', 'short_name' => 'text', 'tagline' => 'text',
                'website_description' => 'text', 'copyright' => 'text', 'copyright_auto_year' => 'bool',
            ],
        ],
        'theme' => [
            'label' => 'Theme colours', 'path' => 'theme', 'permission' => 'edit setting',
            'purpose' => 'Brand colours as #RRGGBB.',
            'fields' => ['primary' => 'color', 'navbar' => 'color', 'footer' => 'color', 'accent' => 'color', 'cream' => 'color', 'text' => 'color', 'heading' => 'color'],
        ],
        'seo' => [
            'label' => 'SEO', 'path' => 'seo', 'permission' => 'edit setting',
            'purpose' => 'Default search title, description, sharing image and search settings.',
            'fields' => [
                'meta_title' => 'text', 'meta_description' => 'text', 'meta_keywords' => 'text', 'og_image' => 'text',
                'twitter_card' => 'choice:summary_large_image,summary', 'canonical_url' => 'text', 'robots' => 'text', 'google_verification' => 'text',
            ],
        ],
        'analytics' => [
            'label' => 'Analytics', 'path' => 'analytics', 'permission' => 'edit website code',
            'purpose' => 'Analytics tool IDs and whether they wait for cookie consent.',
            'fields' => ['google_analytics' => 'text', 'google_tag_manager' => 'text', 'facebook_pixel' => 'text', 'microsoft_clarity' => 'text', 'require_consent' => 'bool'],
        ],
        'custom_code' => [
            'label' => 'Custom code', 'path' => 'custom_code', 'permission' => 'edit website code',
            'purpose' => 'Switch custom CSS / head code / footer code on or off (the code itself is edited by hand).',
            'fields' => ['custom_css_enabled' => 'bool', 'head_code_enabled' => 'bool', 'footer_code_enabled' => 'bool'],
        ],
    ];

    /** Page fields the assistant may change → where they live in the page draft. */
    public const PAGE_FIELDS = [
        'title' => 'title', 'hidden' => 'hidden',
        'seo_title' => 'seo.title', 'seo_description' => 'seo.description', 'seo_noindex' => 'seo.noindex', 'og_image' => 'seo.og_image',
    ];

    private const MENUS = ['header' => 'nav_menu', 'footer' => 'footer_menu'];

    private ?string $changeSet = null;

    private ?int $requestId = null;

    /** @var list<array{section: string, label: string, field: string, old: mixed, new: mixed}> */
    private array $changes = [];

    /** @var array<string, true> */
    private array $touchedSections = [];

    private bool $touchedPages = false;

    public function __construct(
        private readonly WebsiteCmsService $cms,
        private readonly WebsiteCmsValidator $validator,
        private readonly HeadlessWebsiteService $headless,
        private readonly Admin $admin,
    ) {}

    /** Record every change from now on under this change set (one assistant reply = one Undo). */
    public function recordAs(string $changeSet, ?int $requestId = null): void
    {
        $this->changeSet = $changeSet;
        $this->requestId = $requestId;
    }

    public function setRequestId(int $requestId): void
    {
        $this->requestId = $requestId;
        if ($this->changeSet) {
            AiAssistantChange::where('change_set', $this->changeSet)->whereNull('request_id')->update(['request_id' => $requestId]);
        }
    }

    /** @return list<array{section: string, label: string, field: string, old: mixed, new: mixed}> */
    public function changes(): array
    {
        return $this->changes;
    }

    /** CMS data keys (top level) whose draft changed, for the browser to reload. */
    public function touchedDataKeys(): array
    {
        return array_keys($this->touchedSections);
    }

    public function touchedPages(): bool
    {
        return $this->touchedPages;
    }

    /* ───────────────────────── tool definitions ───────────────────────── */

    /** OpenAI function tools. Identical on every request (prompt caching); one-line descriptions. */
    public static function definitions(): array
    {
        $sections = array_keys(self::SECTIONS);
        $menu = ['type' => 'string', 'enum' => array_keys(self::MENUS)];
        $fn = fn (string $name, string $description, array $properties = [], array $required = []) => [
            'type' => 'function',
            'function' => [
                'name' => $name,
                'description' => $description,
                'parameters' => ['type' => 'object', 'properties' => (object) $properties, 'required' => $required, 'additionalProperties' => false],
            ],
        ];

        return [
            $fn('list_sections', 'List the website sections you can read and change.'),
            $fn('get_section', 'Get the current draft values of one section.', ['section' => ['type' => 'string', 'enum' => $sections]], ['section']),
            $fn('update_section', 'Change fields of one section; saved as a draft. Returns changed fields and errors.', [
                'section' => ['type' => 'string', 'enum' => $sections],
                'changes' => ['type' => 'object', 'description' => 'field => new value, using field names from get_section'],
            ], ['section', 'changes']),
            $fn('list_pages', 'List the website pages (id, title, path, visibility).'),
            $fn('update_page', 'Change a page\'s title, visibility or search settings; saved as a draft.', [
                'page_id' => ['type' => 'integer'],
                'changes' => ['type' => 'object', 'description' => 'any of: title, hidden, seo_title, seo_description, seo_noindex, og_image'],
            ], ['page_id', 'changes']),
            $fn('add_menu_item', 'Add a link to the header menu (group = dropdown) or footer (group = column).', [
                'menu' => $menu, 'label' => ['type' => 'string'], 'href' => ['type' => 'string'],
                'group' => ['type' => 'string'], 'position' => ['type' => 'integer'], 'description' => ['type' => 'string'],
            ], ['menu', 'label', 'href']),
            $fn('remove_menu_item', 'Remove a link from the header menu or footer.', ['menu' => $menu, 'label' => ['type' => 'string'], 'group' => ['type' => 'string']], ['menu', 'label']),
            $fn('move_menu_item', 'Move a menu link to another group and/or position (1 = first).', [
                'menu' => $menu, 'label' => ['type' => 'string'], 'group' => ['type' => 'string'],
                'to_group' => ['type' => 'string'], 'position' => ['type' => 'integer'],
            ], ['menu', 'label']),
        ];
    }

    /** Run one tool call. Always returns a compact array for the model; never throws on bad input. */
    public function execute(string $name, array $args): array
    {
        return match ($name) {
            'list_sections' => $this->listSections(),
            'get_section' => $this->getSection((string) ($args['section'] ?? '')),
            'update_section' => $this->updateSection((string) ($args['section'] ?? ''), $args['changes'] ?? null),
            'list_pages' => $this->listPages(),
            'update_page' => $this->updatePage((int) ($args['page_id'] ?? 0), $args['changes'] ?? null),
            'add_menu_item' => $this->addMenuItem($args),
            'remove_menu_item' => $this->removeMenuItem($args),
            'move_menu_item' => $this->moveMenuItem($args),
            default => ['error' => 'Unknown tool.'],
        };
    }

    /* ───────────────────────── reading ───────────────────────── */

    public function listSections(): array
    {
        $out = [];
        foreach (self::SECTIONS as $key => $s) {
            $out[$key] = $s['purpose'];
        }
        $out['pages'] = 'Page titles, visibility and search settings (use list_pages / update_page).';

        return ['sections' => $out];
    }

    public function getSection(string $section): array
    {
        if ($section === 'pages') {
            return $this->listPages();
        }
        $def = self::SECTIONS[$section] ?? null;
        if (! $def) {
            return ['error' => 'Unknown section. Use list_sections.'];
        }
        if (! $this->admin->can('view setting')) {
            return ['error' => 'not allowed'];
        }

        $data = (array) data_get($this->cms->getDraft(), $def['path'], []);
        $values = [];
        foreach ($def['fields'] as $field => $_) {
            $values[$field] = data_get($data, $field);
        }
        if (isset(self::MENUS[$this->menuKeyFor($section)])) {
            $values['items'] = array_map(fn ($i) => array_filter([
                'label' => $i['label'] ?? '', 'href' => $i['href'] ?? '', 'group' => $i['group'] ?? '',
            ], fn ($v) => $v !== ''), (array) ($data['items'] ?? []));
        }
        if ($section === 'custom_code') {
            foreach (array_keys(WebsiteCmsValidator::CUSTOM_CODE_BLOCKS) as $block) {
                $values["{$block}_has_code"] = trim((string) ($data[$block] ?? '')) !== '';
            }
        }
        if ($section === 'site_forms') {
            $values['available_forms'] = array_column($this->headless->availableForms($this->organizationId()), 'name', 'slug');
        }
        if ($section === 'contact') {
            $values['timezones'] = \App\Support\ContactDetails::TIMEZONES;
        }

        return ['section' => $section, 'values' => $values];
    }

    public function listPages(): array
    {
        if (! $this->admin->can('view setting')) {
            return ['error' => 'not allowed'];
        }

        return ['pages' => WebsitePage::where('organization_id', $this->organizationId())
            ->orderBy('sort_order')->orderBy('id')->get()
            ->map(fn (WebsitePage $p) => [
                'id' => $p->id,
                'title' => $p->draft['title'] ?? '',
                'path' => $p->draft['path'] ?? '',
                'built_in' => $p->kind === WebsitePage::KIND_SYSTEM,
                'hidden' => (bool) ($p->draft['hidden'] ?? false),
                'seo_noindex' => (bool) ($p->draft['seo']['noindex'] ?? false),
            ])->all()];
    }

    /* ───────────────────────── writing: site settings ───────────────────────── */

    public function updateSection(string $section, mixed $changes): array
    {
        if (! isset(self::SECTIONS[$section])) {
            return ['error' => $section === 'pages' ? 'Use update_page for pages.' : 'Unknown section. Use list_sections.'];
        }
        if (! is_array($changes) || ! $changes) {
            return ['error' => 'Give changes as {field: value}.'];
        }

        return $this->applySection($section, $changes);
    }

    /**
     * Change fields of one section through the CMS's own validation and draft save.
     * $allowItems lets the menu tools and Undo replace a menu's link list; $raw (Undo only) writes
     * recorded values as they were, skipping the input-type checks meant for the model's input.
     *
     * @return array{changed?: array<string, array{0: mixed, 1: mixed}>, errors?: array<string, string>, error?: string}
     */
    public function applySection(string $section, array $changes, bool $allowItems = false, ?array $display = null, bool $raw = false): array
    {
        $def = self::SECTIONS[$section];
        if (! $this->admin->can('edit setting') || ! $this->admin->can($def['permission'])) {
            return ['error' => 'not allowed'];
        }

        $draft = $this->cms->getDraft();
        $new = $draft;
        $errors = [];
        foreach ($changes as $field => $value) {
            $field = (string) $field;
            if ($field === 'items' && $allowItems && isset(self::MENUS[$this->menuKeyFor($section)])) {
                data_set($new, $def['path'].'.items', array_values((array) $value));

                continue;
            }
            $type = $def['fields'][$field] ?? null;
            if ($type === null) {
                $errors[$field] = 'Unknown field for '.$def['label'].'.';

                continue;
            }
            $clean = $raw ? $value : $this->coerce($type, $value);
            if ($clean instanceof \Closure) {
                $errors[$field] = $clean();

                continue;
            }
            data_set($new, $def['path'].'.'.$field, $clean);
        }
        if ($errors) {
            return ['errors' => $errors];
        }

        if ($problem = $this->validator->problem($new)) {
            $prefix = $def['path'].'.';
            $out = [];
            foreach ($problem['errors'] ?: ['' => [$problem['message']]] as $key => $messages) {
                $out[str_starts_with($key, $prefix) ? substr($key, strlen($prefix)) : ($key ?: 'section')] = (array) $messages === [] ? $problem['message'] : ((array) $messages)[0];
            }

            return ['errors' => $out ?: ['section' => $problem['message']]];
        }

        $this->cms->saveDraft($new);
        $after = $this->cms->getDraft();

        $changed = [];
        foreach (array_keys($changes) as $field) {
            $old = data_get($draft, $def['path'].'.'.$field);
            $now = data_get($after, $def['path'].'.'.$field);
            if (json_encode($old) !== json_encode($now)) {
                $changed[$field] = [$old, $now];
            }
        }
        if ($changed) {
            $this->touchedSections[explode('.', $def['path'])[0]] = true;
            foreach ($changed as $field => [$old, $now]) {
                $this->record('section', $section, $field, $old, $now, $display[$field] ?? null);
            }
        }

        return ['changed' => $display ? array_map(fn ($d) => [$d['old'], $d['new']], array_intersect_key($display, $changed)) : $changed];
    }

    /** @return mixed|\Closure The clean value, or a closure returning the error message. */
    private function coerce(string $type, mixed $value): mixed
    {
        if ($type === 'bool') {
            $bool = is_bool($value) ? $value : filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            return $bool === null ? fn () => 'Use true or false.' : $bool;
        }
        if ($type === 'days') {
            if (! is_array($value) || count($value) !== 7) {
                return fn () => 'Give 7 true/false values, Monday to Sunday.';
            }

            return array_map(fn ($d) => (bool) filter_var($d, FILTER_VALIDATE_BOOLEAN), array_values($value));
        }
        if (is_array($value) || is_object($value)) {
            return fn () => 'Use plain text.';
        }
        $text = trim((string) $value);
        if ($type === 'color') {
            return preg_match('/^#[0-9a-f]{6}$/i', $text) ? strtoupper($text) : fn () => 'Use a colour like #0F274A.';
        }
        if (str_starts_with($type, 'choice:')) {
            $choices = explode(',', substr($type, 7));

            return in_array($text, $choices, true) ? $text : fn () => 'Choose one of: '.implode(', ', $choices).'.';
        }
        if ($type === 'form') {
            $slugs = array_column($this->headless->availableForms($this->organizationId()), 'slug');

            return $text === '' || in_array($text, $slugs, true) ? $text : fn () => 'Choose one of the available forms: '.implode(', ', $slugs).'.';
        }

        return $text;
    }

    /* ───────────────────────── writing: menus ───────────────────────── */

    private function menuKeyFor(string $section): string
    {
        return (string) array_search($section, self::MENUS, true);
    }

    private function menuItems(string $section): array
    {
        return array_values((array) data_get($this->cms->getDraft(), self::SECTIONS[$section]['path'].'.items', []));
    }

    private function menuSection(array $args): ?string
    {
        return self::MENUS[(string) ($args['menu'] ?? '')] ?? null;
    }

    /** Index of the one item matching label (and group, if given), or an error. */
    private function findItem(array $items, string $label, ?string $group): int|string
    {
        $hits = [];
        foreach ($items as $i => $item) {
            if (mb_strtolower(trim((string) ($item['label'] ?? ''))) === mb_strtolower(trim($label))
                && ($group === null || mb_strtolower((string) ($item['group'] ?? '')) === mb_strtolower(trim($group)))) {
                $hits[] = $i;
            }
        }
        if (! $hits) {
            return "No link called \"{$label}\"".($group !== null ? " in \"{$group}\"" : '').'.';
        }
        if (count($hits) > 1) {
            $groups = array_map(fn ($i) => $items[$i]['group'] ?: '(top level)', $hits);

            return "Several links are called \"{$label}\": in ".implode(', ', $groups).'. Say which group.';
        }

        return $hits[0];
    }

    /** Insert $row among the items of its group at a 1-based position (default: last of the group). */
    private function insertItem(array $items, array $row, ?int $position): array
    {
        $siblings = array_keys(array_filter($items, fn ($i) => (string) ($i['group'] ?? '') === $row['group'] && (($i['label'] ?? '') !== '' || ($i['href'] ?? '') !== '')));
        // An empty dropdown placeholder (group only) is replaced by the first real link.
        $placeholder = array_keys(array_filter($items, fn ($i) => (string) ($i['group'] ?? '') === $row['group'] && $row['group'] !== '' && ($i['label'] ?? '') === '' && ($i['href'] ?? '') === ''));
        if ($placeholder && ! $siblings) {
            $items[$placeholder[0]] = $row;

            return array_values($items);
        }
        if (! $siblings) {
            $items[] = $row;

            return $items;
        }
        $at = $position !== null && $position >= 1 && $position <= count($siblings)
            ? $siblings[$position - 1]
            : end($siblings) + 1;
        array_splice($items, $at, 0, [$row]);

        return $items;
    }

    private function where(string $group): string
    {
        return $group === '' ? 'at the top level' : "under {$group}";
    }

    public function addMenuItem(array $args): array
    {
        $section = $this->menuSection($args);
        $label = trim((string) ($args['label'] ?? ''));
        $href = trim((string) ($args['href'] ?? ''));
        if (! $section || $label === '' || $href === '') {
            return ['error' => 'Give menu (header or footer), label and href.'];
        }
        $group = trim((string) ($args['group'] ?? ''));
        if ($section === 'footer_menu' && $group === '') {
            return ['error' => 'Footer links belong to a column: give group (the column heading).'];
        }
        $row = ['label' => $label, 'href' => $href, 'group' => $group];
        if ($section === 'nav_menu') {
            $row['description'] = trim((string) ($args['description'] ?? ''));
        }
        $before = $this->menuItems($section);
        $after = $this->insertItem($before, $row, isset($args['position']) ? (int) $args['position'] : null);

        return $this->saveMenu($section, $before, $after, "Added “{$label}” ".$this->where($group));
    }

    public function removeMenuItem(array $args): array
    {
        $section = $this->menuSection($args);
        if (! $section) {
            return ['error' => 'menu must be header or footer.'];
        }
        $before = $this->menuItems($section);
        $index = $this->findItem($before, (string) ($args['label'] ?? ''), isset($args['group']) ? (string) $args['group'] : null);
        if (is_string($index)) {
            return ['error' => $index];
        }
        $after = $before;
        $removed = $after[$index];
        array_splice($after, $index, 1);

        return $this->saveMenu($section, $before, $after, 'Removed “'.$removed['label'].'” '.$this->where((string) ($removed['group'] ?? '')));
    }

    public function moveMenuItem(array $args): array
    {
        $section = $this->menuSection($args);
        if (! $section) {
            return ['error' => 'menu must be header or footer.'];
        }
        $before = $this->menuItems($section);
        $index = $this->findItem($before, (string) ($args['label'] ?? ''), isset($args['group']) ? (string) $args['group'] : null);
        if (is_string($index)) {
            return ['error' => $index];
        }
        $after = $before;
        $row = $after[$index];
        array_splice($after, $index, 1);
        if (array_key_exists('to_group', $args) && $args['to_group'] !== null) {
            $row['group'] = trim((string) $args['to_group']);
        }
        if ($section === 'footer_menu' && ($row['group'] ?? '') === '') {
            return ['error' => 'Footer links belong to a column: give to_group.'];
        }
        $after = $this->insertItem($after, $row, isset($args['position']) ? (int) $args['position'] : null);

        return $this->saveMenu($section, $before, $after, 'Moved “'.$row['label'].'” '.$this->where((string) $row['group']));
    }

    private function saveMenu(string $section, array $before, array $after, string $summary): array
    {
        $count = fn (array $items) => count(array_filter($items, fn ($i) => ($i['label'] ?? '') !== ''));
        $result = $this->applySection($section, ['items' => $after], true, [
            'items' => ['old' => $count($before).' links', 'new' => $summary],
        ]);

        return isset($result['changed']) ? ['done' => $summary] + $result : $result;
    }

    /* ───────────────────────── writing: pages ───────────────────────── */

    public function updatePage(int $pageId, mixed $changes, bool $record = true): array
    {
        if (! $this->admin->can('edit setting')) {
            return ['error' => 'not allowed'];
        }
        $page = WebsitePage::where('organization_id', $this->organizationId())->find($pageId);
        if (! $page) {
            return ['error' => 'No page with that id. Use list_pages.'];
        }
        if (! is_array($changes) || ! $changes) {
            return ['error' => 'Give changes as {field: value}.'];
        }

        $draft = (array) $page->draft;
        $errors = [];
        foreach ($changes as $field => $value) {
            $path = self::PAGE_FIELDS[$field] ?? null;
            if (! $path) {
                $errors[$field] = 'Not editable here. Allowed: '.implode(', ', array_keys(self::PAGE_FIELDS)).'.';

                continue;
            }
            $clean = $this->coerce(in_array($field, ['hidden', 'seo_noindex'], true) ? 'bool' : 'text', $value);
            if ($clean instanceof \Closure) {
                $errors[$field] = $clean();

                continue;
            }
            data_set($draft, $path, $clean);
        }
        if ($errors) {
            return ['errors' => $errors];
        }

        // Exactly what WebsitePageController::update() does.
        $clean = WebsitePage::normaliseDraft($draft, $page->kind);
        if ($page->kind === WebsitePage::KIND_SYSTEM) {
            $clean['path'] = $page->draft['path'] ?? $clean['path'];
        }
        if ($problem = WebsitePageDrafts::problem($clean, $page->organization_id, $page->id)) {
            $field = array_search($problem['field'], self::PAGE_FIELDS, true) ?: $problem['field'];

            return ['errors' => [$field => $problem['message']]];
        }

        $before = (array) $page->draft;
        $page->update(['draft' => $clean]);

        $changed = [];
        foreach (self::PAGE_FIELDS as $field => $path) {
            $old = data_get($before, $path);
            $now = data_get($clean, $path);
            if (json_encode($old) !== json_encode($now)) {
                $changed[$field] = [$old, $now];
                if ($record) {
                    $this->record('page', (string) $page->id, $field, $old, $now, null, $clean['title'] ?: ($clean['path'] ?? ''));
                }
            }
        }
        if ($changed) {
            $this->touchedPages = true;
        }

        return ['changed' => $changed];
    }

    /* ───────────────────────── audit + undo ───────────────────────── */

    private function record(string $target, string $key, string $field, mixed $old, mixed $new, ?array $display = null, ?string $pageTitle = null): void
    {
        $label = $target === 'page'
            ? 'Page “'.$pageTitle.'”'
            : self::SECTIONS[$key]['label'];
        $this->changes[] = [
            'section' => $target === 'page' ? 'pages' : $key,
            'label' => $label,
            'field' => $field,
            'old' => $display['old'] ?? $old,
            'new' => $display['new'] ?? $new,
        ];
        if ($this->changeSet) {
            AiAssistantChange::create([
                'organization_id' => $this->organizationId(),
                'admin_id' => $this->admin->id,
                'request_id' => $this->requestId,
                'change_set' => $this->changeSet,
                'target' => $target,
                'target_key' => $key,
                'field' => $field,
                'old_value' => $old,
                'new_value' => $new,
            ]);
        }
    }

    /**
     * Put back the old values of one reply's changes, through the same save path.
     *
     * @return array{restored: int, errors: list<string>}
     */
    public function undo(string $changeSet): array
    {
        $rows = AiAssistantChange::where('organization_id', $this->organizationId())
            ->where('change_set', $changeSet)->whereNull('undone_at')->orderByDesc('id')->get();
        $errors = [];
        $restored = 0;
        foreach ($rows->groupBy(fn ($r) => $r->target.':'.$r->target_key) as $group) {
            $first = $group->first();
            // The oldest value wins when one reply changed the same field twice.
            $old = [];
            foreach ($group as $row) {
                $old[$row->field] = $row->old_value;
            }
            $result = $first->target === 'page'
                ? $this->updatePage((int) $first->target_key, $old, false)
                : (isset(self::SECTIONS[$first->target_key]) ? $this->applySection($first->target_key, $old, true, null, true) : ['error' => 'Unknown section.']);
            if (isset($result['error']) || ! empty($result['errors'])) {
                $errors[] = $result['error'] ?? implode(' ', (array) $result['errors']);

                continue;
            }
            AiAssistantChange::whereIn('id', $group->pluck('id'))->update(['undone_at' => now()]);
            $restored += $group->count();
        }

        return ['restored' => $restored, 'errors' => $errors];
    }

    private function organizationId(): int
    {
        return WebsiteCms::instance()->organization_id;
    }
}
