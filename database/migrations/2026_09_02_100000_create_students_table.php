<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('level')->default('مبتدئ');
            $table->string('style')->default('شرح مبسط');
            $table->string('meet_link')->nullable();
            $table->string('color', 20)->default('#6b4bd6');
            $table->string('current_surah')->nullable();
            $table->unsignedSmallInteger('last_ayah')->nullable();
            $table->json('new_mem')->nullable();
            $table->json('recitation')->nullable();
            $table->json('review')->nullable();
            $table->text('last_note')->nullable();
            $table->date('last_note_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
