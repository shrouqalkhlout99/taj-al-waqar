<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardDailyBoardTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsTeacher = true;

    public function test_dashboard_highlights_the_earliest_open_appointment_today(): void
    {
        $later = Student::factory()->create(['name' => 'هند الزهراني']);
        $earlier = Student::factory()->create([
            'name' => 'سارة علي',
            'current_surah' => 'البقرة',
            'last_ayah' => 15,
            'recitation' => ['surah' => 'البقرة', 'from' => 16, 'to' => 20],
            'review' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
        ]);

        Appointment::factory()->for($later)->create([
            'scheduled_time' => '18:00',
            'status' => 'upcoming',
        ]);
        $next = Appointment::factory()->for($earlier)->create([
            'scheduled_time' => '16:00',
            'status' => 'upcoming',
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('الحصة القادمة')
            ->assertSee('سارة علي')
            ->assertSee('16:00')
            ->assertSee('آخر حفظ')
            ->assertSee('البقرة 15')
            ->assertSee('تسميع مقترح')
            ->assertSee('البقرة 16–20')
            ->assertSee('مراجعة مقترحة')
            ->assertSee('الفاتحة 1–7')
            ->assertSee(route('lessons.start', $next), false)
            ->assertSee('إضافة موعد')
            ->assertSee('إضافة طالب');
    }

    public function test_dashboard_skips_finished_appointments_when_choosing_the_next_lesson(): void
    {
        $doneStudent = Student::factory()->create(['name' => 'منتهية اليوم']);
        $openStudent = Student::factory()->create(['name' => 'القادمة بعد المنتهية']);

        Appointment::factory()->for($doneStudent)->done()->create([
            'scheduled_time' => '15:00',
        ]);
        Appointment::factory()->for($openStudent)->create([
            'scheduled_time' => '17:30',
            'status' => 'upcoming',
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('الحصة القادمة')
            ->assertSee('القادمة بعد المنتهية')
            ->assertSee('17:30');
    }

    public function test_dashboard_hides_the_suggested_ranges_when_the_student_has_none(): void
    {
        $student = Student::factory()->create([
            'name' => 'بلا نطاقات',
            'current_surah' => 'الفاتحة',
            'last_ayah' => 1,
            'new_mem' => null,
            'recitation' => null,
            'review' => null,
        ]);
        Appointment::factory()->for($student)->create([
            'status' => 'upcoming',
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('الحصة القادمة')
            ->assertSee('بلا نطاقات')
            ->assertDontSee('تسميع مقترح');
    }

    public function test_header_search_posts_to_the_existing_students_query(): void
    {
        Student::factory()->create(['name' => 'هند الزهراني']);
        Student::factory()->create(['name' => 'سارة علي']);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('name="q"', false)
            ->assertSee('action="'.route('students.index').'"', false);

        $this->get(route('students.index', ['q' => 'هند']))
            ->assertOk()
            ->assertSee('هند الزهراني')
            ->assertDontSee('سارة علي');
    }
}
