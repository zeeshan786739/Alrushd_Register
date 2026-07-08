<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('legacy_route')->nullable();
            $table->string('success_route')->nullable();
            $table->enum('submit_method', ['urlencoded', 'multipart'])->default('urlencoded');
            $table->boolean('is_active')->default(true);
            $table->boolean('show_on_landing')->default(false);
            $table->string('hero_label')->nullable();
            $table->string('hero_variant')->default('outline');
            $table->enum('handler', ['dynamic', 'custom'])->default('dynamic');
            $table->json('settings')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('form_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->foreignId('form_step_id')->nullable()->constrained('form_steps')->nullOnDelete();
            $table->string('key');
            $table->string('label');
            $table->string('type')->default('text');
            $table->string('placeholder')->nullable();
            $table->boolean('required')->default(false);
            $table->json('options')->nullable();
            $table->string('options_source')->nullable();
            $table->json('validation')->nullable();
            $table->unsignedTinyInteger('col_span')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['form_id', 'key']);
        });

        Schema::create('form_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->unsignedBigInteger('entry_id')->nullable();
            $table->json('data');
            $table->string('status')->default('pending');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();

            $table->index(['form_id', 'status']);
            $table->index(['form_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_entries');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('form_steps');
        Schema::dropIfExists('forms');
    }
};
