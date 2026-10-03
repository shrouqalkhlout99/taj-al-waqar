<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\AiAssistant;
use App\Services\LessonPlanSuggester;
use App\Support\Quran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssistantController extends Controller
{
    public function book(): View
    {
        return $this->page('book.index', Student::orderBy('name')->get());
    }

    public function index(): View
    {
        return $this->page('assistant.index', Student::orderBy('name')->get());
    }

    public function explain(Request $request, AiAssistant $ai): View
    {
        $surahMax = Quran::ayahCount($request->string('surah')->toString());

        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'surah' => ['required', 'string', Rule::in(config('quran.surahs'))],
            'ayah' => array_values(array_filter([
                'required',
                'integer',
                'min:1',
                $surahMax > 0 ? 'max:'.$surahMax : null,
            ])),
            'source' => ['nullable', 'in:book,assistant'],
        ], [
            'ayah.max' => 'رقم الآية يتجاوز عدد آيات هذه السورة.',
        ]);

        $student = Student::findOrFail($data['student_id']);
        $result = $ai->explain($student, $data['surah'], (int) $data['ayah']);
        $view = ($data['source'] ?? 'assistant') === 'book' ? 'book.index' : 'assistant.index';

        return view($view, [
            'students' => Student::orderBy('name')->get(),
            'selected' => $student,
            'surah' => $data['surah'],
            'ayah' => $data['ayah'],
            'result' => $result,
            'compose' => null,
        ]);
    }

    public function compose(Request $request, AiAssistant $ai, LessonPlanSuggester $suggester): View
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'task' => ['required', 'in:parent,plan,followup'],
        ]);

        $student = Student::query()->with('lessons')->findOrFail($data['student_id']);
        $plan = $suggester->suggest($student);
        $latestLesson = $student->lessons->first();

        $text = match ($data['task']) {
            'parent' => $ai->parentMessage($student, $latestLesson),
            'plan' => $ai->polishSuggestion($student, $plan),
            'followup' => $ai->followUpPoints($student),
        };

        return view('assistant.index', [
            'students' => Student::orderBy('name')->get(),
            'selected' => $student,
            'surah' => $student->current_surah ?: 'البقرة',
            'ayah' => data_get($student->new_mem, 'to') ?: $student->last_ayah ?: 1,
            'result' => null,
            'compose' => [
                'task' => $data['task'],
                'text' => $text,
            ],
        ]);
    }

    private function page(string $view, $students): View
    {
        $selected = $students->first();

        return view($view, [
            'students' => $students,
            'selected' => $selected,
            'surah' => $selected?->current_surah ?: 'البقرة',
            'ayah' => data_get($selected?->new_mem, 'to') ?: $selected?->last_ayah ?: 1,
            'result' => null,
            'compose' => null,
        ]);
    }
}
