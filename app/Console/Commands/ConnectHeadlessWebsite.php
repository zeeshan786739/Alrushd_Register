<?php

namespace App\Console\Commands;

use App\Models\Form;
use App\Models\Organization;
use App\Models\WebsiteCms;
use App\Models\WebsitePage;
use App\Services\FormBuilderService;
use App\Services\WebsiteCmsService;
use App\Support\PublicOrganizationContext;
use Illuminate\Console\Command;

/**
 * Connects one school to an external (headless) website and seeds what that
 * website needs: CMS content matching the site, Form Center forms for the
 * site's built-in forms, the site's built-in pages and its menus.
 *
 * Safe to re-run: existing forms and pages are left untouched.
 *
 *   php artisan website:connect-headless al-rushd --url=https://www.alrushd.co.uk
 */
class ConnectHeadlessWebsite extends Command
{
    protected $signature = 'website:connect-headless
        {organization : Organization slug, e.g. al-rushd}
        {--url= : Public URL of the external website}
        {--no-content : Do not seed contact, menus, theme and branding}
        {--no-redirect : Keep serving the old built-in landing page}
        {--rotate-token : Issue a new API token even if one exists}';

    protected $description = 'Connect a school to an external (headless) website and seed its pages, menus and forms';

    public function handle(FormBuilderService $forms, WebsiteCmsService $cmsService): int
    {
        $organization = Organization::where('slug', $this->argument('organization'))->first();
        if (! $organization) {
            $this->error('Organization not found.');

            return self::FAILURE;
        }

        $url = rtrim((string) $this->option('url'), '/');
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            $this->error('Pass the website URL with --url=https://…');

            return self::FAILURE;
        }

        PublicOrganizationContext::set($organization);
        $cms = WebsiteCms::forOrganization($organization->id);

        $this->info("Connecting {$organization->name} to {$url}");

        $createdForms = $this->seedForms($organization, $forms);
        $this->line('  Forms created: '.($createdForms ? implode(', ', $createdForms) : 'none (already present)'));

        $createdPages = $this->seedPages($organization);
        $this->line("  Built-in pages registered: {$createdPages}");

        $cms->fill([
            'mode' => WebsiteCms::MODE_HEADLESS,
            'external_url' => $url,
            'redirect_legacy' => ! $this->option('no-redirect'),
        ])->save();

        $token = $cms->api_token;
        if (! $token || $this->option('rotate-token')) {
            $token = $cms->rotateApiToken();
        }

        if (! $this->option('no-content')) {
            $cmsService->saveDraft(array_replace_recursive($cmsService->getDraft(), $this->siteContent($url)));
            // Publishing keeps the previous version in the CMS history, so this is reversible.
            $cmsService->publish(null);
            $this->line('  CMS content seeded and published (previous version kept in Version History).');
        }

        $this->newLine();
        $this->info('Done. Set these on the website (e.g. .env.local or the hosting dashboard):');
        $this->line('  CRM_API_URL='.url('/api/headless/v1'));
        $this->line('  CRM_API_TOKEN='.$token);
        $this->line('  CRM_ORIGIN='.url('/'));
        $this->newLine();
        $this->line('The token is also shown in Website CMS → Website Connection.');

