@extends('layouts.marketing')

@section('title', 'تاج الوقار')

@section('content')
<section class="lp-hero" id="top">
    <div class="lp-container lp-hero-grid">
        <div>
            <p class="lp-kicker">لتاج الوقار · لمعلمة القرآن</p>
            <h1>إدارة حلقاتك القرآنية أصبحت أسهل.</h1>
            <p>تاج الوقار نظام يساعد معلمة القرآن على تنظيم طلابها وحصصها ومواعيدها، وتسجيل تفاصيل كل حصة ومتابعة تقدم الطلاب من مكان واحد.</p>
            <div class="lp-hero-actions">
                <a class="lp-btn lp-btn-primary" href="{{ route('dashboard') }}">ابدئي الآن مجانًا</a>
                <a class="lp-btn lp-btn-ghost" href="#showcase">تعرّفي على النظام</a>
            </div>
        </div>
        <div class="lp-devices" aria-hidden="false">
            <div class="lp-laptop">
                <div class="lp-laptop-bar"><span></span></div>
                <div class="lp-screen">
                    @include('landing.partials.app-preview')
                </div>
            </div>
            <div class="lp-phone">
                <div class="lp-phone-screen">
                    <p class="lp-kicker">{{ $greeting }}، {{ explode(' ', trim($teacherName))[0] ?? $teacherName }}</p>
                    <div class="lp-stat"><b>{{ $studentCount }}</b><span>طالبًا</span></div>
                    <div class="lp-stat" style="margin-top:8px"><b>{{ $todayCount }}</b><span>حصص اليوم</span></div>
                    <div class="lp-stat" style="margin-top:8px">
                        <b>{{ $todayRemaining }}</b>
                        <span>متبقية</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="lp-section lp-problems" id="problems">
    <div class="lp-container">
        <div class="lp-section-head">
            <h2>كم من التفاصيل تحتاجها معلمة القرآن لتتذكرها كل يوم؟</h2>
        </div>
        <div class="lp-cards-5">
            <article class="lp-card"><div class="lp-icon">📅</div><h3>لخبطة المواعيد</h3><p>تعدد الطلاب والحصص يجعل تنظيم الجدول مرهقًا.</p></article>
            <article class="lp-card"><div class="lp-icon">👩🏻‍🏫</div><h3>نسيان مستوى الطالب</h3><p>من الصعب تذكر آخر ما وصل إليه كل طالب.</p></article>
            <article class="lp-card"><div class="lp-icon">📝</div><h3>تفاصيل الحصة السابقة</h3><p>ماذا قرأ؟ أين أخطأ؟ ماذا كان الواجب؟</p></article>
            <article class="lp-card"><div class="lp-icon">📚</div><h3>متابعة الواجبات</h3><p>من أنجز؟ ومن يحتاج إلى متابعة؟</p></article>
            <article class="lp-card"><div class="lp-icon">📈</div><h3>معرفة التطور</h3><p>هل الطالب يتقدم فعلًا؟ وكيف كان أداؤه خلال الفترة الماضية؟</p></article>
        </div>
        <p class="lp-bridge">تاج الوقار يجمع هذه التفاصيل في مكان واحد.</p>
    </div>
</section>

<section class="lp-section lp-solution" id="solution">
    <div class="lp-container lp-solution-grid">
        <div>
            <p class="lp-kicker">الحل</p>
            <h2>كل ما تحتاجينه لإدارة طلابك... في مكان واحد.</h2>
            <p class="lp-lead">صُمم تاج الوقار ليخفف عن معلمة القرآن عبء التنظيم والمتابعة، حتى تتمكن من التركيز على الأهم: تعليم كتاب الله ومتابعة طلابها.</p>
        </div>
        <div class="lp-preview-frame">
            @include('landing.partials.app-preview')
        </div>
    </div>
</section>

