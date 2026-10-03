<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Setting;
use App\Models\Student;
use App\Support\ParentLessonShare;
use App\Support\Quran;
use Illuminate\Support\Facades\Http;

class AiAssistant
{
    public function summarizeLesson(Student $student, Lesson $lesson): string
    {
        $local = $this->localSummary($student, $lesson);

        if (! Setting::aiEnabled() || ! Setting::bool('ai_summarize', true)) {
            return $local;
        }

        try {
            $ai = $this->openai([
                [
                    'role' => 'system',
                    'content' => 'أنت مساعدة لمعلمة قرآن. اكتبي '.$this->planStyleGuidance().' عن أداء الطالب بعد الحصة، بدون مقدمات.',
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($this->studentPayload($student, [
                        'recitation' => $lesson->recitationText(),
                        'recitationGrade' => Quran::gradeLabel($lesson->recitation_grade),
                        'newMem' => $lesson->newMemText(),
                        'newMemGrade' => Quran::gradeLabel($lesson->new_mem_grade),
                        'review' => $lesson->reviewText(),
                        'notes' => Setting::bool('ai_use_student_data', true) ? $lesson->notes : null,
                    ]), JSON_UNESCAPED_UNICODE),
                ],
            ]);

            return $ai ?: $local;
        } catch (\Throwable) {
            return $local;
        }
    }

    public function explain(Student $student, string $surah, int $ayah): array
    {
        $verseText = $this->fetchVerse($surah, $ayah);

        try {
            $ai = $this->openai([
                [
                    'role' => 'system',
                    'content' => 'أنت مساعدة لمعلمة قرآن. اشرحي الآية بأسلوب يناسب مستوى الطالب. '.$this->planStyleGuidance().' أرجعي JSON فقط بالمفاتيح: body, point, verse',
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($this->studentPayload($student, [
                        'surah' => $surah,
                        'ayah' => $ayah,
                        'verseText' => $verseText,
                        'style' => Setting::bool('ai_analyze', true) && Setting::bool('ai_use_student_data', true)
                            ? $student->style
                            : null,
                    ]), JSON_UNESCAPED_UNICODE),
                ],
            ]);

            if ($ai) {
                $parsed = json_decode(trim(preg_replace('/```json|```/', '', $ai)), true);
                if (is_array($parsed)) {
                    return [
                        'body' => $parsed['body'] ?? '',
                        'point' => $parsed['point'] ?? '',
                        'verse' => $parsed['verse'] ?? $verseText,
                    ];
                }
            }
        } catch (\Throwable) {
            // fallback below
        }

        return $this->localExplanation($student, $surah, $ayah, $verseText);
    }

    public function parentMessage(?Student $student, ?Lesson $lesson = null): string
    {
        $local = $this->localParentMessage($student, $lesson);

        if ($student === null || ! Setting::aiEnabled() || ! Setting::bool('ai_student_report', true)) {
            return $local;
        }

        try {
            $ai = $this->openai([
                [
                    'role' => 'system',
                    'content' => 'أنت مساعدة لمعلمة قرآن. اكتبي رسالة واتساب مختصرة لولي الأمر بالعربية، بدون الملاحظات الخاصة للمعلمة، وبدون مقدمات.',
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($this->parentPayload($student, $lesson), JSON_UNESCAPED_UNICODE),
                ],
            ]);

            return $ai ?: $local;
        } catch (\Throwable) {
            return $local;
        }
    }

    public function polishSuggestion(Student $student, array $plan): string
    {
        $local = (new LessonPlanSuggester)->wording($plan);

        if (! Setting::aiEnabled() || (! Setting::bool('ai_next_lesson', true) && ! Setting::bool('ai_lesson_plan', true))) {
            return $local;
        }

        try {
            $ai = $this->openai([
                [
                    'role' => 'system',
                    'content' => 'أنت مساعدة لمعلمة قرآن. صيغي اقتراح الحصة القادمة بجملة عربية واضحة من النطاقات المعطاة فقط، '.$this->planStyleGuidance(),
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($this->studentPayload($student, [
                        'plan' => $local,
                    ]), JSON_UNESCAPED_UNICODE),
                ],
            ]);

            return $ai ?: $local;
        } catch (\Throwable) {
            return $local;
        }
    }

    public function followUpPoints(Student $student): string
    {
        $local = $this->localFollowUpPoints($student);

        if (! Setting::aiEnabled() || ! Setting::bool('ai_weak_points', true)) {
            return $local;
        }

        try {
            $ai = $this->openai([
                [
                    'role' => 'system',
                    'content' => 'أنت مساعدة لمعلمة قرآن. اكتبي نقاط متابعة محايدة من سجل الحصص فقط، بدون أحكام، '.$this->planStyleGuidance(),
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($this->studentPayload($student, [
                        'points' => $local,
                    ]), JSON_UNESCAPED_UNICODE),
                ],
            ]);

            return $ai ?: $local;
        } catch (\Throwable) {
            return $local;
        }
    }

    public function localParentMessage(?Student $student, ?Lesson $lesson = null): string
    {
        if ($student === null) {
            return 'لا يوجد طالب لصياغة الرسالة.';
        }

        if ($lesson !== null) {
            return ParentLessonShare::messageFor($lesson);
        }

        return implode("\n", [
            'السلام عليكم،',
            'متابعة '.$student->name,
            'التسميع الحالي: '.Quran::rangeText($student->recitation),
            'الحفظ الحالي: '.Quran::rangeText($student->new_mem),
            'المراجعة الحالية: '.Quran::rangeText($student->review),
            '',
            'بارك الله فيكم.',
        ]);
    }

