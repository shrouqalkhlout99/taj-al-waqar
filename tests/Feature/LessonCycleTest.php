<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Lesson;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LessonCycleTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsTeacher = true;

    public function test_starting_an_upcoming_appointment_marks_it_in_progress(): void
    {
        $student = Student::factory()->create(['name' => 'سارة علي']);
        $appointment = Appointment::factory()->for($student)->create([
            'status' => 'upcoming',
        ]);

        $this->get(route('lessons.start', $appointment))
            ->assertOk()
            ->assertSee('سارة علي')
            ->assertSee('الحصة الجارية');

        $this->assertSame('progress', $appointment->fresh()->status);
    }

    public function test_starting_a_finished_appointment_redirects_to_the_dashboard(): void
    {
        $appointment = Appointment::factory()->done()->create();

        $this->get(route('lessons.start', $appointment))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('warning');

        $this->assertSame('done', $appointment->fresh()->status);
    }

    public function test_finishing_a_lesson_creates_the_record_updates_the_student_and_marks_the_appointment_done(): void
    {
        Http::preventStrayRequests();
        $this->travelTo('2026-09-12 17:30:00');

        $student = Student::factory()->create([
            'name' => 'أحمد محمد',
            'current_surah' => 'الفاتحة',
            'last_ayah' => 7,
            'new_mem' => ['surah' => 'البقرة', 'from' => 1, 'to' => 5],
            'recitation' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
            'review' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
            'last_note' => 'ملاحظة قديمة',
        ]);
        $appointment = Appointment::factory()->for($student)->create([
            'status' => 'upcoming',
        ]);

        $this->get(route('lessons.start', $appointment))->assertOk();

        $response = $this->from(route('lessons.start', $appointment))
            ->post(route('lessons.finish', $appointment), $this->lessonPayload());

        $lesson = Lesson::query()->first();
        $this->assertNotNull($lesson);
        $response->assertRedirect(route('lessons.summary', $lesson));
        $this->assertSame($student->id, $lesson->student_id);
        $this->assertSame($appointment->id, $lesson->appointment_id);
        $this->assertSame('2026-09-12', $lesson->session_date->toDateString());
        $this->assertStringStartsWith('17:30', (string) $lesson->session_time);
        $this->assertSame(['surah' => 'الفاتحة', 'from' => 1, 'to' => 7], $lesson->recitation);
        $this->assertSame(['surah' => 'البقرة', 'from' => 1, 'to' => 5], $lesson->new_mem);
        $this->assertSame(['surah' => 'الفاتحة', 'from' => 1, 'to' => 7], $lesson->review);
        $this->assertSame(['surah' => 'البقرة', 'from' => 1, 'to' => 5], $lesson->next_recitation);
        $this->assertSame(['surah' => 'البقرة', 'from' => 6, 'to' => 10], $lesson->next_new_mem);
        $this->assertSame(['surah' => 'الفاتحة', 'from' => 1, 'to' => 7], $lesson->next_review);
        $this->assertSame('good', $lesson->recitation_grade);
        $this->assertSame('excellent', $lesson->new_mem_grade);
        $this->assertSame('ثبتت الغنة جيداً', $lesson->notes);
        $this->assertSame(
            'أحمد محمد سمّع الفاتحة 1–7 بمستوى «جيد»، وحفظ البقرة 1–5 بمستوى «ممتاز»، وراجع الفاتحة 1–7. ملاحظة المعلمة: ثبتت الغنة جيداً المطلوب للحصة القادمة: تسميع البقرة 1–5، وحفظ البقرة 6–10.',
            $lesson->ai_summary,
        );

        $this->assertSame('done', $appointment->fresh()->status);

        $student->refresh();
        $this->assertSame('البقرة', $student->current_surah);
        $this->assertSame(5, $student->last_ayah);
        $this->assertSame(['surah' => 'البقرة', 'from' => 6, 'to' => 10], $student->new_mem);
        $this->assertSame(['surah' => 'البقرة', 'from' => 1, 'to' => 5], $student->recitation);
        $this->assertSame(['surah' => 'الفاتحة', 'from' => 1, 'to' => 7], $student->review);
        $this->assertSame('ثبتت الغنة جيداً', $student->last_note);
        $this->assertSame('2026-09-12', $student->last_note_date->toDateString());
        $this->assertSame(6, $student->quran_score);
        $this->assertSame(70, $student->tajweed_score);
        $this->assertSame(38, $student->overall_score);
    }

    public function test_aborting_a_lesson_in_progress_returns_the_appointment_to_upcoming(): void
    {
        $student = Student::factory()->create();
        $appointment = Appointment::factory()->for($student)->create([
            'status' => 'upcoming',
        ]);

        $this->get(route('lessons.start', $appointment))->assertOk();
        $this->assertSame('progress', $appointment->fresh()->status);

        $this->from(route('lessons.start', $appointment))
            ->post(route('lessons.abort', $appointment))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $this->assertSame('upcoming', $appointment->fresh()->status);
        $this->assertSame(0, Lesson::query()->count());
    }

    public function test_finishing_a_lesson_saves_the_local_summary_when_openai_fails(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'api.openai.com/*' => Http::response('error', 500),
        ]);
        Setting::setValue('openai_key', 'sk-test');

        $student = Student::factory()->create(['name' => 'أحمد محمد']);
        $appointment = Appointment::factory()->for($student)->create([
            'status' => 'upcoming',
        ]);

        $this->get(route('lessons.start', $appointment))->assertOk();

        $response = $this->from(route('lessons.start', $appointment))
            ->post(route('lessons.finish', $appointment), $this->lessonPayload());

        $lesson = Lesson::query()->first();
        $this->assertNotNull($lesson);
        $response->assertRedirect(route('lessons.summary', $lesson));
        $this->assertSame(
            'أحمد محمد سمّع الفاتحة 1–7 بمستوى «جيد»، وحفظ البقرة 1–5 بمستوى «ممتاز»، وراجع الفاتحة 1–7. ملاحظة المعلمة: ثبتت الغنة جيداً المطلوب للحصة القادمة: تسميع البقرة 1–5، وحفظ البقرة 6–10.',
            $lesson->ai_summary,
        );
        $this->assertSame('done', $appointment->fresh()->status);
    }

    public function test_finishing_a_lesson_succeeds_when_openai_connection_fails(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'api.openai.com/*' => Http::failedConnection(),
        ]);
        Setting::setValue('openai_key', 'sk-test');

        $student = Student::factory()->create(['name' => 'أحمد محمد']);
        $appointment = Appointment::factory()->for($student)->create([
            'status' => 'upcoming',
        ]);

        $this->get(route('lessons.start', $appointment))->assertOk();

        $response = $this->from(route('lessons.start', $appointment))
            ->post(route('lessons.finish', $appointment), $this->lessonPayload());

        $lesson = Lesson::query()->first();
        $this->assertNotNull($lesson);
        $response->assertRedirect(route('lessons.summary', $lesson));
        $this->assertSame(
            'أحمد محمد سمّع الفاتحة 1–7 بمستوى «جيد»، وحفظ البقرة 1–5 بمستوى «ممتاز»، وراجع الفاتحة 1–7. ملاحظة المعلمة: ثبتت الغنة جيداً المطلوب للحصة القادمة: تسميع البقرة 1–5، وحفظ البقرة 6–10.',
            $lesson->ai_summary,
        );
        $this->assertSame('done', $appointment->fresh()->status);
    }

    public function test_finish_omits_student_name_and_notes_from_openai_when_sharing_is_disabled(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'ملخص آلي يجب ألا يُستخدم للتحقق من الاسم']],
                ],
            ]),
        ]);
        Setting::setValue('openai_key', 'sk-test');
        Setting::setValue('ai_use_student_data', false);

        $student = Student::factory()->create([
            'name' => 'أحمد محمد',
            'last_note' => 'ملاحظة سرية في الملف',
        ]);
        $appointment = Appointment::factory()->for($student)->create([
            'status' => 'upcoming',
        ]);

        $this->get(route('lessons.start', $appointment))->assertOk();
        $this->from(route('lessons.start', $appointment))
            ->post(route('lessons.finish', $appointment), $this->lessonPayload())
            ->assertRedirect();

        $lesson = Lesson::query()->first();
        $this->assertNotNull($lesson);
        $this->assertSame('done', $appointment->fresh()->status);

        Http::assertSent(function (Request $request) {
            if (! str_contains($request->url(), 'api.openai.com')) {
                return false;
            }

            $content = (string) data_get($request->data(), 'messages.1.content', '');

            $this->assertStringContainsString('طالب', $content);
            $this->assertStringNotContainsString('أحمد محمد', $content);
            $this->assertStringNotContainsString('ملاحظة سرية في الملف', $content);
            $this->assertStringNotContainsString('ثبتت الغنة جيداً', $content);
            $this->assertStringNotContainsString('sk-test', $content);

            return true;
        });
    }

    public function test_finishing_a_lesson_saves_the_generated_summary_when_openai_succeeds(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'ملخص آلي ناجح للحصة']],
                ],
            ]),
        ]);
        Setting::setValue('openai_key', 'sk-test');
        Setting::setValue('ai_plan_style', 'detailed');
        Setting::setValue('ai_intervention', 'low');

        $student = Student::factory()->create(['name' => 'أحمد محمد']);
        $appointment = Appointment::factory()->for($student)->create([
            'status' => 'upcoming',
        ]);

        $this->get(route('lessons.start', $appointment))->assertOk();
        $response = $this->from(route('lessons.start', $appointment))
            ->post(route('lessons.finish', $appointment), $this->lessonPayload());

        $lesson = Lesson::query()->first();
        $this->assertNotNull($lesson);
        $response->assertRedirect(route('lessons.summary', $lesson));
        $this->assertSame('ملخص آلي ناجح للحصة', $lesson->ai_summary);
        $this->assertSame('done', $appointment->fresh()->status);

        $this->get(route('lessons.summary', $lesson))
            ->assertOk()
            ->assertSee('ملخص آلي ناجح للحصة')
            ->assertDontSee('sk-test');

        Http::assertSent(function (Request $request) {
            if (! str_contains($request->url(), 'api.openai.com')) {
                return false;
            }

            $system = (string) data_get($request->data(), 'messages.0.content', '');
            $this->assertStringContainsString('فقرة متوسطة فيها نقاط القوة والضعف والمطلوب التالي.', $system);
            $this->assertStringNotContainsString('تدخلك خفيف: توجيه واحد دون إطالة.', $system);
            $this->assertStringNotContainsString('تدخلك', $system);
            $this->assertStringNotContainsString('sk-test', $system);

            return true;
        });
    }

    public function test_finishing_a_lesson_uses_the_local_summary_when_summarize_is_disabled(): void
    {
        Http::preventStrayRequests();
        Setting::setValue('openai_key', 'sk-test');
        Setting::setValue('ai_summarize', false);

        $student = Student::factory()->create(['name' => 'أحمد محمد']);
        $appointment = Appointment::factory()->for($student)->create([
            'status' => 'upcoming',
        ]);

        $this->get(route('lessons.start', $appointment))->assertOk();
        $response = $this->from(route('lessons.start', $appointment))
            ->post(route('lessons.finish', $appointment), $this->lessonPayload());

        $lesson = Lesson::query()->first();
        $this->assertNotNull($lesson);
        $response->assertRedirect(route('lessons.summary', $lesson));
        $this->assertSame(
            'أحمد محمد سمّع الفاتحة 1–7 بمستوى «جيد»، وحفظ البقرة 1–5 بمستوى «ممتاز»، وراجع الفاتحة 1–7. ملاحظة المعلمة: ثبتت الغنة جيداً المطلوب للحصة القادمة: تسميع البقرة 1–5، وحفظ البقرة 6–10.',
            $lesson->ai_summary,
        );
        $this->assertSame('done', $appointment->fresh()->status);
    }

    /**
     * @return array<string, mixed>
     */
    private function lessonPayload(): array
    {
        return [
            'recitation_surah' => 'الفاتحة',
            'recitation_from' => 1,
            'recitation_to' => 7,
            'recitation_grade' => 'good',
            'new_mem_surah' => 'البقرة',
            'new_mem_from' => 1,
            'new_mem_to' => 5,
            'new_mem_grade' => 'excellent',
            'review_surah' => 'الفاتحة',
            'review_from' => 1,
            'review_to' => 7,
            'next_recitation_surah' => 'البقرة',
            'next_recitation_from' => 1,
            'next_recitation_to' => 5,
            'next_new_mem_surah' => 'البقرة',
            'next_new_mem_from' => 6,
            'next_new_mem_to' => 10,
            'next_review_surah' => 'الفاتحة',
            'next_review_from' => 1,
            'next_review_to' => 7,
            'notes' => 'ثبتت الغنة جيداً',
        ];
    }
}
