<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Lesson;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TablesActionsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsTeacher = true;

    public function test_dashboard_keeps_the_start_lesson_action_visible(): void
    {
        $student = Student::factory()->create(['name' => 'سارة علي']);
        $appointment = Appointment::factory()->for($student)->create([
            'status' => 'upcoming',
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('ابدئي الحصة')
            ->assertSee(route('lessons.start', $appointment), false);
    }

    public function test_students_page_keeps_add_as_primary_and_secondary_actions(): void
    {
        $student = Student::factory()->create(['name' => 'هند الزهراني']);

        $this->get(route('students.index'))
            ->assertOk()
            ->assertSee('إضافة طالب')
            ->assertSee('الملف')
            ->assertSee('تعديل')
            ->assertSee(route('students.create'), false)
            ->assertSee(route('students.edit', $student), false);
    }

    public function test_appointments_are_grouped_visually_by_day(): void
    {
        $student = Student::factory()->create();
        Appointment::factory()->for($student)->create([
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '16:00',
        ]);
        Appointment::factory()->for($student)->create([
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '17:00',
        ]);
        Appointment::factory()->for($student)->create([
            'scheduled_date' => now()->subDay()->toDateString(),
            'scheduled_time' => '15:00',
        ]);

        $this->get(route('appointments.index'))
            ->assertOk()
            ->assertSee('اليوم')
            ->assertSee('القادمة')
            ->assertSee('السابقة');
    }

    public function test_lessons_list_keeps_the_summary_link(): void
    {
        $student = Student::factory()->create(['name' => 'أحمد محمد']);
        $appointment = Appointment::factory()->for($student)->create();
        $lesson = Lesson::factory()->for($student)->for($appointment)->create([
            'notes' => 'ملاحظة الحصة',
        ]);

        $this->get(route('lessons.index'))
            ->assertOk()
            ->assertSee('أحمد محمد')
            ->assertSee(route('lessons.summary', $lesson), false);
    }
}
