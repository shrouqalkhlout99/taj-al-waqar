<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FollowUpInbox
{
    /**
     * Neutral follow-up items inferred from appointments, lessons, and current ranges.
     *
     * @return Collection<int, array{student_id: int, student_name: string, message: string, href: string}>
     */
    public function items(): Collection
    {
        if (! Setting::bool('follow_up_enabled', true)) {
            return collect();
        }

        $items = collect();
        $this->addUndocumentedPastAppointments($items);
        $this->addIdleStudents($items);
        $this->addRepeatedCancellations($items);
        $this->addTomorrowWithoutPlan($items);

        return $items
            ->unique(fn (array $item): string => $item['student_id'].'|'.$item['message'])
            ->take(5)
            ->values();
    }

    /**
     * @param  Collection<int, array{student_id: int, student_name: string, message: string, href: string}>  $items
     */
    private function addUndocumentedPastAppointments(Collection $items): void
    {
        $appointments = Appointment::query()
            ->with('student')
            ->whereIn('status', ['upcoming', 'progress'])
            ->whereDate('scheduled_date', '<', now()->toDateString())
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->get();

        foreach ($appointments as $appointment) {
            $student = $appointment->student;
            if ($student === null) {
                continue;
            }

            $items->push([
                'student_id' => $student->id,
                'student_name' => $student->name,
                'message' => 'موعد سابق لم تُسجَّل حصته.',
                'href' => $appointment->canStart()
                    ? route('lessons.start', $appointment)
                    : route('students.index', ['id' => $student->id]),
            ]);
        }
    }

    /**
     * @param  Collection<int, array{student_id: int, student_name: string, message: string, href: string}>  $items
     */
    private function addIdleStudents(Collection $items): void
    {
        $idleDays = max(1, (int) Setting::getValue('follow_up_idle_days', 8));
        $cutoff = now()->subDays($idleDays)->toDateString();

        $students = Student::query()
            ->whereHas('lessons')
            ->withMax('lessons', 'session_date')
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name']);

        foreach ($students as $student) {
            $lastSession = $student->lessons_max_session_date;
            if ($lastSession === null) {
                continue;
            }

            $lastDate = Carbon::parse($lastSession)->toDateString();
            if ($lastDate > $cutoff) {
                continue;
            }

            $items->push([
                'student_id' => $student->id,
                'student_name' => $student->name,
                'message' => 'لم تُسجَّل حصة منذ '.$idleDays.' أيام.',
                'href' => route('students.index', ['id' => $student->id]),
            ]);
        }
    }

    /**
     * @param  Collection<int, array{student_id: int, student_name: string, message: string, href: string}>  $items
     */
    private function addRepeatedCancellations(Collection $items): void
    {
        $since = now()->subDays(30)->toDateString();

        $students = Student::query()
            ->withCount([
                'appointments as recent_cancelled_count' => function ($query) use ($since): void {
                    $query->where('status', 'cancelled')
                        ->whereDate('scheduled_date', '>=', $since);
                },
            ])
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name']);

        foreach ($students as $student) {
            if ((int) $student->recent_cancelled_count < 2) {
                continue;
            }

            $items->push([
                'student_id' => $student->id,
                'student_name' => $student->name,
                'message' => 'تكرر إلغاء المواعيد خلال الفترة الأخيرة.',
                'href' => route('appointments.index'),
            ]);
        }
    }

    /**
     * @param  Collection<int, array{student_id: int, student_name: string, message: string, href: string}>  $items
     */
    private function addTomorrowWithoutPlan(Collection $items): void
    {
        $appointments = Appointment::query()
            ->with('student')
            ->whereDate('scheduled_date', now()->addDay()->toDateString())
            ->whereIn('status', ['upcoming', 'progress'])
            ->orderBy('scheduled_time')
            ->get();

        foreach ($appointments as $appointment) {
            $student = $appointment->student;
            if ($student === null) {
                continue;
            }

            $hasPlan = filled(data_get($student->recitation, 'surah'))
                || filled(data_get($student->new_mem, 'surah'))
                || filled(data_get($student->review, 'surah'));

            if ($hasPlan) {
                continue;
            }

            $items->push([
                'student_id' => $student->id,
                'student_name' => $student->name,
                'message' => 'موعد غدٍ بدون تحضير ظاهر.',
                'href' => route('students.index', ['id' => $student->id]),
            ]);
        }
    }
}