<section class="lp-section" id="features">
    <div class="lp-container">
        <div class="lp-section-head">
            <p class="lp-kicker">المميزات</p>
            <h2>وظائف النظام كما هي الآن</h2>
            <p class="lp-lead">نعرض ما يعمل فعليًا داخل تاج الوقار، لا وعودًا غير موجودة.</p>
        </div>
        <div class="lp-cards-3">
            <article class="lp-card"><div class="lp-icon">📚</div><h3>إدارة الطلاب</h3><p>أضيفي طلابك واحتفظي ببياناتهم ومستوياتهم وملاحظاتهم في مكان واحد.</p></article>
            <article class="lp-card"><div class="lp-icon">📅</div><h3>تنظيم الحصص</h3><p>أنشئي جدول حصص منظم وتابعي مواعيد طلابك بسهولة.</p></article>
            <article class="lp-card"><div class="lp-icon">📝</div><h3>سجل الحصة</h3><p>سجلي ما تم إنجازه في كل حصة، والملاحظات، والمطلوب للحصة القادمة.</p></article>
            <article class="lp-card"><div class="lp-icon">📊</div><h3>متابعة التقدم</h3><p>تابعي مستوى الطالب وموضع الحفظ والتسميع والمراجعة عبر الوقت.</p></article>
            <article class="lp-card"><div class="lp-icon">✨</div><h3>المساعد الذكي</h3><p>احصلي على ملخص الحصة واقتراحات الشرح من داخل نفس النظام.</p></article>
            <article class="lp-card"><div class="lp-icon">📈</div><h3>التقارير</h3><p>احصلي على صورة أوضح عن عدد الطلاب والحصص وتقدم الحفظ والتجويد.</p></article>
        </div>
    </div>
</section>

<section class="lp-section" id="how">
    <div class="lp-container">
        <div class="lp-section-head">
            <p class="lp-kicker">كيف يعمل؟</p>
            <h2>كيف يعمل تاج الوقار؟</h2>
        </div>
        <div class="lp-steps">
            <article class="lp-step"><div class="lp-step-num">01</div><h3>أضيفي طلابك</h3><p class="lp-lead">سجلي بيانات الطلاب ومستوى كل طالب.</p></article>
            <article class="lp-step"><div class="lp-step-num">02</div><h3>نظمي حصصك</h3><p class="lp-lead">حددي مواعيد الحصص وجدولك بسهولة.</p></article>
            <article class="lp-step"><div class="lp-step-num">03</div><h3>تابعي تقدمهم</h3><p class="lp-lead">سجلي تفاصيل كل حصة وتابعي تطور الطلاب.</p></article>
        </div>
        <p class="lp-how-note">أقل وقت في التنظيم، وأكثر تركيزًا على التعليم.</p>
    </div>
</section>

<section class="lp-section lp-showcase" id="showcase">
    <div class="lp-container">
        <div class="lp-section-head">
            <p class="lp-kicker">من الداخل</p>
            <h2>شاهدي تاج الوقار من الداخل</h2>
            <p class="lp-lead">هذه معاينات من الشاشات والبيانات الموجودة على هذا الجهاز.</p>
        </div>
        @include('landing.partials.showcase-panels')
    </div>
</section>

<section class="lp-section" id="audience">
    <div class="lp-container">
        <div class="lp-section-head">
            <h2>تاج الوقار صُمم لمن؟</h2>
        </div>
        <div class="lp-cards-4">
            <article class="lp-card"><div class="lp-icon">👩🏻‍🏫</div><h3>معلمات القرآن</h3><p>لإدارة الطلاب والحصص والمتابعة اليومية.</p></article>
            <article class="lp-card"><div class="lp-icon">📖</div><h3>حلقات القرآن</h3><p>لتنظيم الطلاب والحصص ومتابعة مستوياتهم.</p></article>
            <article class="lp-card"><div class="lp-icon">🏫</div><h3>مراكز تحفيظ القرآن</h3><p>لإدارة العملية التعليمية بصورة أكثر تنظيمًا.</p></article>
            <article class="lp-card"><div class="lp-icon">🌐</div><h3>المعلمات عن بُعد</h3><p>لإدارة الطلاب ورابط اللقاء من ملف كل طالب، مهما اختلفت أماكنهم.</p></article>
        </div>
    </div>
