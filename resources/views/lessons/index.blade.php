@php
    use App\Support\Quran;
@endphp
@extends('layouts.app')

@section('title', 'الحصص')
@section('heading', 'الحصص السابقة')
@section('subheading', 'كل حصة تُحفظ تلقائياً في ملف الطالب')

@section('content')
<div class="card">
    @if ($lessons->isEmpty())
        <x-empty-state
            title="لا توجد حصص محفوظة بعد"
            description="ابدئي حصة من لوحة اليوم أو من صفحة المواعيد، وستظهر هنا تلقائياً بعد إنهائها."
            :action="route('appointments.index')"
            action-label="الانتقال إلى المواعيد"
        />
    @else
        <div class="table-wrap">
            <table>
                    <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>الطالب</th>
                        <th>التسميع</th>
                        <th>الملخص</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lessons as $lesson)
                        <tr>
                            @php
                                $gradeKey = $lesson->recitation_grade;
                                $hasGrade = is_string($gradeKey) && array_key_exists($gradeKey, config('quran.grades'));
                            @endphp
                            <td>{{ $lesson->session_date?->locale('ar')->translatedFormat('j F Y') }} {{ substr((string) $lesson->session_time, 0, 5) }}</td>
                            <td>{{ $lesson->student?->name }}</td>
                            <td>
                                <span class="badge {{ $hasGrade ? $gradeKey : 'neutral' }}">
                                    {{ $hasGrade ? Quran::gradeLabel($gradeKey) : 'بدون تقدير' }}
                                </span>
                            </td>
                            <td>{{ $lesson->ai_summary ?: $lesson->notes ?: '—' }}</td>
                            <td class="col-actions">
                                <a class="btn btn-ghost" href="{{ route('lessons.summary', $lesson) }}">عرض</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
