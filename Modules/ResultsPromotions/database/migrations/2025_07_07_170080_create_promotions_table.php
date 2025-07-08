<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('from_class_id');
            $table->unsignedBigInteger('to_class_id');
            $table->string('term');
            $table->date('date');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('from_class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('to_class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->index(['student_id', 'from_class_id', 'to_class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
