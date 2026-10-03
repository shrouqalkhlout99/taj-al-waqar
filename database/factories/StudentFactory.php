<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => fn () => auth()->user()?->account_id ?? Account::currentId(),
            'name' => fake()->name(),
            'phone' => fake()->numerify('05########'),
            'level' => 'مبتدئ',
            'style' => 'شرح مبسط',
            'meet_link' => 'https://meet.google.com/abc-defg-hij',
            'color' => '#6b4bd6',
            'current_surah' => 'الفاتحة',
            'last_ayah' => 7,
            'new_mem' => ['surah' => 'البقرة', 'from' => 1, 'to' => 5],
            'recitation' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
            'review' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
            'last_note' => null,
            'last_note_date' => null,
        ];
    }
}
