@php
    use App\Support\Quran;
    $student = $lesson->student;
    $hasNextPlan = collect([$lesson->next_recitation, $lesson->next_new_mem, $lesson->next_review])
        ->contains(fn ($range) => filled(data_get($range, 'surah')));
@endphp
@extends('layouts.app')

@section('title', 'ملخص الحصة')
@section('heading', 'ملخص الحصة: '.$student?->name)
@section('subheading', $lesson->session_date?->locale('ar')->translatedFormat('l، j F Y'))

@section('content')
<div class="card form-card summary-card">
    <div class="summary-ai">
        <p class="section-label">ملخص الذكاء الاصطناعي</p>
        @if (filled($lesson->ai_summary))
            <div class="ai-box">{{ $lesson->ai_summary }}</div>
        @else
            <p class="muted">لا يوجد ملخص آلي لهذه الحصة.</p>
        @endif
    </div>

    <section class="session-section session-section-today">
        <h3 class="session-section-title">ما تم اليوم</h3>
        <div class="summary-list">
            <div>
                التسميع: {{ $lesson->recitationText() }}
                @if ($lesson->recitation_grade)
                    <span class="badge {{ $lesson->recitation_grade }}">{{ Quran::gradeLabel($lesson->recitation_grade) }}</span>
                @endif
            </div>
            <div>
                الحفظ: {{ $lesson->newMemText() }}
                @if ($lesson->new_mem_grade)
                    <span class="badge {{ $lesson->new_mem_grade }}">{{ Quran::gradeLabel($lesson->new_mem_grade) }}</span>
                @endif
            </div>
            <div>المراجعة: {{ $lesson->reviewText() }}</div>
            @if (filled($lesson->notes))
                <div>الملاحظات: {{ $lesson->notes }}</div>
            @endif
        </div>
    </section>

    <section class="session-section session-section-next">
        <h3 class="session-section-title">خطة الحصة القادمة</h3>
        @if ($hasNextPlan)
            <div class="summary-list">
                <div>تسميع قادم: {{ Quran::rangeText($lesson->next_recitation) }}</div>
                <div>حفظ قادم: {{ Quran::rangeText($lesson->next_new_mem) }}</div>
                <div>مراجعة قادمة: {{ Quran::rangeText($lesson->next_review) }}</div>
            </div>
        @else
            <x-empty-state title="لا توجد خطة للحصة القادمة" />
        @endif
    </section>

    @if ($parentShare)
        @include('partials.parent-lesson-share', ['parentShare' => $parentShare])
    @elseif (filled($parentDraft ?? null))
        <section class="parent-share" role="region" aria-label="صياغة رسالة ولي الأمر">
            <p class="parent-share-title">صياغة رسالة ولي الأمر</p>
            <p class="muted mb-10">نص للمعاينة والنسخ. لا يُرسل تلقائياً، ولا يتضمن الملاحظة الخاصة.</p>
            <textarea id="parent-draft-text" class="compose-text" readonly>{{ $parentDraft }}</textarea>
            <div class="parent-share-actions">
                <button type="button" class="btn btn-ghost" data-copy-from="parent-draft-text">نسخ</button>
            </div>
        </section>
    @endif

    <div class="modal-actions">
        <a class="btn btn-ghost" href="{{ route('students.index', ['id' => $student?->id]) }}">ملف الطالب</a>
        <a class="btn btn-primary" href="{{ route('dashboard') }}">لوحة اليوم</a>
    </div>
</div>
@endsection
