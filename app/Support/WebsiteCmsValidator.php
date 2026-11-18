<?php

namespace App\Support;

use App\Services\WebsiteCmsService;
use Illuminate\Support\Facades\Auth;

/**
 * The Website CMS's save rules for site settings, shared by the CMS form
 * (WebsiteCmsController::saveDraft / publish) and the AI assistant, so both enforce exactly the same
 * checks. Each check only looks at values that differ from the current draft, so an old value never
 * blocks saving another section.
 *
 * A problem is returned as ['status' => 422|403, 'message' => first message, 'errors' => [path => [messages]]].
 */
class WebsiteCmsValidator
{
    public function __construct(protected WebsiteCmsService $cms) {}

    /** The first problem with a whole CMS data payload, in the order the CMS has always checked. */
    public function problem(array $data): ?array
    {
        return $this->websiteSettings($data) ?? $this->contactSettings($data) ?? $this->socialLinks($data)
            ?? $this->branding($data) ?? $this->seo($data) ?? $this->analytics($data) ?? $this->customCode($data);
    }

    /**
     * Announcement & portal settings (website.*). Only values that changed are checked, so an
     * old value never blocks saving an unrelated section of the CMS.
     */
    public function websiteSettings(array $data): ?array
    {
        $website = $data['website'] ?? null;
        if (! is_array($website)) {
            return null;
        }
        $current = $this->cms->getDraft()['website'] ?? [];
        $link = 'regex:#^(/(?!/)\S*|https?://[^\s/$.?\#].[^\s]*|mailto:\S+|tel:\S+|\#\S*)$#i';
        $rules = [
            'announcement' => ['nullable', 'string', 'max:120'],
            'announcement_link_text' => ['nullable', 'string', 'max:40'],
            'announcement_url' => ['nullable', 'string', 'max:500', $link],
            'announcement_style' => ['nullable', 'in:'.implode(',', WebsiteCmsService::ANNOUNCEMENT_STYLES)],
            'announcement_enabled' => ['nullable', 'boolean'],
            'announcement_starts_at' => ['nullable', 'date_format:Y-m-d'],
            'announcement_ends_at' => ['nullable', 'date_format:Y-m-d'],
            'portal_url' => ['nullable', 'string', 'max:500', 'url:https,http'],
            'portal_button_text' => ['nullable', 'string', 'max:40'],
        ];
        $changed = array_filter(
            $rules,
            fn ($_, $key) => array_key_exists($key, $website) && (string) ($website[$key] ?? '') !== (string) ($current[$key] ?? ''),
            ARRAY_FILTER_USE_BOTH,
        );
        // The end date is compared whenever either date changed.
        if (isset($changed['announcement_starts_at']) || isset($changed['announcement_ends_at'])) {
            $changed['announcement_ends_at'] = ['nullable', 'date_format:Y-m-d', 'after_or_equal:announcement_starts_at'];
            $changed['announcement_starts_at'] ??= $rules['announcement_starts_at'];
        }
        if (! $changed) {
            return null;
        }

        $validator = \Illuminate\Support\Facades\Validator::make($website, $changed, [
            'announcement.max' => 'Keep the message under 120 characters (90 or fewer fits on one line on phones).',
            'announcement_url.regex' => 'Choose one of your pages, or a full web address starting with https://',
            'announcement_ends_at.after_or_equal' => 'The end date must be on or after the start date.',
            'portal_url.url' => 'Enter the full login address, starting with https://',
            'announcement_link_text.max' => 'Keep the link text under 40 characters.',
            'portal_button_text.max' => 'Keep the button text under 40 characters.',
        ]);

        if (! $validator->fails()) {
            return null;
        }

        $errors = collect($validator->errors()->toArray())->mapWithKeys(fn ($m, $k) => ["website.{$k}" => $m])->all();

        return ['status' => 422, 'message' => collect($errors)->flatten()->first(), 'errors' => $errors];
    }

