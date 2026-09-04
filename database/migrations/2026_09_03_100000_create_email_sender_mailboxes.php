<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('em_sender_mailboxes')) {
            Schema::create('em_sender_mailboxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('email');
            $table->string('reply_to')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('imap_host')->nullable();
            $table->unsignedSmallInteger('imap_port')->nullable();
            $table->string('imap_encryption')->nullable();
            $table->string('imap_username')->nullable();
            $table->text('imap_password')->nullable();
            $table->string('inbox_folder')->default('INBOX');
            $table->boolean('validate_cert')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_sync_status')->nullable();
            $table->text('last_sync_error')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'email']);
            $table->index(['organization_id', 'is_active', 'is_verified']);
            });
        }

        if (! Schema::hasColumn('em_messages', 'sender_mailbox_id')) {
            Schema::table('em_messages', function (Blueprint $table) {
                $table->dropUnique(['organization_id', 'imap_uid']);
                $table->foreignId('sender_mailbox_id')->nullable()->after('organization_id')
                    ->constrained('em_sender_mailboxes')->nullOnDelete();
                $table->index(['organization_id', 'sender_mailbox_id', 'folder'], 'em_messages_org_mailbox_folder_idx');
                $table->unique(['organization_id', 'sender_mailbox_id', 'imap_uid'], 'em_messages_org_sender_uid_unique');
            });
        }

        if (! Schema::hasColumn('em_campaigns', 'sender_mailbox_id')) {
            Schema::table('em_campaigns', function (Blueprint $table) {
                $table->foreignId('sender_mailbox_id')->nullable()->after('organization_id')
                    ->constrained('em_sender_mailboxes')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('em_sync_states', 'sender_mailbox_id')) {
            Schema::table('em_sync_states', function (Blueprint $table) {
                // MySQL may use the old composite unique index to support the
                // organization foreign key. Give that FK its own index first.
                $table->index('organization_id', 'em_sync_states_organization_id_idx');
                $table->dropUnique(['organization_id', 'mailbox']);
                $table->foreignId('sender_mailbox_id')->nullable()->after('organization_id')
                    ->constrained('em_sender_mailboxes')->cascadeOnDelete();
                $table->unique(['organization_id', 'sender_mailbox_id', 'mailbox'], 'em_sync_states_org_sender_folder_unique');
            });
        }

        foreach (DB::table('em_mailbox_settings')->orderBy('id')->get() as $settings) {
            if (! $settings->from_email) {
                continue;
            }

            DB::table('em_sender_mailboxes')->updateOrInsert(
                ['organization_id' => $settings->organization_id, 'email' => strtolower($settings->from_email)],
                [
                'name' => $settings->from_name,
                'reply_to' => $settings->reply_to,
                'is_verified' => true,
                'is_default' => true,
                'is_active' => (bool) $settings->is_enabled,
                'imap_host' => $settings->imap_host,
                'imap_port' => $settings->imap_port,
                'imap_encryption' => $settings->imap_encryption,
                'imap_username' => $settings->imap_username,
                'imap_password' => $settings->imap_password,
                'inbox_folder' => $settings->inbox_folder ?: 'INBOX',
                'validate_cert' => (bool) $settings->validate_cert,
                'last_synced_at' => $settings->last_synced_at,
                'last_sync_status' => $settings->last_sync_status,
                'last_sync_error' => $settings->last_sync_error,
                'created_at' => now(),
                'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::table('em_sync_states', function (Blueprint $table) {
            $table->dropUnique('em_sync_states_org_sender_folder_unique');
            $table->dropConstrainedForeignId('sender_mailbox_id');
            $table->unique(['organization_id', 'mailbox']);
        });
        Schema::table('em_campaigns', fn (Blueprint $table) => $table->dropConstrainedForeignId('sender_mailbox_id'));
        Schema::table('em_messages', function (Blueprint $table) {
            $table->dropUnique('em_messages_org_sender_uid_unique');
            $table->dropIndex('em_messages_org_mailbox_folder_idx');
            $table->dropConstrainedForeignId('sender_mailbox_id');
            $table->unique(['organization_id', 'imap_uid']);
        });
        Schema::dropIfExists('em_sender_mailboxes');
    }
};
