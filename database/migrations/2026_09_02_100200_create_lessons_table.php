<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->date('session_date');
            $table->time('session_time')->nullable();
            $table->json('recitation')->nullable();
            $table->json('new_mem')->nullable();
            $table->json('review')->nullable();
            $table->json('next_recitation')->nullable();
            $table->json('next_new_mem')->nullable();
            $table->json('next_review')->nullable();
            $table->string('recitation_grade', 20)->nullable();
            $table->string('new_mem_grade', 20)->nullable();
            $table->text('notes')->nullable();
            $table->text('ai_summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
