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
        if (Schema::hasTable('api_contact_forms')) {
            return;
        }

        Schema::create('api_contact_forms', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('entry_id')->index();
            $table->bigInteger('form_id')->index();
            $table->boolean('is_spam')->default(0);
            $table->dateTime('date_created')->nullable();
            $table->string('name_2')->nullable();
            $table->string('name_4')->nullable();
            $table->string('name_30')->nullable();
            $table->string('name_31')->nullable();
            $table->string('name_32')->nullable();
            $table->string('name_33')->nullable();
            $table->string('name_34')->nullable();
            $table->string('name_35')->nullable();
            $table->string('name_36')->nullable();
            $table->string('name_37')->nullable();
            $table->string('name_38')->nullable();
            $table->string('name_39')->nullable();
            $table->string('phone_2')->nullable();
            $table->string('email_1')->nullable();
            $table->string('email_2')->nullable();
            $table->string('select_3')->nullable();
            $table->json('date_1')->nullable();
            $table->json('date_2')->nullable();
            $table->json('address_6')->nullable();
            $table->json('address_7')->nullable();
            $table->json('address_8')->nullable();
            $table->string('checkbox_1')->nullable();
            $table->longText('textarea_1')->nullable();
            $table->string('radio_2')->nullable();
            $table->string('select_9')->nullable();
            $table->string('select_10')->nullable();
            $table->string('select_11')->nullable();
            $table->string('consent_1')->nullable();
            $table->string('_forminator_user_ip')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_contact_forms');
    }
};
