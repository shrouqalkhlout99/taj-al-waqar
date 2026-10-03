<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use App\Support\Quran;
use Carbon\Carbon;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['student_id', 'scheduled_date', 'scheduled_time', 'status', 'account_id'])]
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use BelongsToAccount, HasFactory;

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
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

    public function lesson(): HasOne
    {
        return $this->hasOne(Lesson::class);
    }

    public function scopeOnDate(Builder $query, $date = null): Builder
    {
        return $query
            ->whereDate('scheduled_date', $date ?? now()->toDateString())
            ->orderBy('scheduled_time');
    }

    public function scopeUpcomingToday(Builder $query): Builder
    {
        return $query->onDate()->whereIn('status', ['upcoming', 'progress']);
    }

    public function statusLabel(): string
    {
        return Quran::statusLabel($this->status);
    }

    public function timeLabel(): string
    {
        return substr((string) $this->scheduled_time, 0, 5);
    }

    public function startsAt(): Carbon
    {
        return Carbon::parse($this->scheduled_date->toDateString().' '.substr((string) $this->scheduled_time, 0, 8));
    }

    public function canStart(): bool
    {
        return in_array($this->status, ['upcoming', 'progress'], true);
    }
}
