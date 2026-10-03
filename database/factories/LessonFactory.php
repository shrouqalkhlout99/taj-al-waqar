<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Appointment;
use App\Models\Lesson;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'appointment_id' => Appointment::factory(),
            'account_id' => function (array $attributes) {
                return Student::query()->whereKey($attributes['student_id'])->value('account_id')
                    ?? Account::currentId();
            },
            'session_date' => now()->toDateString(),
            'session_time' => '16:05',
            'recitation' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
            'new_mem' => ['surah' => 'البقرة', 'from' => 1, 'to' => 5],
            'review' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
            'next_recitation' => ['surah' => 'البقرة', 'from' => 1, 'to' => 5],
            'next_new_mem' => ['surah' => 'البقرة', 'from' => 6, 'to' => 10],
            'next_review' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
            'recitation_grade' => 'good',
            'new_mem_grade' => 'good',
            'notes' => null,
            'ai_summary' => null,
        ];
    }
}
