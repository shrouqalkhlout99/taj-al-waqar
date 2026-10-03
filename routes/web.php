<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/privacy', [LandingController::class, 'privacy'])->name('landing.privacy');
Route::get('/terms', [LandingController::class, 'terms'])->name('landing.terms');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:login');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/{appointment}/edit', [AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');

    Route::get('/lessons', [LessonController::class, 'index'])->name('lessons.index');
    Route::get('/lessons/{appointment}/start', [LessonController::class, 'start'])->name('lessons.start');
    Route::post('/lessons/{appointment}/finish', [LessonController::class, 'finish'])->name('lessons.finish');
    Route::post('/lessons/{appointment}/abort', [LessonController::class, 'abort'])->name('lessons.abort');
    Route::get('/lessons/{lesson}/summary', [LessonController::class, 'summary'])->name('lessons.summary');

    Route::get('/notes', NoteController::class)->name('notes.index');

    Route::get('/book', [AssistantController::class, 'book'])->name('book.index');
    Route::get('/assistant', [AssistantController::class, 'index'])->name('assistant.index');
    Route::post('/assistant/explain', [AssistantController::class, 'explain'])->name('assistant.explain');
    Route::post('/assistant/compose', [AssistantController::class, 'compose'])->name('assistant.compose');

    Route::get('/reports', ReportController::class)->name('reports.index');

    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/settings/password', [AuthController::class, 'editPassword'])->name('password.edit');
    Route::put('/settings/password', [AuthController::class, 'updatePassword'])->middleware('throttle:6,1')->name('password.update');
    Route::post('/settings/backup', [SettingController::class, 'backup'])->name('settings.backup');
    Route::get('/settings/backup/{file}/download', [SettingController::class, 'downloadBackup'])->name('settings.backup.download');
    Route::post('/settings/export', [SettingController::class, 'export'])->name('settings.export');
    Route::post('/settings/restore', [SettingController::class, 'restore'])->name('settings.restore');
    Route::post('/settings/reset-ai', [SettingController::class, 'resetAi'])->name('settings.reset-ai');
    Route::post('/settings/clear-profile', [SettingController::class, 'clearProfile'])->name('settings.clear-profile');
});
