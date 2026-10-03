@extends('layouts.app')

@section('title', 'الطلاب')
@section('heading', 'ملفات الطلاب')
@section('subheading', 'كل بيانات الطالب في مكان واحد')

@section('content')
<div class="toolbar">
    <form class="search-form" method="get">
        <input class="search" name="q" value="{{ $search }}" placeholder="بحث عن طالب...">
    </form>
    <a class="btn btn-primary" href="{{ route('students.create') }}">إضافة طالب</a>
</div>
<div class="content-grid">
    <div class="list">
        @if ($students->isEmpty() && $search !== '')
            <x-empty-state
                class="card"
                title="لا توجد نتائج"
                description="لم يُعثر على طالب يطابق «{{ $search }}»."
                :action="route('students.index')"
                action-label="عرض كل الطلاب"
            />
        @elseif ($students->isEmpty())
            <x-empty-state
                class="card"
                title="لا يوجد طلاب بعد"
                description="أضيفي أول ملف طالب لتبدأ متابعة الحفظ والتسميع."
                :action="route('students.create')"
                action-label="إضافة طالب"
            />
        @else
            @foreach ($students as $item)
                <div class="list-item {{ $selected && $selected->id === $item->id ? 'is-selected' : '' }}">
                    <a class="student-cell plain-link" href="{{ route('students.index', ['id' => $item->id, 'q' => $search]) }}">
                        <div class="s-avatar" style="background:{{ $item->color }}">{{ $item->initials() }}</div>
                        <div>
                            <b>{{ $item->name }}</b>
                            <div class="muted">{{ $item->level }} · {{ $item->requirement() }}</div>
                        </div>
                    </a>
                    <div class="row-actions">
                        <a class="btn btn-ghost" href="{{ route('students.index', ['id' => $item->id, 'q' => $search]) }}">الملف</a>
                        <a class="btn btn-ghost" href="{{ route('students.edit', $item) }}">تعديل</a>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    @if ($students->isNotEmpty())
        <div>
            @include('partials.student-card', [
                'student' => $selected,
                'compact' => true,
                'latestAppointment' => $latestAppointment,
            ])
            <div class="card mt-12 file-timeline">
                <h3>سجل الحصص</h3>
                @if (! $selected || $selected->lessons->isEmpty())
                    <x-empty-state
                        title="لا توجد حصص محفوظة"
                        description="ستظهر هنا بعد إنهاء أول حصة لهذا الطالب."
                        :action="route('dashboard')"
                        action-label="لوحة اليوم"
                    />
                @else
                    @foreach ($selected->lessons as $lesson)
                        <div class="history-item">
                            <b>{{ $lesson->session_date?->locale('ar')->translatedFormat('j F Y') }} {{ substr((string) $lesson->session_time, 0, 5) }}</b>
                            @if ($lesson->ai_summary)
                                <div>{{ $lesson->ai_summary }}</div>
                            @endif
                            @if ($lesson->notes)
                                <div class="note-box mt-12">{{ $lesson->notes }}</div>
                            @endif
                            <p class="mt-12">
                                <a class="btn btn-ghost" href="{{ route('lessons.summary', $lesson) }}">عرض الملخص</a>
                            </p>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
