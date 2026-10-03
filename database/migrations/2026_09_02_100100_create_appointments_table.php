<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('scheduled_date');
            $table->time('scheduled_time');
            $table->string('status', 20)->default('upcoming');
            $table->timestamps();

            $table->index(['scheduled_date', 'scheduled_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
