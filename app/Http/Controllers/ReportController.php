<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Lesson;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __invoke(Request $request): View
    {
        $today = Appointment::onDate()->get();
        $period = $this->periodFrom($request);

        $showAiTips = Setting::bool('report_show_ai_tips', false);
        $showTeacherTips = Setting::bool('report_show_teacher_tips', true);

        $students = Student::query()
            ->orderBy('name')
            ->orderBy('id')
            ->get([
                'id',
                'name',
                'color',
                'new_mem',
                'review',
                'recitation',
                'quran_score',
                'tajweed_score',
                'overall_score',
            ]);

        $focusId = $request->integer('student');
        $studentFilterMissing = false;
        $reportStudents = $students;
        if ($focusId > 0) {
            $reportStudents = $students->where('id', $focusId)->values();
            $studentFilterMissing = $reportStudents->isEmpty();
        }

        $periodLessons = $this->periodLessons($reportStudents, $period, $showTeacherTips);

        return view('reports.index', [
            'studentsCount' => Student::count(),
            'lessonsCount' => $this->lessonsCount($period),
            'todayCount' => $today->count(),
            'todayDone' => $today->where('status', 'done')->count(),
            'progressAverages' => [
                'قرآن' => (int) round($students->avg('quran_score') ?: 0),
                'تجويد' => (int) round($students->avg('tajweed_score') ?: 0),
                'التقدم العام' => (int) round($students->avg('overall_score') ?: 0),
            ],
            'hifzStudents' => $students->filter(
                fn (Student $student): bool => filled(data_get($student->new_mem, 'surah'))
            ),
            'reviewStudents' => $students->filter(
                fn (Student $student): bool => filled(data_get($student->review, 'surah'))
            ),
            'reportStudents' => $reportStudents,
            'latestSummaries' => $this->latestSummariesFor($reportStudents, $showAiTips, $period),
            'latestLessonNotes' => $showTeacherTips ? $periodLessons : collect(),
            'studentFilterMissing' => $studentFilterMissing,
            'showStudent' => Setting::bool('report_student', true),
            'showAttendance' => Setting::bool('report_attendance', true),
            'showHifz' => Setting::bool('report_hifz', true),
            'showReview' => Setting::bool('report_review', true),
            'showProgress' => Setting::bool('report_show_progress', true),
            'showTeacherTips' => $showTeacherTips,
            'showAiTips' => $showAiTips,
            'autoPrint' => $request->boolean('print'),
            'periodFrom' => $period['from'],
            'periodTo' => $period['to'],
        ]);
    }

    /**
     * @return array{from: ?string, to: ?string}
     */
    private function periodFrom(Request $request): array
    {
        $from = $this->optionalDate($request, 'from');
        $to = $this->optionalDate($request, 'to');

        return [
            'from' => $from,
            'to' => $to,
        ];
    }

    private function optionalDate(Request $request, string $key): ?string
    {
        $value = trim((string) $request->query($key, ''));
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array{from: ?string, to: ?string}  $period
     */
    private function lessonsCount(array $period): int
    {
        $query = Lesson::query();
        $this->constrainPeriod($query, $period);

        return $query->count();
    }

    /**
     * @param  Collection<int, Student>  $students
     * @param  array{from: ?string, to: ?string}  $period
     * @return Collection<int, Lesson>
     */
    private function latestSummariesFor(Collection $students, bool $showAiTips, array $period): Collection
    {
        if (! $showAiTips || $students->isEmpty()) {
            return collect();
        }

        $query = Lesson::query()
            ->select('id', 'student_id', 'ai_summary', 'session_date')
            ->whereIn('student_id', $students->pluck('id'))
            ->whereNotNull('ai_summary')
            ->where('ai_summary', '!=', '');
        $this->constrainPeriod($query, $period);

        return $query
            ->orderByDesc('session_date')
            ->orderByDesc('id')
            ->get()
            ->unique('student_id')
            ->keyBy('student_id');
    }

    /**
     * @param  Collection<int, Student>  $students
     * @param  array{from: ?string, to: ?string}  $period
     * @return Collection<int, Lesson>
     */
    private function periodLessons(Collection $students, array $period, bool $showTeacherTips): Collection
    {
        if (! $showTeacherTips || $students->isEmpty()) {
            return collect();
        }

        $query = Lesson::query()
            ->select('id', 'student_id', 'notes', 'session_date')
            ->whereIn('student_id', $students->pluck('id'))
            ->whereNotNull('notes')
            ->where('notes', '!=', '');
        $this->constrainPeriod($query, $period);

        return $query
            ->orderByDesc('session_date')
            ->orderByDesc('id')
            ->get()
            ->unique('student_id')
            ->keyBy('student_id');
    }

    /**
     * @param  Builder<Lesson>  $query
     * @param  array{from: ?string, to: ?string}  $period
     */
    private function constrainPeriod(Builder $query, array $period): void
    {
        if (filled($period['from'])) {
            $query->whereDate('session_date', '>=', $period['from']);
        }

        if (filled($period['to'])) {
            $query->whereDate('session_date', '<=', $period['to']);
        }
    }
}
