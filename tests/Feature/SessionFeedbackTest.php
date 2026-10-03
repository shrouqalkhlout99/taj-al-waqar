<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Lesson;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionFeedbackTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsTeacher = true;

    public function test_appointments_page_asks_to_confirm_cancellation(): void
    {
        $student = Student::factory()->create();
        Appointment::factory()->for($student)->create([
            'status' => 'upcoming',
        ]);

        $this->get(route('appointments.index'))
            ->assertOk()
            ->assertSee('إلغاء الموعد؟')
            ->assertSee('سيتم إلغاء هذا الموعد')
            ->assertSee('نعم، ألغِ الموعد');
    }

    public function test_session_page_uses_close_without_saving_copy(): void
    {
        $student = Student::factory()->create();
        $appointment = Appointment::factory()->for($student)->create([
            'status' => 'upcoming',
        ]);

        $this->get(route('lessons.start', $appointment))
            ->assertOk()
            ->assertSee('إغلاق بدون حفظ')
            ->assertSee('إنهاء الحصة وحفظ')
            ->assertSee('ولا يلغي الموعد')
            ->assertDontSee('إلغاء الحصة');
    }

    public function test_session_page_shows_the_follow_up_note_in_context(): void
    {
        $student = Student::factory()->create([
            'name' => 'سارة علي',
            'phone' => '0501234567',
            'current_surah' => 'البقرة',
            'last_ayah' => 15,
            'last_note' => 'راجعي المد قبل الحصة',
        ]);
        $appointment = Appointment::factory()->for($student)->create([
            'status' => 'upcoming',
        ]);

        $this->get(route('lessons.start', $appointment))
            ->assertOk()
            ->assertSee('سارة علي')
            ->assertSee('0501234567')
            ->assertSee('آخر موضع: البقرة 15')
            ->assertSee('ملاحظة خاصة')
            ->assertSee('راجعي المد قبل الحصة');
    }

    public function test_session_page_does_not_prefill_lesson_notes_with_the_follow_up_note(): void
    {
        $student = Student::factory()->create([
            'last_note' => 'راجعي المد قبل الحصة',
        ]);
        $appointment = Appointment::factory()->for($student)->create([
            'status' => 'upcoming',
        ]);

        $html = $this->get(route('lessons.start', $appointment))
            ->assertOk()
            ->assertSee('هذه الملاحظة تخص الحصة الحالية فقط.')
            ->getContent();

        $this->assertStringContainsString(
            '<textarea name="notes" placeholder="اكتبي ملاحظة سريعة..."></textarea>',
            $html
        );
    }

    public function test_session_page_separates_today_progress_from_the_next_plan(): void
    {
        $student = Student::factory()->create();
        $appointment = Appointment::factory()->for($student)->create([
            'status' => 'upcoming',
        ]);

        $this->get(route('lessons.start', $appointment))
            ->assertOk()
            ->assertSee('ما أُنجز اليوم')
            ->assertSee('خطة الحصة القادمة')
            ->assertSee('الواجب السابق')
            ->assertDontSee('مطلوب الحصة القادمة');
    }

    public function test_session_range_ayah_selects_stop_at_the_surah_length(): void
    {
        $student = Student::factory()->create();
        $appointment = Appointment::factory()->for($student)->create([
            'status' => 'upcoming',
        ]);

        $html = $this->get(route('lessons.start', $appointment))
            ->assertOk()
            ->getContent();

        $this->assertNamedSelectAllowsAyah($html, 'recitation_to', 7);
        $this->assertNamedSelectAllowsAyah($html, 'next_new_mem_to', 286);
        $this->assertStringNotContainsString('name="recitation_to" type="number"', $html);
    }

    public function test_lessons_list_shows_the_stored_recitation_grade(): void
    {
        $student = Student::factory()->create(['name' => 'سارة علي']);
        $appointment = Appointment::factory()->for($student)->create();
        Lesson::factory()->for($student)->for($appointment)->create([
            'recitation_grade' => 'excellent',
        ]);

        $this->get(route('lessons.index'))
            ->assertOk()
            ->assertSee('ممتاز')
            ->assertDontSee('بدون تقدير');
    }

    public function test_lessons_list_shows_a_neutral_grade_when_none_is_stored(): void
    {
        $student = Student::factory()->create(['name' => 'هند الزهراني']);
        $appointment = Appointment::factory()->for($student)->create();
        Lesson::factory()->for($student)->for($appointment)->create([
            'recitation_grade' => null,
        ]);

        $this->get(route('lessons.index'))
            ->assertOk()
            ->assertSee('بدون تقدير')
            ->assertDontSee('ممتاز')
            ->assertDontSee('جيد')
            ->assertDontSee('يحتاج مراجعة');
    }
}
