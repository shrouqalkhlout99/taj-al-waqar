<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Lesson;
use App\Models\ParentContact;
use App\Models\Setting;
use App\Models\Student;
use App\Support\ParentLessonShare;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ParentLessonShareTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsTeacher = true;

    public function test_teacher_sees_share_box_when_parent_has_a_phone_and_summary_is_enabled(): void
    {
        Http::preventStrayRequests();
        Mail::fake();
        Queue::fake();
        Notification::fake();

        $lesson = $this->lessonWithParent(phone: '0551234567', notes: 'ملاحظة داخلية سرية', ai: 'ملخص آلي سري ٨ج');
        $share = ParentLessonShare::for($lesson);

        $this->assertNotNull($share);
        $this->assertStringNotContainsString('ملاحظة داخلية سرية', $share->message);
        $this->assertStringNotContainsString('ملخص آلي سري ٨ج', $share->message);

        $this->get(route('lessons.summary', $lesson))
            ->assertOk()
            ->assertSee('مشاركة ملخص الحصة')
            ->assertSee('نسخ الملخص')
            ->assertSee('مشاركة عبر WhatsApp')
            ->assertSee('هند مشاركة ٨ج')
            ->assertSee('التسميع: الفاتحة 1–7 (جيد)', false);

        Mail::assertNothingSent();
        Queue::assertNothingPushed();
        Notification::assertNothingSent();
    }

    public function test_share_box_is_hidden_when_parent_send_summary_is_off(): void
    {
        $lesson = $this->lessonWithParent(phone: '0551234567');
        Setting::setValue('parent_send_summary', '0');

        $this->get(route('lessons.summary', $lesson))
            ->assertOk()
            ->assertDontSee('مشاركة ملخص الحصة')
            ->assertDontSee('نسخ الملخص')
            ->assertDontSee('مشاركة عبر WhatsApp');
    }

    public function test_share_box_is_hidden_when_the_student_has_no_parent_contact(): void
    {
        $lesson = $this->createLesson();

        $this->get(route('lessons.summary', $lesson))
            ->assertOk()
            ->assertDontSee('مشاركة ملخص الحصة');
    }

    public function test_share_box_is_hidden_when_the_parent_has_no_phone(): void
    {
        $lesson = $this->lessonWithParent(phone: null);

        $this->get(route('lessons.summary', $lesson))
            ->assertOk()
            ->assertDontSee('مشاركة ملخص الحصة')
            ->assertDontSee('مشاركة عبر WhatsApp');
    }

    public function test_guest_cannot_open_the_lesson_summary(): void
    {
        $lesson = $this->lessonWithParent(phone: '0551234567');

        auth()->logout();

        $this->get(route('lessons.summary', $lesson))
            ->assertRedirect(route('login'))
            ->assertDontSee('مشاركة ملخص الحصة')
            ->assertDontSee('هند مشاركة ٨ج');
    }

    public function test_share_text_does_not_include_another_student(): void
    {
        $this->lessonWithParent(phone: '0551234567');
        $other = Student::factory()->create(['name' => 'طالبة أخرى لا تُشارك']);
        $lesson = Lesson::query()->first();

        $this->get(route('lessons.summary', $lesson))
            ->assertOk()
            ->assertSee('مشاركة ملخص الحصة')
            ->assertDontSee('طالبة أخرى لا تُشارك');
    }

    public function test_landing_and_public_pages_do_not_show_parent_or_share_box(): void
    {
        $this->lessonWithParent(phone: '0559988877', parentName: 'خولة ولي ٨ج-خصوصية');

        auth()->logout();

        $this->get(route('landing'))
            ->assertOk()
            ->assertDontSee('مشاركة ملخص الحصة')
            ->assertDontSee('خولة ولي ٨ج-خصوصية')
            ->assertDontSee('0559988877');

        $this->get(route('landing.privacy'))
            ->assertOk()
            ->assertDontSee('مشاركة ملخص الحصة')
            ->assertDontSee('خولة ولي ٨ج-خصوصية');

        $this->get(route('landing.terms'))
            ->assertOk()
            ->assertDontSee('مشاركة ملخص الحصة')
            ->assertDontSee('خولة ولي ٨ج-خصوصية');
    }

    public function test_whatsapp_link_uses_digits_only_and_encodes_the_summary(): void
    {
        Http::preventStrayRequests();

        $lesson = $this->lessonWithParent(phone: '055-123-4567');
        $share = ParentLessonShare::for($lesson->load('student.parentContact'));

        $this->assertNotNull($share);
        $this->assertSame('https://wa.me/0551234567?text='.rawurlencode($share->message), $share->whatsappUrl);
        $this->assertStringNotContainsString('ملاحظة', $share->message);
        $this->assertStringNotContainsString('ملخص آلي', $share->message);

        $this->get(route('lessons.summary', $lesson))
            ->assertOk()
            ->assertSee('https://wa.me/0551234567?text=', false)
            ->assertSee(rawurlencode($share->message), false);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createLesson(array $attributes = []): Lesson
    {
        $student = Student::factory()->create(['name' => 'هند مشاركة ٨ج']);
        $appointment = Appointment::factory()->for($student)->create();

        return Lesson::factory()->for($student)->for($appointment)->create(array_merge([
            'notes' => 'ملاحظة داخلية سرية',
            'ai_summary' => 'ملخص آلي سري ٨ج',
        ], $attributes));
    }

    private function lessonWithParent(?string $phone, string $parentName = 'خولة ولي ٨ج', ?string $notes = null, ?string $ai = null): Lesson
    {
        $lesson = $this->createLesson(array_filter([
            'notes' => $notes,
            'ai_summary' => $ai,
        ], fn ($value) => $value !== null) ?: []);

        ParentContact::query()->create([
            'student_id' => $lesson->student_id,
            'name' => $parentName,
            'phone' => $phone,
            'relation' => 'أم',
        ]);

        return $lesson->fresh(['student.parentContact']);
    }
}
