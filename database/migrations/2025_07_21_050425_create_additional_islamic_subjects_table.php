<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Production-safe: table may already exist on legacy databases.
        if (Schema::hasTable('additional_islamic_subjects')) {
            return;
        }

        Schema::create('additional_islamic_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qualification_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('additional_islamic_subjects');
    }
};
