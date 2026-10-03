@extends('layouts.app')

@section('title', 'الإعدادات')
@section('heading', 'إعدادات تاج الوقار')
@section('subheading', 'مركز التحكم: الحساب، الحصص، المناهج، والمساعد الذكي')

@section('content')
<div class="settings-shell">
    <aside class="settings-nav" aria-label="أقسام الإعدادات">
        <p class="settings-nav-title">⚙️ الإعدادات</p>
        @foreach ($tabs as $key => $meta)
            <a href="{{ route('settings.index', ['tab' => $key]) }}"
               class="{{ $tab === $key ? 'active' : '' }}"
               @if($tab === $key) aria-current="page" @endif>
                <span class="nav-icon" aria-hidden="true">{{ $meta['icon'] }}</span>
                {{ $meta['label'] }}
            </a>
        @endforeach
    </aside>

    <div class="settings-main">
        <label class="settings-mobile-label" for="settings-tab-select">القسم</label>
        <select id="settings-tab-select" class="settings-mobile-select" aria-label="قسم الإعدادات">
            @foreach ($tabs as $key => $meta)
                <option value="{{ route('settings.index', ['tab' => $key]) }}" @selected($tab === $key)>
                    {{ $meta['icon'] }} {{ $meta['label'] }}
                </option>
            @endforeach
        </select>

        @if ($errors->any())
            <div class="note-box mb-14" role="alert">{{ $errors->first() }}</div>
        @endif

        @include('settings.sections.'.$tab)
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const select = document.getElementById('settings-tab-select');
        if (select) {
            select.addEventListener('change', () => {
                window.location.href = select.value;
            });
        }

        const overlays = document.querySelectorAll('.modal-overlay');
        const openModal = (id) => {
            const el = document.getElementById(id);
            if (el) el.classList.remove('hidden');
        };
        const closeModal = (id) => {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        };
        document.querySelectorAll('[data-open-modal]').forEach((btn) => {
            btn.addEventListener('click', () => openModal(btn.getAttribute('data-open-modal')));
        });
        document.querySelectorAll('[data-close-modal]').forEach((btn) => {
            btn.addEventListener('click', () => closeModal(btn.getAttribute('data-close-modal')));
        });
        overlays.forEach((overlay) => {
            overlay.addEventListener('click', (event) => {
                if (event.target === overlay) overlay.classList.add('hidden');
            });
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') overlays.forEach((el) => el.classList.add('hidden'));
        });

        document.querySelectorAll('[data-settings-form]').forEach((form) => {
            const bar = form.querySelector('[data-save-bar]');
            let dirty = false;
            const mark = () => {
                dirty = true;
                if (bar) bar.hidden = false;
            };
            form.addEventListener('input', mark);
            form.addEventListener('change', mark);
            form.addEventListener('submit', () => { dirty = false; });
            form.querySelector('[data-discard]')?.addEventListener('click', () => form.reset());
            window.addEventListener('beforeunload', (event) => {
                if (! dirty) return;
                event.preventDefault();
                event.returnValue = '';
            });
            document.querySelectorAll('.settings-nav a, .settings-mobile-select').forEach((link) => {
                link.addEventListener('click', (event) => {
                    if (! dirty) return;
                    if (! confirm('لديك تغييرات غير محفوظة. هل تتركين الصفحة؟')) {
                        event.preventDefault();
                    } else {
                        dirty = false;
                    }
                });
            });
        });
    })();
</script>
@endpush
