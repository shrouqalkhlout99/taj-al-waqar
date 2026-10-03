<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Lesson;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_the_password_change_page(): void
    {
        $this->get(route('password.edit'))->assertRedirect(route('login'));
    }

    public function test_guest_cannot_update_a_password(): void
    {
        $this->put(route('password.update'), [
            'current_password' => 'password',
            'password' => 'new-secret1',
            'password_confirmation' => 'new-secret1',
        ])->assertRedirect(route('login'));
    }

    public function test_authenticated_teacher_can_open_the_password_change_page(): void
    {
        $this->actingAsTeacher()
            ->get(route('password.edit'))
            ->assertOk()
            ->assertSee('تغيير كلمة المرور')
            ->assertSee('كلمة المرور الحالية')
            ->assertDontSee('بدون تسجيل دخول، لذلك لا توجد كلمة مرور');
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = $this->teacherUser(['email' => 'teacher@example.com']);

        $this->actingAsTeacher($user)
            ->from(route('password.edit'))
            ->put(route('password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'new-secret1',
                'password_confirmation' => 'new-secret1',
            ])
            ->assertRedirect(route('password.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_password_confirmation_mismatch_is_rejected(): void
    {
        $user = $this->teacherUser(['email' => 'teacher@example.com']);

        $this->actingAsTeacher($user)
            ->from(route('password.edit'))
            ->put(route('password.update'), [
                'current_password' => 'password',
                'password' => 'new-secret1',
                'password_confirmation' => 'different-secret',
            ])
            ->assertRedirect(route('password.edit'))
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_new_password_is_hashed_and_not_exposed_in_the_response(): void
    {
        $user = $this->teacherUser(['email' => 'teacher@example.com']);

        $this->actingAsTeacher($user)
            ->from(route('password.edit'))
            ->put(route('password.update'), [
                'current_password' => 'password',
                'password' => 'new-secret1',
                'password_confirmation' => 'new-secret1',
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'account']))
            ->assertSessionHas('success', 'تم تغيير كلمة المرور بنجاح');

        $user->refresh();

        $this->assertTrue(Hash::check('new-secret1', $user->password));
        $this->assertAuthenticatedAs($user);

        $this->get(route('settings.index', ['tab' => 'account']))
            ->assertOk()
            ->assertSee('تم تغيير كلمة المرور بنجاح')
            ->assertDontSee('new-secret1')
            ->assertDontSee($user->getAuthPassword(), false);
    }

    public function test_old_password_fails_and_new_password_succeeds_after_change(): void
    {
        $user = $this->teacherUser(['email' => 'teacher@example.com']);

        $this->actingAsTeacher($user)
            ->put(route('password.update'), [
                'current_password' => 'password',
                'password' => 'new-secret1',
                'password_confirmation' => 'new-secret1',
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'account']));

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();

        $this->from(route('login'))
            ->post(route('login'), [
                'email' => 'teacher@example.com',
                'password' => 'password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->from(route('login'))
            ->post(route('login'), [
                'email' => 'teacher@example.com',
                'password' => 'new-secret1',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_logout_still_ends_the_session_after_password_change(): void
    {
        $user = $this->teacherUser();

        $this->actingAsTeacher($user)
            ->put(route('password.update'), [
                'current_password' => 'password',
                'password' => 'new-secret1',
                'password_confirmation' => 'new-secret1',
            ]);

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_password_change_does_not_affect_students_lessons_or_settings(): void
    {
        $this->actingAsTeacher();

        $student = Student::factory()->create(['name' => 'تحقق 4C']);
        $appointment = Appointment::factory()->create(['student_id' => $student->id]);
        $lesson = Lesson::factory()->create([
            'student_id' => $student->id,
            'appointment_id' => $appointment->id,
        ]);
        Setting::setValue('teacher_city', 'جدة');

        $this->from(route('settings.index', ['tab' => 'account']))
            ->put(route('password.update'), [
                'current_password' => 'password',
                'password' => 'new-secret1',
                'password_confirmation' => 'new-secret1',
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'account']));

        $this->assertSame('تحقق 4C', $student->fresh()->name);
        $this->assertModelExists($appointment);
        $this->assertModelExists($lesson);
        $this->assertSame('جدة', Setting::getValue('teacher_city'));
    }

    public function test_password_change_updates_only_the_authenticated_teacher(): void
    {
        $other = User::factory()->create(['email' => 'other@example.com']);
        $teacher = $this->teacherUser(['email' => 'teacher@example.com']);

        $this->actingAsTeacher($teacher)
            ->put(route('password.update'), [
                'current_password' => 'password',
                'password' => 'new-secret1',
                'password_confirmation' => 'new-secret1',
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'account']));

        $this->assertTrue(Hash::check('password', $other->fresh()->password));
        $this->assertTrue(Hash::check('new-secret1', $teacher->fresh()->password));
    }
}
