<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Appointment;
use App\Models\Lesson;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Services\FollowUpInbox;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowUpInboxTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsTeacher = true;

    public function test_dashboard_lists_an_undocumented_past_appointment(): void
    {
        $this->travelTo('2026-09-18 10:00:00');
        $student = Student::factory()->create(['name' => 'سارة علي']);
        Appointment::factory()->for($student)->create([
            'scheduled_date' => '2026-09-16',
            'scheduled_time' => '16:00',
            'status' => 'upcoming',
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('يحتاج متابعة')
            ->assertSee('سارة علي')
            ->assertSee('موعد سابق لم تُسجَّل حصته.');
    }

    public function test_dashboard_lists_a_student_without_a_lesson_for_the_idle_days(): void
    {
        $this->travelTo('2026-09-18 10:00:00');
        $student = Student::factory()->create(['name' => 'هند الزهراني']);
        $appointment = Appointment::factory()->for($student)->done()->create([
            'scheduled_date' => '2026-09-01',
        ]);
        Lesson::factory()->for($student)->for($appointment)->create([
            'session_date' => '2026-09-01',
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('يحتاج متابعة')
            ->assertSee('هند الزهراني')
            ->assertSee('لم تُسجَّل حصة منذ 8 أيام.');
    }

    public function test_dashboard_does_not_invent_follow_up_items_without_data(): void
    {
        Student::factory()->create(['name' => 'طالبة جديدة']);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('يحتاج متابعة')
            ->assertDontSee('موعد سابق لم تُسجَّل حصته.')
            ->assertDontSee('لم تُسجَّل حصة منذ');
    }

    public function test_dashboard_lists_repeated_cancellations_and_tomorrow_without_a_plan(): void
    {
        $this->travelTo('2026-09-18 10:00:00');
        $cancelled = Student::factory()->create(['name' => 'تكرر إلغاؤها']);
        Appointment::factory()->for($cancelled)->cancelled()->create([
            'scheduled_date' => '2026-09-10',
        ]);
        Appointment::factory()->for($cancelled)->cancelled()->create([
            'scheduled_date' => '2026-09-12',
        ]);

        $unprepared = Student::factory()->create([
            'name' => 'غدًا بلا خطة',
            'new_mem' => null,
            'recitation' => null,
            'review' => null,
        ]);
        Appointment::factory()->for($unprepared)->create([
            'scheduled_date' => '2026-09-19',
            'status' => 'upcoming',
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('تكرر إلغاء المواعيد خلال الفترة الأخيرة.')
            ->assertSee('موعد غدٍ بدون تحضير ظاهر.')
            ->assertSee('تكرر إلغاؤها')
            ->assertSee('غدًا بلا خطة');
    }

    public function test_follow_up_can_be_disabled_from_settings(): void
    {
        $this->travelTo('2026-09-18 10:00:00');
        Setting::setValue('follow_up_enabled', false);
        $student = Student::factory()->create(['name' => 'سارة علي']);
        Appointment::factory()->for($student)->create([
            'scheduled_date' => '2026-09-16',
            'status' => 'upcoming',
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('يحتاج متابعة')
            ->assertDontSee('موعد سابق لم تُسجَّل حصته.');
    }

    public function test_follow_up_does_not_list_another_account_student(): void
    {
        $this->travelTo('2026-09-18 10:00:00');
        $otherAccount = Account::factory()->create();
        $otherTeacher = User::factory()->create(['account_id' => $otherAccount->id]);

        $this->actingAs($otherTeacher);
        $foreign = Student::factory()->create(['name' => 'طالبة حساب آخر']);
        Appointment::factory()->for($foreign)->create([
            'scheduled_date' => '2026-09-16',
            'status' => 'upcoming',
        ]);

        $this->actingAsTeacher();
        Student::factory()->create(['name' => 'طالبة هذا الحساب']);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('طالبة حساب آخر')
            ->assertDontSee('موعد سابق لم تُسجَّل حصته.');

        $this->assertSame(0, app(FollowUpInbox::class)->items()->count());
    }
}
