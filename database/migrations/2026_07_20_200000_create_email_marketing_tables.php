<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('em_mailbox_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('reply_to')->nullable();
            $table->string('smtp_host')->nullable();
            $table->unsignedSmallInteger('smtp_port')->nullable();
            $table->string('smtp_encryption')->nullable();
            $table->string('smtp_username')->nullable();
            $table->text('smtp_password')->nullable();
            $table->string('imap_host')->nullable();
            $table->unsignedSmallInteger('imap_port')->nullable();
            $table->string('imap_encryption')->nullable();
            $table->string('imap_username')->nullable();
            $table->text('imap_password')->nullable();
            $table->string('inbox_folder')->default('INBOX');
            $table->string('sent_folder')->nullable();
            $table->boolean('validate_cert')->default(true);
            $table->boolean('tracking_enabled')->default(true);
            $table->boolean('is_enabled')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_sync_status')->nullable();
            $table->text('last_sync_error')->nullable();
            $table->timestamps();

            $table->unique('organization_id');
        });

        Schema::create('em_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name');
            $table->string('subject')->nullable();
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'is_active']);
        });

        Schema::create('em_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('folder')->default('inbox'); // inbox|sent|draft
            $table->string('direction')->default('inbound'); // inbound|outbound
            $table->string('message_id')->nullable();
            $table->string('imap_uid')->nullable();
            $table->string('thread_id')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('em_messages')->nullOnDelete();
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->text('to')->nullable();
            $table->text('cc')->nullable();
            $table->text('bcc')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->string('delivery_status')->nullable(); // queued|sending|sent|failed|cancelled
            $table->text('delivery_error')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->foreignId('lead_id')->nullable()->constrained('crm_leads')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('crm_customers')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'folder']);
            $table->index(['organization_id', 'is_starred']);
            $table->index(['organization_id', 'delivery_status']);
            $table->unique(['organization_id', 'message_id']);
            $table->unique(['organization_id', 'imap_uid']);
        });

        Schema::create('em_message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('message_id')->constrained('em_messages')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();

            $table->index(['organization_id', 'message_id']);
        });

        Schema::create('em_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name');
            $table->string('subject');
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->foreignId('template_id')->nullable()->constrained('em_templates')->nullOnDelete();
            $table->string('status')->default('draft'); // draft|scheduled|sending|sent|failed|cancelled
            $table->string('recipient_source')->default('manual'); // leads|customers|form_entries|manual|selected_leads|selected_customers
            $table->json('recipient_filters')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('recipient_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('opened_count')->default(0);
            $table->unsignedInteger('clicked_count')->default(0);
            $table->boolean('tracking_enabled')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'scheduled_at']);
        });

        Schema::create('em_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('campaign_id')->constrained('em_campaigns')->cascadeOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->foreignId('lead_id')->nullable()->constrained('crm_leads')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('crm_customers')->nullOnDelete();
            $table->foreignId('form_entry_id')->nullable()->constrained('form_entries')->nullOnDelete();
            $table->string('status')->default('pending'); // pending|queued|sent|failed|skipped
            $table->string('tracking_token', 64)->unique();
            $table->boolean('is_opened')->default(false);
            $table->boolean('is_clicked')->default(false);
            $table->unsignedInteger('open_count')->default(0);
            $table->unsignedInteger('click_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'email']);
            $table->index(['organization_id', 'campaign_id', 'status']);
        });

        Schema::create('em_suppressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('email');
            $table->string('reason')->nullable();
            $table->string('token', 64)->unique();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'email']);
        });

        Schema::create('em_sync_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('mailbox')->default('INBOX');
            $table->string('last_uid')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'mailbox']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('em_sync_states');
        Schema::dropIfExists('em_suppressions');
        Schema::dropIfExists('em_campaign_recipients');
        Schema::dropIfExists('em_campaigns');
        Schema::dropIfExists('em_message_attachments');
        Schema::dropIfExists('em_messages');
        Schema::dropIfExists('em_templates');
        Schema::dropIfExists('em_mailbox_settings');
    }
};
