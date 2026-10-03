@php
    use App\Support\Quran;
@endphp
@if (! $student)
    @if ($emptySchedule ?? false)
        <div class="card">
            <x-empty-state
                title="لا يوجد طالب محدد"
                description="أضيفي موعداً لليوم ليظهر هنا ملف الطالب ورابط الحصة."
                :action="route('appointments.create')"
                action-label="إضافة موعد"
            />
        </div>
    @else
        <div class="card">
            <x-empty-state
                title="لا يوجد طالب محدد"
                description="اختاري طالباً من القائمة لعرض ملفه وسجل حصصه."
                :action="route('students.create')"
                action-label="إضافة طالب"
            />
        </div>
    @endif
@else
    <div class="card student-file">
        <div class="profile-head">
            <div class="s-avatar" style="background:{{ $student->color }}">{{ $student->initials() }}</div>
            <h3>{{ $student->name }}</h3>
            <div class="level">{{ $student->level }} · {{ $student->style }}</div>
            @if ($latestAppointment ?? null)
                <p class="muted">آخر موعد: {{ $latestAppointment->statusLabel() }} · {{ $latestAppointment->scheduled_date?->locale('ar')->translatedFormat('j F') }} {{ $latestAppointment->timeLabel() }}</p>
            @endif
        </div>

        <details class="file-block" open>
            <summary>البيانات الأساسية</summary>
            @if ($student->phone)
                <p class="profile-phone"><a class="plain-link" href="tel:{{ $student->phone }}">{{ $student->phone }}</a></p>
            @endif
            @if ($student->parentContact)
                <p class="profile-parent">
                    <small>ولي الأمر{{ $student->parentContact->relation ? ' · '.$student->parentContact->relation : '' }}</small>
                    <b>{{ $student->parentContact->name }}</b>
                    @if ($student->parentContact->phone)
                        <a class="plain-link" href="tel:{{ $student->parentContact->phone }}">{{ $student->parentContact->phone }}</a>
                    @endif
                </p>
            @endif
            <div class="meet-box">
                <input readonly value="{{ $student->meet_link }}" placeholder="لا يوجد رابط Meet بعد">
                <button type="button" class="btn btn-ghost" data-copy="{{ $student->meet_link }}">نسخ</button>
                @if ($student->meet_link)
                    <a class="btn btn-primary" href="{{ $student->meet_link }}" target="_blank" rel="noopener">دخول</a>
                @endif
            </div>
        </details>

        <details class="file-block" open>
            <summary>الحفظ</summary>
            <div class="progress-grid">
                @if (\App\Models\Setting::bool('show_last_hifz', true))
                    <div><small>آخر موضع</small><b>{{ $student->current_surah }} {{ $student->last_ayah }}</b></div>
                    <div><small>حفظ جديد</small><b>{{ Quran::rangeText($student->new_mem) }}</b></div>
                @endif
                @if (\App\Models\Setting::bool('show_mastery_percent', true))
                    <div><small>نسبة الإتقان</small><b>{{ $student->quran_score }}%</b></div>
                @endif
            </div>
        </details>

        <details class="file-block" open>
            <summary>التسميع والمراجعة</summary>
            <div class="progress-grid">
                <div><small>التسميع</small><b>{{ Quran::rangeText($student->recitation) }}</b></div>
                @if (\App\Models\Setting::bool('show_last_review', true))
                    <div><small>المراجعة</small><b>{{ Quran::rangeText($student->review) }}</b></div>
                @endif
            </div>
        </details>

        <details class="file-block" open>
            <summary>ملاحظة خاصة</summary>
            <p class="field-hint">للمعلمة فقط — لا تُدرج في تقرير المشاركة.</p>
            <div class="note-box">
                {{ $student->last_note ?: 'لا توجد ملاحظات بعد.' }}
                @if ($student->last_note_date)
                    <br><small>{{ $student->last_note_date->locale('ar')->translatedFormat('j F Y') }}</small>
                @endif
            </div>
        </details>

        @unless ($compact ?? false)
            <p class="mt-14">
                <a class="btn btn-ghost" href="{{ route('students.index', ['id' => $student->id]) }}">سجل الحصص</a>
            </p>
        @endunless
    </div>
@endif
