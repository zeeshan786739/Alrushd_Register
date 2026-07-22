<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * Read-only production deployment preflight checks.
 * Never writes to the database.
 */
class DatabaseProductionPreflightCommand extends Command
{
    protected $signature = 'database:production-preflight
                            {--require-backup : Fail unless --backup-confirmed is also passed}
                            {--backup-confirmed : Operator confirms a verified DB backup exists}
                            {--json : Emit machine-readable JSON summary}';

    protected $description = 'Read-only checks before production database migration deployment';

    /** @var list<string> */
    private array $errors = [];

    /** @var list<string> */
    private array $warnings = [];

    /** @var list<string> */
    private array $infoLines = [];

    /** @var array<string, string> migration basename without .php => expected table */
    private array $historicalCreates = [
        '2025_07_21_050425_create_additional_islamic_subjects_table' => 'additional_islamic_subjects',
        '2025_07_21_062333_create_additional_subject_student_islamic_table' => 'additional_subject_student_islamic',
        '2025_07_27_081743_create_coupons_table' => 'coupons',
        '2025_07_29_092340_create_time_tables_table' => 'time_tables',
        '2025_07_30_045156_create_guardiants_table' => 'guardiants',
        '2025_08_03_074503_create_additional_hifdh_programme_student_table' => 'additional_hifdh_programme_student',
        '2025_08_05_101337_create_staff_admission_forms_table' => 'staff_admission_forms',
        '2025_08_18_061618_create_mettings_table' => 'mettings',
        '2025_08_24_064004_create_debits_table' => 'debits',
        '2025_08_24_104722_create_debit_students_table' => 'debit_students',
        '2025_09_09_061641_create_enquires_table' => 'enquires',
        '2025_09_09_151150_create_referrals_table' => 'referrals',
        '2025_09_11_050205_create_open_events_table' => 'open_events',
        '2025_09_11_050211_create_open_event_items_table' => 'open_event_items',
        '2025_09_11_051404_create_meet_speakers_table' => 'meet_speakers',
        '2025_09_14_142517_create_open_event_forms_table' => 'open_event_forms',
        '2025_10_16_053652_create_nationalities_table' => 'nationalities',
        '2025_10_16_053832_create_admission_dates_table' => 'admission_dates',
        '2025_10_16_053915_create_genders_table' => 'genders',
        '2025_10_16_053951_create_terms_and_conditions_table' => 'terms_and_conditions',
        '2025_10_20_114920_create_relation_ships_table' => 'relation_ships',
        '2025_10_20_123227_create_payment_countries_table' => 'payment_countries',
        '2025_10_22_113517_create_api_apply_nows_table' => 'api_apply_nows',
        '2025_10_22_113540_create_api_subscriptions_table' => 'api_subscriptions',
        '2025_10_22_132001_create_api_contact_forms_table' => 'api_contact_forms',
        '2025_10_22_135921_create_api_submissions_table' => 'api_submissions',
        '2025_10_29_042807_create_countries_table' => 'countries',
        '2025_11_04_051104_create_job_applications_table' => 'job_applications',
    ];

    /** @var list<string> */
    private array $featureMigrations = [
        '2026_07_08_000001_create_dynamic_forms_tables',
        '2026_07_08_000002_add_legacy_tracking_to_form_entries',
        '2026_07_09_000001_normalize_form_handlers_for_dynamic_theme',
        '2026_07_20_100000_create_organizations_and_crm_tables',
        '2026_07_20_120000_backfill_organization_ids',
        '2026_07_20_200000_create_email_marketing_tables',
    ];

