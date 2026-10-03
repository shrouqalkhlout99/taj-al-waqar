@props(['id' => 'confirm-modal', 'title', 'body' => ''])

<div class="modal-overlay hidden" id="{{ $id }}" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">
    <div class="modal-box">
        <h3 id="{{ $id }}-title">{{ $title }}</h3>
        @if ($body !== '')
            <p class="muted">{{ $body }}</p>
        @endif
        {{ $slot }}
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" data-close-modal="{{ $id }}">إلغاء</button>
            {{ $actions ?? '' }}
        </div>
    </div>
</div>
