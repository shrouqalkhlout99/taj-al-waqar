<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = ['account_id', 'key', 'value'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public const DEFAULTS = [
        'teacher_name' => 'معلمة قرآن',
        'teacher_email' => '',
        'teacher_whatsapp' => '',
        'teacher_phone' => '',
        'teacher_country' => '',
        'teacher_city' => '',
        'teacher_timezone' => 'Asia/Riyadh',
        'teacher_language' => 'ar',
        'teacher_photo' => '',
        'teacher_video' => '',
        'teacher_display_name' => '',
        'teacher_bio' => '',
        'teacher_specialty' => '',
        'teacher_experience_years' => '',
        'teacher_qualifications' => '',
        'teacher_certificates' => '',
        'teacher_qiraah' => 'حفص عن عاصم',
        'lesson_type' => 'both',
        'default_lesson_duration' => '45',
        'custom_lesson_duration' => '50',
        'max_students_per_group' => '8',
        'working_days' => '["sunday","monday","tuesday","wednesday","thursday"]',
        'work_start' => '16:00',
        'work_end' => '21:00',
        'break_duration' => '10',
        'reminder_before' => '30',
        'allow_auto_booking' => '0',
        'allow_reschedule' => '1',
        'min_cancel_hours' => '12',
        'allow_makeup' => '1',
        'prevent_scheduling_conflicts' => '1',
        'buffer_minutes' => '5',
        'curriculum_quran' => '1',
        'curriculum_tajweed' => '1',
        'curriculum_aqidah' => '0',
        'curriculum_fiqh' => '0',
        'curriculum_custom' => '[]',
        'hifz_method' => 'ayah_range',
        'review_method' => 'ayah_range',
        'smart_revision' => '1',
        'enable_hifz_grade' => '1',
        'enable_tilawa_grade' => '1',
        'enable_tajweed_grade' => '1',
        'enable_error_tracking' => '1',
        'show_last_hifz' => '1',
        'show_last_review' => '1',
        'show_mastery_percent' => '1',
        'show_error_count' => '1',
        'show_review_count' => '1',
        'openai_key' => '',
        'openai_model' => 'gpt-4o-mini',
        'ai_enabled' => '1',
        'ai_lesson_plan' => '1',
        'ai_activities' => '1',
        'ai_homework' => '1',
        'ai_analyze' => '1',
        'ai_revision_plan' => '1',
        'ai_summarize' => '1',
        'ai_student_report' => '1',
        'ai_weak_points' => '1',
        'ai_next_lesson' => '1',
        'ai_plan_style' => 'medium',
        'ai_intervention' => 'medium',
        'ai_use_student_data' => '1',
        'notify_lesson_reminder' => '1',
        'notify_lesson_confirm' => '1',
        'notify_lesson_cancel' => '1',
        'notify_lesson_reschedule' => '1',
        'notify_student_absent' => '1',
        'notify_student_drop' => '1',
        'notify_hifz_drop' => '1',
        'notify_homework_incomplete' => '1',
        'follow_up_enabled' => '1',
        'follow_up_idle_days' => '8',
        'notify_weekly_report' => '1',
        'notify_monthly_report' => '0',
        'notify_channel_inapp' => '1',
        'notify_channel_email' => '0',
        'notify_channel_push' => '0',
        'parents_enabled' => '0',
        'parent_send_summary' => '1',
        'parent_send_homework' => '1',
        'parent_send_grade' => '1',
        'parent_send_progress' => '1',
        'parent_notify_absent' => '1',
        'parent_notify_next' => '1',
        'parent_see_history' => '1',
        'parent_see_hifz' => '1',
        'parent_see_notes' => '0',
        'report_student' => '1',
        'report_hifz' => '1',
        'report_review' => '1',
        'report_attendance' => '1',
        'report_weekly' => '1',
        'report_monthly' => '1',
        'report_auto' => '0',
        'report_send_parent' => '0',
        'report_show_progress' => '1',
        'report_show_strengths' => '1',
        'report_show_weaknesses' => '1',
        'report_show_teacher_tips' => '1',
        'report_show_ai_tips' => '0',
        'theme' => 'light',
        'ui_language' => 'ar',
        'ui_direction' => 'rtl',
        'font_size' => 'medium',
        'density' => 'comfortable',
        'last_backup_at' => '',
    ];

    public const TABS = [
        'account' => ['label' => 'الحساب', 'icon' => '👤'],
        'teacher' => ['label' => 'إعدادات المعلمة', 'icon' => '👩🏻‍🏫'],
        'calendar' => ['label' => 'المواعيد والتقويم', 'icon' => '📅'],
        'curriculum' => ['label' => 'المناهج التعليمية', 'icon' => '📚'],
        'quran' => ['label' => 'القرآن والحفظ', 'icon' => '📖'],
        'ai' => ['label' => 'الذكاء الاصطناعي', 'icon' => '🤖'],
        'notifications' => ['label' => 'الإشعارات', 'icon' => '🔔'],
        'parents' => ['label' => 'أولياء الأمور', 'icon' => '👨‍👩‍👧'],
        'reports' => ['label' => 'التقارير', 'icon' => '📊'],
        'appearance' => ['label' => 'المظهر', 'icon' => '🎨'],
        'privacy' => ['label' => 'الخصوصية والأمان', 'icon' => '🔐'],
        'subscription' => ['label' => 'الاشتراك', 'icon' => '💳'],
        'data' => ['label' => 'البيانات والنسخ الاحتياطي', 'icon' => '💾'],
        'help' => ['label' => 'المساعدة والدعم', 'icon' => '🆘'],
    ];

    public const TIMEZONES = [
        'Asia/Riyadh' => 'الرياض (توقيت السعودية)',
        'Asia/Kuwait' => 'الكويت',
        'Asia/Qatar' => 'قطر',
        'Asia/Bahrain' => 'البحرين',
        'Asia/Dubai' => 'دبي / أبوظبي',
        'Asia/Muscat' => 'مسقط',
        'Asia/Amman' => 'عمّان',
        'Asia/Beirut' => 'بيروت',
        'Asia/Damascus' => 'دمشق',
        'Asia/Baghdad' => 'بغداد',
        'Africa/Cairo' => 'القاهرة',
        'Africa/Khartoum' => 'الخرطوم',
        'Africa/Tripoli' => 'طرابلس',
        'Africa/Algiers' => 'الجزائر',
        'Africa/Casablanca' => 'الدار البيضاء',
        'Africa/Tunis' => 'تونس',
        'Europe/Istanbul' => 'إسطنبول',
        'Europe/London' => 'لندن',
        'UTC' => 'توقيت عالمي UTC',
    ];

    public static function allCached(): array
    {
        $accountId = Account::currentId();

        return Cache::remember('app_settings.'.$accountId, 60, function () use ($accountId) {
            return static::query()->where('account_id', $accountId)->pluck('value', 'key')->all();
        });
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $settings = static::allCached();

        if (array_key_exists($key, $settings)) {
            return $settings[$key];
        }

        if (func_num_args() >= 2) {
            return $default;
        }

        return static::DEFAULTS[$key] ?? null;
    }

    public static function setValue(string $key, mixed $value): void
    {
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        $accountId = Account::currentId();

        static::query()->updateOrCreate(
            ['account_id' => $accountId, 'key' => $key],
            ['value' => $value]
        );
        Cache::forget('app_settings.'.$accountId);
    }

    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            static::setValue($key, $value);
        }
    }

    public static function bag(): array
    {
        return array_merge(static::DEFAULTS, static::allCached());
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $fallback = $default ? '1' : (static::DEFAULTS[$key] ?? '0');
        $value = strtolower((string) static::getValue($key, $fallback));

        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    public static function json(string $key, array $default = []): array
    {
        $value = static::getValue($key, static::DEFAULTS[$key] ?? null);

        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || $value === '') {
            return $default;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : $default;
    }

    public static function teacherName(): string
    {
        return (string) static::getValue('teacher_name', static::DEFAULTS['teacher_name']);
    }

    public static function displayName(): string
    {
        $display = trim((string) static::getValue('teacher_display_name', ''));

        return $display !== '' ? $display : static::teacherName();
    }

    public static function lessonDurationMinutes(): int
    {
        $preset = (string) static::getValue('default_lesson_duration', '45');

        if ($preset === 'custom') {
            return max(5, (int) static::getValue('custom_lesson_duration', '50'));
        }

        return max(5, (int) $preset);
    }

    public static function bufferMinutes(): int
    {
        return max(0, (int) static::getValue('buffer_minutes', '5'));
    }

    public static function aiEnabled(): bool
    {
        return static::bool('ai_enabled', true);
    }

    public static function aiHasKey(): bool
    {
        return (string) (static::getValue('openai_key') ?: env('OPENAI_API_KEY')) !== '';
    }

    public static function mediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public static function whatsappLink(?string $number): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);
        if (! $digits) {
            return null;
        }

        return 'https://wa.me/'.$digits;
    }

    public static function deleteMedia(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
