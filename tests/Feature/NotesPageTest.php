<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Lesson;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotesPageTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsTeacher = true;

    public function test_notes_page_asks_to_add_a_student_when_none_exist(): void
    {
        $this->get(route('notes.index'))
            ->assertOk()
            ->assertSee('لا يوجد طلاب بعد')
            ->assertSee(route('students.create'), false);
    }

    public function test_notes_page_shows_empty_state_when_students_have_no_notes(): void
    {
        Student::factory()->create(['name' => 'هند الزهراني']);

        $this->get(route('notes.index'))
            ->assertOk()
            ->assertSee('لا توجد ملاحظات بعد')
            ->assertSee(route('students.index'), false)
            ->assertDontSee('هند الزهراني');
    }

    public function test_notes_page_shows_the_follow_up_note_and_file_links(): void
    {
        $student = Student::factory()->create([
            'name' => 'سارة علي',
            'last_note' => 'راجعي المد قبل الحصة',
            'last_note_date' => '2026-09-10',
        ]);

        $this->get(route('notes.index'))
            ->assertOk()
            ->assertSee('سارة علي')
            ->assertSee('راجعي المد قبل الحصة')
            ->assertSee('ملاحظة خاصة')
            ->assertSee(route('students.index', ['id' => $student->id]), false)
            ->assertSee(route('students.edit', $student), false);
    }

    public function test_notes_page_shows_recent_lesson_notes_and_the_summary_link(): void
    {
        $student = Student::factory()->create(['name' => 'أحمد محمد']);
        $appointment = Appointment::factory()->for($student)->create();
        $lesson = Lesson::factory()->for($student)->for($appointment)->create([
            'notes' => 'ثبتت الغنة جيداً',
            'ai_summary' => 'ملخص الحصة',
        ]);

        $this->get(route('notes.index'))
            ->assertOk()
            ->assertSee('أحمد محمد')
            ->assertSee('ثبتت الغنة جيداً')
            ->assertSee('ملاحظات الحصص')
            ->assertSee(route('lessons.summary', $lesson), false);
    }
}
