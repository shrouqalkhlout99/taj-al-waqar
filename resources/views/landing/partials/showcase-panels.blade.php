@php
    use App\Support\Quran;
@endphp
<div class="lp-tabs" role="tablist" aria-label="شاشات النظام">
    <button type="button" class="lp-tab is-active" data-tab="dashboard" role="tab" aria-selected="true">لوحة التحكم</button>
    <button type="button" class="lp-tab" data-tab="students" role="tab" aria-selected="false">الطلاب</button>
    <button type="button" class="lp-tab" data-tab="lessons" role="tab" aria-selected="false">الحصص</button>
    <button type="button" class="lp-tab" data-tab="session" role="tab" aria-selected="false">سجل الحصة</button>
    <button type="button" class="lp-tab" data-tab="reports" role="tab" aria-selected="false">التقارير</button>
</div>

<div class="lp-preview-frame">
    <div class="lp-panel is-active" data-panel="dashboard">
        @include('landing.partials.app-preview')
    </div>

    <div class="lp-panel" data-panel="students">
        <div class="lp-app">
            <div class="lp-app-top">
                <div>
                    <h3>ملفات الطلاب</h3>
                    <p>الاسم، المستوى، آخر موضع، والحفظ والمراجعة</p>
                </div>
            </div>
            @forelse ($previewStudents as $item)
                <div class="lp-student-row">
                    <div style="display:flex;gap:10px;align-items:center">
                        <div class="lp-avatar" style="background:{{ $item->color ?: '#388E3C' }}">{{ $item->initials() }}</div>
                        <div>
                            <b>{{ $item->name }}</b>
                            <div class="lp-lead" style="font-size:13px">{{ $item->level }} · {{ $item->requirement() }}</div>
                        </div>
                    </div>
                    <span class="lp-badge">الملف</span>
                </div>
            @empty
                <p class="lp-empty">لا يوجد طلاب بعد. أضيفي أول ملف من صفحة الطلاب.</p>
            @endforelse

            @if ($previewStudent)
                <div class="lp-profile-grid">
                    <div><small>اسم الطالب</small><b>{{ $previewStudent->name }}</b></div>
                    <div><small>المستوى</small><b>{{ $previewStudent->level ?: '—' }}</b></div>
                    <div><small>آخر موضع</small><b>{{ $previewStudent->current_surah }} {{ $previewStudent->last_ayah }}</b></div>
                    <div><small>الحفظ</small><b>{{ Quran::rangeText($previewStudent->new_mem) }}</b></div>
                    <div><small>المراجعة</small><b>{{ Quran::rangeText($previewStudent->review) }}</b></div>
                    <div><small>التسميع</small><b>{{ Quran::rangeText($previewStudent->recitation) }}</b></div>
                </div>
                <p class="lp-lead" style="margin-top:12px">آخر ملاحظة: {{ $previewStudent->last_note ?: 'لا توجد ملاحظات بعد.' }}</p>
            @endif
        </div>
    </div>

    <div class="lp-panel" data-panel="lessons">
        <div class="lp-app">
            <div class="lp-app-top">
                <div>
                    <h3>الحصص السابقة</h3>
                    <p>كل حصة تُحفظ في ملف الطالب</p>
                </div>
            </div>
            @forelse ($recentLessons as $lesson)
                <div class="lp-lesson-row">
                    <div>
                        <b>{{ $lesson->student?->name }}</b>
                        <div class="lp-lead" style="font-size:13px">
                            {{ $lesson->session_date?->locale('ar')->translatedFormat('j F Y') }}
                            {{ substr((string) $lesson->session_time, 0, 5) }}
                        </div>
                        <p>{{ $lesson->ai_summary ?: ($lesson->notes ?: $lesson->recitationText()) }}</p>
                    </div>
                    <span class="lp-badge done">منتهية</span>
                </div>
            @empty
                <p class="lp-empty">بعد إنهاء أول حصة ستظهر هنا تلقائياً.</p>
            @endforelse
        </div>
    </div>

    <div class="lp-panel" data-panel="session">
        <div class="lp-app">
            <div class="lp-app-top">
                <div>
                    <h3>سجل الحصة</h3>
                    <p>تسميع · حفظ جديد · مراجعة · المطلوب القادم</p>
                </div>
            </div>
            @if ($previewStudent)
                <div class="lp-profile-grid">
                    <div><small>التسميع</small><b>{{ Quran::rangeText($previewStudent->recitation) }}</b></div>
                    <div><small>الحفظ الجديد</small><b>{{ Quran::rangeText($previewStudent->new_mem) }}</b></div>
                    <div><small>المراجعة</small><b>{{ Quran::rangeText($previewStudent->review) }}</b></div>
                    <div><small>الملاحظة</small><b>{{ $previewStudent->last_note ?: '—' }}</b></div>
                </div>
            @else
                <p class="lp-empty">يظهر سجل الحصة بعد إضافة طالب وبدء موعد.</p>
            @endif
        </div>
    </div>

    <div class="lp-panel" data-panel="reports">
        <div class="lp-app">
            <div class="lp-app-top">
                <div>
                    <h3>التقارير</h3>
                    <p>صورة سريعة عن الحلقة من بيانات هذا الجهاز</p>
                </div>
            </div>
            <div class="lp-stats">
                <div class="lp-stat"><b>{{ $studentCount }}</b><span>عدد الطلاب</span></div>
                <div class="lp-stat"><b>{{ $lessonsCount }}</b><span>الحصص المسجّلة</span></div>
                <div class="lp-stat"><b>{{ $todayDone }}</b><span>المنتهية اليوم</span></div>
            </div>
            <div class="lp-stats">
                <div class="lp-stat"><b>{{ $todayCount }}</b><span>حصص اليوم</span></div>
                <div class="lp-stat"><b>{{ $todayRemaining }}</b><span>المتبقي اليوم</span></div>
                <div class="lp-stat"><b>{{ $averageScore !== null ? $averageScore.'%' : '—' }}</b><span>متوسط التقدم</span></div>
            </div>
        </div>
    </div>
</div>