    public function handle(): int
    {
        $identity = DB::selectOne(
            'SELECT DATABASE() AS database_name, CURRENT_USER() AS database_user, @@hostname AS database_server, VERSION() AS mysql_version'
        );

        $this->infoLines[] = 'database='.($identity->database_name ?? 'null');
        $this->infoLines[] = 'user='.($identity->database_user ?? 'null');
        $this->infoLines[] = 'server='.($identity->database_server ?? 'null');
        $this->infoLines[] = 'mysql='.($identity->mysql_version ?? 'null');
        $this->infoLines[] = 'app_env='.config('app.env');
        $this->infoLines[] = 'db_host='.config('database.connections.'.config('database.default').'.host');

        if ($this->option('require-backup') && ! $this->option('backup-confirmed')) {
            $this->errors[] = 'Backup confirmation required: pass --backup-confirmed after a verified backup.';
        }

        $applied = DB::table('migrations')->pluck('migration')->all();
        $files = collect(File::files(database_path('migrations')))
            ->map(fn ($f) => pathinfo($f->getFilename(), PATHINFO_FILENAME))
            ->sort()
            ->values()
            ->all();

        $pending = array_values(array_diff($files, $applied));
        $this->infoLines[] = 'migration_files='.count($files);
        $this->infoLines[] = 'migrations_applied='.count($applied);
        $this->infoLines[] = 'migrations_pending='.count($pending);

        foreach ($pending as $migration) {
            $this->infoLines[] = 'pending:'.$migration;
        }

        foreach ($this->historicalCreates as $migration => $table) {
            $isPending = in_array($migration, $pending, true);
            $exists = Schema::hasTable($table);
            $path = database_path('migrations/'.$migration.'.php');
            $src = is_file($path) ? (string) file_get_contents($path) : '';
            $guarded = str_contains($src, "Schema::hasTable('{$table}')")
                || str_contains($src, "Schema::hasTable(\"{$table}\")");

            if ($isPending && $exists && ! $guarded) {
                $this->errors[] = "Pending create {$migration} targets existing table {$table} without Schema::hasTable guard.";
            }

            if ($isPending && $exists && $guarded) {
                $this->infoLines[] = "safe_noop_expected:{$migration}";
            }

            if ($isPending && ! $exists && ! $guarded) {
                $this->warnings[] = "Pending create {$migration}: table {$table} missing (fresh-install create path).";
            }
        }

        foreach ($this->featureMigrations as $migration) {
            if (! in_array($migration, $pending, true) && ! in_array($migration, $applied, true)) {
                $this->errors[] = "Feature migration file missing from repository: {$migration}";
            }
        }

        $featurePending = array_values(array_intersect($pending, $this->featureMigrations));
        $unexpectedPending = array_values(array_diff($pending, array_merge(array_keys($this->historicalCreates), $this->featureMigrations)));
        foreach ($unexpectedPending as $migration) {
            $this->warnings[] = "Unexpected pending migration: {$migration}";
        }

        // Required tables for current application core
        foreach (['users', 'admins', 'form_submissions', 'permissions', 'roles', 'settings'] as $table) {
            if (! Schema::hasTable($table)) {
                $this->errors[] = "Required core table missing: {$table}";
            }
        }

        // Feature dependency graph when CRM/EM code is present
        $needsCrm = class_exists(\App\Models\Crm\Lead::class);
        $needsEm = class_exists(\App\Models\EmailMarketing\Campaign::class);

        if ($needsCrm && ! Schema::hasTable('organizations') && ! in_array('2026_07_20_100000_create_organizations_and_crm_tables', $pending, true)
            && ! in_array('2026_07_20_100000_create_organizations_and_crm_tables', $applied, true)) {
            $this->errors[] = 'CRM code present but organizations migration is neither applied nor pending.';
        }

        if ($needsEm && Schema::hasTable('organizations') && ! Schema::hasTable('em_campaigns')
            && ! in_array('2026_07_20_200000_create_email_marketing_tables', $pending, true)
            && ! in_array('2026_07_20_200000_create_email_marketing_tables', $applied, true)) {
            $this->errors[] = 'Email Marketing code present but em_campaigns migration is neither applied nor pending.';
        }

        if (Schema::hasTable('organizations') && Schema::hasColumn('admins', 'organization_id')) {
            $nullAdmins = DB::table('admins')->whereNull('organization_id')->count();
            if ($nullAdmins > 0) {
                $this->warnings[] = "admins with null organization_id={$nullAdmins}";
            }
        }

        if (Schema::hasTable('forms') && Schema::hasColumn('forms', 'organization_id')) {
            $nullForms = DB::table('forms')->whereNull('organization_id')->count();
            if ($nullForms > 0) {
                $this->warnings[] = "forms with null organization_id={$nullForms}";
            }
        }

        if (Schema::hasTable('form_entries') && Schema::hasColumn('form_entries', 'organization_id')) {
            $nullEntries = DB::table('form_entries')->whereNull('organization_id')->count();
            if ($nullEntries > 0) {
                $this->warnings[] = "form_entries with null organization_id={$nullEntries}";
            }
        }

        // FK prerequisites for upcoming CRM migration
        if (in_array('2026_07_20_100000_create_organizations_and_crm_tables', $pending, true)) {
            if (! Schema::hasTable('forms') && ! in_array('2026_07_08_000001_create_dynamic_forms_tables', $pending, true)
                && ! in_array('2026_07_08_000001_create_dynamic_forms_tables', $applied, true)) {
                $this->errors[] = 'CRM migration pending but dynamic forms migration is unavailable.';
            }
            if (! Schema::hasTable('form_submissions')) {
                $this->errors[] = 'CRM migration requires form_submissions table.';
            }
            if (! Schema::hasTable('admins')) {
                $this->errors[] = 'CRM migration requires admins table.';
            }
        }

        if (in_array('2026_07_20_200000_create_email_marketing_tables', $pending, true)) {
            $crmPending = in_array('2026_07_20_100000_create_organizations_and_crm_tables', $pending, true);
            if (! Schema::hasTable('organizations') && ! $crmPending) {
                $this->errors[] = 'Email Marketing migration requires organizations (or CRM migration pending first).';
            }
        }

        // Duplicate emails that would block unique indexes on users
        if (Schema::hasTable('users')) {
            $dupUsers = DB::table('users')
                ->select('email', DB::raw('COUNT(*) as c'))
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->groupBy('email')
                ->having('c', '>', 1)
                ->count();
            if ($dupUsers > 0) {
                $this->warnings[] = "duplicate_user_email_groups={$dupUsers}";
            }
        }

        $safe = count($this->errors) === 0;

        if ($this->option('json')) {
            $this->line(json_encode([
                'safe' => $safe,
                'errors' => $this->errors,
                'warnings' => $this->warnings,
                'info' => $this->infoLines,
                'feature_pending' => $featurePending,
            ], JSON_PRETTY_PRINT));
        } else {
            $this->info('Database production preflight (read-only)');
            foreach ($this->infoLines as $line) {
                $this->line('  '.$line);
            }
            foreach ($this->warnings as $warning) {
                $this->warn('WARN: '.$warning);
            }
            foreach ($this->errors as $error) {
                $this->error('ERROR: '.$error);
            }
            $this->newLine();
            $this->line($safe ? 'RESULT: SAFE TO PROCEED (subject to operator backup policy)' : 'RESULT: UNSAFE — DO NOT MIGRATE');
        }

        return $safe ? self::SUCCESS : self::FAILURE;
    }
}
