<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasColumn('students', 'current_noor_lesson_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropForeign(['current_noor_lesson_id']);
            });
        }

        foreach ([
            'homework_items',
            'skill_assessments',
            'noor_sessions',
            'student_skill_profiles',
            'skill_activities',
            'noor_lesson_skill',
            'noor_lessons',
            'skills',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::table('students', function (Blueprint $table) {
            $columns = [
                'tracks_noor_bayan',
                'arabic_score',
                'noor_score',
                'current_noor_lesson_id',
            ];

            $existing = array_values(array_filter(
                $columns,
                fn (string $column): bool => Schema::hasColumn('students', $column)
            ));

            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });

        DB::table('settings')->whereIn('key', [
            'curriculum_noor',
            'noor_track_letters',
            'noor_track_harakat',
            'noor_track_mudud',
            'noor_track_sukun',
            'noor_track_shaddah',
            'noor_track_tanween',
            'noor_track_words',
            'noor_track_sentences',
            'noor_track_reading',
            'parent_see_noor',
            'report_noor',
        ])->delete();

        if (Schema::hasColumn('students', 'quran_score') && Schema::hasColumn('students', 'tajweed_score')) {
            DB::statement('UPDATE students SET overall_score = CAST(ROUND((quran_score + tajweed_score) / 2.0) AS INTEGER)');
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'tracks_noor_bayan')) {
                $table->boolean('tracks_noor_bayan')->default(false);
            }
            if (! Schema::hasColumn('students', 'arabic_score')) {
                $table->unsignedTinyInteger('arabic_score')->default(0);
            }
            if (! Schema::hasColumn('students', 'noor_score')) {
                $table->unsignedTinyInteger('noor_score')->default(0);
            }
            if (! Schema::hasColumn('students', 'current_noor_lesson_id')) {
                $table->unsignedBigInteger('current_noor_lesson_id')->nullable();
            }
        });
    }
};
