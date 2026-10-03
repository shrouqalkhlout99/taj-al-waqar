@php
    use App\Support\Quran;
@endphp
@extends('layouts.app')

@section('title', 'التقارير')
@section('heading', 'التقارير')
@section('subheading', 'صورة سريعة عن الحلقة')

@section('content')
<div class="reports-page">
    <div class="toolbar no-print">
        <form class="search-form report-period" method="get" action="{{ route('reports.index') }}">
            @if (request()->filled('student'))
                <input type="hidden" name="student" value="{{ request('student') }}">
            @endif
            <div class="form-grid range-grid">
                <div class="field">
                    <label for="report-from">من</label>
                    <input id="report-from" type="date" name="from" value="{{ $periodFrom }}">
                </div>
                <div class="field">
                    <label for="report-to">إلى</label>
                    <input id="report-to" type="date" name="to" value="{{ $periodTo }}">
                </div>
                <div class="field">
                    <label>&nbsp;</label>
                    <button class="btn btn-ghost" type="submit">تصفية الفترة</button>
                </div>
            </div>
        </form>
        <button type="button" class="btn btn-primary" onclick="window.print()">طباعة / PDF</button>
        <a class="btn btn-ghost" href="{{ route('settings.index', ['tab' => 'reports']) }}">إعدادات التقارير</a>
    </div>
    <div class="progress-grid reports-grid">
        @if ($showStudent)
            <div class="card"><small>عدد الطلاب</small><b class="stat-number">{{ $studentsCount }}</b></div>
            <div class="card"><small>الحصص المسجّلة</small><b class="stat-number">{{ $lessonsCount }}</b></div>
        @endif
        @if ($showAttendance)
            <div class="card"><small>حصص اليوم</small><b class="stat-number">{{ $todayCount }}</b></div>
            <div class="card"><small>المنتهية اليوم</small><b class="stat-number">{{ $todayDone }}</b></div>
        @endif
    </div>

    @if ($showProgress)
        <section class="session-section session-section-today">
            <h3 class="session-section-title">متوسط تقدم الحلقة</h3>
            <div class="level-bars">
                @foreach ($progressAverages as $label => $value)
                    <div class="level-bar">
                        <div class="level-bar-head">
                            <small>{{ $label }}</small>
                            <b>{{ $value }}%</b>
                        </div>
                        <div class="meter"><span style="width: {{ $value }}%"></span></div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if ($showHifz)
        <section class="session-section session-section-today">
            <h3 class="session-section-title">تقرير الحفظ</h3>
            @if ($hifzStudents->isEmpty())
                <x-empty-state title="لا توجد مواضع حفظ بعد" description="ستظهر هنا مواضع الحفظ الحالية من ملفات الطلاب." />
            @else
                <div class="summary-list">
                    @foreach ($hifzStudents as $student)
                        <div>{{ $student->name }}: {{ Quran::rangeText($student->new_mem) }}</div>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    @if ($showReview)
        <section class="session-section session-section-today">
            <h3 class="session-section-title">تقرير المراجعة</h3>
            @if ($reviewStudents->isEmpty())
                <x-empty-state title="لا توجد مواضع مراجعة بعد" description="ستظهر هنا نطاقات المراجعة الحالية من ملفات الطلاب." />
            @else
                <div class="summary-list">
                    @foreach ($reviewStudents as $student)
                        <div>{{ $student->name }}: {{ Quran::rangeText($student->review) }}</div>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    <section class="session-section">
        <h3 class="session-section-title">تقرير الطالب</h3>
        @if ($studentFilterMissing)
            <x-empty-state title="لا يوجد طالب بهذا المعرف" description="تحققي من رابط التقرير أو افتحي صفحة التقارير كاملة." />
        @elseif ($reportStudents->isEmpty())
            <x-empty-state
                title="لا يوجد طلاب بعد"
                description="أضيفي ملف طالب أولًا ليظهر تقريره هنا."
                :action="route('students.create')"
                action-label="إضافة طالب"
            />
        @else
            <div class="report-student-list">
                @foreach ($reportStudents as $student)
                    @include('reports.partials.student-card', ['student' => $student])
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection

@if ($autoPrint)
@push('scripts')
<script>window.addEventListener('load', () => window.print());</script>
@endpush
@endif
