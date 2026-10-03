<?php

namespace App\Models\Concerns;

use App\Models\Account;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToAccount
{
    public static function bootBelongsToAccount(): void
    {
        static::addGlobalScope('account', function (Builder $builder): void {
            $accountId = Account::scopedId();
            $table = $builder->getModel()->getTable();

            if ($accountId === null) {
                $builder->whereRaw('0 = 1');

                return;
            }

            $builder->where($table.'.account_id', $accountId);
        });

        static::creating(function (self $model): void {
            if ($model->account_id) {
                return;
            }

            if ($model->getAttribute('student_id')) {
                $fromStudent = Student::withoutGlobalScopes()
                    ->whereKey($model->student_id)
                    ->value('account_id');

                if ($fromStudent) {
                    $model->account_id = $fromStudent;

                    return;
                }
            }

            $model->account_id = Account::currentId();
        });
    }
}
