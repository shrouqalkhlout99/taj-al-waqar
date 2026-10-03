@php
    use App\Support\Quran;
@endphp
@extends('layouts.app')

@section('title', 'لوحة اليوم')

@section('content')
<div class="daily-board">
    <div class="daily-actions">
        <a class="btn btn-primary" href="{{ route('appointments.create') }}">إضافة موعد</a>
        <a class="btn btn-ghost" href="{{ route('students.create') }}">إضافة طالب</a>
    </div>

    @if ($appointments->isEmpty())
        <div class="card">
            <h3>جدول اليوم</h3>
            <x-empty-state
                title="لا توجد حصص اليوم"
                description="أضيفي موعداً من صفحة المواعيد ليظهر هنا جدول اليوم."
                :action="route('appointments.create')"
                action-label="إضافة موعد"
            />
        </div>
    @else
        @if ($nextAppointment && $nextStudent)
            <article class="card next-lesson-card">
                <p class="section-label">الحصة القادمة</p>
                <div class="next-lesson-head">
                    <div class="s-avatar" style="background:{{ $nextStudent->color }}">{{ $nextStudent->initials() }}</div>
                    <div class="next-lesson-identity">
                        <h3>{{ $nextStudent->name }}</h3>
                        <p class="muted">{{ $nextAppointment->timeLabel() }} · {{ $nextAppointment->statusLabel() }}</p>
                    </div>
                    <a class="btn btn-primary btn-start" href="{{ route('lessons.start', $nextAppointment) }}">ابدئي الحصة</a>
                </div>
                @if ($nextPlan && ($nextPlan['has_ranges'] ?? false))
                    <div class="progress-grid next-lesson-plan">
                        <div><small>آخر حفظ</small><b>{{ $nextPlan['last_hifz'] }}</b></div>
                        <div><small>تسميع مقترح</small><b>{{ Quran::rangeText($nextPlan['today_recitation']) }}</b></div>
                        <div><small>مراجعة مقترحة</small><b>{{ Quran::rangeText($nextPlan['today_review']) }}</b></div>
                    </div>
                @endif
            </article>
        @endif

        <details class="card today-lessons" open>
            <summary>
                <h3>جدول اليوم</h3>
            </summary>
            <div class="list today-lesson-list">
                @foreach ($appointments as $appointment)
                    @php $student = $appointment->student; @endphp
                    @if (! $student) @continue @endif
                    <div class="list-item {{ $nextStudent && $nextStudent->id === $student->id && $appointment->id === $nextAppointment?->id ? 'is-selected' : '' }}">
                        <div class="today-lesson-main">
                            <span class="today-lesson-time">{{ $appointment->timeLabel() }}</span>
                            <a class="student-cell plain-link" href="{{ route('students.index', ['id' => $student->id]) }}">
                                <div class="s-avatar" style="background:{{ $student->color }}">{{ $student->initials() }}</div>
                                {{ $student->name }}
                            </a>
                            <span class="badge {{ $appointment->status }}">{{ $appointment->statusLabel() }}</span>
                        </div>
                        <div class="row-actions">
                            @if ($appointment->canStart())
                                <a class="btn btn-primary btn-start" href="{{ route('lessons.start', $appointment) }}">ابدئي الحصة</a>
                            @else
                                <a class="btn btn-ghost" href="{{ route('students.index', ['id' => $student->id]) }}">السجل</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </details>
    @endif

    @if ($followUps->isNotEmpty())
        <section class="card follow-up-card">
            <h3>يحتاج متابعة</h3>
            <div class="list">
                @foreach ($followUps as $item)
                    <a class="list-item follow-up-item plain-link" href="{{ $item['href'] }}">
                        <div>
                            <b>{{ $item['student_name'] }}</b>
                            <div class="muted">{{ $item['message'] }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
