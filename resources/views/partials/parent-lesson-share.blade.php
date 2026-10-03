@php
    /** @var \App\Support\ParentLessonShare $parentShare */
@endphp
<section class="parent-share" role="region" aria-label="مشاركة ملخص الحصة">
    <p class="parent-share-title">مشاركة ملخص الحصة</p>
    <p class="muted mb-10">مع {{ $parentShare->parent->name }} — لا يُرسل شيء تلقائيًا. تنسخين النص أو تفتحين واتساب ثم ترسلين بنفسك.</p>
    <textarea id="parent-summary-text" class="hidden" readonly>{{ $parentShare->message }}</textarea>
    <div class="parent-share-actions">
        <button type="button" class="btn btn-ghost" data-copy-parent-summary>نسخ الملخص</button>
        <a class="btn btn-primary" href="{{ $parentShare->whatsappUrl }}" target="_blank" rel="noopener noreferrer">مشاركة عبر WhatsApp</a>
    </div>
</section>
@push('scripts')
<script>
    document.querySelectorAll('[data-copy-parent-summary]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const source = document.getElementById('parent-summary-text');
            if (!source) return;
            navigator.clipboard.writeText(source.value).then(() => {
                const original = btn.textContent;
                btn.textContent = 'تم نسخ الملخص';
                setTimeout(() => { btn.textContent = original; }, 1600);
            });
        });
    });
</script>
@endpush
