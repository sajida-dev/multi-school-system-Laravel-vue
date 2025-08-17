<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('paper_id')->constrained('papers')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained('sections')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->enum('term', ['1st_term', '2nd_term', '3rd_term', 'final'])->default('1st_term');
            $table->decimal('obtained_marks', 8, 2);
            $table->decimal('total_marks', 8, 2);
            $table->text('remarks')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['student_id', 'paper_id', 'term'], 'unique_student_paper_term');
            $table->index(['class_id', 'term']);
            $table->index(['school_id', 'term']);
            $table->index(['student_id', 'term']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
