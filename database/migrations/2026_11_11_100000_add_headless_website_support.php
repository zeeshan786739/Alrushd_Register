<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Headless website support.
 *
 * A school can keep the built-in Laravel landing page ("builtin") or connect an
 * external front end (e.g. the Next.js site) that reads its content from the
 * CRM over a token-authenticated API ("headless").
 *
 * website_pages holds the pages the CMS manages for a headless site: "system"
 * pages that exist in the front end's code (only their SEO / navigation is
 * managed here) and "custom" pages built from content blocks in the CMS.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_cms', function (Blueprint $table) {
            if (! Schema::hasColumn('website_cms', 'mode')) {
                $table->string('mode', 20)->default('builtin')->after('organization_id');
            }
            if (! Schema::hasColumn('website_cms', 'external_url')) {
                $table->string('external_url')->nullable()->after('mode');
            }
            if (! Schema::hasColumn('website_cms', 'api_token')) {
                // Encrypted at rest; api_token_hash is used for lookups.
                $table->text('api_token')->nullable()->after('external_url');
            }
            if (! Schema::hasColumn('website_cms', 'api_token_hash')) {
                $table->string('api_token_hash', 64)->nullable()->unique()->after('api_token');
            }
            if (! Schema::hasColumn('website_cms', 'redirect_legacy')) {
                $table->boolean('redirect_legacy')->default(true)->after('api_token_hash');
            }
            if (! Schema::hasColumn('website_cms', 'last_revalidated_at')) {
                $table->timestamp('last_revalidated_at')->nullable()->after('redirect_legacy');
            }
            if (! Schema::hasColumn('website_cms', 'last_revalidate_status')) {
                $table->string('last_revalidate_status')->nullable()->after('last_revalidated_at');
            }
        });

        if (! Schema::hasTable('website_pages')) {
            Schema::create('website_pages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
                $table->string('kind', 20)->default('custom'); // system | custom
                // Path of the published version (lookups); the draft may hold a new path.
                $table->string('path')->nullable();
                $table->json('draft');
                $table->json('published')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['organization_id', 'path']);
                $table->index(['organization_id', 'sort_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('website_pages');

        Schema::table('website_cms', function (Blueprint $table) {
            foreach (['last_revalidate_status', 'last_revalidated_at', 'redirect_legacy', 'api_token_hash', 'api_token', 'external_url', 'mode'] as $column) {
                if (Schema::hasColumn('website_cms', $column)) {
                    if ($column === 'api_token_hash') {
                        $table->dropUnique(['api_token_hash']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
