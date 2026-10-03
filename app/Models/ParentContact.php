<?php

namespace App\Models;

use App\Models\Concerns\ScopedByStudentAccount;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'student_id',
    'name',
    'phone',
    'relation',
])]
class ParentContact extends Model
{
    use ScopedByStudentAccount;

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
