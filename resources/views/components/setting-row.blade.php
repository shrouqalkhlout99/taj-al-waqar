@props(['title', 'description' => '', 'for' => null])

<div {{ $attributes->class('setting-row') }}>
    <div class="setting-row-text">
        <label class="setting-title" @if($for) for="{{ $for }}" @endif>{{ $title }}</label>
        @if ($description !== '')
            <p class="setting-desc">{{ $description }}</p>
        @endif
    </div>
    <div class="setting-row-control">
        {{ $slot }}
    </div>
</div>
