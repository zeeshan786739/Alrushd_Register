<?php

use App\Services\WebsiteCmsService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Website CMS → Announcement & portal gains: a show/hide switch, link text, colour, start/end
 * dates and an editable portal button label. Settings live in the website_cms JSON columns,
 * so this fills the new keys into every school's draft and published settings with values
 * that keep each live website exactly as it is:
 *   announcement_enabled = the bar already had text
 *   portal_button_text   = "Portal Login" (the label the website showed until now)
 *   link text empty      = the website keeps linking the whole message, as before
 *   colour gold, no dates
 */
return new class extends Migration
{
    private const NEW_KEYS = [
        'announcement_enabled', 'announcement_link_text', 'announcement_style',
        'announcement_starts_at', 'announcement_ends_at', 'portal_button_text',
    ];

    public function up(): void
    {
        $this->eachSettings(function (array $settings): array {
            $settings['website'] = is_array($settings['website'] ?? null) ? $settings['website'] : [];
            $settings = WebsiteCmsService::upgradeWebsiteSection($settings);
            $settings['website'] += [
                'announcement_link_text' => '',
                'announcement_style' => 'gold',
                'announcement_starts_at' => '',
                'announcement_ends_at' => '',
            ];

            return $settings;
        });
    }

    public function down(): void
    {
        $this->eachSettings(function (array $settings): array {
            if (is_array($settings['website'] ?? null)) {
                $settings['website'] = array_diff_key($settings['website'], array_flip(self::NEW_KEYS));
            }

            return $settings;
        });
    }

    private function eachSettings(callable $change): void
    {
        DB::table('website_cms')->orderBy('id')->each(function (object $row) use ($change) {
            $update = [];
            foreach (['draft', 'published'] as $column) {
                $settings = json_decode((string) $row->{$column}, true);
                if (is_array($settings)) {
                    $update[$column] = json_encode($change($settings));
                }
            }
            if ($update) {
                DB::table('website_cms')->where('id', $row->id)->update($update);
            }
        });
    }
};
