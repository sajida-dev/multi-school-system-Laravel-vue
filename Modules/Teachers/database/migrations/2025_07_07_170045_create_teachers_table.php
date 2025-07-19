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
            $table->string('cnic')->unique();
            $table->enum('gender', ['Male', 'Female']);
            $table->enum('marital_status', ['Single', 'Married']);
            $table->enum('role', ['teacher', 'principal']);
            $table->date('dob');
            $table->decimal('salary', 12, 2);
            $table->date('date_of_joining');
            $table->integer('experience_years')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('teachers');
    }
};
