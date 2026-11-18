<?php

use App\Services\WebsiteCmsService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Website CMS → Contact details gets structured settings (WhatsApp switch / number / first
 * message, opening days / times / time zone) that generate the existing `whatsapp` link and
 * `working_hours` text the website reads. Settings live in the website_cms JSON columns.
 *
 * Existing values are read into the new fields only when they rebuild to exactly the same
 * link / text (see App\Support\ContactDetails); otherwise the fields stay unset and the old
 * value is kept as it is until someone edits it. Nothing changes on any live website.
 */
return new class extends Migration
{
    private const NEW_KEYS = [
        'whatsapp_enabled', 'whatsapp_same_as_phone', 'whatsapp_number', 'whatsapp_message',
        'opening_days', 'opening_from', 'opening_until', 'opening_timezone',
    ];

    public function up(): void
    {
        $this->eachSettings(function (array $settings): array {
            return is_array($settings['contact'] ?? null)
                ? WebsiteCmsService::upgradeContactSection($settings)
                : $settings;
        });
    }

    public function down(): void
    {
        $this->eachSettings(function (array $settings): array {
            if (is_array($settings['contact'] ?? null)) {
                $settings['contact'] = array_diff_key($settings['contact'], array_flip(self::NEW_KEYS));
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
