<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Setting;
use Illuminate\Support\Collection;

class LessonReminder
{
    /** @var list<int> */
    public const WINDOWS = [15, 30, 60, 1440];

    /**
     * Upcoming appointments whose start time is between now and reminder_before minutes.
     *
     * @return Collection<int, Appointment>
     */
    public static function dueAppointments(): Collection
    {
        if (! Setting::bool('notify_lesson_reminder') || ! Setting::bool('notify_channel_inapp')) {
            return collect();
        }

        $minutes = (int) Setting::getValue('reminder_before', '30');
        if (! in_array($minutes, self::WINDOWS, true)) {
            $minutes = 30;
        }

        $now = now();
        $until = $now->copy()->addMinutes($minutes);

        return Appointment::query()
            ->with('student')
            ->where('status', 'upcoming')
            ->whereDate('scheduled_date', '>=', $now->toDateString())
            ->whereDate('scheduled_date', '<=', $until->toDateString())
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->orderBy('id')
            ->get()
            ->filter(function (Appointment $appointment) use ($now, $until): bool {
                if ($appointment->student === null) {
                    return false;
                }

                $startsAt = $appointment->startsAt();

                return $startsAt->greaterThanOrEqualTo($now) && $startsAt->lessThanOrEqualTo($until);
            })
            ->values();
    }
}
