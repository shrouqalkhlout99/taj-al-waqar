<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmptyStatesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsTeacher = true;

    public function test_dashboard_empty_state_offers_adding_an_appointment(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('جدول اليوم')
            ->assertSee('لا توجد حصص اليوم')
            ->assertDontSee('اختاري طالباً من الجدول')
            ->assertSee(route('appointments.create'), false);
    }

    public function test_students_empty_state_offers_adding_a_student(): void
    {
        $this->get(route('students.index'))
            ->assertOk()
            ->assertSee('لا يوجد طلاب بعد')
            ->assertSee(route('students.create'), false);
    }

    public function test_students_search_without_matches_shows_empty_state(): void
    {
        Student::factory()->create(['name' => 'هند الزهراني']);

        $this->get(route('students.index', ['q' => 'اسم غير موجود']))
            ->assertOk()
            ->assertSee('لا توجد نتائج')
            ->assertDontSee('هند الزهراني');
    }

    public function test_appointments_empty_state_offers_adding_an_appointment(): void
    {
        $this->get(route('appointments.index'))
            ->assertOk()
            ->assertSee('لا توجد مواعيد بعد')
            ->assertSee(route('appointments.create'), false);
    }

    public function test_lessons_empty_state_links_to_appointments(): void
    {
        $this->get(route('lessons.index'))
            ->assertOk()
            ->assertSee('لا توجد حصص محفوظة بعد')
            ->assertSee(route('appointments.index'), false);
    }
}
