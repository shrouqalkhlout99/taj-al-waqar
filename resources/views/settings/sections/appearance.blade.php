<form class="card settings-card" method="post" action="{{ route('settings.update') }}" data-settings-form>
    @csrf
    <input type="hidden" name="section" value="appearance">
    <h3>المظهر والواجهة</h3>
    <p class="muted mb-14">تُطبَّق على تاج الوقار بالكامل بعد الحفظ.</p>

    <p class="section-title">الثيم</p>
    <div class="choice-row">
        @foreach (['light' => 'فاتح', 'dark' => 'داكن', 'system' => 'حسب الجهاز'] as $value => $label)
            <label class="choice-chip">
                <input type="radio" name="theme" value="{{ $value }}" @checked(old('theme', $s['theme']) === $value)>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>

    <p class="section-title">اللغة</p>
    <div class="choice-row">
        @foreach (['ar' => 'العربية', 'en' => 'English'] as $value => $label)
            <label class="choice-chip">
                <input type="radio" name="ui_language" value="{{ $value }}" @checked(old('ui_language', $s['ui_language']) === $value)>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>
    <p class="muted">الواجهة عربية حالياً. اختيار الإنجليزية يحفظ التفضيل ويغيّر الاتجاه إن اخترتِ LTR.</p>

    <p class="section-title">الاتجاه</p>
    <div class="choice-row">
        @foreach (['rtl' => 'من اليمين لليسار', 'ltr' => 'من اليسار لليمين'] as $value => $label)
            <label class="choice-chip">
                <input type="radio" name="ui_direction" value="{{ $value }}" @checked(old('ui_direction', $s['ui_direction']) === $value)>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>

    <p class="section-title">حجم الخط</p>
    <div class="choice-row">
        @foreach (['small' => 'صغير', 'medium' => 'متوسط', 'large' => 'كبير'] as $value => $label)
            <label class="choice-chip">
                <input type="radio" name="font_size" value="{{ $value }}" @checked(old('font_size', $s['font_size']) === $value)>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>

    <p class="section-title">كثافة العرض</p>
    <div class="choice-row">
        @foreach (['comfortable' => 'مريح', 'compact' => 'مضغوط'] as $value => $label)
            <label class="choice-chip">
                <input type="radio" name="density" value="{{ $value }}" @checked(old('density', $s['density']) === $value)>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>

    @include('settings.partials.save-bar')
</form>
