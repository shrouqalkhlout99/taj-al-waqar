<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Setting;
use App\Support\LessonReminder;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('ar');

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(Str::transliterate(
                Str::lower($request->string('email')).'|'.$request->ip()
            ));
        });

        View::composer(['layouts.app', 'layouts.auth'], function ($view) {
            $todayTotal = 0;
            $todayDone = 0;
            $todayRemaining = 0;
            $teacherName = 'معلمة قرآن';
            $appName = config('app.name', 'تاج الوقار');
            $teacherPhoto = null;
            $teacherWhatsapp = null;
            $teacherEmail = null;
            $teacherCountry = null;
            $theme = 'light';
            $fontSize = 'medium';
            $density = 'comfortable';
            $uiLanguage = 'ar';
            $uiDirection = 'rtl';

            try {
                $today = Appointment::onDate()->get();
                $todayTotal = $today->count();
                $todayDone = $today->where('status', 'done')->count();
                $todayRemaining = $today->whereNotIn('status', ['done', 'cancelled'])->count();
                $teacherName = Setting::displayName();
                $teacherPhoto = Setting::mediaUrl(Setting::getValue('teacher_photo'));
                $teacherWhatsapp = Setting::getValue('teacher_whatsapp');
                $teacherEmail = Setting::getValue('teacher_email');
                $teacherCountry = Setting::getValue('teacher_country');
                $theme = (string) Setting::getValue('theme', 'light');
                $fontSize = (string) Setting::getValue('font_size', 'medium');
                $density = (string) Setting::getValue('density', 'comfortable');
                $uiLanguage = (string) Setting::getValue('ui_language', 'ar');
                $uiDirection = (string) Setting::getValue('ui_direction', 'rtl');
            } catch (Throwable) {
                // Database may not be migrated yet.
            }

            $view->with(compact(
                'todayTotal',
                'todayDone',
                'todayRemaining',
                'teacherName',
                'appName',
                'teacherPhoto',
                'teacherWhatsapp',
                'teacherEmail',
                'teacherCountry',
                'theme',
                'fontSize',
                'density',
                'uiLanguage',
                'uiDirection',
            ));
        });

        View::composer('layouts.app', function ($view) {
            $lessonReminders = collect();

            try {
                if (auth()->check()) {
                    $lessonReminders = LessonReminder::dueAppointments();
                }
            } catch (Throwable) {
                // Database may not be migrated yet.
            }

            $view->with('lessonReminders', $lessonReminders);
        });
    }
}
