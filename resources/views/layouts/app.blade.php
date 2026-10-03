<!DOCTYPE html>
<html lang="{{ $uiLanguage ?? 'ar' }}" dir="{{ $uiDirection ?? 'rtl' }}" data-theme="{{ $theme ?? 'light' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة اليوم') — {{ $appName ?? 'تاج الوقار' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) ?: '1' }}">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📖</text></svg>">
</head>
<body class="font-{{ $fontSize ?? 'medium' }} density-{{ $density ?? 'comfortable' }}{{ request()->routeIs('settings.*', 'password.*') ? ' settings-page' : '' }}">
@php
    $nav = [
        ['dashboard', 'لوحة اليوم', '🏠'],
        ['students.index', 'الطلاب', '👩‍🎓'],
        ['appointments.index', 'المواعيد', '📅'],
        ['lessons.index', 'الحصص', '📖'],
        ['notes.index', 'الملاحظات', '📝'],
        ['book.index', 'الكتاب والشرح', '📚'],
        ['assistant.index', 'المساعد الذكي', '✨'],
        ['reports.index', 'التقارير', '📊'],
        ['settings.index', 'الإعدادات', '⚙️'],
    ];
    $firstName = explode(' ', $teacherName)[0] ?? $teacherName;
    $initials = collect(preg_split('/\s+/u', trim($teacherName)))->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('');
@endphp
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">
            <img class="brand-logo" src="{{ asset('images/brand/logo-mark.svg') }}" alt="{{ $appName ?? 'تاج الوقار' }}">
            <h1>{{ $appName ?? 'تاج الوقار' }}</h1>
        </div>
        <nav class="nav">
            @foreach ($nav as [$route, $label, $icon])
                <a href="{{ route($route) }}" class="{{ request()->routeIs(str_replace('.index', '', $route).'*') || request()->routeIs($route) ? 'active' : '' }}">
                    <span class="nav-icon">{{ $icon }}</span>{{ $label }}
                </a>
            @endforeach
        </nav>
        <div class="today-card">
            <h3>حصص اليوم</h3>
            <div class="stats">
                <div><b>{{ $todayTotal }}</b><span>الإجمالي</span></div>
                <div><b>{{ $todayDone }}</b><span>منتهية</span></div>
                <div><b>{{ $todayRemaining }}</b><span>متبقية</span></div>
            </div>
        </div>
        <div class="ayah-quote">وَرَتِّلِ الْقُرْآنَ تَرْتِيلًا</div>
    </aside>

    <section class="main">
        <div class="mobile-today">
            <span><b>{{ $todayTotal }}</b> اليوم</span>
            <span><b>{{ $todayDone }}</b> منتهية</span>
            <span><b>{{ $todayRemaining }}</b> متبقية</span>
        </div>
        <nav class="mobile-nav">
            @foreach ($nav as [$route, $label, $icon])
                <a href="{{ route($route) }}" title="{{ $label }}" class="{{ request()->routeIs(str_replace('.index', '', $route).'*') || request()->routeIs($route) ? 'active' : '' }}">
                    <span class="nav-icon" aria-hidden="true">{{ $icon }}</span>
                    <span class="mobile-nav-label">{{ $label }}</span>
                </a>
            @endforeach
        </nav>
        <header class="header">
            <div>
                <h2>@yield('heading', \App\Support\Quran::greeting().'، '.$firstName)</h2>
                <p>@yield('subheading', \App\Support\Quran::arabicDate())</p>
            </div>
            <form class="header-search" method="get" action="{{ route('students.index') }}" role="search">
                <input class="search" type="search" name="q" value="{{ request('q') }}" placeholder="بحث عن طالب..." aria-label="بحث عن طالب">
            </form>
            <div class="user-chip">
                @if (! empty($teacherPhoto))
                    <img class="avatar avatar-img" src="{{ $teacherPhoto }}" alt="{{ $teacherName }}">
                @else
                    <div class="avatar" style="background:var(--primary)">{{ $initials }}</div>
                @endif
                <div class="user-meta">
                    <strong>{{ $teacherName }}</strong>
                    @if (! empty($teacherCountry))
                        <small>{{ $teacherCountry }}</small>
                    @endif
                </div>
                @auth
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost">خروج</button>
                    </form>
                @endauth
            </div>
        </header>
        @include('partials.lesson-reminder')
        @yield('content')
    </section>
</div>

@if (session('success'))
    <div class="toast" id="toast" role="status">{{ session('success') }}</div>
@endif
@if (session('warning'))
    <div class="toast toast-warn" id="toast-warn" role="status">{{ session('warning') }}</div>
@endif

<script>
    window.QURAN_AYAH_COUNTS = {{ \Illuminate\Support\Js::from(\App\Support\Quran::ayahCounts()) }};

    document.querySelectorAll('[data-surah-controls]').forEach((group) => {
        const surahSelect = group.querySelector('[data-surah-select]');
        if (!surahSelect) {
            return;
        }

        const fillAyahSelect = (select, max) => {
            const allowEmpty = select.hasAttribute('data-ayah-empty');
            const previous = Number.parseInt(select.value, 10);
            select.replaceChildren();

            if (allowEmpty || max < 1) {
                const empty = document.createElement('option');
                empty.value = '';
                empty.textContent = '—';
                select.append(empty);
            }

            for (let n = 1; n <= max; n += 1) {
                const option = document.createElement('option');
                option.value = String(n);
                option.textContent = String(n);
                select.append(option);
            }

            if (Number.isInteger(previous) && previous >= 1 && max >= 1) {
                select.value = String(Math.min(previous, max));
                return;
            }

            select.value = allowEmpty || max < 1 ? '' : '1';
        };

        const rebuild = () => {
            const max = window.QURAN_AYAH_COUNTS[surahSelect.value] || 0;
            group.querySelectorAll('[data-ayah-select]').forEach((select) => fillAyahSelect(select, max));
        };

        surahSelect.addEventListener('change', rebuild);
    });

    document.querySelectorAll('[data-copy]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const value = btn.getAttribute('data-copy') || '';
            if (!value) return;
            navigator.clipboard.writeText(value).then(() => {
                btn.textContent = 'تم النسخ';
                setTimeout(() => btn.textContent = 'نسخ', 1600);
            });
        });
    });
    document.querySelectorAll('[data-copy-from]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const source = document.getElementById(btn.getAttribute('data-copy-from') || '');
            if (!source) return;
            navigator.clipboard.writeText(source.value || source.textContent || '').then(() => {
                const original = btn.textContent;
                btn.textContent = 'تم النسخ';
                setTimeout(() => { btn.textContent = original; }, 1600);
            });
        });
    });
    ['toast', 'toast-warn'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) setTimeout(() => el.classList.add('hidden'), 4500);
    });
</script>
@stack('scripts')
</body>
</html>
