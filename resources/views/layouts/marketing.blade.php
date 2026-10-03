<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="تاج الوقار نظام يساعد معلمة القرآن على تنظيم طلابها وحصصها ومواعيدها ومتابعة تقدم الطلاب من مكان واحد.">
    <title>@yield('title', 'تاج الوقار') — نظام إدارة معلمة القرآن</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/logo-mark.svg') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body class="landing-body">
<div class="lp-wrap">
    <header class="lp-nav" id="lp-nav">
        <div class="lp-container lp-nav-inner">
            <a class="lp-brand" href="{{ route('landing') }}">
                <img src="{{ asset('images/brand/logo-mark.svg') }}?v=3" alt="تاج الوقار">
                <span class="lp-brand-text">
                    <strong>تاج الوقار</strong>
                    <small>نظام إدارة معلمة القرآن</small>
                </span>
            </a>
            <nav class="lp-nav-links" aria-label="أقسام الصفحة">
                <a href="{{ route('landing') }}#top">الرئيسية</a>
                <a href="{{ route('landing') }}#features">المميزات</a>
                <a href="{{ route('landing') }}#how">كيف يعمل؟</a>
                <a href="{{ route('landing') }}#audience">لمن تاج الوقار؟</a>
                <a href="{{ route('landing') }}#faq">الأسئلة الشائعة</a>
            </nav>
            <div class="lp-nav-actions">
                <a class="lp-btn lp-btn-ghost lp-login" href="{{ route('login') }}">تسجيل الدخول</a>
                <a class="lp-btn lp-btn-primary" href="{{ route('dashboard') }}">ابدئي الآن مجانًا</a>
                <button type="button" class="lp-nav-toggle" id="lp-nav-toggle" aria-expanded="false" aria-controls="lp-nav">القائمة</button>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="lp-footer">
        <div class="lp-container">
            <div class="lp-footer-grid">
                <div>
                    <a class="lp-brand lp-footer-brand" href="{{ route('landing') }}">
                        <img src="{{ asset('images/brand/logo-mark.svg') }}?v=3" alt="تاج الوقار">
                        <span class="lp-brand-text">
                            <strong>تاج الوقار</strong>
                            <small>نظام إدارة معلمة القرآن</small>
                        </span>
                    </a>
                    <p>نظام إدارة معلمة القرآن لتنظيم الطلاب والحصص ومتابعة التقدم على جهازك.</p>
                </div>
                <div>
                    <h4>المنتج</h4>
                    <a href="{{ route('landing') }}#features">المميزات</a>
                    <a href="{{ route('landing') }}#how">كيف يعمل؟</a>
                    <a href="{{ route('settings.index', ['tab' => 'subscription']) }}">الخطة</a>
                    <a href="{{ route('landing') }}#faq">الأسئلة الشائعة</a>
                </div>
                <div>
                    <h4>الدعم</h4>
                    @if (! empty($teacherEmail))
                        <a href="mailto:{{ $teacherEmail }}">تواصل معنا</a>
                    @else
                        <a href="{{ route('settings.index', ['tab' => 'help']) }}">تواصل معنا</a>
                    @endif
                    <a href="{{ route('settings.index', ['tab' => 'help']) }}">المساعدة</a>
                </div>
                <div>
                    <h4>قانوني</h4>
                    <a href="{{ route('landing.privacy') }}">سياسة الخصوصية</a>
                    <a href="{{ route('landing.terms') }}">شروط الاستخدام</a>
                </div>
            </div>
            <div class="lp-footer-copy">© 2026 تاج الوقار. جميع الحقوق محفوظة.</div>
        </div>
    </footer>
</div>
<script>
    (function () {
        const nav = document.getElementById('lp-nav');
        const toggle = document.getElementById('lp-nav-toggle');
        if (toggle && nav) {
            toggle.addEventListener('click', () => {
                const open = nav.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
            nav.querySelectorAll('.lp-nav-links a').forEach((link) => {
                link.addEventListener('click', () => nav.classList.remove('is-open'));
            });
        }
    })();
</script>
@stack('scripts')
</body>
</html>
