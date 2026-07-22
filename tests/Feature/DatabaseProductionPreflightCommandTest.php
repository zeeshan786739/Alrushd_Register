<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseProductionPreflightCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_preflight_command_is_registered_and_read_only_safe_on_migrated_schema(): void
    {
        $this->assertSame(
            'alrushd_register_test',
            config('database.connections.mysql.database'),
            'Destructive tests must never target the production clone.'
        );

        $beforeMigrations = DB::table('migrations')->count();
        $beforeTables = collect(DB::select(
            "SELECT COUNT(*) AS c FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE'"
        ))->first()->c;

        $exit = Artisan::call('database:production-preflight');
        $output = Artisan::output();

        $this->assertSame(0, $exit, $output);
        $this->assertStringContainsString('RESULT: SAFE TO PROCEED', $output);
        $this->assertSame($beforeMigrations, DB::table('migrations')->count());
        $afterTables = collect(DB::select(
            "SELECT COUNT(*) AS c FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE'"
        ))->first()->c;
        $this->assertSame($beforeTables, $afterTables);
    }

    public function test_preflight_fails_when_backup_confirmation_required_but_missing(): void
    {
        $exit = Artisan::call('database:production-preflight', [
            '--require-backup' => true,
        ]);

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('Backup confirmation required', Artisan::output());
    }
}
