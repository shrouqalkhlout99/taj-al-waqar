@extends('layouts.app')

@section('title', 'الملاحظات')
@section('heading', 'الملاحظات')
@section('subheading', 'ملاحظة خاصة وآخر ملاحظات الحصص في مكان واحد')

@section('content')
@if ($students->isEmpty())
    <x-empty-state
        class="card"
        title="لا يوجد طلاب بعد"
        description="أضيفي ملف طالب أولًا، ثم ستظهر هنا الملاحظة الخاصة وملاحظات الحصص."
        :action="route('students.create')"
        action-label="إضافة طالب"
    />
@elseif ($studentsWithNotes->isEmpty())
    <x-empty-state
        class="card"
        title="لا توجد ملاحظات بعد"
        description="ستظهر الملاحظة الخاصة بعد كتابتها في ملف الطالب، أو بعد إنهاء حصة فيها ملاحظة."
        :action="route('students.index')"
        action-label="ملفات الطلاب"
    />
@else
    <div class="list">
        @foreach ($studentsWithNotes as $student)
            @php
                $lessonNotes = $student->lessons->filter(fn ($lesson) => filled($lesson->notes))->take(3);
                $latestLesson = $student->lessons->first();
            @endphp
            <div class="card">
                <h3>{{ $student->name }}</h3>
                <p class="section-label">ملاحظة خاصة</p>
                <div class="note-box">
                    {{ $student->last_note ?: 'لا توجد ملاحظة متابعة.' }}
                    @if ($student->last_note_date)
                        <br><small>{{ $student->last_note_date->locale('ar')->translatedFormat('j F Y') }}</small>
                    @endif
                </div>
                @if ($lessonNotes->isNotEmpty())
                    <p class="section-label mt-14">ملاحظات الحصص</p>
                    @foreach ($lessonNotes as $lesson)
                        <div class="history-item">
                            <b>{{ $lesson->session_date?->locale('ar')->translatedFormat('j F Y') }}</b>
                            <div>{{ $lesson->notes }}</div>
                        </div>
                    @endforeach
                @endif
                <div class="row-actions mt-14">
                    <a class="btn btn-ghost" href="{{ route('students.index', ['id' => $student->id]) }}">الملف</a>
                    <a class="btn btn-ghost" href="{{ route('students.edit', $student) }}">تعديل</a>
                    @if ($latestLesson)
                        <a class="btn btn-ghost" href="{{ route('lessons.summary', $latestLesson) }}">الملخص</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