</section>

<section class="lp-section" id="journey">
    <div class="lp-container lp-cards-2">
        <div>
            <p class="lp-kicker">رحلة الطالب</p>
            <h2>لأن كل طالب له رحلة مختلفة</h2>
            <p class="lp-lead">لا يتوقف تعليم القرآن عند مقدار الحفظ فقط. تاج الوقار يساعدك على الاحتفاظ بصورة أوضح عن رحلة كل طالب، من مستواه الحالي إلى ما تم إنجازه وما يحتاج إلى متابعة.</p>
        </div>
        <article class="lp-card">
            <h3>ما يُحفظ في ملف الطالب</h3>
            <p>المستوى، أسلوب الشرح، موضع الحفظ، التسميع، المراجعة، والملاحظات.</p>
        </article>
    </div>
</section>

<section class="lp-section" id="trust">
    <div class="lp-container">
        <div class="lp-trust">
            <div class="lp-icon">🔒</div>
            <div>
                <h2>بيانات طلابك في مكان منظم وآمن</h2>
                <p class="lp-lead">تاج الوقار منصة محلية على جهازك. بيانات الطلاب والحصص تُحفظ في ملف داخل المشروع، ولا تُرفع إلى خادم خارجي إلا إذا أدخلتِ مفتاح OpenAI وفعّلتِ إرسال بيانات الطالب من الإعدادات.</p>
            </div>
        </div>
    </div>
</section>

<section class="lp-section" id="faq">
    <div class="lp-container">
        <div class="lp-section-head">
            <h2>الأسئلة الشائعة</h2>
        </div>
        <div class="lp-faq">
            <details open>
                <summary>هل تاج الوقار مناسب للمعلمة الفردية؟</summary>
                <p>نعم، صُمم ليكون مناسبًا للمعلمات اللواتي يتابعن عددًا محدودًا من الطلاب، وكذلك لإدارة حلقات أكبر على الجهاز نفسه.</p>
            </details>
            <details>
                <summary>هل يمكن استخدامه من الهاتف؟</summary>
                <p>نعم. الواجهة الحالية متجاوبة، ويمكن فتح النظام من متصفح الجوال على نفس الجهاز أو الشبكة المحلية.</p>
            </details>
            <details>
                <summary>هل أحتاج إلى خبرة تقنية؟</summary>
                <p>لا، الواجهة مصممة لتكون بسيطة: لوحة اليوم، ملفات الطلاب، بدء الحصة، ثم الحفظ.</p>
            </details>
            <details>
                <summary>هل يمكنني تجربة النظام قبل الاشتراك؟</summary>
                <p>النظام يعمل حاليًا كخطة محلية على هذا الجهاز بدون نظام دفع. ابدئي مباشرة من لوحة اليوم دون إنشاء حساب سحابي.</p>
            </details>
        </div>
    </div>
</section>

<section class="lp-section lp-cta" id="start">
    <div class="lp-container">
        <h2>واجعلي إدارة حلقاتك أسهل من أي وقت مضى.</h2>
        <p>ابدئي بتنظيم طلابك وحصصك ومتابعتهم من مكان واحد.</p>
        <a class="lp-btn lp-btn-light" href="{{ route('dashboard') }}">ابدئي الآن مجانًا</a>
        <div class="lp-cta-note">لا تحتاجين إلى خبرة تقنية.</div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (function () {
        const tabs = document.querySelectorAll('.lp-tab');
        const panels = document.querySelectorAll('.lp-panel');
        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                const id = tab.getAttribute('data-tab');
                tabs.forEach((item) => {
                    item.classList.toggle('is-active', item === tab);
                    item.setAttribute('aria-selected', item === tab ? 'true' : 'false');
                });
                panels.forEach((panel) => {
                    panel.classList.toggle('is-active', panel.getAttribute('data-panel') === id);
                });
            });
        });
    })();
</script>
@endpush
