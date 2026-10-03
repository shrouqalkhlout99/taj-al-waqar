<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\FollowUpInbox;
use App\Services\LessonPlanSuggester;
use App\Support\Quran;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(LessonPlanSuggester $suggester, FollowUpInbox $followUp): View
    {
        $appointments = Appointment::onDate()->with(['student.parentContact'])->get();
        $nextAppointment = $appointments->first(
            fn (Appointment $appointment): bool => $appointment->canStart() && $appointment->student !== null
        );
        $nextStudent = $nextAppointment?->student;
        $nextPlan = $nextStudent ? $suggester->suggest($nextStudent) : null;

        return view('dashboard.index', [
            'appointments' => $appointments,
            'nextAppointment' => $nextAppointment,
            'nextStudent' => $nextStudent,
            'nextPlan' => $nextPlan,
            'followUps' => $followUp->items(),
            'arabicDate' => Quran::arabicDate(),
            'greeting' => Quran::greeting(),
        ]);
    }
}
