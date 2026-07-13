<?php

namespace App\Services;

use App\Models\Form;
use App\Models\Setting;
use App\Models\WebsiteCms;
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
                'company_name' => $setting?->company_name ?? 'Al Rushd',
                'short_name' => 'Al Rushd',
                'tagline' => 'Where Young Minds Thrive',
                'logo' => $setting?->header_logo ? asset('storage/'.$setting->header_logo) : asset('frontend/assets/img/logo.png'),
                'logo_dark' => $setting?->header_logo ? asset('storage/'.$setting->header_logo) : asset('frontend/assets/img/logo.png'),
                'logo_light' => $setting?->header_logo ? asset('storage/'.$setting->header_logo) : asset('frontend/assets/img/logo.png'),
                'footer_logo' => $setting?->footer_logo ? asset('storage/'.$setting->footer_logo) : asset('frontend/assets/img/logo.png'),
                'favicon' => $setting?->favicon ? asset('storage/'.$setting->favicon) : asset('frontend/assets/img/logo.png'),
                'website_title' => $setting?->meta_title ?? 'Al Rushd Online School',
                'browser_title' => $setting?->meta_title ?? 'Al Rushd Online School — Where Young Minds Thrive',
                'website_description' => $setting?->meta_description ?? 'Quality Islamic & modern education online.',
                'default_language' => 'en',
                'copyright' => $setting?->copyright ?? '© '.date('Y').' Al Rushd Online School. All rights reserved.',
                'company_registration' => '',
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
                'heading' => 'Welcome to Al Rushd Online',
                'heading_highlight' => 'Al Rushd',
                'subheading' => 'Where Young Minds Thrive Through Quality Islamic & Modern Education',
                'description' => 'Al Rushd Online School delivers world-class education in a nurturing Islamic environment. Join thousands of families who trust us to shape their children\'s future.',
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
                'heading' => 'About Al Rushd Online School',
                'description' => "Al Rushd is a pioneering online Islamic school committed to providing exceptional education that blends traditional Islamic values with a modern, internationally recognised curriculum.\n\nOur qualified teachers, flexible online platform, and supportive community make quality education accessible to families across the globe.",
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
                'heading' => 'Life at Al Rushd',
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
                'description' => 'Hear from families who have chosen Al Rushd for their children\'s education.',
                'auto_slider' => true,
                'items' => $landing['testimonials'] ?? [],
            ],
            'faq' => [
                'enabled' => true,
                'eyebrow' => 'FAQ',
                'heading' => 'Frequently Asked Questions',
                'description' => 'Everything you need to know about applying to Al Rushd Online School.',
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
                'address' => $landing['contact']['address'] ?? ($setting?->address ?? 'Al Rushd Online School, United Kingdom'),
                'map_embed' => $landing['contact']['map_embed'] ?? '',
                'working_hours' => 'Monday – Friday, 8:30 am – 6:00 pm',
                'support_email' => $setting?->email_two ?? '',
                'emergency_contact' => '',
            ],
            'cta' => [
                'enabled' => true,
                'heading' => 'Ready to Join Al Rushd?',
                'description' => 'Take the first step towards exceptional education for your child today.',
                'primary_text' => 'Apply Now',
                'primary_url' => '#forms',
                'secondary_text' => 'Contact Us',
                'secondary_url' => '#contact',
            ],
            'footer' => [
                'description' => 'Al Rushd Online School provides quality Islamic and modern education to students worldwide through our innovative online platform.',
                'quick_links' => [
                    ['label' => 'Home', 'href' => '#home'],
                    ['label' => 'About', 'href' => '#about'],
                    ['label' => 'Programs', 'href' => '#programs'],
                    ['label' => 'Testimonials', 'href' => '#testimonials'],
                    ['label' => 'FAQ', 'href' => '#faq'],
                ],
                'privacy_url' => '#',
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
                'meta_title' => $setting?->meta_title ?? 'Al Rushd Online School',
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
            ],
            'custom_code' => [
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
            'sections_order' => [
                'hero', 'statistics', 'about', 'features', 'forms_section',
                'how_it_works', 'programs', 'gallery', 'testimonials', 'faq', 'contact', 'cta',
            ],
        ];
    }

    public function merge(array $data): array
    {
        return array_replace_recursive($this->defaults(), $data);
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

        return $cms;
    }

    public function discardDraft(): WebsiteCms
    {
        $cms = WebsiteCms::instance();
        $cms->draft = $cms->published ?? $this->defaults();
        $cms->save();

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
