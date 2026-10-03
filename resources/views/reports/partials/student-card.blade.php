@php
    use App\Support\Quran;
    $aiSummary = $latestSummaries->get($student->id);
    $lessonNote = $latestLessonNotes->get($student->id);
@endphp
<article class="card report-student-card">
    <div class="report-student-head">
        <div class="report-student-identity">
            <div class="s-avatar" style="background:{{ $student->color }}">{{ $student->initials() }}</div>
            <div>
                <h3>{{ $student->name }}</h3>
                <p class="muted">تسميع: {{ Quran::rangeText($student->recitation) }}</p>
            </div>
        </div>
        <a class="btn btn-ghost no-print" href="{{ route('students.index', ['id' => $student->id]) }}">ملف الطالب</a>
    </div>

    <section class="session-section session-section-today">
        <h3 class="session-section-title">الموضع الحالي</h3>
        <div class="summary-list">
            <div>الحفظ: {{ Quran::rangeText($student->new_mem) }}</div>
            <div>المراجعة: {{ Quran::rangeText($student->review) }}</div>
        </div>
    </section>

    <section class="session-section session-section-next">
        <h3 class="session-section-title">خطة الحصة القادمة</h3>
        <div class="summary-list">
            <div>تسميع قادم: {{ Quran::rangeText($student->recitation) }}</div>
            <div>حفظ قادم: {{ Quran::rangeText($student->new_mem) }}</div>
            <div>مراجعة قادمة: {{ Quran::rangeText($student->review) }}</div>
        </div>
    </section>

    @if ($showProgress)
        @include('partials.level-bars', ['student' => $student])
    @endif

    @if ($showTeacherTips)
        <p class="section-title">توصيات المعلمة</p>
        <div class="note-box">
            {{ filled($lessonNote?->notes) ? $lessonNote->notes : 'لا توجد ملاحظة من الحصة.' }}
            @if ($lessonNote?->session_date)
                <br><small>{{ $lessonNote->session_date->locale('ar')->translatedFormat('j F Y') }}</small>
            @endif
        </div>
    @endif

    @if ($showAiTips)
        <p class="section-title">توصيات الذكاء الاصطناعي</p>
        @if (filled($aiSummary?->ai_summary))
            <div class="ai-box">{{ $aiSummary->ai_summary }}</div>
        @else
            <p class="muted">لا يوجد ملخص آلي محفوظ لهذا الطالب.</p>
        @endif
    @endif
</article>
