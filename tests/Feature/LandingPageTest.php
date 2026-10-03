<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_is_the_public_home(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('إدارة حلقاتك القرآنية أصبحت أسهل.')
            ->assertSee('ابدئي الآن مجانًا')
            ->assertSee(route('login'), false)
            ->assertSee(route('dashboard'), false);
    }

    public function test_login_button_goes_to_the_login_page(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('lp-login', $html);
        $this->assertMatchesRegularExpression(
            '/class="[^"]*lp-login[^"]*" href="'.preg_quote(route('login'), '/').'"/',
            $html,
        );
    }

    public function test_dashboard_remains_available_at_named_route(): void
    {
        $this->actingAsTeacher()
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('جدول اليوم');
    }

    public function test_privacy_and_terms_pages_load(): void
    {
        $this->get(route('landing.privacy'))
            ->assertOk()
            ->assertSee('سياسة الخصوصية');

        $this->get(route('landing.terms'))
            ->assertOk()
            ->assertSee('شروط الاستخدام');
    }

    public function test_guest_landing_does_not_show_live_student_data(): void
    {
        $this->actingAsTeacher();
        $student = Student::factory()->create(['name' => 'طالب تجريبي للهبوط']);
        Appointment::factory()->for($student)->create([
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '16:00',
            'status' => 'upcoming',
        ]);

        auth()->logout();

        $this->get('/')
            ->assertOk()
            ->assertSee('إدارة حلقاتك القرآنية أصبحت أسهل.')
            ->assertDontSee('طالب تجريبي للهبوط');
    }

    public function test_existing_app_pages_are_unchanged(): void
    {
        $this->actingAsTeacher();

        $this->get('/students')->assertOk();
        $this->get('/settings')->assertOk()->assertSee('إعدادات تاج الوقار');
    }
}
