<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonReminderTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsTeacher = true;

    public function test_teacher_sees_upcoming_lesson_within_reminder_window(): void
    {
        $this->travelTo(now()->setTime(16, 0));
        $student = $this->studentNamed('هند تذكير ٨ب');
        $this->upcomingAt($student, now()->toDateString(), '16:20:00');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('تذكير الحصة')
            ->assertSee('هند تذكير ٨ب')
            ->assertSee('16:20');

        $this->get(route('students.index'))
            ->assertOk()
            ->assertSee('تذكير الحصة')
            ->assertSee('هند تذكير ٨ب');
    }

    public function test_does_not_show_lesson_outside_reminder_window(): void
    {
        $this->travelTo(now()->setTime(16, 0));
        $this->upcomingAt($this->studentNamed('سارة خارج النافذة'), now()->toDateString(), '16:50:00');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('تذكير الحصة');
    }

    public function test_does_not_show_past_upcoming_lesson(): void
    {
        $this->travelTo(now()->setTime(16, 0));
        $this->upcomingAt($this->studentNamed('ليان موعد فائت'), now()->toDateString(), '15:40:00');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('تذكير الحصة');
    }

    public function test_does_not_show_in_progress_or_done_or_cancelled_lessons(): void
    {
        $this->travelTo(now()->setTime(16, 0));

        Appointment::factory()->inProgress()->create([
            'student_id' => $this->studentNamed('جارية ٨ب')->id,
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '16:10:00',
        ]);
        Appointment::factory()->done()->create([
            'student_id' => $this->studentNamed('منتهية ٨ب')->id,
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '16:15:00',
        ]);
        Appointment::factory()->cancelled()->create([
            'student_id' => $this->studentNamed('ملغاة ٨ب')->id,
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '16:20:00',
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('تذكير الحصة');
    }

    public function test_does_not_show_when_lesson_reminder_setting_is_off(): void
    {
        $this->travelTo(now()->setTime(16, 0));
        $this->upcomingAt($this->studentNamed('هند تذكير ٨ب'), now()->toDateString(), '16:20:00');
        Setting::setValue('notify_lesson_reminder', '0');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('تذكير الحصة');
    }

    public function test_does_not_show_when_in_app_channel_is_off(): void
    {
        $this->travelTo(now()->setTime(16, 0));
        $this->upcomingAt($this->studentNamed('هند تذكير ٨ب'), now()->toDateString(), '16:20:00');
        Setting::setValue('notify_channel_inapp', '0');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('تذكير الحصة');
    }

    public function test_day_ahead_window_includes_tomorrow_lesson_within_1440_minutes(): void
    {
        $this->travelTo(now()->setTime(16, 0));
        Setting::setValue('reminder_before', '1440');
        $student = $this->studentNamed('غدًا داخل يوم');
        $this->upcomingAt($student, now()->addDay()->toDateString(), '15:00:00');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('تذكير الحصة')
            ->assertSee('غدًا داخل يوم')
            ->assertSee('15:00');
    }

    public function test_guest_and_landing_do_not_see_lesson_reminder_banner(): void
    {
        $this->travelTo(now()->setTime(16, 0));
        $this->upcomingAt($this->studentNamed('هند تذكير ٨ب'), now()->toDateString(), '16:20:00');

        auth()->logout();

        $this->get(route('landing'))
            ->assertOk()
            ->assertDontSee('تذكير الحصة');

        $this->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertDontSee('تذكير الحصة');
    }

    private function studentNamed(string $name): Student
    {
        return Student::factory()->create(['name' => $name]);
    }

    private function upcomingAt(Student $student, string $date, string $time): Appointment
    {
        return Appointment::factory()->create([
            'student_id' => $student->id,
            'scheduled_date' => $date,
            'scheduled_time' => $time,
            'status' => 'upcoming',
        ]);
    }
}
