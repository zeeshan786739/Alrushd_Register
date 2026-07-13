<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_cms', function (Blueprint $table) {
            $table->id();
            $table->json('draft')->nullable();
            $table->json('published')->nullable();
            $table->json('version_history')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_cms');
    }
};
