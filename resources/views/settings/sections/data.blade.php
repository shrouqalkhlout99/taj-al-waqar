@php
    $sizeMb = $dbSize > 0 ? number_format($dbSize / 1024 / 1024, 2) : '0.00';
@endphp
<div class="card settings-card">
    <h3>البيانات والنسخ الاحتياطي</h3>
    <p class="muted mb-14">النسخ يُحفظ على هذا الجهاز داخل مجلد المشروع. مفتاح OpenAI لا يُضمَّن في التصدير.</p>

    <div class="progress-grid">
        <div>
            <small>آخر نسخة احتياطية</small>
            <b>{{ $lastBackupAt ?: 'لا توجد بعد' }}</b>
        </div>
        <div>
            <small>حجم البيانات</small>
            <b>{{ $sizeMb }} MB</b>
        </div>
        <div>
            <small>حالة النسخ</small>
            <b>{{ $backups ? 'جاهزة' : 'لم تُنشأ' }}</b>
        </div>
    </div>

    <div class="row-actions mt-14">
        <form method="post" action="{{ route('settings.backup') }}">
            @csrf
            <button class="btn btn-primary" type="submit">إنشاء نسخة احتياطية الآن</button>
        </form>
        <form method="post" action="{{ route('settings.export') }}">
            @csrf
            <button class="btn btn-ghost" type="submit">تصدير بياناتي</button>
        </form>
    </div>

    @if ($backups)
        <p class="section-title">النسخ المحفوظة</p>
        <div class="list">
            @foreach ($backups as $item)
                <div class="list-item">
                    <div>
                        <b>{{ $item->name }}</b>
                        <div class="muted">{{ $item->mtime }} · {{ number_format($item->size / 1024, 1) }} KB</div>
                    </div>
                    <a class="btn btn-outline" href="{{ route('settings.backup.download', $item->name) }}">تنزيل</a>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="card settings-card mt-14">
    <h3>استعادة نسخة احتياطية</h3>
    <p class="muted mb-14">تستبدل ملف قاعدة البيانات الحالي. الطلاب والحصص سيعودون لحالة تلك النسخة.</p>
    @if (! $backups)
        <p class="empty">أنشئي نسخة أولاً.</p>
    @else
        <form method="post" action="{{ route('settings.restore') }}" id="restore-form">
            @csrf
            <div class="field">
                <label for="backup_file">النسخة</label>
                <select id="backup_file" name="backup_file" required>
                    @foreach ($backups as $item)
                        <option value="{{ $item->name }}">{{ $item->name }} — {{ $item->mtime }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field mt-12">
                <label for="confirm_restore">اكتبي «استعادة» للتأكيد</label>
                <input id="confirm_restore" name="confirm_restore" required placeholder="استعادة">
            </div>
            <button type="button" class="btn btn-outline mt-12" data-open-modal="restore-modal">استعادة نسخة احتياطية</button>
        </form>
    @endif
</div>

<div class="card settings-card danger-zone mt-14">
    <h3>منطقة خطرة</h3>
    <p class="muted">حذف الحساب في هذا النظام يعني مسح ملف المعلمة والصورة والفيديو ومفتاح الذكاء فقط. الطلاب والحصص لن تُحذف.</p>
    <button type="button" class="btn btn-danger mt-12" data-open-modal="delete-modal">حذف الحساب</button>
</div>

<x-confirm-modal id="restore-modal" title="استعادة النسخة؟" body="سيُستبدل ملف البيانات الحالي بهذه النسخة. لا تُحذف الملفات إلا بعد نجاح الاستعادة.">
    <x-slot:actions>
        <button type="submit" form="restore-form" class="btn btn-primary">نعم، استعد</button>
    </x-slot:actions>
</x-confirm-modal>

<x-confirm-modal id="delete-modal" title="مسح ملف المعلمة؟" body="لن يُحذف الطلاب ولا الحصص. اكتبي كلمة «حذف» ثم أكّدي.">
    <form method="post" action="{{ route('settings.clear-profile') }}" id="clear-profile-form">
        @csrf
        <div class="field">
            <label for="confirm_delete">اكتبي «حذف»</label>
            <input id="confirm_delete" name="confirm_delete" required placeholder="حذف">
        </div>
    </form>
    <x-slot:actions>
        <button type="submit" form="clear-profile-form" class="btn btn-danger">تأكيد المسح</button>
    </x-slot:actions>
</x-confirm-modal>
