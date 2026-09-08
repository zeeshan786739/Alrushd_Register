<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_lead_imports', function (Blueprint $table) {
            $table->unsignedInteger('undone_rows')->default(0)->after('failed_rows');
            $table->foreignId('undone_by')->nullable()->after('completed_at')->constrained('admins')->nullOnDelete();
            $table->timestamp('undone_at')->nullable()->after('undone_by');
        });
    }

    public function down(): void
    {
        Schema::table('crm_lead_imports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('undone_by');
            $table->dropColumn(['undone_at', 'undone_rows']);
        });
    }
};
