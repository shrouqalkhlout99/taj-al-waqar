<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Services\DataBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SettingController extends Controller
{
    public function index(Request $request, DataBackupService $backup): View
    {
        $tab = (string) $request->query('tab', 'account');
        if (! array_key_exists($tab, Setting::TABS)) {
            $tab = 'account';
        }

        $s = Setting::bag();
        $hasStoredOpenAiKey = filled($s['openai_key'] ?? '');
        $s['openai_key'] = '';

        $user = $request->user();
        if ($user instanceof User) {
            if (blank($s['teacher_email'] ?? '')) {
                $s['teacher_email'] = (string) $user->email;
            }
            if (blank($s['teacher_name'] ?? '')) {
                $s['teacher_name'] = (string) $user->name;
            }
        }

        return view('settings.index', [
            'tab' => $tab,
            'tabs' => Setting::TABS,
            's' => $s,
            'hasStoredOpenAiKey' => $hasStoredOpenAiKey,
            'teacherPhoto' => Setting::mediaUrl($s['teacher_photo'] ?? null),
            'teacherVideo' => Setting::mediaUrl($s['teacher_video'] ?? null),
            'studentCount' => Student::query()->count(),
            'dbSize' => $backup->databaseSize(),
            'lastBackupAt' => $backup->lastBackupAt(),
            'backups' => $backup->listBackups(),
            'aiAvailable' => Setting::aiEnabled() && Setting::aiHasKey(),
            'timezones' => Setting::TIMEZONES,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $section = (string) $request->input('section', 'account');
        $method = match ($section) {
            'account' => 'updateAccount',
            'teacher' => 'updateTeacher',
            'calendar' => 'updateCalendar',
            'curriculum' => 'updateCurriculum',
            'quran' => 'updateQuran',
            'ai' => 'updateAi',
            'notifications' => 'updateNotifications',
            'parents' => 'updateParents',
            'reports' => 'updateReports',
            'appearance' => 'updateAppearance',
            'privacy' => 'updatePrivacy',
            default => null,
        };

        if (! $method) {
            return back()->with('warning', 'هذا القسم يُحفظ من أزراره الخاصة.');
        }

        $this->{$method}($request);

        return redirect()
            ->route('settings.index', ['tab' => $section])
            ->with('success', 'تم حفظ التغييرات بنجاح');
    }

    public function backup(DataBackupService $backup): RedirectResponse
    {
        try {
            $name = $backup->createBackup();
        } catch (Throwable $e) {
            return redirect()
                ->route('settings.index', ['tab' => 'data'])
                ->with('warning', $e->getMessage());
        }

        return redirect()
            ->route('settings.index', ['tab' => 'data'])
            ->with('success', 'تم إنشاء نسخة احتياطية: '.$name);
    }

    public function downloadBackup(string $file, DataBackupService $backup)
    {
        try {
            $path = $backup->backupAbsolutePath($file);
        } catch (Throwable $e) {
            return redirect()
                ->route('settings.index', ['tab' => 'data'])
                ->with('warning', $e->getMessage());
        }

        return response()->download($path, basename($file));
    }

    public function export(DataBackupService $backup): StreamedResponse
    {
        $payload = $backup->exportPayload();
        $filename = 'taj-alwaqar-export-'.now()->format('Y-m-d').'.json';

        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }, $filename, ['Content-Type' => 'application/json; charset=UTF-8']);
    }

    public function restore(Request $request, DataBackupService $backup): RedirectResponse
    {
        $data = $request->validate([
            'backup_file' => ['required', 'string'],
            'confirm_restore' => ['required', 'in:استعادة'],
        ], [
            'confirm_restore.in' => 'اكتبي كلمة «استعادة» للتأكيد.',
        ]);

        try {
            $backup->restore($data['backup_file']);
        } catch (Throwable $e) {
            return redirect()
                ->route('settings.index', ['tab' => 'data'])
                ->with('warning', $e->getMessage());
        }

        return redirect()
            ->route('settings.index', ['tab' => 'data'])
            ->with('success', 'تمت استعادة النسخة الاحتياطية. راجعي البيانات قبل المتابعة.');
    }

    public function resetAi(): RedirectResponse
    {
        Setting::setMany([
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
            'openai_model' => 'gpt-4o-mini',
        ]);

        return redirect()
            ->route('settings.index', ['tab' => 'ai'])
            ->with('success', 'تمت إعادة ضبط إعدادات الذكاء الاصطناعي');
    }

    public function clearProfile(Request $request, DataBackupService $backup): RedirectResponse
    {
        $request->validate([
            'confirm_delete' => ['required', 'in:حذف'],
        ], [
            'confirm_delete.in' => 'اكتبي كلمة «حذف» لتأكيد مسح ملف المعلمة فقط.',
        ]);

        $backup->clearTeacherProfile();

        return redirect()
            ->route('settings.index', ['tab' => 'data'])
            ->with('success', 'تم مسح بيانات المعلمة فقط. الطلاب والحصص لم تُمس.');
    }

    private function updateAccount(Request $request): void
    {
        $data = $request->validate([
            'teacher_name' => ['required', 'string', 'max:80'],
            'teacher_email' => [
                'nullable',
                'email',
                'max:120',
                Rule::unique('users', 'email')->ignore($request->user()?->id),
            ],
            'teacher_whatsapp' => ['nullable', 'string', 'max:30'],
            'teacher_phone' => ['nullable', 'string', 'max:30'],
            'teacher_country' => ['nullable', 'string', 'max:80'],
            'teacher_city' => ['nullable', 'string', 'max:80'],
            'teacher_timezone' => ['required', Rule::in(array_keys(Setting::TIMEZONES))],
            'teacher_language' => ['required', Rule::in(['ar', 'en'])],
            'teacher_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'teacher_video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:81920'],
        ]);

        Setting::setMany([
            'teacher_name' => $data['teacher_name'],
            'teacher_email' => $data['teacher_email'] ?? '',
            'teacher_whatsapp' => $data['teacher_whatsapp'] ?? '',
            'teacher_phone' => $data['teacher_phone'] ?? '',
            'teacher_country' => $data['teacher_country'] ?? '',
            'teacher_city' => $data['teacher_city'] ?? '',
            'teacher_timezone' => $data['teacher_timezone'],
            'teacher_language' => $data['teacher_language'],
        ]);

        $user = $request->user();
        if ($user instanceof User) {
            $user->name = $data['teacher_name'];
            if (filled($data['teacher_email'] ?? null)) {
                $user->email = $data['teacher_email'];
            }
            $user->save();
        }

        if ($request->boolean('remove_photo')) {
            Setting::deleteMedia(Setting::getValue('teacher_photo'));
            Setting::setValue('teacher_photo', '');
        } elseif ($request->hasFile('teacher_photo')) {
            Setting::deleteMedia(Setting::getValue('teacher_photo'));
            Setting::setValue('teacher_photo', $request->file('teacher_photo')->store('teacher', 'public'));
        }

        if ($request->boolean('remove_video')) {
            Setting::deleteMedia(Setting::getValue('teacher_video'));
            Setting::setValue('teacher_video', '');
        } elseif ($request->hasFile('teacher_video')) {
            Setting::deleteMedia(Setting::getValue('teacher_video'));
            Setting::setValue('teacher_video', $request->file('teacher_video')->store('teacher', 'public'));
        }
    }

    private function updateTeacher(Request $request): void
    {
        $data = $request->validate([
            'teacher_display_name' => ['nullable', 'string', 'max:80'],
            'teacher_bio' => ['nullable', 'string', 'max:500'],
            'teacher_specialty' => ['nullable', 'string', 'max:120'],
            'teacher_experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
            'teacher_qualifications' => ['nullable', 'string', 'max:400'],
            'teacher_certificates' => ['nullable', 'string', 'max:400'],
            'teacher_qiraah' => ['nullable', 'string', 'max:80'],
            'lesson_type' => ['required', Rule::in(['individual', 'group', 'both'])],
            'default_lesson_duration' => ['required', Rule::in(['30', '45', '60', 'custom'])],
            'custom_lesson_duration' => ['nullable', 'integer', 'min:5', 'max:180'],
            'max_students_per_group' => ['nullable', 'integer', 'min:2', 'max:40'],
            'working_days' => ['nullable', 'array'],
            'working_days.*' => [Rule::in(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'])],
            'work_start' => ['required', 'date_format:H:i'],
            'work_end' => ['required', 'date_format:H:i'],
            'break_duration' => ['nullable', 'integer', 'min:0', 'max:60'],
        ]);

        Setting::setMany([
            'teacher_display_name' => $data['teacher_display_name'] ?? '',
            'teacher_bio' => $data['teacher_bio'] ?? '',
            'teacher_specialty' => $data['teacher_specialty'] ?? '',
            'teacher_experience_years' => (string) ($data['teacher_experience_years'] ?? ''),
            'teacher_qualifications' => $data['teacher_qualifications'] ?? '',
            'teacher_certificates' => $data['teacher_certificates'] ?? '',
            'teacher_qiraah' => $data['teacher_qiraah'] ?? '',
            'lesson_type' => $data['lesson_type'],
            'default_lesson_duration' => $data['default_lesson_duration'],
            'custom_lesson_duration' => (string) ($data['custom_lesson_duration'] ?? '50'),
            'max_students_per_group' => (string) ($data['max_students_per_group'] ?? '8'),
            'working_days' => array_values($data['working_days'] ?? []),
            'work_start' => $data['work_start'],
            'work_end' => $data['work_end'],
            'break_duration' => (string) ($data['break_duration'] ?? '10'),
        ]);
    }

    private function updateCalendar(Request $request): void
    {
        $data = $request->validate([
            'teacher_timezone' => ['required', Rule::in(array_keys(Setting::TIMEZONES))],
            'default_lesson_duration' => ['required', Rule::in(['30', '45', '60', 'custom'])],
            'custom_lesson_duration' => ['nullable', 'integer', 'min:5', 'max:180'],
            'reminder_before' => ['required', Rule::in(['15', '30', '60', '1440'])],
            'min_cancel_hours' => ['nullable', 'integer', 'min:0', 'max:72'],
            'buffer_minutes' => ['required', Rule::in(['0', '5', '10', '15'])],
        ]);

        Setting::setMany([
            'teacher_timezone' => $data['teacher_timezone'],
            'default_lesson_duration' => $data['default_lesson_duration'],
            'custom_lesson_duration' => (string) ($data['custom_lesson_duration'] ?? Setting::getValue('custom_lesson_duration', '50')),
            'reminder_before' => $data['reminder_before'],
            'min_cancel_hours' => (string) ($data['min_cancel_hours'] ?? '12'),
            'buffer_minutes' => $data['buffer_minutes'],
            'allow_auto_booking' => $request->boolean('allow_auto_booking'),
            'allow_reschedule' => $request->boolean('allow_reschedule'),
            'allow_makeup' => $request->boolean('allow_makeup'),
            'prevent_scheduling_conflicts' => $request->boolean('prevent_scheduling_conflicts'),
        ]);
    }

    private function updateCurriculum(Request $request): void
    {
        $custom = Setting::json('curriculum_custom');
        $enabledCustom = $request->input('custom_enabled', []);

        foreach ($custom as $i => $item) {
            $custom[$i]['enabled'] = in_array((string) $i, array_map('strval', (array) $enabledCustom), true) ? '1' : '0';
        }

        if ($request->filled('new_curriculum_name')) {
            $name = trim((string) $request->input('new_curriculum_name'));
            if ($name !== '') {
                $custom[] = ['name' => mb_substr($name, 0, 80), 'enabled' => '1'];
            }
        }

        Setting::setMany([
            'curriculum_quran' => $request->boolean('curriculum_quran'),
            'curriculum_tajweed' => $request->boolean('curriculum_tajweed'),
            'curriculum_aqidah' => $request->boolean('curriculum_aqidah'),
            'curriculum_fiqh' => $request->boolean('curriculum_fiqh'),
            'curriculum_custom' => $custom,
        ]);
    }

    private function updateQuran(Request $request): void
    {
        $request->validate([
            'hifz_method' => ['required', Rule::in(['ayah_range', 'page', 'juz'])],
            'review_method' => ['required', Rule::in(['ayah_range', 'page', 'juz'])],
        ]);

        Setting::setMany([
            'hifz_method' => $request->input('hifz_method'),
            'review_method' => $request->input('review_method'),
            'smart_revision' => $request->boolean('smart_revision'),
            'enable_hifz_grade' => $request->boolean('enable_hifz_grade'),
            'enable_tilawa_grade' => $request->boolean('enable_tilawa_grade'),
            'enable_tajweed_grade' => $request->boolean('enable_tajweed_grade'),
            'enable_error_tracking' => $request->boolean('enable_error_tracking'),
            'show_last_hifz' => $request->boolean('show_last_hifz'),
            'show_last_review' => $request->boolean('show_last_review'),
            'show_mastery_percent' => $request->boolean('show_mastery_percent'),
            'show_error_count' => $request->boolean('show_error_count'),
            'show_review_count' => $request->boolean('show_review_count'),
        ]);
    }

    private function updateAi(Request $request): void
    {
        $data = $request->validate([
            'openai_key' => ['nullable', 'string', 'max:200'],
            'openai_model' => ['nullable', 'string', 'max:80'],
            'ai_plan_style' => ['required', Rule::in(['short', 'medium', 'detailed'])],
            'ai_intervention' => ['required', Rule::in(['low', 'medium', 'high'])],
        ]);

        $values = [
            'openai_model' => $data['openai_model'] ?: 'gpt-4o-mini',
            'ai_plan_style' => $data['ai_plan_style'],
            'ai_intervention' => $data['ai_intervention'],
            'ai_enabled' => $request->boolean('ai_enabled'),
            'ai_lesson_plan' => $request->boolean('ai_lesson_plan'),
            'ai_activities' => $request->boolean('ai_activities'),
            'ai_homework' => $request->boolean('ai_homework'),
            'ai_analyze' => $request->boolean('ai_analyze'),
            'ai_revision_plan' => $request->boolean('ai_revision_plan'),
            'ai_summarize' => $request->boolean('ai_summarize'),
            'ai_student_report' => $request->boolean('ai_student_report'),
            'ai_weak_points' => $request->boolean('ai_weak_points'),
            'ai_next_lesson' => $request->boolean('ai_next_lesson'),
            'ai_use_student_data' => $request->boolean('ai_use_student_data'),
        ];

        $key = trim((string) ($data['openai_key'] ?? ''));
        if ($key !== '') {
            $values['openai_key'] = $key;
        }

        Setting::setMany($values);
    }

    private function updateNotifications(Request $request): void
    {
        Setting::setMany([
            'notify_lesson_reminder' => $request->boolean('notify_lesson_reminder'),
            'notify_lesson_confirm' => $request->boolean('notify_lesson_confirm'),
            'notify_lesson_cancel' => $request->boolean('notify_lesson_cancel'),
            'notify_lesson_reschedule' => $request->boolean('notify_lesson_reschedule'),
            'notify_student_absent' => $request->boolean('notify_student_absent'),
            'notify_student_drop' => $request->boolean('notify_student_drop'),
            'notify_hifz_drop' => $request->boolean('notify_hifz_drop'),
            'notify_homework_incomplete' => $request->boolean('notify_homework_incomplete'),
            'follow_up_enabled' => $request->boolean('follow_up_enabled'),
            'follow_up_idle_days' => (string) max(1, min(60, $request->integer('follow_up_idle_days', 8))),
            'notify_weekly_report' => $request->boolean('notify_weekly_report'),
            'notify_monthly_report' => $request->boolean('notify_monthly_report'),
            'notify_channel_inapp' => $request->boolean('notify_channel_inapp'),
            'notify_channel_email' => $request->boolean('notify_channel_email'),
            'notify_channel_push' => $request->boolean('notify_channel_push'),
        ]);
    }

    private function updateParents(Request $request): void
    {
        Setting::setMany([
            'parents_enabled' => $request->boolean('parents_enabled'),
            'parent_send_summary' => $request->boolean('parent_send_summary'),
            'parent_send_homework' => $request->boolean('parent_send_homework'),
            'parent_send_grade' => $request->boolean('parent_send_grade'),
            'parent_send_progress' => $request->boolean('parent_send_progress'),
            'parent_notify_absent' => $request->boolean('parent_notify_absent'),
            'parent_notify_next' => $request->boolean('parent_notify_next'),
            'parent_see_history' => $request->boolean('parent_see_history'),
            'parent_see_hifz' => $request->boolean('parent_see_hifz'),
            'parent_see_notes' => $request->boolean('parent_see_notes'),
        ]);
    }

    private function updateReports(Request $request): void
    {
        Setting::setMany([
            'report_student' => $request->boolean('report_student'),
            'report_hifz' => $request->boolean('report_hifz'),
            'report_review' => $request->boolean('report_review'),
            'report_attendance' => $request->boolean('report_attendance'),
            'report_weekly' => $request->boolean('report_weekly'),
            'report_monthly' => $request->boolean('report_monthly'),
            'report_auto' => $request->boolean('report_auto'),
            'report_send_parent' => $request->boolean('report_send_parent'),
            'report_show_progress' => $request->boolean('report_show_progress'),
            'report_show_strengths' => $request->boolean('report_show_strengths'),
            'report_show_weaknesses' => $request->boolean('report_show_weaknesses'),
            'report_show_teacher_tips' => $request->boolean('report_show_teacher_tips'),
            'report_show_ai_tips' => $request->boolean('report_show_ai_tips'),
        ]);
    }

    private function updateAppearance(Request $request): void
    {
        $data = $request->validate([
            'theme' => ['required', Rule::in(['light', 'dark', 'system'])],
            'ui_language' => ['required', Rule::in(['ar', 'en'])],
            'ui_direction' => ['required', Rule::in(['rtl', 'ltr'])],
            'font_size' => ['required', Rule::in(['small', 'medium', 'large'])],
            'density' => ['required', Rule::in(['comfortable', 'compact'])],
        ]);

        Setting::setMany($data);
    }

    private function updatePrivacy(Request $request): void
    {
        Setting::setValue('ai_use_student_data', $request->boolean('ai_use_student_data'));
    }
}
