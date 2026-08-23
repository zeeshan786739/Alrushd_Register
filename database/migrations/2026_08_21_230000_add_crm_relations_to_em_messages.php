<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive CRM relation columns for transactional email history.
 * DO NOT run automatically.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('em_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('em_messages', 'quotation_id')) {
                $table->foreignId('quotation_id')->nullable()->after('customer_id')
                    ->constrained('crm_quotations')->nullOnDelete();
            }
            if (! Schema::hasColumn('em_messages', 'invoice_id')) {
                $table->foreignId('invoice_id')->nullable()->after('quotation_id')
                    ->constrained('crm_invoices')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('em_messages', function (Blueprint $table) {
            if (Schema::hasColumn('em_messages', 'invoice_id')) {
                $table->dropConstrainedForeignId('invoice_id');
            }
            if (Schema::hasColumn('em_messages', 'quotation_id')) {
                $table->dropConstrainedForeignId('quotation_id');
            }
        });
    }
};
