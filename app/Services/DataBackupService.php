<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Appointment;
use App\Models\Lesson;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DataBackupService
{
    public function databasePath(): string
    {
        $path = config('database.connections.sqlite.database');

        if (! $path || $path === ':memory:') {
            $path = database_path('database.sqlite');
        }

        return $path;
    }

    public function databaseSize(): int
    {
        $path = $this->databasePath();

        return File::exists($path) ? (int) File::size($path) : 0;
    }

    public function lastBackupAt(): ?string
    {
        return Setting::getValue('last_backup_at') ?: $this->latestBackup()?->mtime;
    }

    public function listBackups(): array
    {
        if (! Storage::disk('local')->exists('backups')) {
            return [];
        }

        return collect(Storage::disk('local')->files('backups'))
            ->filter(fn (string $file) => str_ends_with($file, '.sqlite'))
            ->map(fn (string $file) => (object) [
                'name' => basename($file),
                'path' => $file,
                'size' => Storage::disk('local')->size($file),
                'mtime' => date('Y-m-d H:i', Storage::disk('local')->lastModified($file)),
            ])
            ->sortByDesc('mtime')
            ->values()
            ->all();
    }

    public function latestBackup(): ?object
    {
        return $this->listBackups()[0] ?? null;
    }

    public function createBackup(): string
    {
        $source = $this->databasePath();
        if (! File::exists($source)) {
            throw new RuntimeException('ملف قاعدة البيانات غير موجود.');
        }

        Storage::disk('local')->makeDirectory('backups');
        $name = 'taj-alwaqar-'.now()->format('Y-m-d-His').'.sqlite';
        Storage::disk('local')->put('backups/'.$name, File::get($source));
        Setting::setValue('last_backup_at', now()->toDateTimeString());

        return $name;
    }

    public function backupAbsolutePath(string $name): string
    {
        $safe = basename($name);
        $relative = 'backups/'.$safe;
        if (! Storage::disk('local')->exists($relative)) {
            throw new RuntimeException('النسخة الاحتياطية غير موجودة.');
        }

        return Storage::disk('local')->path($relative);
    }

    public function restore(string $name): void
    {
        $backup = $this->backupAbsolutePath($name);
        $target = $this->databasePath();

        if (! File::copy($backup, $target)) {
            throw new RuntimeException('تعذّر استعادة النسخة. أغلقي التطبيق ثم أعيدي المحاولة.');
        }

        Setting::setValue('last_backup_at', now()->toDateTimeString());
    }

    public function exportPayload(): array
    {
        $accountId = Account::currentId();
        $settings = Setting::query()
            ->where('account_id', $accountId)
            ->pluck('value', 'key')
            ->all();
        unset($settings['openai_key']);

        return [
            'app' => 'تاج الوقار',
            'exported_at' => now()->toIso8601String(),
            'account_id' => $accountId,
            'settings' => $settings,
            'students' => Student::query()->get()->toArray(),
            'appointments' => Appointment::query()->get()->toArray(),
            'lessons' => Lesson::query()->get()->toArray(),
        ];
    }

    public function clearTeacherProfile(): void
    {
        Setting::deleteMedia(Setting::getValue('teacher_photo'));
        Setting::deleteMedia(Setting::getValue('teacher_video'));

        Setting::setMany([
            'teacher_name' => Setting::DEFAULTS['teacher_name'],
            'teacher_email' => '',
            'teacher_whatsapp' => '',
            'teacher_phone' => '',
            'teacher_country' => '',
            'teacher_city' => '',
            'teacher_display_name' => '',
            'teacher_bio' => '',
            'teacher_specialty' => '',
            'teacher_experience_years' => '',
            'teacher_qualifications' => '',
            'teacher_certificates' => '',
            'teacher_photo' => '',
            'teacher_video' => '',
            'openai_key' => '',
        ]);
    }
}
