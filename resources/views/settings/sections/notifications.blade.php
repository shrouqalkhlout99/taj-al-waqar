<form class="card settings-card" method="post" action="{{ route('settings.update') }}" data-settings-form>
    @csrf
    <input type="hidden" name="section" value="notifications">
    <h3>الإشعارات</h3>
    <p class="muted mb-14">اختاري ما تريدين تذكيرك به. التنبيه داخل النظام يعمل عبر رسائل النجاح والتحذير الحالية. البريد وPush سيُربطان لاحقاً.</p>

    <p class="section-title">الحصص</p>
    <x-setting-row title="تذكير الحصة" description="تذكير قبل الموعد حسب الوقت المختار في التقويم.">
        <x-setting-toggle name="notify_lesson_reminder" :checked="$s['notify_lesson_reminder']" title="تذكير الحصة" />
    </x-setting-row>
    <x-setting-row title="تأكيد الحصة" description="عند بدء الحصة أو إنهائها.">
        <x-setting-toggle name="notify_lesson_confirm" :checked="$s['notify_lesson_confirm']" title="تأكيد الحصة" />
    </x-setting-row>
    <x-setting-row title="إلغاء الحصة" description="عند إلغاء موعد من صفحة المواعيد.">
        <x-setting-toggle name="notify_lesson_cancel" :checked="$s['notify_lesson_cancel']" title="إلغاء الحصة" />
    </x-setting-row>
    <x-setting-row title="تغيير موعد الحصة" description="عند تعديل التاريخ أو الساعة.">
        <x-setting-toggle name="notify_lesson_reschedule" :checked="$s['notify_lesson_reschedule']" title="تغيير الموعد" />
    </x-setting-row>

    <p class="section-title">الطلاب</p>
    <x-setting-row title="غياب الطالب" description="سيُربط عند تسجيل الحضور لاحقاً.">
        <x-setting-toggle name="notify_student_absent" :checked="$s['notify_student_absent']" title="الغياب" />
    </x-setting-row>
    <x-setting-row title="انخفاض مستوى الطالب" description="عند تراجع درجات الحصص الأخيرة.">
        <x-setting-toggle name="notify_student_drop" :checked="$s['notify_student_drop']" title="انخفاض المستوى" />
    </x-setting-row>
    <x-setting-row title="تراجع الحفظ" description="إذا تكرر تقدير «يحتاج مراجعة».">
        <x-setting-toggle name="notify_hifz_drop" :checked="$s['notify_hifz_drop']" title="تراجع الحفظ" />
    </x-setting-row>
    <x-setting-row title="واجب غير مكتمل" description="يبقى تفضيلاً محفوظاً. لا يوجد عمود حالة واجب بعد، لذلك لا يُنشئ تنبيهاً من لوحة المتابعة.">
        <x-setting-toggle name="notify_homework_incomplete" :checked="$s['notify_homework_incomplete']" title="واجب غير مكتمل" />
    </x-setting-row>
    <x-setting-row title="قائمة يحتاج متابعة" description="بنود محايدة في لوحة اليوم من المواعيد غير الموثّقة وانقطاع الحصص.">
        <x-setting-toggle name="follow_up_enabled" :checked="$s['follow_up_enabled']" title="يحتاج متابعة" />
    </x-setting-row>
    <x-setting-row title="أيام الانقطاع" description="بعد كم يوماً بدون حصة مسجّلة يظهر البند. الافتراضي 8." for="follow_up_idle_days">
        <input id="follow_up_idle_days" class="setting-number" type="number" name="follow_up_idle_days" min="1" max="60" value="{{ old('follow_up_idle_days', $s['follow_up_idle_days']) }}">
    </x-setting-row>

    <p class="section-title">التقارير</p>
    <x-setting-row title="تقرير أسبوعي" description="تفضيل لإنشاء ملخص الأسبوع لاحقاً.">
        <x-setting-toggle name="notify_weekly_report" :checked="$s['notify_weekly_report']" title="تقرير أسبوعي" />
    </x-setting-row>
    <x-setting-row title="تقرير شهري" description="تفضيل لملخص الشهر.">
        <x-setting-toggle name="notify_monthly_report" :checked="$s['notify_monthly_report']" title="تقرير شهري" />
    </x-setting-row>

    <p class="section-title">طرق الإشعار</p>
    <x-setting-row title="داخل النظام" description="رسائل أسفل الشاشة كما هي الآن.">
        <x-setting-toggle name="notify_channel_inapp" :checked="$s['notify_channel_inapp']" title="داخل النظام" />
    </x-setting-row>
    <x-setting-row title="البريد الإلكتروني" description="غير مربوط بعد. يُحفظ التفضيل فقط.">
        <x-setting-toggle name="notify_channel_email" :checked="$s['notify_channel_email']" title="البريد" />
    </x-setting-row>
    <x-setting-row title="إشعار الجهاز" description="غير مربوط بعد. يُحفظ التفضيل فقط.">
        <x-setting-toggle name="notify_channel_push" :checked="$s['notify_channel_push']" title="Push" />
    </x-setting-row>

    @include('settings.partials.save-bar')
</form>
