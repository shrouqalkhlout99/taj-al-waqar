@php
    $sizeMb = $dbSize > 0 ? number_format($dbSize / 1024 / 1024, 2) : '0.00';
@endphp
<div class="card settings-card">
    <h3>الاشتراك</h3>
    <p class="muted mb-14">تاج الوقار يعمل حالياً كخطة محلية على هذا الجهاز. لا يوجد نظام دفع مدمج.</p>

    <div class="plan-card">
        <div>
            <small>الخطة الحالية</small>
            <strong>محلية — Professional على الجهاز</strong>
        </div>
        <span class="badge done">نشطة</span>
    </div>

    <div class="progress-grid mt-14">
        <div>
            <small>الطلاب</small>
            <b class="stat-number">{{ $studentCount }}</b>
            <span class="muted">بدون حد اشتراك حالياً</span>
        </div>
        <div>
            <small>الذكاء الاصطناعي</small>
            <b class="stat-number">{{ $aiAvailable ? 'متصل' : 'محلي' }}</b>
            <span class="muted">حسب المفتاح في إعدادات المساعد</span>
        </div>
        <div>
            <small>التخزين</small>
            <b class="stat-number">{{ $sizeMb }} MB</b>
            <span class="muted">حجم قاعدة البيانات</span>
        </div>
        <div>
            <small>التجديد</small>
            <b class="stat-number">—</b>
            <span class="muted">لا يوجد تجديد لأن الدفع غير مدمج</span>
        </div>
    </div>

    <div class="row-actions mt-14">
        <button type="button" class="btn btn-primary is-disabled" disabled title="الدفع غير مدمج بعد">ترقية الباقة</button>
        <button type="button" class="btn btn-outline is-disabled" disabled>إدارة الاشتراك</button>
    </div>
    <p class="note-box mt-14">واجهة جاهزة للتكامل لاحقاً. لن ننشئ دفعاً وهمياً أو أرقاماً غير حقيقية.</p>
</div>
