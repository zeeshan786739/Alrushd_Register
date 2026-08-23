<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive marketing suppression + ASM group fields.
 * DO NOT run automatically.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('em_suppressions', function (Blueprint $table) {
            if (! Schema::hasColumn('em_suppressions', 'source')) {
                $table->string('source', 40)->nullable()->after('reason');
            }
            if (! Schema::hasColumn('em_suppressions', 'provider')) {
                $table->string('provider', 40)->nullable()->after('source');
            }
            if (! Schema::hasColumn('em_suppressions', 'provider_group_id')) {
                $table->unsignedBigInteger('provider_group_id')->nullable()->after('provider');
            }
            if (! Schema::hasColumn('em_suppressions', 'resubscribed_at')) {
                $table->timestamp('resubscribed_at')->nullable()->after('unsubscribed_at');
            }
        });

        Schema::table('em_mailbox_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('em_mailbox_settings', 'sendgrid_asm_group_id')) {
                $table->unsignedBigInteger('sendgrid_asm_group_id')->nullable()->after('click_tracking');
            }
        });
    }

    public function down(): void
    {
        Schema::table('em_suppressions', function (Blueprint $table) {
            foreach (['source', 'provider', 'provider_group_id', 'resubscribed_at'] as $column) {
                if (Schema::hasColumn('em_suppressions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('em_mailbox_settings', function (Blueprint $table) {
            if (Schema::hasColumn('em_mailbox_settings', 'sendgrid_asm_group_id')) {
                $table->dropColumn('sendgrid_asm_group_id');
            }
        });
    }
};
