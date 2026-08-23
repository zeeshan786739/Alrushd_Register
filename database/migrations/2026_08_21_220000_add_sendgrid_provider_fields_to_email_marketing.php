<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive SendGrid provider correlation + event storage.
 * DO NOT run automatically — apply manually when ready.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('em_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('em_messages', 'correlation_uuid')) {
                $table->uuid('correlation_uuid')->nullable()->after('message_id');
            }
            if (! Schema::hasColumn('em_messages', 'provider')) {
                $table->string('provider', 32)->nullable()->after('delivery_error');
            }
            if (! Schema::hasColumn('em_messages', 'provider_message_id')) {
                $table->string('provider_message_id')->nullable()->after('provider');
            }
            if (! Schema::hasColumn('em_messages', 'provider_status')) {
                $table->string('provider_status', 40)->nullable()->after('provider_message_id');
            }
            if (! Schema::hasColumn('em_messages', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('sent_at');
            }
            if (! Schema::hasColumn('em_messages', 'opened_at')) {
                $table->timestamp('opened_at')->nullable()->after('delivered_at');
            }
            if (! Schema::hasColumn('em_messages', 'clicked_at')) {
                $table->timestamp('clicked_at')->nullable()->after('opened_at');
            }
            if (! Schema::hasColumn('em_messages', 'bounced_at')) {
                $table->timestamp('bounced_at')->nullable()->after('clicked_at');
            }
        });

        $this->tryIndex('em_messages', function (Blueprint $table) {
            $table->unique('correlation_uuid');
        });
        $this->tryIndex('em_messages', function (Blueprint $table) {
            $table->index(['organization_id', 'provider_status'], 'em_messages_org_provider_status_index');
            $table->index('provider_message_id', 'em_messages_provider_message_id_index');
        });

        Schema::table('em_campaign_recipients', function (Blueprint $table) {
            if (! Schema::hasColumn('em_campaign_recipients', 'correlation_uuid')) {
                $table->uuid('correlation_uuid')->nullable()->after('tracking_token');
            }
            if (! Schema::hasColumn('em_campaign_recipients', 'provider')) {
                $table->string('provider', 32)->nullable()->after('error_message');
            }
            if (! Schema::hasColumn('em_campaign_recipients', 'provider_message_id')) {
                $table->string('provider_message_id')->nullable()->after('provider');
            }
            if (! Schema::hasColumn('em_campaign_recipients', 'provider_status')) {
                $table->string('provider_status', 40)->nullable()->after('provider_message_id');
            }
            if (! Schema::hasColumn('em_campaign_recipients', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('sent_at');
            }
            // opened_at / clicked_at already exist on em_campaign_recipients.
            if (! Schema::hasColumn('em_campaign_recipients', 'bounced_at')) {
                $table->timestamp('bounced_at')->nullable()->after('clicked_at');
            }
        });

        $this->tryIndex('em_campaign_recipients', function (Blueprint $table) {
            $table->unique('correlation_uuid');
        });
        $this->tryIndex('em_campaign_recipients', function (Blueprint $table) {
            $table->index('provider_message_id', 'em_campaign_recipients_provider_message_id_index');
        });

        Schema::table('em_mailbox_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('em_mailbox_settings', 'inbound_domain')) {
                $table->string('inbound_domain')->nullable()->after('reply_to');
            }
            if (! Schema::hasColumn('em_mailbox_settings', 'inbound_enabled')) {
                $table->boolean('inbound_enabled')->default(false)->after('inbound_domain');
            }
            if (! Schema::hasColumn('em_mailbox_settings', 'open_tracking')) {
                $table->boolean('open_tracking')->default(true)->after('tracking_enabled');
            }
            if (! Schema::hasColumn('em_mailbox_settings', 'click_tracking')) {
                $table->boolean('click_tracking')->default(true)->after('open_tracking');
            }
        });

        if (! Schema::hasTable('em_provider_events')) {
            Schema::create('em_provider_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
                $table->string('provider', 32)->default('sendgrid');
                $table->string('provider_event_id')->nullable();
                $table->string('event_type', 40);
                $table->uuid('correlation_uuid')->nullable();
                $table->string('provider_message_id')->nullable();
                $table->foreignId('em_message_id')->nullable()->constrained('em_messages')->nullOnDelete();
                $table->foreignId('em_campaign_recipient_id')->nullable()->constrained('em_campaign_recipients')->nullOnDelete();
                $table->string('email')->nullable();
                $table->timestamp('occurred_at')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->unique(['provider', 'provider_event_id']);
                $table->index(['correlation_uuid', 'event_type']);
                $table->index(['organization_id', 'event_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('em_provider_events');

        Schema::table('em_mailbox_settings', function (Blueprint $table) {
            foreach (['click_tracking', 'open_tracking', 'inbound_enabled', 'inbound_domain'] as $col) {
                if (Schema::hasColumn('em_mailbox_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('em_campaign_recipients', function (Blueprint $table) {
            foreach (['bounced_at', 'delivered_at', 'provider_status', 'provider_message_id', 'provider', 'correlation_uuid'] as $col) {
                if (Schema::hasColumn('em_campaign_recipients', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('em_messages', function (Blueprint $table) {
            foreach ([
                'bounced_at', 'clicked_at', 'opened_at', 'delivered_at',
                'provider_status', 'provider_message_id', 'provider', 'correlation_uuid',
            ] as $col) {
                if (Schema::hasColumn('em_messages', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    private function tryIndex(string $table, callable $callback): void
    {
        try {
            Schema::table($table, $callback);
        } catch (\Throwable) {
            // Index may already exist on re-run.
        }
    }
};
