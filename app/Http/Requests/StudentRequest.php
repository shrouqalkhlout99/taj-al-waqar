<?php

namespace App\Http\Requests;

use App\Models\Student;
use App\Support\Quran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $surahMax = Quran::ayahCount($this->string('current_surah')->toString());

        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'level' => ['required', 'string', Rule::in(config('quran.levels'))],
            'style' => ['required', 'string', Rule::in(config('quran.styles'))],
            'meet_link' => ['nullable', 'string', 'max:255'],
            'current_surah' => ['nullable', 'string', Rule::in(config('quran.surahs'))],
            'last_ayah' => array_values(array_filter([
                'nullable',
                'integer',
                'min:1',
                $surahMax > 0 ? 'max:'.$surahMax : null,
            ])),
            'last_note' => ['nullable', 'string'],
            'parent_name' => ['nullable', 'string', 'max:120', 'required_with:parent_phone,parent_relation'],
            'parent_phone' => ['nullable', 'string', 'max:30'],
            'parent_relation' => ['nullable', 'string', Rule::in(config('quran.parent_relations'))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'حقل الاسم مطلوب.',
            'phone.required' => 'حقل الجوال مطلوب.',
            'level.required' => 'حقل المستوى مطلوب.',
            'level.in' => 'اختاري مستوى صالحًا.',
            'style.required' => 'حقل طريقة الشرح مطلوب.',
            'style.in' => 'اختاري طريقة شرح صالحة.',
            'last_ayah.max' => 'رقم الآية يتجاوز عدد آيات هذه السورة.',
        ];
    }

    public function shouldSyncParentContact(): bool
    {
        return $this->exists('parent_name')
            || $this->exists('parent_phone')
            || $this->exists('parent_relation');
    }

    /**
     * @return array{name: string, phone: ?string, relation: ?string}|null
     */
    public function parentContactAttributes(): ?array
    {
        $name = $this->normalizedString($this->input('parent_name'));
        $phone = $this->normalizedString($this->input('parent_phone'));
        $relation = $this->normalizedString($this->input('parent_relation'));

        if ($name === null && $phone === null && $relation === null) {
            return null;
        }

        return [
            'name' => (string) $name,
            'phone' => $phone,
            'relation' => $relation,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function studentPayload(): array
    {
        $data = $this->safe()->only([
            'name',
            'phone',
            'level',
            'style',
            'meet_link',
            'current_surah',
            'last_ayah',
            'last_note',
        ]);

        $note = $this->normalizedNote($data['last_note'] ?? null);
        $data['last_note'] = $note;

        $existing = $this->route('student');
        $previousNote = $existing instanceof Student
            ? $this->normalizedNote($existing->last_note)
            : null;

        if ($note !== $previousNote) {
            $data['last_note_date'] = $note !== null ? now()->toDateString() : null;
        }

        return $data + [
            'new_mem' => Quran::rangeFromRequest($this->all(), 'new_mem'),
            'recitation' => Quran::rangeFromRequest($this->all(), 'recitation'),
            'review' => Quran::rangeFromRequest($this->all(), 'review'),
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['parent_name', 'parent_phone', 'parent_relation'] as $field) {
            if (! $this->exists($field)) {
                continue;
            }

            $this->merge([
                $field => $this->normalizedString($this->input($field)),
            ]);
        }
    }

    private function normalizedNote(mixed $note): ?string
    {
        return $this->normalizedString($note);
    }

    private function normalizedString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
