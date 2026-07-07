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
        Schema::create('api_apply_nows', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('entry_id')->index();
            $table->bigInteger('form_id')->index();
            $table->boolean('is_spam')->default(0);
            $table->dateTime('date_created')->nullable();
            $table->string('select_10')->nullable();
            $table->string('name_2')->nullable();
            $table->string('name_3')->nullable();
            $table->string('name_4')->nullable();
            $table->string('select_1')->nullable();
            $table->string('date_1')->nullable();
            $table->string('radio_1')->nullable();
            $table->json('address_3')->nullable();
            $table->json('upload_1')->nullable();
            $table->json('upload_5')->nullable();
            $table->string('radio_2')->nullable();
            $table->string('name_21')->nullable();
            $table->string('name_22')->nullable();
            $table->string('name_20')->nullable();
            $table->string('select_5')->nullable();
            $table->string('date_2')->nullable();
            $table->string('radio_3')->nullable();
            $table->json('address_2')->nullable();
            $table->json('upload_8')->nullable();
            $table->json('upload_11')->nullable();
            $table->string('radio_14')->nullable();
            $table->string('name_31')->nullable();
            $table->string('name_32')->nullable();
            $table->string('name_33')->nullable();
            $table->string('select_9')->nullable();
            $table->string('date_3')->nullable();
            $table->string('radio_13')->nullable();
            $table->json('address_4')->nullable();
            $table->json('upload_12')->nullable();
            $table->json('upload_13')->nullable();
            $table->string('select_2')->nullable();
            $table->string('name_24')->nullable();
            $table->string('name_25')->nullable();
            $table->string('select_3')->nullable();
            $table->json('address_1')->nullable();
            $table->string('email_1')->nullable();
            $table->string('email_2')->nullable();
            $table->string('phone_1')->nullable();
            $table->string('phone_2')->nullable();
            $table->string('name_15')->nullable();
            $table->json('upload_16')->nullable();
            $table->json('upload_17')->nullable();
            $table->string('radio_5')->nullable();
            $table->string('select_7')->nullable();
            $table->string('name_28')->nullable();
            $table->string('name_29')->nullable();
            $table->string('select_8')->nullable();
            $table->string('radio_6')->nullable();
            $table->json('address_5')->nullable();
            $table->string('email_5')->nullable();
            $table->string('email_6')->nullable();
            $table->string('phone_3')->nullable();
            $table->string('phone_4')->nullable();
            $table->string('phone_5')->nullable();
            $table->json('upload_20')->nullable();
            $table->json('upload_21')->nullable();
            $table->string('radio_7')->nullable();
            $table->string('radio_8')->nullable();
            $table->string('radio_9')->nullable();
            $table->string('radio_10')->nullable();
            $table->string('radio_11')->nullable();
            $table->string('radio_12')->nullable();
            $table->boolean('consent_1')->default(0);
            $table->string('text_1')->nullable();
            $table->string('_forminator_user_ip')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_apply_nows');
    }
};
