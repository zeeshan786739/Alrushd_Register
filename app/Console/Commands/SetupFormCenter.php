<?php

namespace App\Console\Commands;

use App\Services\LegacyFormDataMigrator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupFormCenter extends Command
{
    protected $signature = 'forms:setup {--fresh-submissions : Re-import legacy submissions into form_entries}';

    protected $description = 'Install Form Center tables, seed all form definitions, and import legacy submission data';

    public function handle(LegacyFormDataMigrator $migrator): int
    {
        $this->info('Running Form Center migrations...');
        Artisan::call('migrate', [
            '--force' => true,
            '--path' => 'database/migrations/2026_07_08_000001_create_dynamic_forms_tables.php',
        ]);
        Artisan::call('migrate', [
            '--force' => true,
            '--path' => 'database/migrations/2026_07_08_000002_add_legacy_tracking_to_form_entries.php',
        ]);
        $this->line(Artisan::output());

        $this->info('Seeding all form definitions...');
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\FormDefinitionsSeeder', '--force' => true]);
        $this->line(Artisan::output());

        if ($this->option('fresh-submissions')) {
            $this->warn('Clearing previously imported legacy submissions...');
            \App\Models\FormEntry::whereNotNull('legacy_source')->delete();
        }

        $this->info('Importing legacy submission data into form_entries...');
        $counts = $migrator->migrateAll();

        foreach ($counts as $source => $count) {
            $this->line("  {$source}: {$count} records");
        }

        $this->newLine();
        $this->info('Form Center is ready.');
        $this->line('Open CRM → Form Center to manage all forms.');

        return self::SUCCESS;
    }
}
