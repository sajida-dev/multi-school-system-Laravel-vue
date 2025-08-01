<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->string('cnic')->unique();
            $table->enum('gender', ['Male', 'Female']);
            $table->enum('marital_status', ['Single', 'Married']);
            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('restrict');
            $table->date('dob');
            $table->decimal('salary', 12, 2);
            $table->date('date_of_joining');
            $table->integer('experience_years')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('class_subject_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->unique(['class_id', 'subject_id', 'teacher_id'], 'class_subject_teacher_unique');
            $table->timestamps();
        });
    }

    public function down()
    {

        Schema::dropIfExists('class_subject_teacher');
        Schema::dropIfExists('teachers');
    }
};