        return self::SUCCESS;
    }

    /** @return list<string> slugs created */
    private function seedForms(Organization $organization, FormBuilderService $builder): array
    {
        $years = array_map(fn ($i) => "Year {$i}", range(1, 13));
        $consent = ['key' => 'consent', 'label' => 'I agree to be contacted about my enquiry', 'type' => 'checkbox', 'required' => true];

        $definitions = [
            [
                'name' => 'Website: Apply Now', 'slug' => 'website-apply', 'sort_order' => 90,
                'description' => 'Application form on the website (/apply-now).',
                'steps' => [
                    ['title' => 'Parent or guardian', 'fields' => [
                        ['key' => 'full_name', 'label' => 'Parent or guardian name', 'type' => 'text', 'required' => true],
                        ['key' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'required' => true],
                        ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                    ]],
                    ['title' => 'Children', 'fields' => [
                        ['key' => 'students', 'label' => 'Children', 'type' => 'repeater', 'required' => true, 'settings' => ['fields' => [
                            ['key' => 'student_name', 'label' => 'Student name', 'type' => 'text'],
                            ['key' => 'year_group', 'label' => 'Year group', 'type' => 'select', 'options' => $years],
                            ['key' => 'nationality', 'label' => 'Nationality', 'type' => 'text'],
                            ['key' => 'gender', 'label' => 'Gender', 'type' => 'select', 'options' => ['Male', 'Female']],
                            ['key' => 'support', 'label' => 'Additional support needs', 'type' => 'textarea'],
                        ]]],
                        ['key' => 'plus', 'label' => 'Interested in Al-Rushd Plus', 'type' => 'text'],
                        $consent,
                    ]],
                ],
            ],
            [
                'name' => 'Website: Contact', 'slug' => 'website-contact', 'sort_order' => 91,
                'description' => 'Contact form on the website (/contact).',
                'steps' => [['title' => 'Your enquiry', 'fields' => [
                    ['key' => 'full_name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                    ['key' => 'phone', 'label' => 'Phone', 'type' => 'tel'],
                    ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                    ['key' => 'topic', 'label' => 'Enquiry about', 'type' => 'select', 'options' => ['Admissions', 'Fees', 'Curriculum', 'Hifz or Madrasah', 'Something else']],
                    ['key' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => true],
                    $consent,
                ]]],
            ],
            [
                'name' => 'Website: Review', 'slug' => 'website-review', 'sort_order' => 92,
                'description' => 'Review form on the website (/reviews).',
                'steps' => [['title' => 'Your review', 'fields' => [
                    ['key' => 'role', 'label' => 'I am a', 'type' => 'text', 'required' => true],
                    ['key' => 'rating', 'label' => 'Rating', 'type' => 'select', 'required' => true, 'options' => ['1', '2', '3', '4', '5']],
                    ['key' => 'full_name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                    ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                    ['key' => 'context', 'label' => 'Year group or subject', 'type' => 'text'],
                    ['key' => 'title', 'label' => 'Review title', 'type' => 'text'],
                    ['key' => 'review', 'label' => 'Review', 'type' => 'textarea', 'required' => true],
                    ['key' => 'consent', 'label' => 'I agree to my review being published', 'type' => 'checkbox', 'required' => true],
                ]]],
            ],
            [
                'name' => 'Website: Newsletter', 'slug' => 'website-newsletter', 'sort_order' => 93,
                'description' => 'Newsletter sign-up on the website.',
                'steps' => [['title' => 'Subscribe', 'fields' => [
                    ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                    ['key' => 'year_group', 'label' => 'Child\'s year group', 'type' => 'select', 'options' => $years],
                ]]],
            ],
        ];

        $created = [];
        foreach ($definitions as $definition) {
            $exists = Form::where('organization_id', $organization->id)->where('slug', $definition['slug'])->exists();
            if ($exists) {
                continue;
            }

            $builder->syncForm($definition + [
                'handler' => 'dynamic',
                'is_active' => true,
                'placements' => [], // not shown on the old built-in landing page
            ], new Form(['organization_id' => $organization->id]));
            $created[] = $definition['slug'];
        }

        return $created;
    }

    private function seedPages(Organization $organization): int
    {
        $pages = [
            ['/', 'Home'],
            ['/about-us', 'About Us'],
            ['/why-al-rushd', 'Why Al-Rushd?'],
            ['/principals-welcome', 'Principal’s Welcome'],
            ['/how-we-teach', 'How We Teach'],
            ['/character-development', 'Character Development'],
            ['/international-recognition', 'International Recognition'],
            ['/reviews', 'Reviews'],
            ['/blog-news', 'Blog & News'],
            ['/job-list', 'Careers'],
            ['/primary', 'Primary (Years 1–6)'],
            ['/secondary-school-ks3', 'Key Stage 3 (Years 7–9)'],
            ['/secondary-school-ks4', 'Key Stage 4 · GCSE'],
            ['/advanced-levels', 'A-Levels (Years 12–13)'],
            ['/hifz-hifdh-programme', 'Hifz Programme'],
            ['/al-rushd-online-madrasah', 'Online Madrasah'],
            ['/grade-placement', 'Grade Placement'],
            ['/timetable', 'Timetable'],
            ['/school-holidays', 'School Holidays'],
            ['/admission-process', 'Admissions'],
            ['/fees', 'Fees'],
            ['/apply-now', 'Apply Now'],
            ['/contact', 'Contact'],
            ['/privacy-policy', 'Privacy Policy'],
        ];

        $created = 0;
        foreach ($pages as $index => [$path, $title]) {
            $exists = WebsitePage::where('organization_id', $organization->id)->where('path', $path)->exists();
            if ($exists) {
                continue;
            }

            $draft = WebsitePage::normaliseDraft(['title' => $title, 'path' => $path], WebsitePage::KIND_SYSTEM);
            WebsitePage::create([
                'organization_id' => $organization->id,
                'kind' => WebsitePage::KIND_SYSTEM,
                'path' => $path,
                'draft' => $draft,
                'published' => $draft,
                'published_at' => now(),
                'sort_order' => $index + 1,
            ]);
            $created++;
        }

        return $created;
    }

    /** Content matching the new website, so switching over changes nothing visible. */
    private function siteContent(string $url): array
    {
        $nav = fn (string $label, string $href, string $group = '', string $description = '') => compact('label', 'href', 'group', 'description');
        $foot = fn (string $group, string $label, string $href) => compact('group', 'label', 'href');

        return [
            'branding' => [
                'company_name' => 'Al-Rushd Online School',
                'short_name' => 'Al-Rushd',
                'tagline' => 'A British Education. Rooted in Faith.',
                'website_description' => 'Award-winning online Islamic school teaching the British National Curriculum from Year 1 to A-Level, with live lessons, Quran, Arabic and Islamic Studies included.',
                'copyright' => 'Al-Rushd Online School · UKRLP Registered · ICO Registered',
            ],
            'theme' => [
                'primary' => '#143256',
                'navbar' => '#0F2646',
                'footer' => '#0A1B33',
                'accent' => '#AE9A66',
                'cream' => '#F6F2E9',
                'text' => '#3F4A5B',
                'heading' => '#1F2A3A',
            ],
            'contact' => [
                'email' => 'admin@alrushd.co.uk',
                'phone' => '+44 20 3633 0757',
                'whatsapp' => 'https://wa.me/442036330757',
                'address' => 'Unit 16, 1 Dorset Place, London E15 1BZ',
                'working_hours' => 'Mon–Fri, 8:30am–4:30pm (UK)',
            ],
            'social' => [
                'facebook' => 'https://www.facebook.com/alrushd.independentschool/',
                'youtube' => 'https://www.youtube.com/channel/UCY2LpEsW1zJm2mDC8aRLtGg',
                'instagram' => 'https://www.instagram.com/al_rushd_academy/',
                'linkedin' => 'https://www.linkedin.com/company/alrushdschool/',
                'tiktok' => 'https://www.tiktok.com/@alrushd',
                'enabled' => ['facebook' => true, 'youtube' => true, 'tiktok' => true, 'whatsapp' => true, 'instagram' => false, 'linkedin' => false, 'twitter' => false, 'threads' => false],
            ],
            'seo' => [
                'meta_title' => 'Al-Rushd Online School | British Curriculum Islamic School UK',
                'meta_description' => 'Award-winning online Islamic school teaching the British National Curriculum from Year 1 to A-Level, with live lessons, Quran, Arabic and Islamic Studies included.',
                'canonical_url' => $url,
            ],
            'website' => [
                'portal_url' => 'https://school.alrushd.co.uk/login',
            ],
            'nav_menu' => [
                'cta_text' => 'Apply Now',
                'cta_url' => '/apply-now',
                'items' => [
                    $nav('About Us', '/about-us', 'About', 'Our story, mission and values'),
                    $nav('Why Al-Rushd?', '/why-al-rushd', 'About', 'Six reasons families choose us'),
                    $nav('Principal’s Welcome', '/principals-welcome', 'About', 'A message from our Principal'),
                    $nav('How We Teach', '/how-we-teach', 'About', 'Live lessons and our platform'),
                    $nav('Character Development', '/character-development', 'About', 'Tarbiyah, faith and wellbeing'),
                    $nav('International Recognition', '/international-recognition', 'About', 'Accreditation and exam boards'),
                    $nav('Reviews', '/reviews', 'About', 'Parents, students and teachers'),
                    $nav('Blog & News', '/blog-news', 'About', 'Guides for Muslim families'),
                    $nav('Careers', '/job-list', 'About', 'Teach with Al-Rushd'),
                    $nav('Primary (Years 1–6)', '/primary', 'Key Stages', 'Key Stages 1–2'),
                    $nav('Key Stage 3 (Years 7–9)', '/secondary-school-ks3', 'Key Stages', 'Secondary'),
                    $nav('Key Stage 4 · GCSE', '/secondary-school-ks4', 'Key Stages', 'Years 10–11'),
                    $nav('A-Levels (Years 12–13)', '/advanced-levels', 'Key Stages', 'Sixth Form'),
                    $nav('Hifz Programme', '/hifz-hifdh-programme', 'Key Stages', 'Memorise the Qur’an'),
                    $nav('Online Madrasah', '/al-rushd-online-madrasah', 'Key Stages', 'Ages 5–16, evenings'),
                    $nav('Grade Placement', '/grade-placement', 'Key Stages', 'UK, US and IB equivalents'),
                    $nav('Timetable', '/timetable', 'Key Stages', 'A day at Al-Rushd'),
                    $nav('School Holidays', '/school-holidays', 'Key Stages', 'Term dates 2026–27'),
                    $nav('Admissions', '/admission-process'),
                    $nav('Fees', '/fees'),
                    $nav('Contact', '/contact'),
                ],
            ],
            'footer_menu' => [
                'heading' => 'Give Your Child the|Best of Both Worlds',
                'description' => 'An award-winning online Islamic school teaching the British curriculum from Year 1 to A‑Level.',
                'items' => [
                    $foot('School', 'About Us', '/about-us'),
                    $foot('School', 'Primary', '/primary'),
                    $foot('School', 'Secondary', '/secondary-school-ks3'),
                    $foot('School', 'GCSE', '/secondary-school-ks4'),
                    $foot('School', 'Sixth Form', '/advanced-levels'),
                    $foot('School', 'Islamic Studies', '/al-rushd-online-madrasah'),
                    $foot('Admissions', 'Apply Now', '/apply-now'),
                    $foot('Admissions', 'Book a Call', '/contact'),
                    $foot('Admissions', 'Fees', '/fees'),
                    $foot('Admissions', 'School Holidays', '/school-holidays'),
                    $foot('Admissions', 'FAQs', '/#faq-title'),
                    $foot('Admissions', 'Blog & News', '/blog-news'),
                    $foot('Policies', 'Safeguarding', '/privacy-policy#safeguarding'),
                    $foot('Policies', 'Privacy Policy', '/privacy-policy'),
                    $foot('Policies', 'Refund Policy', '/privacy-policy#refunds'),
                    $foot('Policies', 'Terms & Conditions', '/privacy-policy#terms'),
                    $foot('Policies', 'Cookie Policy', '/privacy-policy#cookies'),
                ],
            ],
        ];
    }
}
