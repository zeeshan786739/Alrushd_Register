<?php

namespace App\Console\Commands;

use App\Models\Form;
use App\Models\FormEntry;
use App\Models\Organization;
use App\Services\LegacyFormDataMigrator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class SetupFormCenter extends Command
{
    protected $signature = 'forms:setup
                            {--fresh-submissions : Delete previously imported legacy form_entries (legacy_source not null) before re-import}
                            {--skip-submissions : Seed form definitions only; do not import legacy submissions}';

    protected $description = 'Seed Form Center definitions and idempotently import legacy submissions into form_entries';

    public function handle(LegacyFormDataMigrator $migrator): int
    {
        if (! Schema::hasTable('forms') || ! Schema::hasTable('form_entries')) {
            $this->error('Dynamic Forms tables are missing. Run migrations first:');
            $this->line('  php artisan migrate --force');

            return self::FAILURE;
        }

        if (Schema::hasTable('organizations')) {
            $organization = Organization::default();
            $this->info("Using organization #{$organization->id} ({$organization->slug}).");
        }

        $this->info('Ensuring Form Center migrations are applied...');
        Artisan::call('migrate', [
            '--force' => true,
            '--path' => 'database/migrations/2026_07_08_000001_create_dynamic_forms_tables.php',
        ]);
        Artisan::call('migrate', [
            '--force' => true,
            '--path' => 'database/migrations/2026_07_08_000002_add_legacy_tracking_to_form_entries.php',
        ]);
        $migrateOutput = trim(Artisan::output());
        if ($migrateOutput !== '') {
            $this->line($migrateOutput);
        }

        $formsBefore = Form::count();
        $entriesBefore = FormEntry::count();

        $this->info('Seeding form definitions (idempotent, non-destructive)...');
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\FormDefinitionsSeeder',
            '--force' => true,
        ]);
        $this->line(trim(Artisan::output()));

        $formsAfterSeed = Form::count();
        $this->line("  Forms before: {$formsBefore}; after seed: {$formsAfterSeed}.");

        if ($this->option('skip-submissions')) {
            $this->warn('Skipping legacy submission import (--skip-submissions).');
            $this->printReadySummary();

            return self::SUCCESS;
        }

        if ($this->option('fresh-submissions')) {
            $this->warn('Clearing previously imported legacy submissions only (legacy_source IS NOT NULL)...');
            $deleted = FormEntry::query()->whereNotNull('legacy_source')->delete();
            $this->line("  Removed {$deleted} imported legacy form_entries. Legacy source tables were not modified.");
        }

        $this->info('Importing legacy submission data into form_entries (updateOrCreate by legacy key)...');
        $counts = $migrator->migrateAll();

        $imported = 0;
        foreach ($counts as $source => $count) {
            $this->line("  {$source}: {$count} records");
            $imported += $count;
        }

        if ($imported === 0 && Form::count() === 0) {
            $this->error('No forms were seeded. Check FormDefinitionsSeeder.');

            return self::FAILURE;
        }

        if ($imported === 0) {
            $this->warn('No legacy submissions were imported. Definitions may exist without matching legacy tables/slugs.');
        }

        $this->line('  form_entries before: '.$entriesBefore.'; after: '.FormEntry::count().'.');
        $this->printReadySummary();

        return self::SUCCESS;
    }

    private function printReadySummary(): void
    {
        $this->newLine();
        $this->info('Form Center is ready.');
        $this->line('  forms='.Form::count());
        $this->line('  form_steps='.(Schema::hasTable('form_steps') ? \App\Models\FormStep::count() : 0));
        $this->line('  form_fields='.(Schema::hasTable('form_fields') ? \App\Models\FormField::count() : 0));
        $this->line('  form_entries='.FormEntry::count());
        $this->line('Open CRM → Form Center to manage all forms.');
        $this->line('Re-running this command is safe: definitions upsert by slug; entries upsert by legacy key.');
    }
}
