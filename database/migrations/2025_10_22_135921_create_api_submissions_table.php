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
        if (Schema::hasTable('api_submissions')) {
            return;
        }

        Schema::create('api_submissions', function (Blueprint $table) {
            $table->id();
            $table->string("element_id")->nullable();
            $table->string("entry_id")->nullable();
            $table->string("form_created_at")->nullable();
            $table->longText("name")->nullable();
            $table->string("field_a579d1c")->nullable();
            $table->string("email")->nullable();
            $table->string("field_c90264c")->nullable();
            $table->string("message")->nullable();
            $table->string("field_fa1fc8d")->nullable();
            $table->string("field_36f8b56")->nullable();
            $table->string("field_facd0f4")->nullable();
            $table->string("field_6ccfce5")->nullable();
            $table->string("field_926b28e")->nullable();
            $table->string("field_e85f923")->nullable();
            $table->string("field_47d2c6a")->nullable();
            $table->string("field_0bc35f1")->nullable();
            $table->string("field_047fc60")->nullable();
            $table->string("field_a068999")->nullable();
            $table->string("field_e95ba4d")->nullable();
            $table->string("field_b2dcf7c")->nullable();
            $table->string("field_a8c4c14")->nullable();
            $table->string("field_dae410a")->nullable();
            $table->string("field_f2a4f37")->nullable();
            $table->string("field_0064921")->nullable();
            $table->string("field_28fb8c2")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_submissions');
    }
};
