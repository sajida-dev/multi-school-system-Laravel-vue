<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // e.g., "Mid Term Examination 2024"
            $table->enum('exam_type', [
                '1st Term',
                '2nd Term',
                '3rd Term',
                'Final Term',
                'Unit Test',
                'Quiz',
                'Assignment',
                'Project',
                'Other'
            ]);
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id')->nullable();
            $table->string('academic_year'); // e.g., "2024-2025"
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('instructions')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('set null');
            $table->index(['class_id', 'section_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