    public function localFollowUpPoints(Student $student): string
    {
        $lessons = $student->lessons()->limit(3)->get();
        if ($lessons->isEmpty()) {
            return 'لا يوجد سجل حصص لنقاط المتابعة بعد.';
        }

        $lines = $lessons->map(function (Lesson $lesson): string {
            return trim(sprintf(
                '%s: تسميع %s (%s)، حفظ %s (%s).',
                $lesson->session_date?->toDateString() ?: 'حصة',
                $lesson->recitationText(),
                Quran::gradeLabel($lesson->recitation_grade),
                $lesson->newMemText(),
                Quran::gradeLabel($lesson->new_mem_grade),
            ));
        });

        return $lines->implode(' ');
    }

    public function localSummary(Student $student, Lesson $lesson): string
    {
        $notes = $lesson->notes
            ? 'ملاحظة المعلمة: '.$lesson->notes
            : 'لا توجد ملاحظات إضافية.';

        return sprintf(
            '%s سمّع %s بمستوى «%s»، وحفظ %s بمستوى «%s»، وراجع %s. %s المطلوب للحصة القادمة: تسميع %s، وحفظ %s.',
            $student->name,
            $lesson->recitationText(),
            Quran::gradeLabel($lesson->recitation_grade),
            $lesson->newMemText(),
            Quran::gradeLabel($lesson->new_mem_grade),
            $lesson->reviewText(),
            $notes,
            Quran::rangeText($lesson->next_recitation),
            Quran::rangeText($lesson->next_new_mem),
        );
    }

    public function localExplanation(Student $student, string $surah, int $ayah, string $verseText = ''): array
    {
        $verse = $verseText ? '﴿'.$verseText.'﴾' : "سورة {$surah} الآية {$ayah}";
        $style = $student->style ?: 'شرح مبسط';

        $body = match ($style) {
            'قصة مبسطة' => "تخيّلي أنكِ تروين لـ{$student->name} قصة قصيرة: الآية {$verse} تذكّرنا أن كلام الله نور يهدينا. اربطي المعنى بشيء يعيشه في يومه، ثم اسأليه: ماذا تعلمنا من هذه الآية؟",
            'أسئلة ونقاش' => "ابدئي بقراءة الآية {$verse} ثم اسألي {$student->name}: من المتحدث؟ وما الطلب أو المعنى؟ وكيف نطبّقها اليوم؟",
            'شرح تفصيلي' => "اشرحي المفردات ثم المعنى الإجمالي ثم الفائدة العملية للآية {$verse}، مع ربطها بما سبق أن حفظه في {$student->current_surah}.",
            default => "بلّغي المعنى بجملة واحدة سهلة ثم أعيدي الآية {$verse} مع {$student->name} مرتين، لأن مستواه «{$student->level}».",
        };

        $point = $student->last_note
            ? 'رابط بملاحظتك السابقة: '.$student->last_note
            : "ركّزي على تثبيت الآية وربطها بما وصل إليه في {$student->current_surah}.";

        return [
            'body' => $body,
            'point' => $point,
            'verse' => $verseText,
        ];
    }

    public function fetchVerse(string $surahName, int $ayah): string
    {
        $index = Quran::surahNumber($surahName);
        if (! $index) {
            return '';
        }

        try {
            $response = Http::timeout(8)->get("https://api.alquran.cloud/v1/ayah/{$index}:{$ayah}/ar");

            return (string) data_get($response->json(), 'data.text', '');
        } catch (\Throwable) {
            return '';
        }
    }

    private function planStyleGuidance(): string
    {
        return match (Setting::getValue('ai_plan_style', 'medium')) {
            'short' => 'جملتين فقط.',
            'detailed' => 'فقرة متوسطة فيها نقاط القوة والضعف والمطلوب التالي.',
            default => 'ملخصاً عربياً قصيراً وواضحاً.',
        };
    }

    private function studentPayload(Student $student, array $extra = []): array
    {
        $share = Setting::bool('ai_use_student_data', true);

        return array_filter(array_merge([
            'student' => $share ? $student->name : 'طالب',
            'level' => $share && Setting::bool('ai_analyze', true) ? $student->level : null,
            'lastNote' => $share ? $student->last_note : null,
        ], $extra), fn ($value) => $value !== null && $value !== '');
    }

    /**
     * Payload for a parent-facing draft. Never includes the teacher's private last_note
     * unless sharing student data and parent_see_notes are both enabled.
     *
     * @return array<string, mixed>
     */
    private function parentPayload(Student $student, ?Lesson $lesson): array
    {
        $share = Setting::bool('ai_use_student_data', true);
        $includeNotes = $share && Setting::bool('parent_see_notes', false);

        $payload = [
            'student' => $share ? $student->name : 'طالب',
            'recitation' => $lesson?->recitationText() ?: Quran::rangeText($student->recitation),
            'newMem' => $lesson?->newMemText() ?: Quran::rangeText($student->new_mem),
            'review' => $lesson?->reviewText() ?: Quran::rangeText($student->review),
            'notes' => $includeNotes ? ($lesson?->notes) : null,
            'lastNote' => $includeNotes ? $student->last_note : null,
        ];

        return array_filter($payload, fn ($value) => $value !== null && $value !== '');
    }

    private function openai(array $messages): ?string
    {
        if (! Setting::aiEnabled()) {
            return null;
        }

        $key = Setting::getValue('openai_key') ?: env('OPENAI_API_KEY');
        if (! $key) {
            return null;
        }

        $model = Setting::getValue('openai_model', 'gpt-4o-mini');
        $response = Http::timeout(20)
            ->withToken($key)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.4,
            ]);

        if (! $response->successful()) {
            return null;
        }

        return trim((string) data_get($response->json(), 'choices.0.message.content', ''));
    }
}
