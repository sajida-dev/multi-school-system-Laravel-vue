<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // exam definition for single class
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("school_id");
            $table->string('title'); // e.g., "Mid Term Examination 2024"
            $table->foreignId('exam_type_id')->constrained('exam_types');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id')->nullable();
            $table->string('academic_year'); // e.g., "2024-2025"
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('instructions')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('set null');
            // $table->index(['school_id', 'class_id', 'section_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
