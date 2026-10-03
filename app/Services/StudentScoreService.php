<?php

namespace App\Services;

use App\Models\Student;
use App\Support\Quran;

class StudentScoreService
{
    public function recalculate(Student $student): void
    {
        $student->quran_score = $this->quranScore($student);
        $student->tajweed_score = $this->tajweedScore($student);
        $student->overall_score = (int) round((
            $student->quran_score
            + $student->tajweed_score
        ) / 2);
        $student->save();
    }

    private function quranScore(Student $student): int
    {
        $number = Quran::surahNumber((string) $student->current_surah) ?: 1;
        $ayah = max(1, (int) $student->last_ayah);

        return min(100, (int) round(($number / 114) * 85 + min($ayah, 20)));
    }

    private function tajweedScore(Student $student): int
    {
        $grades = $student->lessons()->limit(8)->pluck('recitation_grade')->filter();
        if ($grades->isEmpty()) {
            return 0;
        }

        $map = ['excellent' => 100, 'good' => 70, 'review' => 35];
        $avg = $grades->map(fn ($g) => $map[$g] ?? 50)->avg();

        return (int) round($avg);
    }
}
