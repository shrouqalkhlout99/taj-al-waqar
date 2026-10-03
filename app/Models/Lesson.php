<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use App\Support\Quran;
use Database\Factories\LessonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'student_id',
    'appointment_id',
    'session_date',
    'session_time',
    'recitation',
    'new_mem',
    'review',
    'next_recitation',
    'next_new_mem',
    'next_review',
    'recitation_grade',
    'new_mem_grade',
    'notes',
    'ai_summary',
    'account_id',
])]
class Lesson extends Model
{
    /** @use HasFactory<LessonFactory> */
    use BelongsToAccount, HasFactory;

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'recitation' => 'array',
            'new_mem' => 'array',
            'review' => 'array',
            'next_recitation' => 'array',
            'next_new_mem' => 'array',
            'next_review' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function recitationText(): string
    {
        return Quran::rangeText($this->recitation);
    }

    public function newMemText(): string
    {
        return Quran::rangeText($this->new_mem);
    }

    public function reviewText(): string
    {
        return Quran::rangeText($this->review);
    }
}
