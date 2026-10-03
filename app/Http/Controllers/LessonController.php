<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Lesson;
use App\Services\AiAssistant;
use App\Services\LessonPlanSuggester;
use App\Services\StudentScoreService;
use App\Support\ParentLessonShare;
use App\Support\Quran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function index(): View
    {
        $lessons = Lesson::with('student')->latest('session_date')->latest('id')->get();

        return view('lessons.index', compact('lessons'));
    }

    public function start(Appointment $appointment, LessonPlanSuggester $suggester): View|RedirectResponse
    {
        if (! $appointment->canStart()) {
            return redirect()
                ->route('dashboard')
                ->with('warning', 'هذه الحصة منتهية أو ملغاة. يمكن فتح موعد جديد من صفحة المواعيد.');
        }

        $appointment->load('student');
        $appointment->update(['status' => 'progress']);
        $student = $appointment->student;
        $plan = $suggester->suggest($student);

        return view('lessons.session', [
            'appointment' => $appointment,
            'student' => $student,
            'plan' => $plan,
            'nextNewMem' => $plan['next_new_mem'],
        ]);
    }

    public function finish(Request $request, Appointment $appointment, AiAssistant $ai, StudentScoreService $scores): RedirectResponse
    {
        $student = $appointment->student;
        $input = $request->all();

        $lesson = Lesson::create([
            'student_id' => $student->id,
            'appointment_id' => $appointment->id,
            'session_date' => now()->toDateString(),
            'session_time' => now()->format('H:i'),
            'recitation' => Quran::rangeFromRequest($input, 'recitation'),
            'new_mem' => Quran::rangeFromRequest($input, 'new_mem'),
            'review' => Quran::rangeFromRequest($input, 'review'),
            'next_recitation' => Quran::rangeFromRequest($input, 'next_recitation'),
            'next_new_mem' => Quran::rangeFromRequest($input, 'next_new_mem'),
            'next_review' => Quran::rangeFromRequest($input, 'next_review'),
            'recitation_grade' => $request->input('recitation_grade', 'good'),
            'new_mem_grade' => $request->input('new_mem_grade', 'good'),
            'notes' => $request->input('notes'),
        ]);

        $lesson->ai_summary = $ai->summarizeLesson($student, $lesson);
        $lesson->save();

        $student->applyLesson($lesson);
        $scores->recalculate($student->fresh());
        $appointment->update(['status' => 'done']);

        return redirect()->route('lessons.summary', $lesson);
    }

    public function abort(Appointment $appointment): RedirectResponse
    {
        if ($appointment->status === 'progress') {
            $appointment->update(['status' => 'upcoming']);
        }

        return redirect()->route('dashboard')->with('success', 'تم إلغاء الحصة بدون حفظ');
    }

    public function summary(Lesson $lesson, AiAssistant $ai): View
    {
        $lesson->load('student.parentContact');
        $parentShare = ParentLessonShare::for($lesson);
        $parentDraft = $parentShare ? null : $ai->localParentMessage($lesson->student, $lesson);

        return view('lessons.summary', compact('lesson', 'parentShare', 'parentDraft'));
    }
}
