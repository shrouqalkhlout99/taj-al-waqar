@php
    $days = [
        'sunday' => 'الأحد',
        'monday' => 'الاثنين',
        'tuesday' => 'الثلاثاء',
        'wednesday' => 'الأربعاء',
        'thursday' => 'الخميس',
        'friday' => 'الجمعة',
        'saturday' => 'السبت',
    ];
    $working = old('working_days', \App\Models\Setting::json('working_days'));
@endphp
<form class="card settings-card" method="post" action="{{ route('settings.update') }}" data-settings-form>
    @csrf
    <input type="hidden" name="section" value="teacher">
    <h3>إعدادات المعلمة</h3>
    <p class="muted mb-14">كيف تظهرين للطلاب، وطبيعة حصصك وأوقات عملك.</p>

    <div class="form-grid">
        <div class="field">
            <label for="teacher_display_name">الاسم الذي يظهر للطلاب</label>
            <input id="teacher_display_name" name="teacher_display_name" maxlength="80" value="{{ old('teacher_display_name', $s['teacher_display_name']) }}" placeholder="{{ $s['teacher_name'] }}">
        </div>
        <div class="field">
            <label for="teacher_specialty">التخصص</label>
            <input id="teacher_specialty" name="teacher_specialty" maxlength="120" value="{{ old('teacher_specialty', $s['teacher_specialty']) }}" placeholder="تحفيظ · تجويد">
        </div>
        <div class="field">
            <label for="teacher_experience_years">سنوات الخبرة</label>
            <input id="teacher_experience_years" type="number" min="0" max="60" name="teacher_experience_years" value="{{ old('teacher_experience_years', $s['teacher_experience_years']) }}">
        </div>
        <div class="field">
            <label for="teacher_qiraah">رواية القرآن</label>
            <input id="teacher_qiraah" name="teacher_qiraah" maxlength="80" value="{{ old('teacher_qiraah', $s['teacher_qiraah']) }}">
        </div>
        <div class="field full">
            <label for="teacher_bio">نبذة قصيرة</label>
            <textarea id="teacher_bio" name="teacher_bio" maxlength="500">{{ old('teacher_bio', $s['teacher_bio']) }}</textarea>
        </div>
        <div class="field full">
            <label for="teacher_qualifications">المؤهلات</label>
            <textarea id="teacher_qualifications" name="teacher_qualifications" maxlength="400">{{ old('teacher_qualifications', $s['teacher_qualifications']) }}</textarea>
        </div>
        <div class="field full">
            <label for="teacher_certificates">الشهادات</label>
            <textarea id="teacher_certificates" name="teacher_certificates" maxlength="400">{{ old('teacher_certificates', $s['teacher_certificates']) }}</textarea>
        </div>
    </div>

    <p class="section-title">نوع الحصص</p>
    <div class="choice-row">
        @foreach (['individual' => 'فردية', 'group' => 'جماعية', 'both' => 'كلاهما'] as $value => $label)
            <label class="choice-chip">
                <input type="radio" name="lesson_type" value="{{ $value }}" @checked(old('lesson_type', $s['lesson_type']) === $value)>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>

    <p class="section-title">مدة الحصة الافتراضية</p>
    <div class="choice-row">
        @foreach (['30' => '30 دقيقة', '45' => '45 دقيقة', '60' => '60 دقيقة', 'custom' => 'مخصصة'] as $value => $label)
            <label class="choice-chip">
                <input type="radio" name="default_lesson_duration" value="{{ $value }}" @checked(old('default_lesson_duration', $s['default_lesson_duration']) === $value)>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>
    <div class="form-grid mt-12">
        <div class="field">
            <label for="custom_lesson_duration">المدة المخصصة (دقيقة)</label>
            <input id="custom_lesson_duration" type="number" min="5" max="180" name="custom_lesson_duration" value="{{ old('custom_lesson_duration', $s['custom_lesson_duration']) }}">
        </div>
        <div class="field">
            <label for="max_students_per_group">الحد الأقصى في المجموعة</label>
            <input id="max_students_per_group" type="number" min="2" max="40" name="max_students_per_group" value="{{ old('max_students_per_group', $s['max_students_per_group']) }}">
        </div>
    </div>

    <p class="section-title">أيام العمل</p>
    <div class="days-grid">
        @foreach ($days as $key => $label)
            <label class="check-label">
                <input type="checkbox" name="working_days[]" value="{{ $key }}" @checked(in_array($key, $working, true))>
                {{ $label }}
            </label>
        @endforeach
    </div>

    <div class="form-grid mt-12">
        <div class="field">
            <label for="work_start">بداية الدوام</label>
            <input id="work_start" type="time" name="work_start" required value="{{ old('work_start', $s['work_start']) }}">
        </div>
        <div class="field">
            <label for="work_end">نهاية الدوام</label>
            <input id="work_end" type="time" name="work_end" required value="{{ old('work_end', $s['work_end']) }}">
        </div>
        <div class="field">
            <label for="break_duration">مدة الاستراحة (دقيقة)</label>
            <input id="break_duration" type="number" min="0" max="60" name="break_duration" value="{{ old('break_duration', $s['break_duration']) }}">
        </div>
    </div>

    @include('settings.partials.save-bar', ['saveLabel' => 'حفظ إعدادات المعلمة'])
</form>
