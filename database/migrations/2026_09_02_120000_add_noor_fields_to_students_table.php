<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->boolean('tracks_noor_bayan')->default(false)->after('last_note_date');
            $table->unsignedTinyInteger('quran_score')->default(0);
            $table->unsignedTinyInteger('tajweed_score')->default(0);
            $table->unsignedTinyInteger('arabic_score')->default(0);
            $table->unsignedTinyInteger('noor_score')->default(0);
            $table->unsignedTinyInteger('overall_score')->default(0);
            $table->foreignId('current_noor_lesson_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'tracks_noor_bayan',
                'quran_score',
                'tajweed_score',
                'arabic_score',
                'noor_score',
                'overall_score',
                'current_noor_lesson_id',
            ]);
        });
    }
};
