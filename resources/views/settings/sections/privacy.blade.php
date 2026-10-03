<form class="card settings-card" method="post" action="{{ route('settings.update') }}" data-settings-form>
    @csrf
    <input type="hidden" name="section" value="privacy">
    <h3>الخصوصية والأمان</h3>
    <p class="muted mb-14">تاج الوقار منصة محلية على جهازك. لا يوجد تسجيل دخول بعد، لذلك لا توجد جلسات متعددة أو مصادقة ثنائية.</p>

    <div class="note-box mb-14">
        بيانات الطلاب والحصص تُحفظ في ملف SQLite داخل المشروع. لا تُرفع إلى خادم خارجي إلا إذا أدخلتِ مفتاح OpenAI وفعّلتِ إرسال بيانات الطالب.
    </div>

    <x-setting-row title="استخدام بيانات الطالب لتحسين اقتراحات الذكاء" description="إن أُغلق، يشرح المساعد الآية دون إرسال الاسم أو الملاحظات إلى OpenAI.">
        <x-setting-toggle name="ai_use_student_data" :checked="$s['ai_use_student_data']" title="بيانات الطالب للذكاء" />
    </x-setting-row>

    @include('settings.partials.save-bar')
</form>

<div class="card settings-card mt-14">
    <h3>الجلسات النشطة</h3>
    <div class="session-card-item">
        <div>
            <strong>هذا الجهاز</strong>
            <div class="muted">المتصفح الحالي · الجلسة الوحيدة</div>
        </div>
        <span class="badge upcoming">الحالية</span>
    </div>
    <p class="muted mt-12">تسجيل الخروج من الأجهزة الأخرى سيظهر بعد إضافة الحسابات.</p>
    <div class="row-actions mt-12">
        <button type="button" class="btn btn-outline is-disabled" disabled title="غير متاح بدون تسجيل دخول">تسجيل الخروج من هذا الجهاز</button>
        <button type="button" class="btn btn-outline is-disabled" disabled title="غير متاح بدون تسجيل دخول">تسجيل الخروج من جميع الأجهزة</button>
    </div>
</div>
