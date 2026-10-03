<?php

namespace Tests\Feature;

use App\Models\ParentContact;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentContactPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_does_not_see_parent_contact_on_landing_privacy_or_terms(): void
    {
        $this->createStudentWithPrivateParent();

        $this->get(route('landing'))
            ->assertOk()
            ->assertDontSee('خولة ولي أمر ٨أ-خصوصية')
            ->assertDontSee('0599988877');

        $this->get(route('landing.privacy'))
            ->assertOk()
            ->assertDontSee('خولة ولي أمر ٨أ-خصوصية')
            ->assertDontSee('0599988877');

        $this->get(route('landing.terms'))
            ->assertOk()
            ->assertDontSee('خولة ولي أمر ٨أ-خصوصية')
            ->assertDontSee('0599988877');
    }

    public function test_guest_cannot_access_parent_contact_on_internal_pages(): void
    {
        $student = $this->createStudentWithPrivateParent();

        $this->get(route('students.index', ['id' => $student->id]))
            ->assertRedirect(route('login'))
            ->assertDontSee('خولة ولي أمر ٨أ-خصوصية')
            ->assertDontSee('0599988877');

        $this->get(route('students.edit', $student))
            ->assertRedirect(route('login'))
            ->assertDontSee('خولة ولي أمر ٨أ-خصوصية')
            ->assertDontSee('0599988877');

        $this->get(route('dashboard', ['student' => $student->id]))
            ->assertRedirect(route('login'))
            ->assertDontSee('خولة ولي أمر ٨أ-خصوصية')
            ->assertDontSee('0599988877');
    }

    public function test_teacher_sees_parent_contact_inside_the_app_only(): void
    {
        $student = $this->createStudentWithPrivateParent();

        $this->actingAsTeacher()
            ->get(route('students.index', ['id' => $student->id]))
            ->assertOk()
            ->assertSee('خولة ولي أمر ٨أ-خصوصية')
            ->assertSee('0599988877');

        $this->actingAsTeacher()
            ->get(route('students.edit', $student))
            ->assertOk()
            ->assertSee('خولة ولي أمر ٨أ-خصوصية')
            ->assertSee('0599988877');
    }

    private function createStudentWithPrivateParent(): Student
    {
        $student = Student::factory()->create([
            'name' => 'طالبة الخصوصية',
            'phone' => '0501112233',
        ]);

        ParentContact::query()->create([
            'student_id' => $student->id,
            'name' => 'خولة ولي أمر ٨أ-خصوصية',
            'phone' => '0599988877',
            'relation' => 'أم',
        ]);

        return $student;
    }
}
