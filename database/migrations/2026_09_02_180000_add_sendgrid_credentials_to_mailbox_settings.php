<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('em_mailbox_settings', function (Blueprint $table) {
            $table->text('sendgrid_api_key')->nullable()->after('inbound_enabled');
            $table->text('sendgrid_event_webhook_public_key')->nullable()->after('sendgrid_api_key');
        });
    }

    public function down(): void
    {
        Schema::table('em_mailbox_settings', function (Blueprint $table) {
            $table->dropColumn([
                'sendgrid_api_key',
                'sendgrid_event_webhook_public_key',
            ]);
        });
    }
};
