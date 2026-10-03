<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsTeacher = true;

    public function test_prevents_overlapping_appointments_when_enabled(): void
    {
        $student = Student::query()->create([
            'name' => 'طالب تجريبي',
            'level' => 'مبتدئ',
            'style' => 'شرح مبسط',
        ]);

        Appointment::query()->create([
            'student_id' => $student->id,
            'scheduled_date' => '2026-09-04',
            'scheduled_time' => '16:00',
            'status' => 'upcoming',
        ]);

        Setting::setValue('prevent_scheduling_conflicts', '1');
        Setting::setValue('default_lesson_duration', '45');
        Setting::setValue('buffer_minutes', '5');

        $this->from('/appointments/create')
            ->post('/appointments', [
                'student_id' => $student->id,
                'scheduled_date' => '2026-09-04',
                'scheduled_time' => '16:20',
            ])
            ->assertRedirect('/appointments/create')
            ->assertSessionHas('warning');

        $this->assertSame(1, Appointment::query()->count());
    }
}