    /**
     * Contact details. Like invalidWebsiteSettings(), only checks what changed, so an old value
     * never blocks saving another section. Rules: phone has 7+ digits, email is valid, WhatsApp
     * has a number when it is on, and the office closes after it opens.
     */
    public function contactSettings(array $data): ?array
    {
        $contact = $data['contact'] ?? null;
        if (! is_array($contact)) {
            return null;
        }
        $current = $this->cms->getDraft()['contact'] ?? [];
        $changed = fn (string ...$keys) => collect($keys)->contains(
            fn ($k) => array_key_exists($k, $contact) && json_encode($contact[$k] ?? null) !== json_encode($current[$k] ?? null)
        );
        $digits = fn ($v) => strlen(ContactDetails::digits((string) $v));
        $errors = [];

        $phone = trim((string) ($contact['phone'] ?? ''));
        if ($changed('phone') && $phone !== '' && $digits($phone) < 7) {
            $errors['contact.phone'] = ['Enter a phone number with at least 7 digits, including the country code.'];
        }
        $email = trim((string) ($contact['email'] ?? ''));
        if ($changed('email') && $email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['contact.email'] = ['Please enter a full email address, like name@school.co.uk'];
        }
        if ($changed('whatsapp_enabled', 'whatsapp_same_as_phone', 'whatsapp_number', 'phone')
            && filter_var($contact['whatsapp_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN)
            && array_key_exists('whatsapp_same_as_phone', $contact) && $contact['whatsapp_same_as_phone'] !== null) {
            $same = filter_var($contact['whatsapp_same_as_phone'], FILTER_VALIDATE_BOOLEAN);
            if (! $same && $digits($contact['whatsapp_number'] ?? '') < 7) {
                $errors['contact.whatsapp_number'] = ['Enter the WhatsApp number, with the country code.'];
            } elseif ($same && $digits($phone) < 7) {
                $errors['contact.phone'] ??= ['Add your phone number to use it for WhatsApp.'];
            }
        }
        if ($changed('whatsapp_message') && mb_strlen((string) ($contact['whatsapp_message'] ?? '')) > 300) {
            $errors['contact.whatsapp_message'] = ['Keep the first message under 300 characters.'];
        }
        if ($changed('opening_days', 'opening_from', 'opening_until', 'opening_timezone') && is_array($contact['opening_days'] ?? null)) {
            $time = '/^([01]\d|2[0-3]):[0-5]\d$/';
            $from = (string) ($contact['opening_from'] ?? '');
            $until = (string) ($contact['opening_until'] ?? '');
            if (! preg_match($time, $from) || ! preg_match($time, $until)) {
                $errors['contact.opening_until'] = ['Choose an opening and a closing time.'];
            } elseif (in_array(true, array_map(fn ($d) => filter_var($d, FILTER_VALIDATE_BOOLEAN), $contact['opening_days']), true) && $until <= $from) {
                $errors['contact.opening_until'] = ['The closing time must be after the opening time.'];
            }
            if (! in_array($contact['opening_timezone'] ?? 'UK', ContactDetails::TIMEZONES, true)) {
                $errors['contact.opening_timezone'] = ['Choose a time zone from the list.'];
            }
        }

        return $errors
            ? ['status' => 422, 'message' => collect($errors)->flatten()->first(), 'errors' => $errors]
            : null;
    }

    /** Social media: each changed link must be a full link to its own network. */
    public function socialLinks(array $data): ?array
    {
        $social = $data['social'] ?? null;
        if (! is_array($social)) {
            return null;
        }
        $current = $this->cms->getDraft()['social'] ?? [];
        $errors = [];
        foreach (SocialProfiles::NETWORKS as $key => $network) {
            if (! array_key_exists($key, $social) || (string) ($social[$key] ?? '') === (string) ($current[$key] ?? '')) {
                continue;
            }
            if ($key === 'whatsapp' && filter_var($social['whatsapp_use_contact'] ?? true, FILTER_VALIDATE_BOOLEAN)) {
                continue; // comes from Contact details
            }
            $problem = SocialProfiles::linkProblem($key, $social[$key] ?? '');
            if ($problem === 'format') {
                $errors["social.{$key}"] = ['Paste the full link, starting with https://'];
            } elseif ($problem === 'domain') {
                $article = preg_match('/^[AEIOU]/i', $network['name']) ? 'an' : 'a';
                $errors["social.{$key}"] = ["This doesn't look like {$article} {$network['name']} link."];
            }
        }

        return $errors
            ? ['status' => 422, 'message' => collect($errors)->flatten()->first(), 'errors' => $errors]
            : null;
    }

    /** Branding: names are required, the short name fits next to the logo, texts stay short. */
    public function branding(array $data): ?array
    {
        $branding = $data['branding'] ?? null;
        if (! is_array($branding)) {
            return null;
        }
        $current = $this->cms->getDraft()['branding'] ?? [];
        $rules = [
            'company_name' => ['required', 'string', 'max:120'],
            'short_name' => ['required', 'string', 'max:30'],
            'tagline' => ['nullable', 'string', 'max:120'],
            'website_description' => ['nullable', 'string', 'max:300'],
            'copyright' => ['nullable', 'string', 'max:150'],
            'copyright_auto_year' => ['nullable', 'boolean'],
            'website_logo' => ['nullable', 'string', 'max:500', 'url:https,http'],
            'website_favicon' => ['nullable', 'string', 'max:500', 'url:https,http'],
        ];
        $changed = array_filter(
            $rules,
            fn ($_, $key) => array_key_exists($key, $branding) && json_encode($branding[$key] ?? null) !== json_encode($current[$key] ?? null),
            ARRAY_FILTER_USE_BOTH,
        );
        if (! $changed) {
            return null;
        }
        $validator = \Illuminate\Support\Facades\Validator::make($branding, $changed, [
            'company_name.required' => 'Enter your school’s full name.',
            'short_name.required' => 'Enter a short name — it sits next to your logo.',
            'short_name.max' => 'Keep the short name to 30 characters so it fits next to the logo.',
        ]);
        if (! $validator->fails()) {
            return null;
        }
        $errors = collect($validator->errors()->toArray())->mapWithKeys(fn ($m, $k) => ["branding.{$k}" => $m])->all();

        return ['status' => 422, 'message' => collect($errors)->flatten()->first(), 'errors' => $errors];
    }

    /**
     * SEO: values the website reads must keep their format. A test address or "hide" is allowed
     * in a draft (the screen asks for confirmation before publishing), but must still be a link.
     */
    public function seo(array $data): ?array
    {
        $seo = $data['seo'] ?? null;
        if (! is_array($seo)) {
            return null;
        }
        $current = $this->cms->getDraft()['seo'] ?? [];
        $rules = [
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'string', 'max:500', 'url:https,http'],
            'twitter_card' => ['nullable', 'in:'.implode(',', SeoSettings::TWITTER_CARDS)],
            'canonical_url' => ['nullable', 'string', 'max:255', 'url:https,http'],
            'robots' => ['nullable', 'string', 'max:100'],
            'google_verification' => ['nullable', 'string', 'max:500'],
        ];
        $changed = array_filter(
            $rules,
            fn ($_, $key) => array_key_exists($key, $seo) && (string) ($seo[$key] ?? '') !== (string) ($current[$key] ?? ''),
            ARRAY_FILTER_USE_BOTH,
        );
        if (! $changed) {
            return null;
        }
        $validator = \Illuminate\Support\Facades\Validator::make($seo, $changed, [
            'canonical_url.url' => 'Enter your website’s full address, like https://www.yourschool.co.uk',
            'og_image.url' => 'The sharing image must be a full link starting with https://',
            'twitter_card.in' => 'Choose a picture size.',
        ]);
        $validator->after(function ($v) use ($seo, $changed) {
            if (isset($changed['google_verification']) && trim((string) ($seo['google_verification'] ?? '')) !== ''
                && strlen(SeoSettings::verificationCode($seo['google_verification'])) < 10) {
                $v->errors()->add('google_verification', 'That doesn’t look like a Google verification code. Paste the code, or the whole tag Google shows.');
            }
        });
        if (! $validator->fails()) {
            return null;
        }
        $errors = collect($validator->errors()->toArray())->mapWithKeys(fn ($m, $k) => ["seo.{$k}" => $m])->all();

        return ['status' => 422, 'message' => collect($errors)->flatten()->first(), 'errors' => $errors];
    }

    /** Analytics: a changed ID must match its tool's format (spaces and letter case are fixed for you). */
    public function analytics(array $data): ?array
    {
        $analytics = $data['analytics'] ?? null;
        if (! is_array($analytics)) {
            return null;
        }
        $current = $this->cms->getDraft()['analytics'] ?? [];
        $errors = [];
        foreach (AnalyticsSettings::TOOLS as $tool => $info) {
            if (! array_key_exists($tool, $analytics)
                || AnalyticsSettings::normalise($tool, $analytics[$tool] ?? '') === AnalyticsSettings::normalise($tool, $current[$tool] ?? '')) {
                continue;
            }
            if (! AnalyticsSettings::isValid($tool, $analytics[$tool] ?? '')) {
                $errors["analytics.{$tool}"] = ["It should look like {$info['example']}."];
            }
        }

        return $errors
            ? ['status' => 422, 'message' => collect($errors)->flatten()->first(), 'errors' => $errors]
            : null;
    }

    public const CUSTOM_CODE_BLOCKS = ['custom_css' => 'custom_css_enabled', 'head_scripts' => 'head_code_enabled', 'footer_scripts' => 'footer_code_enabled'];

    public const CUSTOM_CODE_MAX_BYTES = 51200;

    /**
     * Custom code runs on every page of the live website: changing it needs the
     * "edit website code" permission; each block is at most 50 KB, and CSS can't close its <style>.
     */
    public function customCode(array $data): ?array
    {
        $code = $data['custom_code'] ?? null;
        if (! is_array($code)) {
            return null;
        }
        $current = $this->cms->getDraft()['custom_code'] ?? [];
        $changed = [];
        foreach (self::CUSTOM_CODE_BLOCKS as $block => $flag) {
            if (array_key_exists($block, $code) && (string) ($code[$block] ?? '') !== (string) ($current[$block] ?? '')) {
                $changed[] = $block;
            }
            if (array_key_exists($flag, $code) && filter_var($code[$flag], FILTER_VALIDATE_BOOLEAN) !== filter_var($current[$flag] ?? true, FILTER_VALIDATE_BOOLEAN)) {
                $changed[] = $flag;
            }
        }
        if (! $changed) {
            return null;
        }
        if (! Auth::guard('admin')->user()?->can('edit website code')) {
            return ['status' => 403, 'message' => 'Only people allowed to edit website code can change Custom code.', 'errors' => []];
        }
        $errors = [];
        foreach (array_keys(self::CUSTOM_CODE_BLOCKS) as $block) {
            if (! in_array($block, $changed, true)) {
                continue;
            }
            $value = (string) ($code[$block] ?? '');
            if (strlen($value) > self::CUSTOM_CODE_MAX_BYTES) {
                $errors["custom_code.{$block}"] = ['This is over 50 KB. Keep custom code small — host larger files elsewhere and link to them.'];
            } elseif ($block === 'custom_css' && preg_match('#</\s*style#i', $value)) {
                $errors["custom_code.{$block}"] = ['CSS can’t contain “</style”. Paste only the CSS rules, without <style> tags.'];
            }
        }

        return $errors
            ? ['status' => 422, 'message' => collect($errors)->flatten()->first(), 'errors' => $errors]
            : null;
    }
}
