<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('class_section', function (Blueprint $table) {
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('sections')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['class_id', 'section_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_section');
    }
};
