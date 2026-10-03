<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Lesson;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsTeacher = true;

    public function test_summary_page_shows_the_next_lesson_plan_and_lesson_notes(): void
    {
        $lesson = $this->createLesson([
            'notes' => 'ثبتت الغنة جيداً',
            'next_recitation' => ['surah' => 'النساء', 'from' => 10, 'to' => 15],
            'next_new_mem' => ['surah' => 'المائدة', 'from' => 2, 'to' => 8],
            'next_review' => ['surah' => 'الأنعام', 'from' => 20, 'to' => 25],
        ]);

        $this->get(route('lessons.summary', $lesson))
            ->assertOk()
            ->assertSee('خطة الحصة القادمة')
            ->assertSee('تسميع قادم: النساء 10–15')
            ->assertSee('حفظ قادم: المائدة 2–8')
            ->assertSee('مراجعة قادمة: الأنعام 20–25')
            ->assertSee('ما تم اليوم')
            ->assertSee('الملاحظات: ثبتت الغنة جيداً');
    }

    public function test_summary_page_shows_a_neutral_message_when_ai_summary_is_missing(): void
    {
        $lesson = $this->createLesson([
            'ai_summary' => null,
        ]);

        $this->get(route('lessons.summary', $lesson))
            ->assertOk()
            ->assertSee('لا يوجد ملخص آلي لهذه الحصة.')
            ->assertDontSee('class="ai-box"', false);
    }

    public function test_summary_page_shows_the_ai_summary_when_it_is_present(): void
    {
        $lesson = $this->createLesson([
            'ai_summary' => 'ملخص آلي تجريبي للحصة',
        ]);

        $this->get(route('lessons.summary', $lesson))
            ->assertOk()
            ->assertSee('ملخص آلي تجريبي للحصة')
            ->assertSee('class="ai-box"', false)
            ->assertDontSee('لا يوجد ملخص آلي لهذه الحصة.');
    }

    public function test_summary_page_shows_an_empty_state_when_the_next_plan_is_missing(): void
    {
        $lesson = $this->createLesson([
            'next_recitation' => null,
            'next_new_mem' => null,
            'next_review' => null,
        ]);

        $this->get(route('lessons.summary', $lesson))
            ->assertOk()
            ->assertSee('لا توجد خطة للحصة القادمة')
            ->assertDontSee('تسميع قادم:');
    }

    public function test_summary_page_offers_a_parent_draft_without_the_private_note(): void
    {
        $student = Student::factory()->create([
            'name' => 'سارة علي',
            'last_note' => 'ملاحظة خاصة لا تُرسل',
        ]);
        $appointment = Appointment::factory()->for($student)->create();
        $lesson = Lesson::factory()->for($student)->for($appointment)->create([
            'notes' => 'ملاحظة الحصة',
        ]);

        $this->get(route('lessons.summary', $lesson))
            ->assertOk()
            ->assertSee('صياغة رسالة ولي الأمر')
            ->assertSee('التسميع: الفاتحة 1–7')
            ->assertDontSee('ملاحظة خاصة لا تُرسل');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createLesson(array $attributes = []): Lesson
    {
        $student = Student::factory()->create();
        $appointment = Appointment::factory()->for($student)->create();

        return Lesson::factory()->for($student)->for($appointment)->create($attributes);
    }
}
