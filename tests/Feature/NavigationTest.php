<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsTeacher = true;

    public function test_app_shell_uses_the_brand_mark_and_keeps_all_nav_links(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(asset('images/brand/logo-mark.svg'), false)
            ->assertSee('لوحة اليوم')
            ->assertSee('الطلاب')
            ->assertSee('المواعيد')
            ->assertSee('الحصص')
            ->assertSee('الملاحظات')
            ->assertSee('الكتاب والشرح')
            ->assertSee('المساعد الذكي')
            ->assertSee('التقارير')
            ->assertSee('الإعدادات')
            ->assertSee('حصص اليوم')
            ->assertSee('name="q"', false)
            ->assertSee('بحث عن طالب');
    }
}
