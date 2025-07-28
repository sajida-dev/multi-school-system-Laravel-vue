<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('class_subject_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Ensure unique combination of class, subject, and teacher
            $table->unique(['class_id', 'subject_id', 'teacher_id'], 'class_subject_teacher_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_subject_teacher');
    }
};
