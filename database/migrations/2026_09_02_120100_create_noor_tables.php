<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('noor_lessons', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->string('title');
            $table->string('unit')->nullable();
            $table->text('objective')->nullable();
            $table->foreignId('skill_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('noor_lesson_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noor_lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->unique(['noor_lesson_id', 'skill_id']);
        });

        Schema::create('student_skill_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->string('grade', 20)->default('beginner');
            $table->unsignedTinyInteger('percent')->default(25);
            $table->date('last_assessed_at')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'skill_id']);
        });

        Schema::create('skill_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('prompt');
            $table->timestamps();
        });

        Schema::create('noor_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('noor_lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('suggested_next_id')->nullable()->constrained('noor_lessons')->nullOnDelete();
            $table->text('homework')->nullable();
            $table->text('notes')->nullable();
            $table->date('session_date')->nullable();
            $table->timestamps();
        });

        Schema::create('skill_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noor_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->string('result', 20);
            $table->string('grade', 20);
            $table->timestamps();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->foreign('current_noor_lesson_id')->references('id')->on('noor_lessons')->nullOnDelete();
        });

        Schema::create('homework_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('noor_session_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->string('status', 20)->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['current_noor_lesson_id']);
        });
        Schema::dropIfExists('homework_items');
        Schema::dropIfExists('skill_assessments');
        Schema::dropIfExists('noor_sessions');
        Schema::dropIfExists('skill_activities');
        Schema::dropIfExists('student_skill_profiles');
        Schema::dropIfExists('noor_lesson_skill');
        Schema::dropIfExists('noor_lessons');
        Schema::dropIfExists('skills');
    }
};
