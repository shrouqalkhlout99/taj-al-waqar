<form class="card settings-card" method="post" action="{{ route('settings.update') }}" data-settings-form>
    @csrf
    <input type="hidden" name="section" value="quran">
    <h3>القرآن والحفظ</h3>
    <p class="muted mb-14">طريقة التسجيل في الحصص، وما يظهر في ملف الطالب.</p>

    <div class="form-grid">
        <div class="field">
            <label for="hifz_method">طريقة تسجيل الحفظ</label>
            <select id="hifz_method" name="hifz_method">
                <option value="ayah_range" @selected(old('hifz_method', $s['hifz_method']) === 'ayah_range')>نطاق آيات (المستخدم حالياً)</option>
                <option value="page" @selected(old('hifz_method', $s['hifz_method']) === 'page')>بالصفحة — لاحقاً</option>
                <option value="juz" @selected(old('hifz_method', $s['hifz_method']) === 'juz')>بالجزء — لاحقاً</option>
            </select>
        </div>
        <div class="field">
            <label for="review_method">طريقة تسجيل المراجعة</label>
            <select id="review_method" name="review_method">
                <option value="ayah_range" @selected(old('review_method', $s['review_method']) === 'ayah_range')>نطاق آيات (المستخدم حالياً)</option>
                <option value="page" @selected(old('review_method', $s['review_method']) === 'page')>بالصفحة — لاحقاً</option>
                <option value="juz" @selected(old('review_method', $s['review_method']) === 'juz')>بالجزء — لاحقاً</option>
            </select>
        </div>
    </div>

    <x-setting-row title="المراجعة الذكية" description="يقترح النظام السور التي تحتاج مراجعة من درجات الحصص السابقة. التحليل الكامل سيُوسَّع لاحقاً.">
        <x-setting-toggle name="smart_revision" :checked="$s['smart_revision']" title="المراجعة الذكية" />
    </x-setting-row>
    <x-setting-row title="تقييم الحفظ" description="إظهار تقدير الحفظ الجديد في الحصة.">
        <x-setting-toggle name="enable_hifz_grade" :checked="$s['enable_hifz_grade']" title="تقييم الحفظ" />
    </x-setting-row>
    <x-setting-row title="تقييم التلاوة" description="إظهار تقدير التسميع في الحصة.">
        <x-setting-toggle name="enable_tilawa_grade" :checked="$s['enable_tilawa_grade']" title="تقييم التلاوة" />
    </x-setting-row>
    <x-setting-row title="تقييم التجويد" description="يُحفظ التفضيل؛ درجة التجويد المنفصلة ستُضاف لاحقاً دون حذف السجلات.">
        <x-setting-toggle name="enable_tajweed_grade" :checked="$s['enable_tajweed_grade']" title="تقييم التجويد" />
    </x-setting-row>
    <x-setting-row title="تتبع الأخطاء" description="يُستخدم مع ملاحظات الحصة. سجل أخطاء مفصّل سيأتي لاحقاً.">
        <x-setting-toggle name="enable_error_tracking" :checked="$s['enable_error_tracking']" title="تتبع الأخطاء" />
    </x-setting-row>

    <p class="section-title">ما يظهر في تقدم الطالب</p>
    <x-setting-row title="آخر موضع حفظ" description="آخر سورة وآية وصل إليها في الحفظ.">
        <x-setting-toggle name="show_last_hifz" :checked="$s['show_last_hifz']" title="آخر حفظ" />
    </x-setting-row>
    <x-setting-row title="آخر موضع مراجعة" description="نطاق المراجعة الحالي في ملف الطالب.">
        <x-setting-toggle name="show_last_review" :checked="$s['show_last_review']" title="آخر مراجعة" />
    </x-setting-row>
    <x-setting-row title="نسبة الإتقان" description="درجة القرآن المحسوبة من تقدمه.">
        <x-setting-toggle name="show_mastery_percent" :checked="$s['show_mastery_percent']" title="نسبة الإتقان" />
    </x-setting-row>
    <x-setting-row title="عدد الأخطاء" description="سيظهر عند توفر سجل الأخطاء.">
        <x-setting-toggle name="show_error_count" :checked="$s['show_error_count']" title="عدد الأخطاء" />
    </x-setting-row>
    <x-setting-row title="عدد مرات المراجعة" description="سيُحسب من سجل الحصص لاحقاً.">
        <x-setting-toggle name="show_review_count" :checked="$s['show_review_count']" title="عدد المراجعات" />
    </x-setting-row>

    @include('settings.partials.save-bar')
</form>
