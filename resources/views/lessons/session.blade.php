@php
    use App\Support\Quran;
@endphp
@extends('layouts.app')

@section('title', 'الحصة الجارية')
@section('heading', 'الحصة الجارية: '.$student->name)
@section('subheading', Quran::arabicDate().' · '.$appointment->timeLabel())

@section('content')
<div class="card session-context">
    <div class="session-context-head">
        <div class="s-avatar" style="background:{{ $student->color }}">{{ $student->initials() }}</div>
        <div class="session-context-identity">
            <h3>{{ $student->name }}</h3>
            <div class="session-context-meta">
                @if ($student->phone)
                    <a class="plain-link" href="tel:{{ $student->phone }}">{{ $student->phone }}</a>
                @endif
                <span>آخر موضع: {{ $student->current_surah }} {{ $student->last_ayah }}</span>
            </div>
        </div>
    </div>
    <p class="section-label">ملاحظة خاصة</p>
    <div class="note-box">
        {{ $student->last_note ?: 'لا توجد ملاحظة خاصة.' }}
        @if ($student->last_note_date)
            <br><small>{{ $student->last_note_date->locale('ar')->translatedFormat('j F Y') }}</small>
        @endif
    </div>
    <section class="session-section session-section-homework">
        <h3 class="session-section-title">الواجب السابق</h3>
        @if ($plan['has_ranges'] ?? false)
            <div class="summary-list">
                <div>التسميع: {{ Quran::rangeText($plan['today_recitation']) }}</div>
                <div>الحفظ: {{ Quran::rangeText($plan['today_new_mem']) }}</div>
                <div>المراجعة: {{ Quran::rangeText($plan['today_review']) }}</div>
            </div>
        @else
            <p class="muted">لا يوجد واجب سابق ظاهر في ملف الطالب.</p>
        @endif
    </section>
</div>

<form class="card form-card session-card" method="post" action="{{ route('lessons.finish', $appointment) }}">
    @csrf
    <section class="session-section session-section-today">
        <h3 class="session-section-title">ما أُنجز اليوم</h3>
        <p class="section-title">التسميع</p>
        @include('partials.range-fields', ['prefix' => 'recitation', 'range' => $plan['today_recitation'] ?? $student->recitation])
        @if (\App\Models\Setting::bool('enable_tilawa_grade', true))
            @include('partials.grade-fields', ['name' => 'recitation_grade', 'value' => 'good'])
        @endif

        <p class="section-title">الحفظ الجديد</p>
        @include('partials.range-fields', ['prefix' => 'new_mem', 'range' => $plan['today_new_mem'] ?? $student->new_mem])
        @if (\App\Models\Setting::bool('enable_hifz_grade', true))
            @include('partials.grade-fields', ['name' => 'new_mem_grade', 'value' => 'good'])
        @endif

        <p class="section-title">المراجعة</p>
        @include('partials.range-fields', ['prefix' => 'review', 'range' => $plan['today_review'] ?? $student->review])

        <div class="field full mt-12">
            <label>ملاحظتي على الأداء</label>
            <textarea name="notes" placeholder="اكتبي ملاحظة سريعة...">{{ old('notes') }}</textarea>
            <p class="field-hint">هذه الملاحظة تخص الحصة الحالية فقط.</p>
        </div>
    </section>

    <section class="session-section session-section-next">
        <h3 class="session-section-title">خطة الحصة القادمة</h3>
        <p class="field-hint">قيم مقترحة من ملف الطالب، ويمكن تعديلها قبل الحفظ.</p>
        <p class="section-title">تسميع قادم</p>
        @include('partials.range-fields', ['prefix' => 'next_recitation', 'range' => $plan['next_recitation'] ?? $student->new_mem])
        <p class="section-title">حفظ قادم</p>
        @include('partials.range-fields', ['prefix' => 'next_new_mem', 'range' => $plan['next_new_mem'] ?? $nextNewMem])
        <p class="section-title">مراجعة قادمة</p>
        @include('partials.range-fields', ['prefix' => 'next_review', 'range' => $plan['next_review'] ?? $student->review])
    </section>

    <div class="session-actions">
        <p class="session-actions-hint">إغلاق بدون حفظ يخرج من الجلسة دون حفظ النتائج، ولا يلغي الموعد.</p>
        <div class="session-actions-buttons">
            <button class="btn btn-danger" form="abort-lesson" title="يخرج من الجلسة دون حفظ، ولا يلغي الموعد">إغلاق بدون حفظ</button>
            <button class="btn btn-success" type="submit">إنهاء الحصة وحفظ</button>
        </div>
    </div>
</form>
<form id="abort-lesson" method="post" action="{{ route('lessons.abort', $appointment) }}">
    @csrf
</form>
@endsection
