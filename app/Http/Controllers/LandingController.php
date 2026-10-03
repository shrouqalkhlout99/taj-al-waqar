<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Lesson;
use App\Models\Setting;
use App\Models\Student;
use App\Support\Quran;
use Illuminate\View\View;
use Throwable;

class LandingController extends Controller
{
    public function index(): View
    {
        return view('landing.index', $this->previewData());
    }

    public function privacy(): View
    {
        return view('landing.privacy', $this->chromeData());
    }

    public function terms(): View
    {
        return view('landing.terms', $this->chromeData());
    }

    /**
     * @return array<string, mixed>
     */
    private function chromeData(): array
    {
        return [
            'teacherName' => $this->teacherName(),
            'teacherEmail' => $this->teacherEmail(),
            'appName' => config('app.name', 'تاج الوقار'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function previewData(): array
    {
        $data = $this->chromeData() + [
            'studentCount' => 0,
            'todayCount' => 0,
            'todayDone' => 0,
            'todayRemaining' => 0,
            'lessonsCount' => 0,
            'averageScore' => null,
            'todayAppointments' => collect(),
            'previewStudents' => collect(),
            'recentLessons' => collect(),
            'previewStudent' => null,
            'arabicDate' => Quran::arabicDate(),
            'greeting' => Quran::greeting(),
        ];

        try {
            if (! auth()->check()) {
                return $data;
            }

            $today = Appointment::onDate()->with('student')->get();
            $students = Student::query()->orderBy('name')->get();
            $lessons = Lesson::query()->with('student')->latest('session_date')->latest('id')->limit(4)->get();

            $average = $students->avg('overall_score');
            if ($average === null) {
                $average = $students->avg('quran_score');
            }

            $data['studentCount'] = $students->count();
            $data['todayCount'] = $today->count();
            $data['todayDone'] = $today->where('status', 'done')->count();
            $data['todayRemaining'] = $today->whereNotIn('status', ['done', 'cancelled'])->count();
            $data['lessonsCount'] = Lesson::query()->count();
            $data['averageScore'] = $students->isNotEmpty() && $average !== null ? (int) round($average) : null;
            $data['todayAppointments'] = $today;
            $data['previewStudents'] = $students->take(4);
            $data['recentLessons'] = $lessons;
            $data['previewStudent'] = $today->first()?->student ?? $students->first();
        } catch (Throwable) {
            // Database may not be migrated yet.
        }

        return $data;
    }

    private function teacherName(): string
    {
        try {
            return Setting::displayName();
        } catch (Throwable) {
            return 'معلمة قرآن';
        }
    }

    private function teacherEmail(): string
    {
        try {
            return (string) Setting::getValue('teacher_email', '');
        } catch (Throwable) {
            return '';
        }
    }
}
