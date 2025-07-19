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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained('sections')->onDelete('set null');
            $table->enum('nationality', [
                'Pakistan',
                'India',
                'Bangladesh',
                'Afghanistan',
                'China',
                'Saudi Arabia',
                'United Arab Emirates',
                'United States',
                'United Kingdom',
                'Canada',
                'Australia',
                'Turkey',
                'Iran',
                'Indonesia',
                'Malaysia',
                'Egypt',
                'South Africa',
                'Germany',
                'France',
                'Italy',
                'Other',
            ]);
            $table->string('registration_number');
            $table->string('name');
            $table->string('b_form_number');
            $table->date('admission_date');
            $table->date('date_of_birth');
            $table->enum('gender', [
                'Male',
                'Female',
                'Other',
            ]);
            $table->enum('class_shift', [
                'Morning',
                'Evening',
                'Other',
            ]);
            $table->string('previous_school')->nullable();
            $table->enum('inclusive', [
                'No Disability',
                'Physical',
                'Visual',
                'Hearing',
                'Intellectual',
                'Other',
            ]);
            $table->string('other_inclusive_type')->nullable();
            $table->enum('religion', [
                'Islam',
                'Christianity',
                'Hinduism',
                'Other',
            ]);
            $table->boolean('is_bricklin')->default(false);
            $table->boolean('is_orphan')->default(false);
            $table->boolean('is_qsc')->default(false);
            $table->string('profile_photo_path')->nullable();
            // Family Information
            $table->string('father_name');
            $table->string('guardian_name')->nullable();
            $table->string('father_cnic');
            $table->string('mother_cnic')->nullable();
            $table->enum('father_profession', [
                'Unemployed',
                'Private/Self Employed',
                'Government',
                'Other',
            ]);
            $table->integer('no_of_children')->nullable();
            $table->enum('job_type', [
                'Private/Self Employed',
                'Government',
                'Other',
            ])->nullable();
            $table->enum('father_education', [
                'None',
                'Primary',
                'Middle',
                'Matric',
                'Intermediate',
                'Graduate',
                'Post Graduate',
            ]);
            $table->enum('mother_education', [
                'None',
                'Primary',
                'Middle',
                'Matric',
                'Intermediate',
                'Graduate',
                'Post Graduate',
            ]);
            $table->enum('mother_profession', [
                'House Wife',
                'Private/Self Employed',
                'Government',
                'Other',
            ]);
            $table->enum('father_income', [
                'INCOME LEVEL BETWEEN RS. 0 - 20,000',
                'INCOME LEVEL BETWEEN RS. 20,001 - 27,000',
                'INCOME LEVEL BETWEEN RS. 27,001 - 35,000',
                'INCOME LEVEL BETWEEN RS. 35,001 - 50,000',
                'INCOME LEVEL ABOVE RS. 50,000',
            ]);
            $table->enum('mother_income', [
                'NOT APPLICABLE',
                'INCOME LEVEL BETWEEN RS. 0 - 20,000',
                'INCOME LEVEL BETWEEN RS. 20,001 - 27,000',
                'INCOME LEVEL BETWEEN RS. 27,001 - 35,000',
                'INCOME LEVEL BETWEEN RS. 35,001 - 50,000',
                'INCOME LEVEL ABOVE RS. 50,000',
            ])->nullable();
            $table->enum('household_income', [
                'INCOME LEVEL BETWEEN RS. 0 - 20,000',
                'INCOME LEVEL BETWEEN RS. 20,001 - 27,000',
                'INCOME LEVEL BETWEEN RS. 27,001 - 35,000',
                'INCOME LEVEL BETWEEN RS. 35,001 - 50,000',
                'INCOME LEVEL ABOVE RS. 50,000',
            ]);
            $table->string('permanent_address');
            $table->string('phone_no')->nullable();
            $table->string('mobile_no');
            $table->enum('status', ['applicant', 'admitted', 'rejected'])->default('applicant');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
