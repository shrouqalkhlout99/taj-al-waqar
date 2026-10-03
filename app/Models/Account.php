<?php

namespace App\Models;

use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    public static function currentId(): int
    {
        $authenticatedId = auth()->user()?->account_id;

        if ($authenticatedId) {
            return (int) $authenticatedId;
        }

        $id = static::query()->orderBy('id')->value('id');

        if ($id) {
            return (int) $id;
        }

        throw new \RuntimeException('لا يوجد حساب صالح لتعيين account_id.');
    }

    public static function scopedId(): ?int
    {
        $id = auth()->user()?->account_id;

        return $id ? (int) $id : null;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }
}
