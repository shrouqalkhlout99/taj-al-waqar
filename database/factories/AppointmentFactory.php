<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Appointment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'account_id' => function (array $attributes) {
                return Student::query()->whereKey($attributes['student_id'])->value('account_id')
                    ?? Account::currentId();
            },
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '16:00',
            'status' => 'upcoming',
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'progress',
        ]);
    }

    public function done(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'done',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'cancelled',
        ]);
    }
}
