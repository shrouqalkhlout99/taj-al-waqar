<?php

namespace App\Services;

use App\Models\Student;
use App\Support\Quran;

class LessonPlanSuggester
{
    /**
     * Build today's ranges and the next-lesson suggestion from the student's current file.
     *
     * @return array{
     *     last_hifz: string,
     *     today_recitation: array<string, mixed>,
     *     today_new_mem: array<string, mixed>,
     *     today_review: array<string, mixed>,
     *     next_recitation: array<string, mixed>,
     *     next_new_mem: array<string, mixed>,
     *     next_review: array<string, mixed>,
     *     has_ranges: bool
     * }
     */
    public function suggest(Student $student): array
    {
        $todayNewMem = is_array($student->new_mem) ? $student->new_mem : [];
        $todayRecitation = is_array($student->recitation) ? $student->recitation : [];
        $todayReview = is_array($student->review) ? $student->review : [];

        return [
            'last_hifz' => $this->lastHifz($student),
            'today_recitation' => $todayRecitation,
            'today_new_mem' => $todayNewMem,
            'today_review' => $todayReview,
            'next_recitation' => $todayNewMem,
            'next_new_mem' => $this->nextNewMemRange($todayNewMem, $student),
            'next_review' => $todayReview,
            'has_ranges' => $this->hasRanges($todayRecitation, $todayNewMem, $todayReview),
        ];
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    public function wording(array $plan): string
    {
        if (! ($plan['has_ranges'] ?? false)) {
            return 'لا توجد نطاقات ظاهرة لاقتراح الحصة.';
        }

        return sprintf(
            'آخر حفظ: %s. تسميع مقترح: %s. حفظ مقترح: %s. مراجعة مقترحة: %s.',
            $plan['last_hifz'] ?? '—',
            Quran::rangeText($plan['next_recitation'] ?? null),
            Quran::rangeText($plan['next_new_mem'] ?? null),
            Quran::rangeText($plan['next_review'] ?? null),
        );
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    public function previousHomeworkWording(array $plan): string
    {
        if (! ($plan['has_ranges'] ?? false)) {
            return 'لا يوجد واجب سابق ظاهر في ملف الطالب.';
        }

        return sprintf(
            'التسميع: %s. الحفظ: %s. المراجعة: %s.',
            Quran::rangeText($plan['today_recitation'] ?? null),
            Quran::rangeText($plan['today_new_mem'] ?? null),
            Quran::rangeText($plan['today_review'] ?? null),
        );
    }

    /**
     * @param  array<string, mixed>  $todayNewMem
     * @return array{surah: mixed, from: ?int, to: ?int}
     */
    private function nextNewMemRange(array $todayNewMem, Student $student): array
    {
        $surah = data_get($todayNewMem, 'surah', $student->current_surah);
        $surahName = is_string($surah) ? $surah : null;
        $nextFrom = (int) data_get($todayNewMem, 'to', 0);

        return [
            'surah' => $surah,
            'from' => Quran::clampAyah($surahName, $nextFrom ? $nextFrom + 1 : null),
            'to' => Quran::clampAyah($surahName, $nextFrom ? $nextFrom + 5 : null),
        ];
    }

    private function lastHifz(Student $student): string
    {
        $label = trim((string) $student->current_surah.' '.(string) $student->last_ayah);

        if ($label !== '') {
            return $label;
        }

        $fromRange = Quran::rangeText($student->new_mem);

        return $fromRange === '—' ? '—' : $fromRange;
    }

    /**
     * @param  array<string, mixed>  $recitation
     * @param  array<string, mixed>  $newMem
     * @param  array<string, mixed>  $review
     */
    private function hasRanges(array $recitation, array $newMem, array $review): bool
    {
        return filled(data_get($recitation, 'surah'))
            || filled(data_get($newMem, 'surah'))
            || filled(data_get($review, 'surah'));
    }
}
