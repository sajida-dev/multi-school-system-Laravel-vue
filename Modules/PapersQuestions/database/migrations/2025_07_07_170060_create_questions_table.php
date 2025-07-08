<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paper_id');
            $table->text('text');
            $table->string('type');
            $table->json('options')->nullable();
            $table->string('answer')->nullable();
            $table->timestamps();

            $table->foreign('paper_id')->references('id')->on('papers')->onDelete('cascade');
            $table->index('paper_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
