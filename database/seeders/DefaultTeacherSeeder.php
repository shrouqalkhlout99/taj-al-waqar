<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DefaultTeacherSeeder extends Seeder
{
    public const EMAIL = 'teacher@localhost';

    public const PASSWORD = 'password';

    public function run(): void
    {
        if (User::query()->exists()) {
            return;
        }

        $email = trim((string) Setting::getValue('teacher_email', ''));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = self::EMAIL;
        }

        User::query()->create([
            'account_id' => Account::currentId(),
            'name' => Setting::teacherName(),
            'email' => $email,
            'password' => self::PASSWORD,
            'email_verified_at' => now(),
        ]);
    }
}
