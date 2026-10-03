<form class="card settings-card" method="post" action="{{ route('settings.update') }}" data-settings-form>
    @csrf
    <input type="hidden" name="section" value="ai">
    <h3>مساعد تاج الوقار الذكي</h3>
    <p class="muted mb-14">مساعد ذكي يساعد المعلمة في التخطيط للحصص، تحليل مستوى الطلاب، إنشاء الواجبات واقتراح خطط المراجعة.</p>

    @if (! $aiAvailable)
        <div class="note-box mb-14" role="status">
            المساعد الذكي غير متاح حالياً — لا يوجد مفتاح OpenAI أو تم إيقافه. الشرح المحلي يعمل بدون إنترنت.
            <a class="plain-link" href="{{ route('assistant.index') }}"><u>جرّبي المساعد</u></a>
        </div>
    @endif

    <x-setting-row title="تفعيل المساعد الذكي" description="عند الإيقاف يبقى الشرح المحلي الجاهز، ولن تُرسل طلبات إلى OpenAI.">
        <x-setting-toggle name="ai_enabled" :checked="$s['ai_enabled']" title="تفعيل المساعد" />
    </x-setting-row>

    <div class="form-grid">
        <div class="field">
            <label for="openai_key">مفتاح OpenAI (اختياري)</label>
            <input id="openai_key" name="openai_key" type="password" autocomplete="new-password" value="{{ old('openai_key') }}" placeholder="{{ $hasStoredOpenAiKey ? 'مفتاح محفوظ — اتركيه فارغاً للإبقاء عليه' : 'sk-...' }}">
        </div>
        <div class="field">
            <label for="openai_model">النموذج</label>
            <input id="openai_model" name="openai_model" value="{{ old('openai_model', $s['openai_model']) }}">
        </div>
    </div>

    <p class="section-title">الميزات</p>
    <x-setting-row title="إنشاء خطة الحصة" description="يُستخدم لصياغة اقتراح الحصة في المساعد عند توفر المفتاح، مع صياغة محلية دائماً.">
        <x-setting-toggle name="ai_lesson_plan" :checked="$s['ai_lesson_plan']" title="خطة الحصة" />
    </x-setting-row>
    <x-setting-row title="اقتراح الأنشطة" description="تفضيل محفوظ — سيُربط لاحقاً باقتراحات الحصة.">
        <x-setting-toggle name="ai_activities" :checked="$s['ai_activities']" title="الأنشطة" />
    </x-setting-row>
    <x-setting-row title="إنشاء الواجبات" description="سيُوسَّع لاحقاً. الواجبات تُكتب اليوم يدوياً من الحصة.">
        <x-setting-toggle name="ai_homework" :checked="$s['ai_homework']" title="الواجبات" />
    </x-setting-row>
    <x-setting-row title="تحليل مستوى الطالب" description="يُستخدم مستوى الطالب وأسلوبه في شرح الآية.">
        <x-setting-toggle name="ai_analyze" :checked="$s['ai_analyze']" title="تحليل المستوى" />
    </x-setting-row>
    <x-setting-row title="اقتراح خطة المراجعة" description="يعمل مع خيار المراجعة الذكية في إعدادات القرآن.">
        <x-setting-toggle name="ai_revision_plan" :checked="$s['ai_revision_plan']" title="خطة المراجعة" />
    </x-setting-row>
    <x-setting-row title="تلخيص الحصة" description="يُكتب ملخص عربي بعد إنهاء الحصة، أو ملخص محلي إن لم يتوفر المفتاح.">
        <x-setting-toggle name="ai_summarize" :checked="$s['ai_summarize']" title="تلخيص الحصة" />
    </x-setting-row>
    <x-setting-row title="إنشاء تقرير الطالب" description="صياغة رسالة ولي الأمر من المساعد أو ملخص الحصة. لا تُرسل الملاحظة الخاصة إذا كان ولي الأمر لا يراها.">
        <x-setting-toggle name="ai_student_report" :checked="$s['ai_student_report']" title="تقرير الطالب" />
    </x-setting-row>
    <x-setting-row title="اقتراح نقاط الضعف" description="نقاط متابعة من سجل الحصص في المساعد، مع صياغة محلية إن تعذّر الاتصال.">
        <x-setting-toggle name="ai_weak_points" :checked="$s['ai_weak_points']" title="نقاط الضعف" />
    </x-setting-row>
    <x-setting-row title="اقتراح الحصة القادمة" description="طبقة اختيارية فوق الاقتراح المحلي. الحصة تعمل بدونها.">
        <x-setting-toggle name="ai_next_lesson" :checked="$s['ai_next_lesson']" title="الحصة القادمة" />
    </x-setting-row>
    <x-setting-row title="استخدام بيانات الطالب لتحسين الاقتراحات" description="إن أُغلق، لن يُرسل اسم الطالب أو ملاحظاته إلى OpenAI.">
        <x-setting-toggle name="ai_use_student_data" :checked="$s['ai_use_student_data']" title="بيانات الطالب" />
    </x-setting-row>

    <p class="section-title">أسلوب الخطة</p>
    <p class="muted mb-14">يؤثر على طلب OpenAI للشرح والملخص عند توفر المفتاح. الشرح المحلي لا يتغير.</p>
    <div class="choice-row">
        @foreach (['short' => 'مختصر', 'medium' => 'متوسط', 'detailed' => 'مفصّل'] as $value => $label)
            <label class="choice-chip">
                <input type="radio" name="ai_plan_style" value="{{ $value }}" @checked(old('ai_plan_style', $s['ai_plan_style']) === $value)>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>

    <p class="section-title">مستوى التدخل</p>
    <p class="muted mb-14">تفضيل محفوظ الآن. لم يُربط بطلب OpenAI بعد.</p>
    <div class="choice-row">
        @foreach (['low' => 'منخفض', 'medium' => 'متوسط', 'high' => 'مرتفع'] as $value => $label)
            <label class="choice-chip">
                <input type="radio" name="ai_intervention" value="{{ $value }}" @checked(old('ai_intervention', $s['ai_intervention']) === $value)>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>

    @include('settings.partials.save-bar')
</form>

<form class="card settings-card mt-14" method="post" action="{{ route('settings.reset-ai') }}">
    @csrf
    <h3>إعادة الضبط</h3>
    <p class="muted">يعيد ميزات المساعد وأسلوبه للافتراضي، ويبقي مفتاح OpenAI كما هو.</p>
    <button class="btn btn-outline mt-12" type="submit">إعادة ضبط إعدادات الذكاء الاصطناعي</button>
</form>
