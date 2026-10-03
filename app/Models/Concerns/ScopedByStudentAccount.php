<?php

namespace App\Models\Concerns;

use App\Models\Account;
use Illuminate\Database\Eloquent\Builder;

trait ScopedByStudentAccount
{
    public static function bootScopedByStudentAccount(): void
    {
        static::addGlobalScope('account', function (Builder $builder): void {
            $accountId = Account::scopedId();

            if ($accountId === null) {
                $builder->whereRaw('0 = 1');

                return;
            }

            $builder->whereHas('student', function (Builder $query) use ($accountId): void {
                $query->withoutGlobalScopes()->where('account_id', $accountId);
            });
        });
    }
}
