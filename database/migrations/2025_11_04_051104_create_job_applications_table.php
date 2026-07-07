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

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('nationality')->nullable();
            $table->string('religion')->nullable();
            $table->string('ethnicity')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country_of_residence')->nullable();
            $table->string('position_applied_for')->nullable();
            $table->string('department')->nullable();
            $table->string('type_of_employment')->nullable();
            $table->string('start_date')->nullable();
            $table->string('preferred_working_hours')->nullable();
            $table->string('expected_salary')->nullable();
            $table->text('about_this_job')->nullable();
            $table->text('teaching_qualification')->nullable();
            $table->text('relevant_certificates')->nullable();
            $table->string('subject_skill')->nullable();
            $table->string('languages_spoken')->nullable();
            $table->string('level_of_computer')->nullable();
            $table->string('microsoft_teams')->nullable();
            $table->string('online_teaching_experience')->nullable();
            $table->string('years_of_teaching_xperience')->nullable();
            $table->text('other_relevant_skills')->nullable();
            $table->string('relationship_to_applicant')->nullable();
            $table->string('right_to_work')->nullable();
            $table->string('insurance_number')->nullable();
            $table->string('dbs_certificate')->nullable();
            $table->date('dbs_issue_date')->nullable();
            $table->string('upload_cv')->nullable();
            $table->string('upload_dbs_certificate')->nullable();
            $table->json('upload_certificates')->nullable();
            $table->json('education')->nullable();
            $table->json('previous_employment')->nullable();
            $table->json('references')->nullable();
            $table->text('background_check')->nullable();
            $table->text('special_requirement')->nullable();
            $table->text('personal_statement')->nullable();
            $table->boolean('true_and_complete')->default(false);
            $table->boolean('recruitment_purposes')->default(false);
            $table->string('status')->default(0);
            $table->timestamp('date_of_submission')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};

