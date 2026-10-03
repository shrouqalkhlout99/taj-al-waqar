<form class="card settings-card" method="post" action="{{ route('settings.update') }}" data-settings-form>
    @csrf
    <input type="hidden" name="section" value="parents">
    <h3>أولياء الأمور</h3>
    <p class="muted mb-14">لا توجد بوابة لأولياء الأمور بعد. هذه الخيارات تُحفظ لتُطبَّق عند إضافتها، ولن تظهر الملاحظات الداخلية إلا إذا سمحتِ بذلك صراحة.</p>

    <x-setting-row title="تفعيل حساب ولي الأمر" description="عند توفر الحسابات سيُستخدم هذا المفتاح. لا يُنشئ حسابات الآن.">
        <x-setting-toggle name="parents_enabled" :checked="$s['parents_enabled']" title="حساب ولي الأمر" />
    </x-setting-row>

    <p class="section-title">ما يُرسل لولي الأمر</p>
    <x-setting-row title="إرسال ملخص الحصة" description="ملخص الأداء بعد إنهاء الحصة.">
        <x-setting-toggle name="parent_send_summary" :checked="$s['parent_send_summary']" title="ملخص الحصة" />
    </x-setting-row>
    <x-setting-row title="إرسال الواجب" description="الواجب المكتوب في الحصة.">
        <x-setting-toggle name="parent_send_homework" :checked="$s['parent_send_homework']" title="الواجب" />
    </x-setting-row>
    <x-setting-row title="إرسال تقييم الطالب" description="تقدير التسميع والحفظ.">
        <x-setting-toggle name="parent_send_grade" :checked="$s['parent_send_grade']" title="التقييم" />
    </x-setting-row>
    <x-setting-row title="إرسال نسبة التقدم" description="درجات القرآن والتقدم العام.">
        <x-setting-toggle name="parent_send_progress" :checked="$s['parent_send_progress']" title="التقدم" />
    </x-setting-row>
    <x-setting-row title="إشعار الغياب" description="عند تسجيل غياب لاحقاً.">
        <x-setting-toggle name="parent_notify_absent" :checked="$s['parent_notify_absent']" title="الغياب" />
    </x-setting-row>
    <x-setting-row title="إشعار الموعد القادم" description="تذكير بموعد الحصة التالية.">
        <x-setting-toggle name="parent_notify_next" :checked="$s['parent_notify_next']" title="الموعد القادم" />
    </x-setting-row>

    <p class="section-title">الخصوصية</p>
    <x-setting-row title="السماح برؤية سجل الطالب" description="سجل الحصص السابقة.">
        <x-setting-toggle name="parent_see_history" :checked="$s['parent_see_history']" title="سجل الطالب" />
    </x-setting-row>
    <x-setting-row title="السماح برؤية مستوى الحفظ" description="آخر موضع ونسب الإتقان.">
        <x-setting-toggle name="parent_see_hifz" :checked="$s['parent_see_hifz']" title="الحفظ" />
    </x-setting-row>
    <x-setting-row title="السماح برؤية الملاحظات" description="ملاحظاتك الداخلية لا تُشارك إلا إذا فعّلتِ هذا الخيار.">
        <x-setting-toggle name="parent_see_notes" :checked="$s['parent_see_notes']" title="الملاحظات" />
    </x-setting-row>

    @include('settings.partials.save-bar')
</form>
