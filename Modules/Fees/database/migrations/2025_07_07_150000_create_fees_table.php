<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->enum('type', ['admission', 'tuition', 'papers']);
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['unpaid', 'paid', 'cancelled'])->default('unpaid');
            $table->date('due_date');
            $table->dateTime('paid_at')->nullable();
            $table->string('voucher_number')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
