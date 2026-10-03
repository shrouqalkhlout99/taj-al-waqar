<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Services\LessonPlanSuggester;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonPlanSuggesterTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsTeacher = true;

    public function test_suggests_the_next_new_mem_from_the_last_ayah(): void
    {
        $student = Student::factory()->make([
            'current_surah' => 'البقرة',
            'last_ayah' => 5,
            'new_mem' => ['surah' => 'البقرة', 'from' => 1, 'to' => 5],
            'recitation' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
            'review' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
        ]);

        $plan = (new LessonPlanSuggester)->suggest($student);

        $this->assertTrue($plan['has_ranges']);
        $this->assertSame('البقرة 5', $plan['last_hifz']);
        $this->assertSame(['surah' => 'الفاتحة', 'from' => 1, 'to' => 7], $plan['today_recitation']);
        $this->assertSame(['surah' => 'البقرة', 'from' => 1, 'to' => 5], $plan['next_recitation']);
        $this->assertSame(['surah' => 'البقرة', 'from' => 6, 'to' => 10], $plan['next_new_mem']);
        $this->assertSame(['surah' => 'الفاتحة', 'from' => 1, 'to' => 7], $plan['next_review']);
    }

    public function test_clamps_the_next_new_mem_to_the_surah_length(): void
    {
        $student = Student::factory()->make([
            'current_surah' => 'الفاتحة',
            'last_ayah' => 7,
            'new_mem' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
            'recitation' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
            'review' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
        ]);

        $plan = (new LessonPlanSuggester)->suggest($student);

        $this->assertSame(['surah' => 'الفاتحة', 'from' => 7, 'to' => 7], $plan['next_new_mem']);
    }

    public function test_reports_missing_ranges_when_the_file_has_none(): void
    {
        $student = Student::factory()->make([
            'current_surah' => null,
            'last_ayah' => null,
            'new_mem' => null,
            'recitation' => null,
            'review' => null,
        ]);

        $plan = (new LessonPlanSuggester)->suggest($student);

        $this->assertFalse($plan['has_ranges']);
        $this->assertSame('—', $plan['last_hifz']);
        $this->assertSame('لا توجد نطاقات ظاهرة لاقتراح الحصة.', (new LessonPlanSuggester)->wording($plan));
    }
}
