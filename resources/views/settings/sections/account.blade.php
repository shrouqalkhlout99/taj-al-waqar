<form class="card settings-card" method="post" action="{{ route('settings.update') }}" enctype="multipart/form-data" data-settings-form>
    @csrf
    <input type="hidden" name="section" value="account">
    <h3>الحساب الشخصي</h3>
    <p class="muted mb-14">بياناتك كما تظهر في تاج الوقار. التغييرات تُحفظ على هذا الجهاز.</p>

    <div class="profile-upload">
        @if ($teacherPhoto)
            <img class="photo-preview" src="{{ $teacherPhoto }}" alt="صورة المعلمة">
        @else
            <div class="photo-preview photo-placeholder">{{ mb_substr($s['teacher_name'] ?? 'م', 0, 1) }}</div>
        @endif
        <div>
            <label class="btn btn-ghost" for="teacher_photo">تغيير الصورة</label>
            <input class="hidden" id="teacher_photo" type="file" name="teacher_photo" accept="image/jpeg,image/png,image/webp">
            @if ($teacherPhoto)
                <label class="check-label">
                    <input type="checkbox" name="remove_photo" value="1">
                    إزالة الصورة الحالية
                </label>
            @endif
            <p class="muted">JPG أو PNG حتى 2MB</p>
        </div>
    </div>

    <div class="form-grid mt-12">
        <div class="field">
            <label for="teacher_name">الاسم الكامل</label>
            <input id="teacher_name" name="teacher_name" required maxlength="80" value="{{ old('teacher_name', $s['teacher_name']) }}">
        </div>
        <div class="field">
            <label for="teacher_email">البريد الإلكتروني</label>
            <input id="teacher_email" type="email" name="teacher_email" maxlength="120" value="{{ old('teacher_email', $s['teacher_email']) }}" placeholder="name@example.com">
        </div>
        <div class="field">
            <label for="teacher_phone">رقم الهاتف</label>
            <input id="teacher_phone" name="teacher_phone" maxlength="30" value="{{ old('teacher_phone', $s['teacher_phone']) }}" placeholder="05xxxxxxxx">
        </div>
        <div class="field">
            <label for="teacher_whatsapp">رقم واتساب</label>
            <input id="teacher_whatsapp" name="teacher_whatsapp" maxlength="30" value="{{ old('teacher_whatsapp', $s['teacher_whatsapp']) }}" placeholder="9665xxxxxxxx">
        </div>
        <div class="field">
            <label for="teacher_country">الدولة</label>
            <input id="teacher_country" name="teacher_country" maxlength="80" value="{{ old('teacher_country', $s['teacher_country']) }}" placeholder="المملكة العربية السعودية">
        </div>
        <div class="field">
            <label for="teacher_city">المدينة</label>
            <input id="teacher_city" name="teacher_city" maxlength="80" value="{{ old('teacher_city', $s['teacher_city']) }}">
        </div>
        <div class="field">
            <label for="teacher_timezone">المنطقة الزمنية</label>
            <select id="teacher_timezone" name="teacher_timezone" required>
                @foreach ($timezones as $tz => $label)
                    <option value="{{ $tz }}" @selected(old('teacher_timezone', $s['teacher_timezone']) === $tz)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="teacher_language">اللغة</label>
            <select id="teacher_language" name="teacher_language">
                <option value="ar" @selected(old('teacher_language', $s['teacher_language']) === 'ar')>العربية</option>
                <option value="en" @selected(old('teacher_language', $s['teacher_language']) === 'en')>English</option>
            </select>
        </div>
    </div>

    <p class="section-title">الفيديو التعريفي</p>
    @if ($teacherVideo)
        <video class="video-preview" controls src="{{ $teacherVideo }}"></video>
        <label class="check-label">
            <input type="checkbox" name="remove_video" value="1">
            إزالة الفيديو الحالي
        </label>
    @endif
    <div class="field">
        <label for="teacher_video">رفع فيديو من الجهاز (MP4 أو WebM حتى 80MB)</label>
        <input id="teacher_video" type="file" name="teacher_video" accept="video/mp4,video/webm,video/quicktime">
    </div>

    @include('settings.partials.save-bar', ['saveLabel' => 'حفظ التغييرات'])
</form>

<div class="card settings-card mt-14">
    <h3>كلمة المرور</h3>
    <p class="muted mb-14">حسابك محمي بتسجيل دخول على هذا الجهاز. يمكنكِ تغيير كلمة المرور في أي وقت.</p>
    @auth
        <p class="muted">بريد الدخول الحالي: {{ auth()->user()->email }}</p>
    @endauth
    <a class="btn btn-primary mt-12" href="{{ route('password.edit') }}">تغيير كلمة المرور</a>
</div>
