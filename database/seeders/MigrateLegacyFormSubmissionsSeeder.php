<?php

namespace Database\Seeders;

use App\Services\LegacyFormDataMigrator;
use Illuminate\Database\Seeder;

class MigrateLegacyFormSubmissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(LegacyFormDataMigrator::class)->migrateAll();
    }
}
