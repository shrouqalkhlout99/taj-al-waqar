<form class="card settings-card" method="post" action="{{ route('settings.update') }}" data-settings-form>
    @csrf
    <input type="hidden" name="section" value="calendar">
    <h3>المواعيد والتقويم</h3>
    <p class="muted mb-14">تُستخدم هذه القيم عند إنشاء موعد جديد، وعند منع تعارض الحصص.</p>

    <div class="form-grid">
        <div class="field">
            <label for="cal_timezone">المنطقة الزمنية</label>
            <select id="cal_timezone" name="teacher_timezone" required>
                @foreach ($timezones as $tz => $label)
                    <option value="{{ $tz }}" @selected(old('teacher_timezone', $s['teacher_timezone']) === $tz)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="cal_duration">مدة الحصة الافتراضية</label>
            <select id="cal_duration" name="default_lesson_duration">
                <option value="30" @selected(old('default_lesson_duration', $s['default_lesson_duration']) === '30')>30 دقيقة</option>
                <option value="45" @selected(old('default_lesson_duration', $s['default_lesson_duration']) === '45')>45 دقيقة</option>
                <option value="60" @selected(old('default_lesson_duration', $s['default_lesson_duration']) === '60')>60 دقيقة</option>
                <option value="custom" @selected(old('default_lesson_duration', $s['default_lesson_duration']) === 'custom')>مخصصة</option>
            </select>
        </div>
        <div class="field">
            <label for="cal_custom">مدة مخصصة (دقيقة)</label>
            <input id="cal_custom" type="number" min="5" max="180" name="custom_lesson_duration" value="{{ old('custom_lesson_duration', $s['custom_lesson_duration']) }}">
        </div>
        <div class="field">
            <label for="min_cancel_hours">الحد الأدنى للإلغاء (ساعة)</label>
            <input id="min_cancel_hours" type="number" min="0" max="72" name="min_cancel_hours" value="{{ old('min_cancel_hours', $s['min_cancel_hours']) }}">
        </div>
    </div>

    <p class="section-title">التذكير قبل الحصة</p>
    <div class="choice-row">
        @foreach (['15' => 'قبل 15 دقيقة', '30' => 'قبل 30 دقيقة', '60' => 'قبل ساعة', '1440' => 'قبل يوم'] as $value => $label)
            <label class="choice-chip">
                <input type="radio" name="reminder_before" value="{{ $value }}" @checked(old('reminder_before', $s['reminder_before']) === $value)>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>

    <x-setting-row title="السماح بالحجز التلقائي" description="سيُفعَّل لاحقاً عندما يتوفر حجز من ولي الأمر. يُحفظ التفضيل الآن فقط.">
        <x-setting-toggle name="allow_auto_booking" :checked="$s['allow_auto_booking']" title="الحجز التلقائي" />
    </x-setting-row>
    <x-setting-row title="السماح بإعادة جدولة الحصة" description="يظهر خيار تعديل الموعد في صفحة المواعيد.">
        <x-setting-toggle name="allow_reschedule" :checked="$s['allow_reschedule']" title="إعادة الجدولة" />
    </x-setting-row>
    <x-setting-row title="السماح بحصص التعويض" description="تذكير لكِ بإمكانية تعويض حصة ملغاة. لا يحذف المواعيد القديمة.">
        <x-setting-toggle name="allow_makeup" :checked="$s['allow_makeup']" title="حصص التعويض" />
    </x-setting-row>
    <x-setting-row title="منع تعارض المواعيد" description="إذا فُعّل، لن يُحفظ موعد جديد في وقت تتداخل فيه حصة أخرى.">
        <x-setting-toggle name="prevent_scheduling_conflicts" :checked="$s['prevent_scheduling_conflicts']" title="منع التعارض" />
    </x-setting-row>

    <p class="section-title">فاصل بين الحصص</p>
    <div class="choice-row">
        @foreach (['0' => 'بدون فاصل', '5' => '5 دقائق', '10' => '10 دقائق', '15' => '15 دقيقة'] as $value => $label)
            <label class="choice-chip">
                <input type="radio" name="buffer_minutes" value="{{ $value }}" @checked(old('buffer_minutes', $s['buffer_minutes']) === $value)>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>
    <p class="muted">إذا كان الطالب في دولة أخرى، يُعرض الوقت حسب المنطقة الزمنية المحفوظة هنا. إضافة منطقة زمنية لكل طالب ستأتي لاحقاً.</p>

    @include('settings.partials.save-bar')
</form>
