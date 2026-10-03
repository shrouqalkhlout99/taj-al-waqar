<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsTeacher = true;

    public function test_settings_page_loads(): void
    {
        $this->get('/settings')
            ->assertOk()
            ->assertSee('settings-page', false)
            ->assertSee('إعدادات تاج الوقار')
            ->assertSee('الحساب الشخصي');
    }

    public function test_settings_tab_opens_teacher_section(): void
    {
        $this->get('/settings?tab=teacher')
            ->assertOk()
            ->assertSee('إعدادات المعلمة')
            ->assertSee('حفظ إعدادات المعلمة');
    }

    public function test_appearance_tab_keeps_the_save_button_label(): void
    {
        $this->get(route('settings.index', ['tab' => 'appearance']))
            ->assertOk()
            ->assertSee('حفظ التغييرات')
            ->assertDontSee('>مضغوط</button>', false);
    }

    public function test_can_save_account_section(): void
    {
        $this->from('/settings?tab=account')
            ->post('/settings', [
                'section' => 'account',
                'teacher_name' => 'معلمة الاختبار',
                'teacher_email' => 'teacher@example.com',
                'teacher_phone' => '0500000000',
                'teacher_whatsapp' => '966500000000',
                'teacher_country' => 'السعودية',
                'teacher_city' => 'الرياض',
                'teacher_timezone' => 'Asia/Riyadh',
                'teacher_language' => 'ar',
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'account']));

        $this->assertSame('معلمة الاختبار', Setting::teacherName());
        $this->assertSame('teacher@example.com', Setting::getValue('teacher_email'));
        $this->assertSame('معلمة الاختبار', auth()->user()->fresh()->name);
        $this->assertSame('teacher@example.com', auth()->user()->fresh()->email);
    }

    public function test_account_validation_keeps_old_input(): void
    {
        $this->from('/settings?tab=account')
            ->post('/settings', [
                'section' => 'account',
                'teacher_name' => '',
                'teacher_email' => 'not-an-email',
                'teacher_timezone' => 'Asia/Riyadh',
                'teacher_language' => 'ar',
            ])
            ->assertRedirect('/settings?tab=account')
            ->assertSessionHasErrors(['teacher_name', 'teacher_email']);
    }

    public function test_password_card_reflects_an_existing_login_account(): void
    {
        $this->get('/settings')
            ->assertOk()
            ->assertSee('تغيير كلمة المرور')
            ->assertSee(route('password.edit'), false)
            ->assertDontSee('بدون تسجيل دخول، لذلك لا توجد كلمة مرور');
    }

    public function test_can_save_calendar_conflict_toggle(): void
    {
        $this->post('/settings', [
            'section' => 'calendar',
            'teacher_timezone' => 'Asia/Riyadh',
            'default_lesson_duration' => '45',
            'reminder_before' => '30',
            'buffer_minutes' => '10',
            'prevent_scheduling_conflicts' => '1',
            'allow_reschedule' => '1',
        ])->assertRedirect(route('settings.index', ['tab' => 'calendar']));

        $this->assertTrue(Setting::bool('prevent_scheduling_conflicts'));
        $this->assertSame(10, Setting::bufferMinutes());
    }

    public function test_reset_ai_keeps_api_key(): void
    {
        Setting::setValue('openai_key', 'sk-test');
        Setting::setValue('ai_plan_style', 'detailed');

        $this->post('/settings/reset-ai')->assertRedirect(route('settings.index', ['tab' => 'ai']));

        $this->assertSame('sk-test', Setting::getValue('openai_key'));
        $this->assertSame('medium', Setting::getValue('ai_plan_style'));
    }

    public function test_ai_settings_page_does_not_render_the_stored_api_key(): void
    {
        Setting::setValue('openai_key', 'sk-secret-must-not-render');

        $this->get(route('settings.index', ['tab' => 'ai']))
            ->assertOk()
            ->assertSee('مفتاح محفوظ — اتركيه فارغاً للإبقاء عليه')
            ->assertDontSee('sk-secret-must-not-render')
            ->assertDontSee('sk-secret');
    }

    public function test_saving_ai_settings_without_a_new_key_keeps_the_stored_key(): void
    {
        Setting::setValue('openai_key', 'sk-keep-me');

        $this->from(route('settings.index', ['tab' => 'ai']))
            ->post(route('settings.update'), $this->aiSettingsPayload([
                'openai_key' => '',
                'ai_plan_style' => 'short',
                'ai_intervention' => 'high',
            ]))
            ->assertRedirect(route('settings.index', ['tab' => 'ai']));

        $this->assertSame('sk-keep-me', Setting::getValue('openai_key'));
        $this->assertSame('short', Setting::getValue('ai_plan_style'));
        $this->assertSame('high', Setting::getValue('ai_intervention'));

        $this->get(route('settings.index', ['tab' => 'ai']))
            ->assertOk()
            ->assertDontSee('sk-keep-me')
            ->assertSee('تفضيل محفوظ الآن. لم يُربط بطلب OpenAI بعد.');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function aiSettingsPayload(array $overrides = []): array
    {
        return array_merge([
            'section' => 'ai',
            'openai_key' => '',
            'openai_model' => 'gpt-4o-mini',
            'ai_plan_style' => 'medium',
            'ai_intervention' => 'medium',
            'ai_enabled' => '1',
            'ai_lesson_plan' => '1',
            'ai_activities' => '1',
            'ai_homework' => '1',
            'ai_analyze' => '1',
            'ai_revision_plan' => '1',
            'ai_summarize' => '1',
            'ai_student_report' => '1',
            'ai_weak_points' => '1',
            'ai_next_lesson' => '1',
            'ai_use_student_data' => '1',
        ], $overrides);
    }
}
