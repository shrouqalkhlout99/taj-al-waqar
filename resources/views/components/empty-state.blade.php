@props([
    'title',
    'description' => '',
    'action' => null,
    'actionLabel' => null,
])

<div {{ $attributes->class('empty-state') }}>
    <p class="empty-state-title">{{ $title }}</p>
    @if ($description !== '')
        <p class="empty-state-desc">{{ $description }}</p>
    @endif
    @if ($action && $actionLabel)
        <a class="btn btn-primary" href="{{ $action }}">{{ $actionLabel }}</a>
    @endif
    {{ $slot }}
</div>
