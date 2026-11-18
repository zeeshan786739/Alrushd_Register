<?php

namespace App\Services;

use App\Models\Form;
use App\Models\Setting;
use App\Models\WebsiteCms;
use App\Support\AnalyticsSettings;
use App\Support\ContactDetails;
use App\Support\SeoSettings;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class WebsiteCmsService
{
    public function defaults(): array
    {
        $landing = config('frontend.landing', []);
        $setting = Setting::first();

        return [
            'branding' => [
                'company_name' => $setting?->company_name ?? 'AL-Rushd',
                'short_name' => 'AL-Rushd',
                'tagline' => 'Where Young Minds Thrive',
                'logo' => $setting?->header_logo ? asset('storage/'.$setting->header_logo) : asset('frontend/assets/img/logo.png'),
                'logo_dark' => $setting?->header_logo ? asset('storage/'.$setting->header_logo) : asset('frontend/assets/img/logo.png'),
                'logo_light' => $setting?->header_logo ? asset('storage/'.$setting->header_logo) : asset('frontend/assets/img/logo.png'),
                'footer_logo' => $setting?->footer_logo ? asset('storage/'.$setting->footer_logo) : asset('frontend/assets/img/logo.png'),
                'favicon' => $setting?->favicon ? asset('storage/'.$setting->favicon) : asset('frontend/assets/img/logo.png'),
                'website_title' => $setting?->meta_title ?? 'AL-Rushd Online School',
                'browser_title' => $setting?->meta_title ?? 'AL-Rushd Online School — Where Young Minds Thrive',
                'website_description' => $setting?->meta_description ?? 'Quality Islamic & modern education online.',
                'default_language' => 'en',
                'copyright' => $setting?->copyright ?? '© '.date('Y').' AL-Rushd Online School. All rights reserved.',
                'company_registration' => '',
                // Connected website only (Branding screen). Empty = the website keeps its own
                // built-in crest / browser icons. Kept apart from logo/favicon above, which the
                // built-in landing page uses.
                'website_logo' => '',
                'website_favicon' => '',
                'website_favicon_icons' => [],
                // true: the footer shows "© {current year} {copyright}" (what the website always did).
                'copyright_auto_year' => true,
            ],
            'theme' => [
                'primary' => '#0F274A',
                'secondary' => '#1a3a6b',
                'accent' => '#C5A86D',
                'background' => '#ffffff',
                'text' => '#0F274A',
                'heading' => '#0F274A',
                'button' => '#C5A86D',
                'button_hover' => '#b8995f',
                'navbar' => '#0F274A',
                'footer' => '#0a1c35',
                'card_bg' => '#ffffff',
                'border' => '#e5e0d5',
                'success' => '#16a34a',
                'danger' => '#dc2626',
                'warning' => '#f59e0b',
                'cream' => '#F9F7F0',
            ],
            'typography' => [
                'heading_font' => 'Libre Baskerville',
                'body_font' => 'Inter',
                'button_font' => 'Inter',
                'base_size' => '16',
                'heading_weight' => '700',
                'line_height' => '1.6',
                'letter_spacing' => '0',
                'container_width' => '1200',
            ],
            'navbar' => [
                'sticky' => true,
                'transparent' => true,
                'glass_effect' => true,
                'height' => '76',
                'border_radius' => '0',
                'show_search' => true,
                'show_login' => true,
                'show_apply' => true,
                'login_text' => 'Login',
                'login_url' => '/admin/login',
                'apply_text' => 'Apply Now',
                'apply_url' => '#forms',
                'apply_color' => '#C5A86D',
                'menu_items' => [
                    ['label' => 'Home', 'href' => '#home', 'enabled' => true],
                    ['label' => 'About', 'href' => '#about', 'enabled' => true],
                    ['label' => 'Programs', 'href' => '#programs', 'enabled' => true],
                    ['label' => 'Admissions', 'href' => '#forms', 'enabled' => true],
                    ['label' => 'Contact', 'href' => '#contact', 'enabled' => true],
                ],
            ],
            'hero' => [
                'enabled' => true,
                'badge' => 'Trusted by 5,000+ families worldwide',
                'badge_icon' => 'fa-star',
                'heading' => 'Welcome to AL-Rushd Online',
                'heading_highlight' => 'AL-Rushd',
                'subheading' => 'Where Young Minds Thrive Through Quality Islamic & Modern Education',
                'description' => 'AL-Rushd Online School delivers world-class education in a nurturing Islamic environment. Join thousands of families who trust us to shape their children\'s future.',
                'bg_image' => '',
                'bg_video' => '',
                'overlay_opacity' => '0.6',
                'primary_btn_text' => 'Apply Now',
                'primary_btn_url' => '#forms',
                'secondary_btn_text' => 'Explore Programs',
                'secondary_btn_url' => '#programs',
                'images' => [
                    asset('frontend/assets/img/01.jpg'),
                    asset('frontend/assets/img/02.png'),
                    asset('frontend/assets/img/03.jpg'),
                ],
                'float_cards' => [
                    ['icon' => 'fa-users', 'title' => '5000+ Students', 'subtitle' => 'Enrolled worldwide'],
                    ['icon' => 'fa-star', 'title' => '95% Satisfaction', 'subtitle' => 'From parents'],
                ],
                'animation' => 'float',
            ],
            'about' => [
                'enabled' => true,
                'eyebrow' => 'About Us',
                'heading' => 'About AL-Rushd Online School',
                'description' => "AL-Rushd is a pioneering online Islamic school committed to providing exceptional education that blends traditional Islamic values with a modern, internationally recognised curriculum.\n\nOur qualified teachers, flexible online platform, and supportive community make quality education accessible to families across the globe.",
                'image' => asset('frontend/assets/img/open.jpg'),
                'badge' => 'Est. Excellence in Education',
                'features' => [
                    ['text' => 'Qualified Teachers', 'icon' => 'fa-check'],
                    ['text' => 'Online Learning', 'icon' => 'fa-check'],
                    ['text' => 'Islamic Environment', 'icon' => 'fa-check'],
                    ['text' => 'Flexible Schedule', 'icon' => 'fa-check'],
                ],
                'button_text' => '',
                'button_url' => '',
            ],
            'features' => [
                'enabled' => true,
                'eyebrow' => 'Why Choose Us',
                'heading' => 'Excellence in Every Lesson',
                'description' => 'We combine academic rigour with Islamic values to nurture confident, capable learners.',
                'items' => [
                    ['icon' => 'fa-chalkboard-teacher', 'title' => 'Expert Teachers', 'desc' => 'Highly qualified educators passionate about student success.'],
                    ['icon' => 'fa-laptop-code', 'title' => 'Interactive Learning', 'desc' => 'Engaging live classes with modern digital tools and resources.'],
                    ['icon' => 'fa-book-open', 'title' => 'Modern Curriculum', 'desc' => 'Internationally aligned syllabus with Islamic studies integrated.'],
                    ['icon' => 'fa-shield-alt', 'title' => 'Secure Platform', 'desc' => 'Safe, monitored online environment for all students.'],
                    ['icon' => 'fa-clock', 'title' => 'Flexible Timings', 'desc' => 'Classes scheduled to suit families across time zones.'],
                    ['icon' => 'fa-certificate', 'title' => 'Certificates', 'desc' => 'Recognised completion certificates for all programmes.'],
                ],
            ],
            'forms_section' => [
                'enabled' => true,
                'eyebrow' => 'Online Forms',
                'heading' => 'Available Forms',
                'description' => 'Complete your application or enquiry online — quick, secure, and easy.',
                'use_form_center' => true,
            ],
            'statistics' => [
                'enabled' => true,
                'animate' => true,
                'items' => $landing['stats'] ?? [],
            ],
            'programs' => [
                'enabled' => true,
                'eyebrow' => 'Our Programmes',
                'heading' => 'Education for Every Stage',
                'description' => 'Comprehensive programmes designed to nurture minds and hearts at every level.',
                'items' => $landing['programs'] ?? [],
            ],
            'how_it_works' => [
                'enabled' => true,
                'eyebrow' => 'Simple Process',
                'heading' => 'How It Works',
                'description' => 'From application to enrolment in five straightforward steps.',
                'steps' => ['Choose Form', 'Fill Details', 'Submit', 'Receive Confirmation', 'Admin Review'],
            ],
            'gallery' => [
                'enabled' => false,
                'eyebrow' => 'Campus Life',
                'heading' => 'Life at AL-Rushd',
                'description' => 'A glimpse into our vibrant online learning community and student achievements.',
                'items' => [
                    ['image' => asset('frontend/assets/img/01.jpg'), 'caption' => 'Interactive online classes'],
                    ['image' => asset('frontend/assets/img/02.png'), 'caption' => 'Engaged students'],
                    ['image' => asset('frontend/assets/img/03.jpg'), 'caption' => 'Islamic studies'],
                    ['image' => asset('frontend/assets/img/open.jpg'), 'caption' => 'Community events'],
                ],
            ],
            'testimonials' => [
                'enabled' => true,
                'eyebrow' => 'Testimonials',
                'heading' => 'What Parents Say',
                'description' => 'Hear from families who have chosen AL-Rushd for their children\'s education.',
                'auto_slider' => true,
                'items' => $landing['testimonials'] ?? [],
            ],
            'faq' => [
                'enabled' => true,
                'eyebrow' => 'FAQ',
                'heading' => 'Frequently Asked Questions',
                'description' => 'Everything you need to know about applying to AL-Rushd Online School.',
                'items' => $landing['faq'] ?? [],
            ],
            'contact' => [
                'enabled' => true,
                'eyebrow' => 'Get In Touch',
                'heading' => 'Contact Us',
                'description' => 'Have questions? Our team is here to help you every step of the way.',
                'email' => $landing['contact']['email'] ?? ($setting?->email_one ?? 'info@alrushd.online'),
                'phone' => $landing['contact']['phone'] ?? ($setting?->phone_one ?? '+44 20 3633 0757'),
                'whatsapp' => '',
                'address' => $landing['contact']['address'] ?? ($setting?->address ?? 'AL-Rushd Online School, United Kingdom'),
                'map_embed' => $landing['contact']['map_embed'] ?? '',
                'working_hours' => 'Monday – Friday, 8:30 am – 6:00 pm',
                'support_email' => $setting?->email_two ?? '',
                'emergency_contact' => '',
                // Contact details editor (see App\Support\ContactDetails). null = not set yet:
                // the stored whatsapp link / working_hours text is used as it is.
                'whatsapp_enabled' => null,
                'whatsapp_same_as_phone' => null,
                'whatsapp_number' => '',
                'whatsapp_message' => '',
                'opening_days' => null,
                'opening_from' => '08:30',
                'opening_until' => '16:30',
                'opening_timezone' => 'UK',
            ],
            'cta' => [
                'enabled' => true,
                'heading' => 'Ready to Join AL-Rushd?',
                'description' => 'Take the first step towards exceptional education for your child today.',
                'primary_text' => 'Apply Now',
                'primary_url' => '#forms',
                'secondary_text' => 'Contact Us',
                'secondary_url' => '#contact',
            ],
            'footer' => [
                'description' => 'AL-Rushd Online School provides quality Islamic and modern education to students worldwide through our innovative online platform.',
                'quick_links' => [
                    ['label' => 'Home', 'href' => '#home'],
                    ['label' => 'About', 'href' => '#about'],
                    ['label' => 'Programs', 'href' => '#programs'],
                    ['label' => 'Testimonials', 'href' => '#testimonials'],
                    ['label' => 'FAQ', 'href' => '#faq'],
                ],
                'privacy_url' => '/privacy-policy',
                'terms_url' => '#',
                'cookies_url' => '#',
                'newsletter_enabled' => true,
                'newsletter_text' => 'Subscribe to our newsletter for news and updates.',
            ],
            'social' => [
                'facebook' => $setting?->facebook ?? '',
                'instagram' => $setting?->instagram ?? '',
                'linkedin' => $setting?->linkedin ?? '',
                'twitter' => $setting?->twitter ?? '',
                'youtube' => $setting?->youtube ?? '',
                'tiktok' => '',
                'whatsapp' => '',
                'threads' => '',
                // The WhatsApp icon uses the link from Contact details (what the website always did).
                'whatsapp_use_contact' => true,
                'enabled' => [
                    'facebook' => true,
                    'instagram' => true,
                    'linkedin' => true,
                    'twitter' => true,
                    'youtube' => false,
                    'tiktok' => false,
                    'whatsapp' => false,
                    'threads' => false,
                ],
            ],
            'buttons' => [
                'primary_radius' => '999',
                'secondary_radius' => '999',
                'padding_x' => '28',
                'padding_y' => '14',
                'shadow' => true,
                'hover_lift' => true,
            ],
            'animations' => [
                'fade' => true,
                'slide' => true,
                'scale' => true,
                'parallax' => false,
                'counter' => true,
                'floating_images' => true,
                'card_hover' => true,
                'button_hover' => true,
                'scroll_reveal' => true,
            ],
            'seo' => [
                'meta_title' => $setting?->meta_title ?? 'AL-Rushd Online School',
                'meta_description' => $setting?->meta_description ?? '',
                'meta_keywords' => $setting?->meta_keyword ?? '',
                'og_image' => $setting?->meta_image ? asset('storage/'.$setting->meta_image) : '',
                'twitter_card' => 'summary_large_image',
                'canonical_url' => url('/'),
                'robots' => 'index, follow',
                'google_verification' => '',
            ],
            'analytics' => [
                'google_analytics' => '',
                'google_tag_manager' => '',
                'facebook_pixel' => '',
                'hotjar' => '',
                'microsoft_clarity' => '',
                // Connected website: tools load only after a visitor accepts the cookie banner.
                'require_consent' => true,
            ],
            'custom_code' => [
                // Off = the code is kept but not added to the website.
                'custom_css_enabled' => true,
                'head_code_enabled' => true,
                'footer_code_enabled' => true,
                'custom_css' => '',
                'custom_js' => '',
                'head_scripts' => '',
                'footer_scripts' => '',
            ],
            'system' => [
                'stripe_key' => $setting?->stripe_key ?? '',
                'stripe_secret' => '',
                'payment_online' => (bool) ($setting?->payment_method_status ?? true),
            ],
            // Settings used only when an external (headless) website is connected.
            'website' => [
                'forms' => [
                    'apply' => 'website-apply',
                    'contact' => 'website-contact',
                    'review' => 'website-review',
                    'newsletter' => 'website-newsletter',
                ],
                'portal_url' => '',
                'portal_button_text' => 'Parent & student login',
                'announcement' => '',
                'announcement_url' => '',
                'announcement_enabled' => false,
                'announcement_link_text' => '',
                'announcement_style' => 'gold',
                'announcement_starts_at' => '',
                'announcement_ends_at' => '',
            ],
            // Menus of a connected (headless) website. An item with a "group" is shown in
            // that dropdown; an item without one is a top-level link.
            'nav_menu' => [
                'items' => [
                    ['label' => 'Home', 'href' => '/', 'group' => '', 'description' => ''],
                ],
                'cta_text' => 'Apply Now',
                'cta_url' => '/apply-now',
            ],
            'footer_menu' => [
                'heading' => '',
                'description' => '',
                'items' => [],
            ],
            'sections_order' => [
                'hero', 'statistics', 'about', 'features', 'forms_section',
                'how_it_works', 'programs', 'gallery', 'testimonials', 'faq', 'contact', 'cta',
            ],
        ];
    }

    /** Portal button label the website showed before it became editable. */
    public const LEGACY_PORTAL_BUTTON_TEXT = 'Portal Login';

    public const ANNOUNCEMENT_STYLES = ['gold', 'navy', 'cream'];

    public function merge(array $data): array
    {
        $data = self::upgradeContactSection(self::upgradeWebsiteSection($data));
        $merged = array_replace_recursive($this->defaults(), $data);
        $merged['website'] = self::normaliseWebsiteSection($merged['website'] ?? []);
        $merged['contact'] = self::normaliseContactSection($merged['contact'] ?? []);
        $merged['seo'] = self::normaliseSeoSection($merged['seo'] ?? []);
        foreach (array_keys(AnalyticsSettings::TOOLS) as $tool) {
            $merged['analytics'][$tool] = AnalyticsSettings::normalise($tool, $merged['analytics'][$tool] ?? '');
        }
        $merged['analytics']['require_consent'] = AnalyticsSettings::requiresConsent($merged['analytics'] ?? []);
        foreach (['custom_css_enabled', 'head_code_enabled', 'footer_code_enabled'] as $flag) {
            $merged['custom_code'][$flag] = filter_var($merged['custom_code'][$flag] ?? true, FILTER_VALIDATE_BOOLEAN);
        }
        // Icon sizes belong to one upload: never mix old and new sizes, and drop them with the icon.
        if (isset($data['branding']) && array_key_exists('website_favicon_icons', $data['branding'])) {
            $merged['branding']['website_favicon_icons'] = is_array($data['branding']['website_favicon_icons']) ? $data['branding']['website_favicon_icons'] : [];
        }
        if (trim((string) ($merged['branding']['website_favicon'] ?? '')) === '') {
            $merged['branding']['website_favicon_icons'] = [];
        }

        // Menu item lists are ordered lists, not settings: array_replace_recursive would merge
        // them index-by-index with the defaults (so removing every link brought "Home" back,
        // and a short list inherited fields from default rows). Use the saved list as-is.
        foreach (['nav_menu', 'footer_menu'] as $menu) {
            if (isset($data[$menu]['items']) && is_array($data[$menu]['items'])) {
                $merged[$menu]['items'] = array_values($data[$menu]['items']);
            }
        }
        if (isset($data['nav_menu']['items']) && is_array($data['nav_menu']['items'])) {
            $merged['nav_menu']['items'] = self::normaliseNavItems($data['nav_menu']['items']);
        }
        if (isset($data['footer_menu']['items']) && is_array($data['footer_menu']['items'])) {
            $merged['footer_menu']['items'] = self::normaliseFooterItems($data['footer_menu']['items']);
        }
        if (isset($data['footer_menu']['heading']) && is_string($data['footer_menu']['heading'])) {
            $merged['footer_menu']['heading'] = self::normaliseFooterHeading($data['footer_menu']['heading']);
        }

        return $merged;
    }

    /**
     * Settings saved before Announcement & portal gained its switch and button label: keep the
     * website exactly as it was — the bar shows when it has text, the button says "Portal Login".
     */
    public static function upgradeWebsiteSection(array $data): array
    {
        if (! isset($data['website']) || ! is_array($data['website'])) {
            return $data;
        }
        $website = $data['website'];
        if (! array_key_exists('announcement_enabled', $website)) {
            $website['announcement_enabled'] = trim((string) ($website['announcement'] ?? '')) !== '';
        }
        if (! array_key_exists('portal_button_text', $website)) {
            $website['portal_button_text'] = self::LEGACY_PORTAL_BUTTON_TEXT;
        }
        $data['website'] = $website;

        return $data;
    }

    /**
     * Contact settings saved before the structured editor: read the WhatsApp link and the
     * opening-hours text into the new fields when they rebuild to exactly the same value;
     * otherwise leave them unset so the existing link / text stays as it is.
     */
    public static function upgradeContactSection(array $data): array
    {
        if (! isset($data['contact']) || ! is_array($data['contact'])) {
            return $data;
        }
        $c = $data['contact'];
        $link = trim((string) ($c['whatsapp'] ?? ''));
        if (! array_key_exists('whatsapp_enabled', $c)) {
            $c['whatsapp_enabled'] = $link !== '';
            // No link yet: leave the number unset, so nothing is generated until WhatsApp is switched on.
            if ($parsed = ContactDetails::parseWhatsapp($link, (string) ($c['phone'] ?? ''))) {
                $c += $parsed;
            }
        }
        if (! array_key_exists('opening_days', $c) && ($parsed = ContactDetails::parseHours((string) ($c['working_hours'] ?? '')))) {
            $c += $parsed;
        }
        $data['contact'] = $c;

        return $data;
    }

    /** SEO values in the formats the website reads (see App\Support\SeoSettings). */
    public static function normaliseSeoSection(array $seo): array
    {
        foreach (['meta_title', 'meta_description', 'meta_keywords', 'og_image', 'canonical_url', 'robots'] as $key) {
            if (array_key_exists($key, $seo)) {
                $seo[$key] = trim((string) ($seo[$key] ?? ''));
            }
        }
        if (! in_array($seo['twitter_card'] ?? null, SeoSettings::TWITTER_CARDS, true)) {
            $seo['twitter_card'] = 'summary_large_image';
        }
        if (($seo['robots'] ?? '') === '') {
            $seo['robots'] = SeoSettings::ROBOTS_ALLOW;
        }
        // Accept the whole <meta> tag Google shows; store only the code.
        $seo['google_verification'] = SeoSettings::verificationCode($seo['google_verification'] ?? '');
        $seo['canonical_url'] = rtrim($seo['canonical_url'] ?? '', '/');

        return $seo;
    }

    /** Clean the contact settings and rebuild the values the website reads from them. */
    public static function normaliseContactSection(array $c): array
    {
        foreach (['phone', 'email', 'whatsapp', 'whatsapp_number', 'whatsapp_message', 'address', 'working_hours'] as $key) {
            if (array_key_exists($key, $c)) {
                $c[$key] = trim((string) ($c[$key] ?? ''));
            }
        }
        $c['whatsapp_enabled'] = ($c['whatsapp_enabled'] ?? null) === null
            ? ($c['whatsapp'] ?? '') !== ''
            : filter_var($c['whatsapp_enabled'], FILTER_VALIDATE_BOOLEAN);

        // WhatsApp: once the number is set here, the link is always built from it.
        if (($c['whatsapp_same_as_phone'] ?? null) !== null) {
            $c['whatsapp_same_as_phone'] = filter_var($c['whatsapp_same_as_phone'], FILTER_VALIDATE_BOOLEAN);
            $number = $c['whatsapp_same_as_phone'] ? ($c['phone'] ?? '') : ($c['whatsapp_number'] ?? '');
            $c['whatsapp'] = ContactDetails::whatsappLink($number, $c['whatsapp_message'] ?? '');
        }

        // Opening hours: once days are set here, the text is always built from them.
        if (is_array($c['opening_days'] ?? null)) {
            $c['opening_days'] = array_values(array_map(fn ($d) => filter_var($d, FILTER_VALIDATE_BOOLEAN), array_pad(array_slice($c['opening_days'], 0, 7), 7, false)));
            foreach (['opening_from' => '08:30', 'opening_until' => '16:30'] as $key => $fallback) {
                $c[$key] = preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', (string) ($c[$key] ?? '')) ? $c[$key] : $fallback;
            }
            if (! in_array($c['opening_timezone'] ?? null, ContactDetails::TIMEZONES, true)) {
                $c['opening_timezone'] = 'UK';
            }
            $c['working_hours'] = ContactDetails::hoursText($c['opening_days'], $c['opening_from'], $c['opening_until'], $c['opening_timezone']);
        } else {
            $c['opening_days'] = null;
        }

        return $c;
    }

    /** Types and shapes the announcement / portal settings always have once stored. */
    public static function normaliseWebsiteSection(array $website): array
    {
        foreach (['announcement', 'announcement_url', 'announcement_link_text', 'portal_url', 'portal_button_text'] as $key) {
            $website[$key] = trim((string) ($website[$key] ?? ''));
        }
        $website['announcement_enabled'] = filter_var($website['announcement_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if (! in_array($website['announcement_style'] ?? null, self::ANNOUNCEMENT_STYLES, true)) {
            $website['announcement_style'] = 'gold';
        }
        foreach (['announcement_starts_at', 'announcement_ends_at'] as $key) {
            $value = (string) ($website[$key] ?? '');
            $website[$key] = preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : '';
        }

        return $website;
    }

    /**
     * Whether the announcement bar shows on the given day (school time): switched on, has a
     * message, and today is inside the optional start/end dates (both inclusive).
     */
    public static function announcementIsActive(array $website, ?string $today = null): bool
    {
        $website = self::normaliseWebsiteSection(self::upgradeWebsiteSection(['website' => $website])['website']);
        if (! $website['announcement_enabled'] || $website['announcement'] === '') {
            return false;
        }
        $today ??= now('Europe/London')->toDateString();
        if ($website['announcement_starts_at'] !== '' && $today < $website['announcement_starts_at']) {
            return false;
        }

        return $website['announcement_ends_at'] === '' || $today <= $website['announcement_ends_at'];
    }

    /**
     * Footer heading is stored as "first line|second line" (the website shows the part after
     * "|" in gold). Trim both lines; with no second line, store just the first.
     */
    public static function normaliseFooterHeading(string $heading): string
    {
        [$first, $second] = array_pad(explode('|', $heading, 2), 2, '');
        $first = Str::limit(trim($first), 120, '');
        $second = Str::limit(trim(str_replace('|', ' ', $second)), 120, '');

        return $second === '' ? $first : "{$first}|{$second}";
    }

    /**
     * Footer links, flat as the website reads them: { group (column heading), label, href }.
     * Columns appear in the order of their first link. A row with a group but no label/link
     * keeps an empty column in the CMS; the website skips such rows.
     *
     * @return list<array{group: string, label: string, href: string}>
     */
    public static function normaliseFooterItems(array $items): array
    {
        $clean = [];
        foreach (array_slice(array_values($items), 0, 200) as $item) {
            if (! is_array($item)) {
                continue;
            }
            $row = [];
            foreach (['group' => 80, 'label' => 120, 'href' => 500] as $key => $max) {
                $row[$key] = Str::limit(trim((string) ($item[$key] ?? '')), $max, '');
            }
            if ($row['label'] === '' && $row['href'] === '' && $row['group'] === '') {
                continue;
            }
            $clean[] = $row;
        }

        return $clean;
    }

    /**
     * Navigation menu items, in the flat shape the website reads: an item with a `group` sits
     * in the dropdown of that name (dropdowns appear where their first item is); an item
     * without one is a top-level link. A row with a group but no label/link keeps an empty
     * dropdown in the CMS — the website skips such rows, so empty dropdowns never show.
     *
     * @return list<array{label: string, href: string, group: string, description: string}>
     */
    public static function normaliseNavItems(array $items): array
    {
        $clean = [];
        foreach (array_slice(array_values($items), 0, 200) as $item) {
            if (! is_array($item)) {
                continue;
            }
            $row = [];
            foreach (['label' => 120, 'href' => 500, 'group' => 120, 'description' => 300] as $key => $max) {
                $row[$key] = Str::limit(trim((string) ($item[$key] ?? '')), $max, '');
            }
            if ($row['label'] === '' && $row['href'] === '' && $row['group'] === '') {
                continue;
            }
            $clean[] = $row;
        }

        return $clean;
    }

    public function getDraft(): array
    {
        $cms = WebsiteCms::instance();

        return $this->merge($cms->draft ?? []);
    }

    public function getPublished(): array
    {
        $cms = WebsiteCms::instance();

        if (! empty($cms->published)) {
            return $this->merge($cms->published);
        }

        return $this->defaults();
    }

    public function getForFrontend(bool $preview = false): array
    {
        if ($preview && Auth::guard('admin')->check()) {
            $cacheKey = 'cms_live_preview_'.Auth::guard('admin')->id();
            $live = Cache::get($cacheKey);
            if ($live) {
                return $this->merge($live);
            }

            return $this->getDraft();
        }

        return $this->getPublished();
    }

    public function saveDraft(array $data): WebsiteCms
    {
        $cms = WebsiteCms::instance();
        $cms->draft = $this->merge($data);
        $cms->save();

        return $cms;
    }

    public function publish(?int $adminId = null): WebsiteCms
    {
        $cms = WebsiteCms::instance();
        $draft = $this->getDraft();

        $history = $cms->version_history ?? [];
        if ($cms->published) {
            array_unshift($history, [
                'published_at' => $cms->published_at?->toIso8601String(),
                'data' => $cms->published,
            ]);
            $history = array_slice($history, 0, 10);
        }

        $cms->published = $draft;
        $cms->draft = $draft;
        $cms->version_history = $history;
        $cms->published_at = now();
        $cms->published_by = $adminId;
        $cms->save();

        $this->syncLegacySettings($draft);
        Cache::forget('website_cms_published');
        Cache::forget(HeadlessWebsiteService::liveCacheKey($cms->organization_id));

        $headless = app(HeadlessWebsiteService::class);
        $headless->publishPages($cms->organization_id);
        $headless->revalidate($cms);

        return $cms;
    }

    public function discardDraft(): WebsiteCms
    {
        $cms = WebsiteCms::instance();
        $cms->draft = $cms->published ?? $this->defaults();
        $cms->save();

        Cache::forget(HeadlessWebsiteService::liveCacheKey($cms->organization_id));
        app(HeadlessWebsiteService::class)->discardPages($cms->organization_id);

        return $cms;
    }

    public function resetSection(string $section): array
    {
        $defaults = $this->defaults();
        $draft = $this->getDraft();

        if (isset($defaults[$section])) {
            $draft[$section] = $defaults[$section];
        }

        if ($section === 'homepage_sections') {
            $draft['sections_order'] = $defaults['sections_order'];
        }

        $this->saveDraft($draft);

        return $draft;
    }

    public function setLivePreview(array $data, int $adminId): void
    {
        Cache::put('cms_live_preview_'.$adminId, $data, 3600);
        // Headless sites read draft content per organization (they have no admin session).
        Cache::put(HeadlessWebsiteService::liveCacheKey(WebsiteCms::instance()->organization_id), $data, 3600);
    }

    public function restoreVersion(int $index): ?WebsiteCms
    {
        $cms = WebsiteCms::instance();
        $history = $cms->version_history ?? [];

        if (! isset($history[$index]['data'])) {
            return null;
        }

        $cms->draft = $this->merge($history[$index]['data']);
        $cms->save();

        return $cms;
    }

    protected function syncLegacySettings(array $data): void
    {
        $setting = Setting::firstOrCreate([]);
        $branding = $data['branding'] ?? [];
        $contact = $data['contact'] ?? [];
        $social = $data['social'] ?? [];
        $seo = $data['seo'] ?? [];

        $setting->update([
            'company_name' => $branding['company_name'] ?? $setting->company_name,
            'copyright' => $branding['copyright'] ?? $setting->copyright,
            'meta_title' => $seo['meta_title'] ?? $setting->meta_title,
            'meta_description' => $seo['meta_description'] ?? $setting->meta_description,
            'meta_keyword' => $seo['meta_keywords'] ?? $setting->meta_keyword,
            'email_one' => $contact['email'] ?? $setting->email_one,
            'phone_one' => $contact['phone'] ?? $setting->phone_one,
            'address' => $contact['address'] ?? $setting->address,
            'facebook' => $social['facebook'] ?? $setting->facebook,
            'instagram' => $social['instagram'] ?? $setting->instagram,
            'linkedin' => $social['linkedin'] ?? $setting->linkedin,
            'twitter' => $social['twitter'] ?? $setting->twitter,
            'youtube' => $social['youtube'] ?? $setting->youtube,
        ]);
    }

    public function cssVariables(array $cms): string
    {
        $t = $cms['theme'] ?? [];
        $typo = $cms['typography'] ?? [];
        $btn = $cms['buttons'] ?? [];

        $vars = [
            '--lp-navy' => $t['primary'] ?? '#0F274A',
            '--lp-navy-light' => $t['secondary'] ?? '#1a3a6b',
            '--lp-navy-dark' => $t['footer'] ?? '#0a1c35',
            '--lp-gold' => $t['accent'] ?? '#C5A86D',
            '--lp-gold-hover' => $t['button_hover'] ?? '#b8995f',
            '--lp-cream' => $t['cream'] ?? '#F9F7F0',
            '--lp-white' => $t['background'] ?? '#ffffff',
            '--lp-muted' => '#6b7c93',
            '--lp-font' => "'".($typo['body_font'] ?? 'Inter')."', sans-serif",
            '--lp-serif' => "'".($typo['heading_font'] ?? 'Libre Baskerville')."', serif",
            '--lp-btn-font' => "'".($typo['button_font'] ?? ($typo['body_font'] ?? 'Inter'))."', sans-serif",
            '--lp-base-size' => ($typo['base_size'] ?? '16').'px',
            '--lp-line-height' => $typo['line_height'] ?? '1.6',
            '--lp-heading-weight' => $typo['heading_weight'] ?? '700',
            '--lp-container' => ($typo['container_width'] ?? '1200').'px',
            '--lp-btn-radius' => ($btn['primary_radius'] ?? '999').'px',
        ];

        $lines = array_map(fn ($k, $v) => "{$k}: {$v};", array_keys($vars), $vars);

        return ':root { '.implode(' ', $lines).' }';
    }
}
