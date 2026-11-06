<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the admission intake (such as "Sept 27/28") and year groups for a parent's
 * other children. The first child's year stays in year_group.
 *
 * Existing leads are filled in from their imported "Current status" column and from
 * the "students" list on their form submission. Only a clear September intake is
 * recognised; anything else is left blank for staff to set.
 *
 * The parsing is deliberately duplicated rather than read from LeadFormOptions:
 * a migration must not change when app code changes.
 */
return new class extends Migration
{
    private const MAX_CHILDREN = 4;

    public function up(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->string('admission_intake', 30)->nullable()->after('year_group')->index();
            $table->json('extra_year_groups')->nullable()->after('admission_intake');
        });

        DB::table('crm_leads')
            ->select(['id', 'year_group', 'custom_data', 'form_entry_id'])
            ->where(fn ($query) => $query->whereNotNull('custom_data')->orWhereNotNull('form_entry_id'))
            ->orderBy('id')
            ->chunkById(500, function ($leads) {
                $entries = DB::table('form_entries')
                    ->whereIn('id', $leads->pluck('form_entry_id')->filter()->all())
                    ->pluck('data', 'id');

                foreach ($leads as $lead) {
                    $changes = $this->backfill($lead, $entries[$lead->form_entry_id] ?? null);
                    if ($changes !== []) {
                        DB::table('crm_leads')->where('id', $lead->id)->update($changes);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->dropIndex(['admission_intake']);
            $table->dropColumn(['admission_intake', 'extra_year_groups']);
        });
    }

    /** @return array<string, mixed> */
    private function backfill(object $lead, ?string $entryData): array
    {
        $custom = json_decode((string) $lead->custom_data, true);
        $data = json_decode((string) $entryData, true);
        $fields = is_array($data['fields'] ?? null) ? $data['fields'] : (is_array($data) ? $data : []);
        $students = array_values(array_filter(is_array($fields['students'] ?? null) ? $fields['students'] : [], 'is_array'));

        $candidates = [is_array($custom) ? ($custom['Current status'] ?? null) : null, $fields['student_start_date'] ?? null];
        foreach ($students as $student) {
            $candidates[] = $student['start_date'] ?? null;
        }

        $changes = [];
        foreach ($candidates as $candidate) {
            if (is_scalar($candidate) && ($intake = $this->parseIntake((string) $candidate)) !== null) {
                $changes['admission_intake'] = $intake;
                break;
            }
        }

        $years = [];
        foreach ($students as $student) {
            $year = $student['year_group'] ?? null;
            if (is_string($year) && trim($year) !== '') {
                $years[] = mb_substr(trim($year), 0, 50);
            }
        }
        $years = array_slice($years, 0, self::MAX_CHILDREN);

        if ($lead->year_group === null || trim((string) $lead->year_group) === '') {
            if ($years !== []) {
                $changes['year_group'] = $years[0];
            }
        }
        if (count($years) > 1) {
            $changes['extra_year_groups'] = json_encode(array_slice($years, 1));
        }

        return $changes;
    }

    private function parseIntake(string $text): ?string
    {
        $text = trim($text);

        if (preg_match('/^(20\d{2})-(\d{1,2})-\d{1,2}/', $text, $match)) {
            return (int) $match[2] === 9 ? $this->intakeForYear((int) substr($match[1], 2)) : null;
        }
        if (preg_match('/^\d{1,2}[\/.-](\d{1,2})[\/.-](?:20)?(\d{2})$/', $text, $match)) {
            return (int) $match[1] === 9 ? $this->intakeForYear((int) $match[2]) : null;
        }
        if (preg_match('/(?<!\d)(?:20)?(\d{2})\s*[\/-]\s*(?:20)?(\d{2})(?!\d)/', $text, $match) && (int) $match[2] === (int) $match[1] + 1) {
            return $this->intakeForYear((int) $match[1]);
        }
        if (preg_match('/\bsep(?:t|tember)?\b/i', $text)) {
            if (preg_match('/\b20(\d{2})\b/', $text, $match)) {
                return $this->intakeForYear((int) $match[1]);
            }
            if (preg_match('/\bsep(?:t|tember)?\b\W*(\d{2})\b/i', $text, $match)) {
                return $this->intakeForYear((int) $match[1]);
            }
        }

        return null;
    }

    private function intakeForYear(int $year): string
    {
        return sprintf('Sept %02d/%02d', $year, $year + 1);
    }
};
