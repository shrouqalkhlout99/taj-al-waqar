<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Lesson;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsTeacher = true;

    public function test_reports_page_returns_ok(): void
    {
        $this->get(route('reports.index'))->assertOk();
    }

    public function test_reports_page_keeps_the_existing_circle_stats(): void
    {
        $student = Student::factory()->create([
            'overall_score' => 80,
        ]);
        $appointment = Appointment::factory()->for($student)->done()->create([
            'scheduled_date' => now()->toDateString(),
        ]);
        Lesson::factory()->for($student)->for($appointment)->create();

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('عدد الطلاب')
            ->assertSee('الحصص المسجّلة')
            ->assertSee('حصص اليوم')
            ->assertSee('المنتهية اليوم')
            ->assertDontSee('طلاب نور البيان')
            ->assertDontSee('متوسط تقدم نور البيان')
            ->assertSee('80%');
    }

    public function test_reports_page_shows_current_hifz_and_review_positions(): void
    {
        Student::factory()->create([
            'name' => 'سارة علي',
            'new_mem' => ['surah' => 'البقرة', 'from' => 1, 'to' => 5],
            'review' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
        ]);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('تقرير الحفظ')
            ->assertSee('سارة علي: البقرة 1–5')
            ->assertSee('تقرير المراجعة')
            ->assertSee('سارة علي: الفاتحة 1–7')
            ->assertDontSee('ستُوسَّع لاحقاً')
            ->assertDontSee('يُبنى من مواضع الحفظ')
            ->assertDontSee('يُبنى من نطاق المراجعة الحالي');
    }

    public function test_reports_page_hides_hifz_when_the_setting_is_off(): void
    {
        Setting::setValue('report_hifz', false);
        Student::factory()->create([
            'name' => 'سارة علي',
            'new_mem' => ['surah' => 'البقرة', 'from' => 1, 'to' => 5],
        ]);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertDontSee('تقرير الحفظ')
            ->assertDontSee('سارة علي: البقرة 1–5');
    }

    public function test_reports_page_hides_review_when_the_setting_is_off(): void
    {
        Setting::setValue('report_review', false);
        Student::factory()->create([
            'name' => 'سارة علي',
            'review' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
        ]);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertDontSee('تقرير المراجعة')
            ->assertDontSee('سارة علي: الفاتحة 1–7');
    }

    public function test_reports_page_shows_an_empty_state_when_hifz_positions_are_missing(): void
    {
        Student::factory()->create([
            'name' => 'هند الزهراني',
            'new_mem' => null,
            'review' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
        ]);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('لا توجد مواضع حفظ بعد')
            ->assertDontSee('هند الزهراني: البقرة');
    }

    public function test_reports_page_shows_an_empty_state_when_review_positions_are_missing(): void
    {
        Student::factory()->create([
            'name' => 'هند الزهراني',
            'new_mem' => ['surah' => 'البقرة', 'from' => 1, 'to' => 5],
            'review' => null,
        ]);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('لا توجد مواضع مراجعة بعد')
            ->assertDontSee('هند الزهراني: الفاتحة');
    }

    public function test_reports_page_accepts_the_print_query(): void
    {
        $html = $this->get(route('reports.index', ['print' => 1]))
            ->assertOk()
            ->assertSee('class="toolbar no-print"', false)
            ->getContent();

        $this->assertStringContainsString('window.print()', $html);
        $this->assertStringContainsString('onclick="window.print()"', $html);
    }

    public function test_reports_page_shows_circle_progress_when_enabled(): void
    {
        Student::factory()->create(['overall_score' => 40]);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('متوسط تقدم الحلقة')
            ->assertSee('التقدم العام');
    }

    public function test_reports_page_hides_circle_progress_when_the_setting_is_off(): void
    {
        Setting::setValue('report_show_progress', false);
        Student::factory()->create(['overall_score' => 40]);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertDontSee('متوسط تقدم الحلقة');
    }

    public function test_reports_page_shows_student_report_cards(): void
    {
        Student::factory()->create([
            'name' => 'سارة علي',
            'new_mem' => ['surah' => 'آل عمران', 'from' => 10, 'to' => 15],
            'review' => ['surah' => 'النساء', 'from' => 2, 'to' => 8],
            'recitation' => ['surah' => 'المائدة', 'from' => 1, 'to' => 4],
        ]);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('تقرير الطالب')
            ->assertSee('الحفظ: آل عمران 10–15')
            ->assertSee('المراجعة: النساء 2–8')
            ->assertSee('تسميع: المائدة 1–4')
            ->assertDontSee('توصيات الذكاء الاصطناعي');
    }

    public function test_reports_page_does_not_show_skill_strengths(): void
    {
        Student::factory()->create(['name' => 'سارة علي']);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertDontSee('نقاط القوة')
            ->assertDontSee('مستوى الحروف العربية');
    }

    public function test_reports_page_shows_lesson_notes_not_the_private_last_note(): void
    {
        $student = Student::factory()->create([
            'name' => 'سارة علي',
            'last_note' => 'ملاحظة خاصة لا تُطبع',
        ]);
        Lesson::factory()->for($student)->create([
            'notes' => 'ثبتت الغنة جيداً',
        ]);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('توصيات المعلمة')
            ->assertSee('ثبتت الغنة جيداً')
            ->assertSee('خطة الحصة القادمة')
            ->assertDontSee('ملاحظة خاصة لا تُطبع');
    }

    public function test_reports_page_hides_teacher_notes_when_the_setting_is_off(): void
    {
        Setting::setValue('report_show_teacher_tips', false);
        $student = Student::factory()->create([
            'name' => 'سارة علي',
            'last_note' => 'ثبتت الغنة جيداً',
        ]);
        Lesson::factory()->for($student)->create([
            'notes' => 'ملاحظة من الحصة',
        ]);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertDontSee('توصيات المعلمة')
            ->assertDontSee('ملاحظة من الحصة')
            ->assertDontSee('ثبتت الغنة جيداً');
    }

    public function test_reports_page_filters_lesson_notes_by_period(): void
    {
        $student = Student::factory()->create(['name' => 'سارة علي']);
        Lesson::factory()->for($student)->create([
            'session_date' => '2026-08-01',
            'notes' => 'ملاحظة أغسطس',
        ]);
        Lesson::factory()->for($student)->create([
            'session_date' => '2026-09-12',
            'notes' => 'ملاحظة سبتمبر',
        ]);

        $this->get(route('reports.index', ['from' => '2026-09-01', 'to' => '2026-09-30']))
            ->assertOk()
            ->assertSee('ملاحظة سبتمبر')
            ->assertDontSee('ملاحظة أغسطس');
    }

    public function test_reports_page_shows_stored_ai_summary_when_enabled(): void
    {
        Setting::setValue('report_show_ai_tips', true);
        $student = Student::factory()->create(['name' => 'سارة علي']);
        Lesson::factory()->for($student)->create([
            'ai_summary' => 'ملخص آلي محفوظ للتقارير',
        ]);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('توصيات الذكاء الاصطناعي')
            ->assertSee('ملخص آلي محفوظ للتقارير');
    }

    public function test_reports_page_does_not_show_student_cards_for_an_unknown_filter(): void
    {
        Student::factory()->create(['name' => 'سارة علي']);

        $this->get(route('reports.index', ['student' => 999]))
            ->assertOk()
            ->assertSee('لا يوجد طالب بهذا المعرف')
            ->assertDontSee('تسميع:');
    }
}
