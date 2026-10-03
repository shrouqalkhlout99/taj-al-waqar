@php
    $firstName = explode(' ', trim($teacherName))[0] ?? $teacherName;
@endphp
<div class="lp-app">
    <div class="lp-app-top">
        <div>
            <h3>{{ $greeting }}، {{ $firstName }}</h3>
            <p>{{ $arabicDate }}</p>
        </div>
        <span class="lp-badge">لوحة اليوم</span>
    </div>
    <div class="lp-stats">
        <div class="lp-stat"><b>{{ $studentCount }}</b><span>طالبًا</span></div>
        <div class="lp-stat"><b>{{ $todayCount }}</b><span>حصص اليوم</span></div>
        <div class="lp-stat">
            <b>{{ $averageScore !== null ? $averageScore.'%' : '—' }}</b>
            <span>متوسط التقدم</span>
        </div>
    </div>
    <table class="lp-mini-table">
        <thead>
            <tr><th>الوقت</th><th>الطالب</th><th>المطلوب</th><th>الحالة</th></tr>
        </thead>
        <tbody>
        @forelse ($todayAppointments as $appointment)
            @php $student = $appointment->student; @endphp
            @if (! $student) @continue @endif
            <tr>
                <td>{{ $appointment->timeLabel() }}</td>
                <td>{{ $student->name }}</td>
                <td>{{ $student->requirement() }}</td>
                <td><span class="lp-badge {{ $appointment->status }}">{{ $appointment->statusLabel() }}</span></td>
            </tr>
        @empty
            <tr><td colspan="4">لا توجد حصص اليوم في هذا الجهاز بعد.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
