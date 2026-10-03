<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use App\Support\Quran;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'name',
    'phone',
    'level',
    'style',
    'meet_link',
    'color',
    'current_surah',
    'last_ayah',
    'new_mem',
    'recitation',
    'review',
    'last_note',
    'last_note_date',
    'quran_score',
    'tajweed_score',
    'overall_score',
    'account_id',
])]
class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use BelongsToAccount, HasFactory;

    protected function casts(): array
    {
        return [
            'new_mem' => 'array',
            'recitation' => 'array',
            'review' => 'array',
            'last_note_date' => 'date',
            'last_ayah' => 'integer',
            'quran_score' => 'integer',
            'tajweed_score' => 'integer',
            'overall_score' => 'integer',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->latest('session_date')->latest('id');
    }

    public function parentContact(): HasOne
    {
        return $this->hasOne(ParentContact::class);
    }

    /**
     * @param  array{name: string, phone: ?string, relation: ?string}|null  $data
     */
    public function syncParentContact(?array $data): void
    {
        if ($data === null) {
            $this->parentContact()->delete();

            return;
        }

        $this->parentContact()->updateOrCreate([], $data);
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/u', trim($this->name)) ?: [];
        $letters = collect($parts)->take(2)->map(fn ($part) => mb_substr($part, 0, 1));

        return $letters->implode('') ?: 'ط';
    }

    public function requirement(): string
    {
        return 'تسميع: '.Quran::rangeText($this->recitation);
    }

    public function applyLesson(Lesson $lesson): void
    {
        $this->forceFill([
            'current_surah' => data_get($lesson->new_mem, 'surah') ?: $this->current_surah,
            'last_ayah' => data_get($lesson->new_mem, 'to') ?: $this->last_ayah,
            'new_mem' => $lesson->next_new_mem ?: $lesson->new_mem,
            'recitation' => $lesson->next_recitation ?: $lesson->recitation,
            'review' => $lesson->next_review ?: $lesson->review,
            'last_note' => $lesson->notes ?: $this->last_note,
            'last_note_date' => now()->toDateString(),
        ])->save();
    }
}
