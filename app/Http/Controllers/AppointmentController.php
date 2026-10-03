<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Setting;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(): View
    {
        $appointments = Appointment::with('student')
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->get();

        return view('appointments.index', [
            'appointments' => $appointments,
            'allowReschedule' => Setting::bool('allow_reschedule', true),
        ]);
    }

    public function create(): View
    {
        return view('appointments.form', [
            'appointment' => new Appointment([
                'scheduled_date' => now()->toDateString(),
                'scheduled_time' => Setting::getValue('work_start', '16:00'),
                'student_id' => Student::query()->value('id'),
            ]),
            'students' => Student::orderBy('name')->get(),
            'lessonMinutes' => Setting::lessonDurationMinutes(),
            'allowReschedule' => Setting::bool('allow_reschedule', true),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->payload($request);
        if ($block = $this->conflictResponse($data)) {
            return $block;
        }
        $warning = $this->overlapWarning($data);
        Appointment::create($data + ['status' => 'upcoming']);

        return redirect()
            ->route('appointments.index')
            ->with('success', 'تم حفظ الموعد')
            ->with('warning', $warning);
    }

    public function edit(Appointment $appointment): View|RedirectResponse
    {
        if (! Setting::bool('allow_reschedule', true)) {
            return redirect()
                ->route('appointments.index')
                ->with('warning', 'إعادة جدولة المواعيد غير مفعّلة من الإعدادات.');
        }

        return view('appointments.form', [
            'appointment' => $appointment,
            'students' => Student::orderBy('name')->get(),
            'lessonMinutes' => Setting::lessonDurationMinutes(),
            'allowReschedule' => Setting::bool('allow_reschedule', true),
        ]);
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $this->payload($request);
        if ($block = $this->conflictResponse($data, $appointment->id)) {
            return $block;
        }
        $warning = $this->overlapWarning($data, $appointment->id);
        $appointment->update($data);

        return redirect()
            ->route('appointments.index')
            ->with('success', 'تم تعديل الموعد')
            ->with('warning', $warning);
    }

    public function cancel(Appointment $appointment): RedirectResponse
    {
        $appointment->update(['status' => 'cancelled']);

        return back()->with('success', 'تم إلغاء الموعد');
    }

    private function payload(Request $request): array
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'scheduled_date' => ['required', 'date'],
            'scheduled_time' => ['required'],
        ]);

        $data['scheduled_time'] = substr((string) $data['scheduled_time'], 0, 5);

        return $data;
    }

    private function conflictResponse(array $data, ?int $ignoreId = null): ?RedirectResponse
    {
        if (! Setting::bool('prevent_scheduling_conflicts', true) || ! $this->hasOverlap($data, $ignoreId)) {
            return null;
        }

        return back()
            ->withInput()
            ->with('warning', 'لا يمكن حفظ الموعد: يوجد تعارض مع حصة أخرى في نفس الفترة، مع احتساب مدة الحصة والفاصل.');
    }

    private function overlapWarning(array $data, ?int $ignoreId = null): ?string
    {
        if (Setting::bool('prevent_scheduling_conflicts', true)) {
            return null;
        }

        return $this->hasOverlap($data, $ignoreId)
            ? 'تنبيه: يوجد موعد آخر في نفس الفترة. تأكدي قبل المتابعة.'
            : null;
    }

    private function hasOverlap(array $data, ?int $ignoreId = null): bool
    {
        $span = Setting::lessonDurationMinutes() + Setting::bufferMinutes();
        $start = Carbon::parse($data['scheduled_date'].' '.$data['scheduled_time']);
        $end = $start->copy()->addMinutes($span);

        $existing = Appointment::query()
            ->whereDate('scheduled_date', $data['scheduled_date'])
            ->where('status', '!=', 'cancelled')
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->get();

        foreach ($existing as $appointment) {
            $otherStart = Carbon::parse($appointment->scheduled_date->toDateString().' '.$appointment->timeLabel());
            $otherEnd = $otherStart->copy()->addMinutes($span);
            if ($start->lt($otherEnd) && $end->gt($otherStart)) {
                return true;
            }
        }

        return false;
    }
}
