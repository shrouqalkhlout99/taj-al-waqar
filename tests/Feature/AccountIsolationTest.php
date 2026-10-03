<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Appointment;
use App\Models\Lesson;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Services\DataBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_does_not_see_another_account_student_on_app_pages(): void
    {
        [$first, $second] = $this->twoTeachers();
        $this->actingAs($first);
        Student::factory()->create(['name' => 'طالبة المعلمة الأولى']);

        $this->actingAs($second);
        Student::factory()->create(['name' => 'طالبة المعلمة الثانية']);

        $this->get(route('students.index'))
            ->assertOk()
            ->assertSee('طالبة المعلمة الثانية')
            ->assertDontSee('طالبة المعلمة الأولى');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('طالبة المعلمة الأولى');

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertDontSee('طالبة المعلمة الأولى');
    }

    public function test_teacher_gets_404_for_another_account_student_appointment_and_lesson(): void
    {
        [$first, $second] = $this->twoTeachers();

        $this->actingAs($first);
        $student = Student::factory()->create(['name' => 'ملف معلمة أخرى']);
        $appointment = Appointment::factory()->for($student)->create();
        $lesson = Lesson::factory()->for($student)->create([
            'appointment_id' => $appointment->id,
        ]);

        $this->actingAs($second);

        $this->get(route('students.edit', $student))->assertNotFound();
        $this->get(route('appointments.edit', $appointment))->assertNotFound();
        $this->get(route('lessons.start', $appointment))->assertNotFound();
        $this->get(route('lessons.summary', $lesson))->assertNotFound();
    }

    public function test_settings_do_not_leak_between_accounts(): void
    {
        [$first, $second] = $this->twoTeachers();

        $this->actingAs($first);
        Setting::setValue('teacher_city', 'جدة');

        $this->actingAs($second);
        $this->assertNotSame('جدة', Setting::getValue('teacher_city'));

        Setting::setValue('teacher_city', 'الدمام');
        $this->assertSame('الدمام', Setting::getValue('teacher_city'));

        $this->actingAs($first);
        $this->assertSame('جدة', Setting::getValue('teacher_city'));
    }

    public function test_export_payload_contains_only_the_current_account(): void
    {
        [$first, $second] = $this->twoTeachers();

        $this->actingAs($first);
        Student::factory()->create(['name' => 'تصدير أولى']);

        $this->actingAs($second);
        Student::factory()->create(['name' => 'تصدير ثانية']);

        $payload = app(DataBackupService::class)->exportPayload();
        $names = collect($payload['students'])->pluck('name');

        $this->assertSame($second->account_id, $payload['account_id']);
        $this->assertTrue($names->contains('تصدير ثانية'));
        $this->assertFalse($names->contains('تصدير أولى'));
    }

    public function test_guest_landing_does_not_list_another_teachers_students(): void
    {
        [$first] = $this->twoTeachers();
        $this->actingAs($first);
        Student::factory()->create(['name' => 'لا تظهر للزائر']);
        auth()->logout();

        $this->get(route('landing'))
            ->assertOk()
            ->assertDontSee('لا تظهر للزائر');
    }

    /**
     * @return array{0: User, 1: User}
     */
    private function twoTeachers(): array
    {
        $firstAccount = Account::factory()->create();
        $secondAccount = Account::factory()->create();

        return [
            User::factory()->create(['account_id' => $firstAccount->id]),
            User::factory()->create(['account_id' => $secondAccount->id]),
        ];
    }
}
