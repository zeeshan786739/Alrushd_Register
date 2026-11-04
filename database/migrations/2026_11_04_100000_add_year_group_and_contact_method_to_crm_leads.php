<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->string('year_group', 50)->nullable()->after('phone');
            $table->string('contact_method', 100)->nullable()->after('year_group');
            $table->index(['organization_id', 'year_group'], 'crm_leads_org_year_group_idx');
            $table->index(['organization_id', 'contact_method'], 'crm_leads_org_contact_method_idx');
        });

        $this->backfill();
    }

    public function down(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->dropIndex('crm_leads_org_year_group_idx');
            $table->dropIndex('crm_leads_org_contact_method_idx');
            $table->dropColumn(['year_group', 'contact_method']);
        });
    }

    /**
     * Imported spreadsheets already carry these values in custom_data, and
     * enquiry forms carry a year group, so existing leads do not start empty.
     */
    public function backfill(): void
    {
        DB::table('crm_leads')
            ->select(['crm_leads.id', 'crm_leads.custom_data', 'form_entries.data as entry_data'])
            ->leftJoin('form_entries', 'form_entries.id', '=', 'crm_leads.form_entry_id')
            ->where(function ($query) {
                $query->whereNotNull('crm_leads.custom_data')->orWhereNotNull('crm_leads.form_entry_id');
            })
            ->orderBy('crm_leads.id')
            ->chunk(500, function ($rows) {
                foreach ($rows as $row) {
                    $custom = json_decode((string) $row->custom_data, true);
                    $custom = is_array($custom) ? $custom : [];
                    $entry = json_decode((string) $row->entry_data, true);
                    $entry = is_array($entry) ? $entry : [];
                    $fields = is_array($entry['fields'] ?? null) ? $entry['fields'] : $entry;

                    $year = $this->text($custom['Year'] ?? null) ?? $this->text($fields['year_group'] ?? null);
                    $method = $this->contactMethod($this->text($custom['Lead Status'] ?? null));

                    $updates = array_filter([
                        'year_group' => $year !== null ? mb_substr($year, 0, 50) : null,
                        'contact_method' => $method !== null ? mb_substr($method, 0, 100) : null,
                    ], fn ($value) => $value !== null);

                    if ($updates !== []) {
                        DB::table('crm_leads')->where('id', $row->id)->update($updates);
                    }
                }
            });
    }

    private function text(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $text = trim((string) $value);

        return $text !== '' ? $text : null;
    }

    /** Align common spreadsheet spellings with the dropdown; keep anything else as entered. */
    private function contactMethod(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return match (mb_strtolower($value)) {
            'call', 'phone', 'phone call', 'telephone', 'called' => 'Phone call',
            'whatsapp', 'whats app', 'wa' => 'WhatsApp',
            'email', 'e-mail', 'mail' => 'Email',
            'sms', 'text', 'text message' => 'SMS',
            'in person', 'in-person', 'visit' => 'In person',
            default => $value,
        };
    }
};
