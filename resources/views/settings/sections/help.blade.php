<div class="card settings-card">
    <h3>المساعدة والدعم</h3>
    <p class="muted mb-14">تاج الوقار منصة محلية لمعلمة القرآن: الطلاب، الحصص، الحفظ، والمساعد الذكي.</p>

    <p class="section-title">أسئلة شائعة</p>
    <div class="faq-list">
        <details class="faq-item">
            <summary>أين تُحفظ البيانات؟</summary>
            <p>في ملف SQLite داخل المشروع. يمكنكِ نسخها من إعدادات البيانات.</p>
        </details>
        <details class="faq-item">
            <summary>كيف أبدأ حصة؟</summary>
            <p>من لوحة اليوم أو المواعيد اضغطي «بدء الحصة»، ثم سجّلي التسميع والحفظ والمراجعة.</p>
        </details>
        <details class="faq-item">
            <summary>المساعد لا يعمل بالذكاء؟</summary>
            <p>أضيفي مفتاح OpenAI من قسم الذكاء الاصطناعي. بدون المفتاح يبقى الشرح المحلي.</p>
        </details>
    </div>

    <p class="section-title">حول تاج الوقار</p>
    <div class="summary-list">
        <div>الاسم: تاج الوقار</div>
        <div>الإصدار: 1.1 (إعدادات المعلمة)</div>
        <div>المنصة: Laravel · تعمل على جهازك</div>
    </div>

    <div class="row-actions mt-14">
        <a class="btn btn-primary" href="mailto:{{ $s['teacher_email'] ?: 'support@taj-alwaqar.local' }}">تواصل مع الدعم</a>
        <a class="btn btn-ghost" href="mailto:{{ $s['teacher_email'] ?: 'support@taj-alwaqar.local' }}?subject={{ rawurlencode('اقتراح ميزة لتاج الوقار') }}">اقتراح ميزة</a>
        <a class="btn btn-outline" href="{{ route('notes.index') }}">الإبلاغ عن مشكلة (الملاحظات)</a>
    </div>
</div>
