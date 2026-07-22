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
        if (Schema::hasTable('api_subscriptions')) {
            return;
        }

        Schema::create('api_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('entry_id')->index();
            $table->bigInteger('form_id')->index();
            $table->boolean('is_spam')->default(0);
            $table->dateTime('date_created')->nullable();
            $table->string('name_2')->nullable();
            $table->string('phone_1')->nullable();
            $table->string('email_2')->nullable();
            $table->string('select_3')->nullable();
            $table->string('_forminator_user_ip')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_subscriptions');
    }
};
