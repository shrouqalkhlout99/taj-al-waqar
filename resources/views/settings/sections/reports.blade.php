<form class="card settings-card" method="post" action="{{ route('settings.update') }}" data-settings-form>
    @csrf
    <input type="hidden" name="section" value="reports">
    <h3>التقارير</h3>
    <p class="muted mb-14">اختاري ما يظهر في صفحة التقارير. الطباعة تستخدم تقرير الصفحة الحالية.</p>

    <p class="section-title">أنواع التقارير</p>
    <x-setting-row title="تقرير الطالب" description="عدد الطلاب في الحلقة.">
        <x-setting-toggle name="report_student" :checked="$s['report_student']" title="تقرير الطالب" />
    </x-setting-row>
    <x-setting-row title="تقرير الحفظ" description="سيُوسَّع من مواضع الحفظ الحالية.">
        <x-setting-toggle name="report_hifz" :checked="$s['report_hifz']" title="الحفظ" />
    </x-setting-row>
    <x-setting-row title="تقرير المراجعة" description="سيُوسَّع من نطاق المراجعة.">
        <x-setting-toggle name="report_review" :checked="$s['report_review']" title="المراجعة" />
    </x-setting-row>
    <x-setting-row title="تقرير الحضور" description="حصص اليوم والمنتهية.">
        <x-setting-toggle name="report_attendance" :checked="$s['report_attendance']" title="الحضور" />
    </x-setting-row>
    <x-setting-row title="التقرير الأسبوعي" description="سيُبنى لاحقاً من سجل الحصص.">
        <x-setting-toggle name="report_weekly" :checked="$s['report_weekly']" title="أسبوعي" />
    </x-setting-row>
    <x-setting-row title="التقرير الشهري" description="سيُبنى لاحقاً من سجل الحصص.">
        <x-setting-toggle name="report_monthly" :checked="$s['report_monthly']" title="شهري" />
    </x-setting-row>

    <p class="section-title">خيارات العرض</p>
    <x-setting-row title="إنشاء التقرير تلقائياً" description="تفضيل محفوظ — الجدولة غير مفعلة بعد.">
        <x-setting-toggle name="report_auto" :checked="$s['report_auto']" title="تلقائي" />
    </x-setting-row>
    <x-setting-row title="إرسال التقرير لولي الأمر" description="يحتاج حساب ولي الأمر لاحقاً.">
        <x-setting-toggle name="report_send_parent" :checked="$s['report_send_parent']" title="إرسال لولي الأمر" />
    </x-setting-row>
    <x-setting-row title="إظهار نسبة التقدم" description="درجات القرآن والتجويد والتقدم العام.">
        <x-setting-toggle name="report_show_progress" :checked="$s['report_show_progress']" title="نسبة التقدم" />
    </x-setting-row>
    <x-setting-row title="إظهار نقاط القوة" description="سيُوسَّع لاحقاً من تقييم الحصص.">
        <x-setting-toggle name="report_show_strengths" :checked="$s['report_show_strengths']" title="نقاط القوة" />
    </x-setting-row>
    <x-setting-row title="إظهار نقاط الضعف" description="سيُوسَّع لاحقاً من تقييم الحصص.">
        <x-setting-toggle name="report_show_weaknesses" :checked="$s['report_show_weaknesses']" title="نقاط الضعف" />
    </x-setting-row>
    <x-setting-row title="إظهار توصيات المعلمة" description="ملاحظات الحصة (lessons.notes) فقط — الملاحظة الخاصة في الملف لا تُطبع.">
        <x-setting-toggle name="report_show_teacher_tips" :checked="$s['report_show_teacher_tips']" title="توصيات المعلمة" />
    </x-setting-row>
    <x-setting-row title="إظهار توصيات الذكاء الاصطناعي" description="إن كان المساعد مفعّلاً.">
        <x-setting-toggle name="report_show_ai_tips" :checked="$s['report_show_ai_tips']" title="توصيات الذكاء" />
    </x-setting-row>

    <div class="row-actions mt-12">
        <a class="btn btn-ghost" href="{{ route('reports.index') }}">فتح التقارير</a>
        <a class="btn btn-outline" href="{{ route('reports.index') }}?print=1">طباعة / PDF</a>
    </div>

    @include('settings.partials.save-bar')
</form>
