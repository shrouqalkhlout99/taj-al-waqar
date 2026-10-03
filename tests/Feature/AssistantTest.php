<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AssistantTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsTeacher = true;

    public function test_assistant_page_shows_the_explain_form(): void
    {
        Student::factory()->create(['name' => 'زينة الحسن']);

        $this->get(route('assistant.index'))
            ->assertOk()
            ->assertSee('المساعد الذكي')
            ->assertSee('زينة الحسن')
            ->assertSee('جهّزي الشرح');
    }

    public function test_book_page_shows_the_explain_form(): void
    {
        Student::factory()->create(['name' => 'زينة الحسن']);

        $this->get(route('book.index'))
            ->assertOk()
            ->assertSee('الكتاب والشرح')
            ->assertSee('زينة الحسن')
            ->assertSee('جهّزي الشرح');
    }

    public function test_explain_form_limits_ayah_options_to_the_selected_surah(): void
    {
        Student::factory()->create([
            'name' => 'زينة الحسن',
            'current_surah' => 'الفاتحة',
            'last_ayah' => 7,
            'new_mem' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
        ]);

        $html = $this->get(route('assistant.index'))
            ->assertOk()
            ->getContent();

        $this->assertNamedSelectAllowsAyah($html, 'ayah', 7);
        $this->assertStringNotContainsString('name="ayah" type="number"', $html);
    }

    public function test_rejects_explain_ayah_beyond_the_surah_length(): void
    {
        $student = $this->explainStudent();

        $this->from(route('assistant.index'))
            ->post(route('assistant.explain'), [
                'student_id' => $student->id,
                'surah' => 'الفاتحة',
                'ayah' => 8,
                'source' => 'assistant',
            ])
            ->assertRedirect(route('assistant.index'))
            ->assertSessionHasErrors(['ayah' => 'رقم الآية يتجاوز عدد آيات هذه السورة.']);
    }

    #[DataProvider('explainSources')]
    public function test_explain_without_an_api_key_shows_the_local_explanation(string $source): void
    {
        $this->fakeVerseApi();
        $student = $this->explainStudent();

        $this->from(route($source === 'book' ? 'book.index' : 'assistant.index'))
            ->post(route('assistant.explain'), $this->explainPayload($student, $source))
            ->assertOk()
            ->assertSee('بلّغي المعنى بجملة واحدة سهلة ثم أعيدي الآية ﴿نص آية للاختبار﴾ مع زينة الحسن مرتين، لأن مستواه «مبتدئ».')
            ->assertSee('ركّزي على تثبيت الآية وربطها بما وصل إليه في الفاتحة.');

        Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'api.openai.com'));
    }

    public function test_explain_uses_local_explanation_when_ai_is_disabled(): void
    {
        $this->fakeVerseApi();
        Setting::setValue('ai_enabled', false);
        Setting::setValue('openai_key', 'sk-test');
        $student = $this->explainStudent();

        $this->post(route('assistant.explain'), $this->explainPayload($student))
            ->assertOk()
            ->assertSee('بلّغي المعنى بجملة واحدة سهلة ثم أعيدي الآية ﴿نص آية للاختبار﴾ مع زينة الحسن مرتين، لأن مستواه «مبتدئ».');

        Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'api.openai.com'));
    }

    public function test_explain_falls_back_to_the_local_explanation_when_openai_fails(): void
    {
        $this->fakeVerseAndOpenAi(Http::response('error', 500));
        Setting::setValue('openai_key', 'sk-test');
        $student = $this->explainStudent();

        $this->post(route('assistant.explain'), $this->explainPayload($student))
            ->assertOk()
            ->assertSee('بلّغي المعنى بجملة واحدة سهلة ثم أعيدي الآية ﴿نص آية للاختبار﴾ مع زينة الحسن مرتين، لأن مستواه «مبتدئ».')
            ->assertDontSee('شرح آلي يجب ألا يظهر');
    }

    public function test_explain_falls_back_to_the_local_explanation_when_openai_connection_fails(): void
    {
        $this->fakeVerseAndOpenAi(Http::failedConnection());
        Setting::setValue('openai_key', 'sk-test');
        $student = $this->explainStudent();

        $this->post(route('assistant.explain'), $this->explainPayload($student))
            ->assertOk()
            ->assertSee('بلّغي المعنى بجملة واحدة سهلة ثم أعيدي الآية ﴿نص آية للاختبار﴾ مع زينة الحسن مرتين، لأن مستواه «مبتدئ».');
    }

    public function test_explain_omits_student_name_and_notes_from_openai_when_sharing_is_disabled(): void
    {
        $this->fakeVerseAndOpenAi(Http::response([
            'choices' => [
                ['message' => ['content' => '{"body":"شرح آلي للاختبار","point":"نقطة آلية","verse":"نص آية للاختبار"}']],
            ],
        ]));
        Setting::setValue('openai_key', 'sk-test');
        Setting::setValue('ai_use_student_data', false);
        $student = $this->explainStudent([
            'last_note' => 'ملاحظة سرية للطالبة',
        ]);

        $this->post(route('assistant.explain'), $this->explainPayload($student))
            ->assertOk()
            ->assertSee('شرح آلي للاختبار');

        Http::assertSent(function (Request $request) {
            if (! str_contains($request->url(), 'api.openai.com')) {
                return false;
            }

            $content = (string) data_get($request->data(), 'messages.1.content', '');

            $this->assertStringContainsString('طالب', $content);
            $this->assertStringNotContainsString('زينة الحسن', $content);
            $this->assertStringNotContainsString('ملاحظة سرية للطالبة', $content);
            $this->assertStringNotContainsString('last_note', $content);
            $this->assertStringNotContainsString('"notes"', $content);
            $this->assertStringNotContainsString('شرح مبسط', $content);
            $this->assertStringNotContainsString('sk-test', $content);

            return true;
        });
    }

    public function test_explain_uses_the_generated_openai_result_when_the_api_succeeds(): void
    {
        $this->fakeVerseAndOpenAi(Http::response([
            'choices' => [
                ['message' => ['content' => '{"body":"شرح آلي ناجح","point":"نقطة آلية ناجحة","verse":"نص آية للاختبار"}']],
            ],
        ]));
        Setting::setValue('openai_key', 'sk-test');
        Setting::setValue('ai_plan_style', 'short');
        Setting::setValue('ai_intervention', 'high');
        $student = $this->explainStudent();

        $this->post(route('assistant.explain'), $this->explainPayload($student))
            ->assertOk()
            ->assertSee('شرح آلي ناجح')
            ->assertSee('نقطة آلية ناجحة')
            ->assertDontSee('بلّغي المعنى بجملة واحدة سهلة')
            ->assertDontSee('sk-test');

        Http::assertSent(function (Request $request) {
            if (! str_contains($request->url(), 'api.openai.com')) {
                return false;
            }

            $system = (string) data_get($request->data(), 'messages.0.content', '');
            $this->assertStringContainsString('جملتين فقط.', $system);
            $this->assertStringNotContainsString('تدخلك أوضح: اذكري خطوة تالية ونقطة تركيز واحدة.', $system);
            $this->assertStringNotContainsString('تدخلك', $system);
            $this->assertStringNotContainsString('sk-test', $system);

            return true;
        });
    }

    public function test_explain_omits_level_and_style_when_analysis_is_disabled(): void
    {
        $this->fakeVerseAndOpenAi(Http::response([
            'choices' => [
                ['message' => ['content' => '{"body":"شرح بلا تحليل","point":"نقطة","verse":"نص آية للاختبار"}']],
            ],
        ]));
        Setting::setValue('openai_key', 'sk-test');
        Setting::setValue('ai_analyze', false);
        $student = $this->explainStudent([
            'level' => 'متقدم',
            'style' => 'قصة مبسطة',
        ]);

        $this->post(route('assistant.explain'), $this->explainPayload($student))
            ->assertOk()
            ->assertSee('شرح بلا تحليل');

        Http::assertSent(function (Request $request) {
            if (! str_contains($request->url(), 'api.openai.com')) {
                return false;
            }

            $content = (string) data_get($request->data(), 'messages.1.content', '');
            $this->assertStringContainsString('زينة الحسن', $content);
            $this->assertStringNotContainsString('متقدم', $content);
            $this->assertStringNotContainsString('قصة مبسطة', $content);

            return true;
        });
    }

    public function test_compose_parent_message_falls_back_locally_and_omits_the_private_note(): void
    {
        Http::preventStrayRequests();
        $student = $this->explainStudent([
            'last_note' => 'ملاحظة سرية للطالبة',
            'recitation' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
            'new_mem' => ['surah' => 'البقرة', 'from' => 1, 'to' => 5],
            'review' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
        ]);

        $this->from(route('assistant.index'))
            ->post(route('assistant.compose'), [
                'student_id' => $student->id,
                'task' => 'parent',
            ])
            ->assertOk()
            ->assertSee('متابعة زينة الحسن')
            ->assertSee('الفاتحة 1–7')
            ->assertDontSee('ملاحظة سرية للطالبة');

        Http::assertNothingSent();
    }

    public function test_compose_parent_message_omits_the_private_note_from_openai(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'رسالة آلية لولي الأمر']],
                ],
            ]),
        ]);
        Setting::setValue('openai_key', 'sk-test');
        $student = $this->explainStudent([
            'last_note' => 'ملاحظة سرية للطالبة',
        ]);

        $this->post(route('assistant.compose'), [
            'student_id' => $student->id,
            'task' => 'parent',
        ])
            ->assertOk()
            ->assertSee('رسالة آلية لولي الأمر')
            ->assertDontSee('ملاحظة سرية للطالبة');

        Http::assertSent(function (Request $request) {
            if (! str_contains($request->url(), 'api.openai.com')) {
                return false;
            }

            $content = (string) data_get($request->data(), 'messages.1.content', '');
            $this->assertStringNotContainsString('ملاحظة سرية للطالبة', $content);
            $this->assertStringNotContainsString('lastNote', $content);
            $this->assertStringNotContainsString('sk-test', $content);

            return true;
        });
    }

    public function test_compose_plan_uses_the_local_wording_when_ai_is_disabled(): void
    {
        Http::preventStrayRequests();
        Setting::setValue('ai_enabled', false);
        $student = $this->explainStudent([
            'current_surah' => 'البقرة',
            'last_ayah' => 5,
            'new_mem' => ['surah' => 'البقرة', 'from' => 1, 'to' => 5],
            'recitation' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
            'review' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
        ]);

        $this->post(route('assistant.compose'), [
            'student_id' => $student->id,
            'task' => 'plan',
        ])
            ->assertOk()
            ->assertSee('آخر حفظ: البقرة 5')
            ->assertSee('حفظ مقترح: البقرة 6–10');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function explainSources(): array
    {
        return [
            'assistant' => ['assistant'],
            'book' => ['book'],
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function explainStudent(array $attributes = []): Student
    {
        return Student::factory()->create(array_merge([
            'name' => 'زينة الحسن',
            'level' => 'مبتدئ',
            'style' => 'شرح مبسط',
            'current_surah' => 'الفاتحة',
            'last_note' => null,
        ], $attributes));
    }

    /**
     * @return array<string, mixed>
     */
    private function explainPayload(Student $student, string $source = 'assistant'): array
    {
        return [
            'student_id' => $student->id,
            'surah' => 'البقرة',
            'ayah' => 255,
            'source' => $source,
        ];
    }

    private function fakeVerseApi(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'api.alquran.cloud/*' => Http::response([
                'data' => ['text' => 'نص آية للاختبار'],
            ]),
        ]);
    }

    private function fakeVerseAndOpenAi(mixed $openAiResponse): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'api.alquran.cloud/*' => Http::response([
                'data' => ['text' => 'نص آية للاختبار'],
            ]),
            'api.openai.com/*' => $openAiResponse,
        ]);
    }
}
