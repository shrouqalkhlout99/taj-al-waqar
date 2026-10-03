@props(['name', 'checked' => false, 'title' => null, 'id' => null])

@php
    $id = $id ?? $name;
    $value = old($name, $checked);
    $isChecked = is_string($value)
        ? in_array($value, ['1', 'true', 'on', 'yes'], true)
        : (bool) $value;
@endphp

<label class="switch">
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1" @checked($isChecked) {{ $attributes }}>
    <span class="switch-ui" aria-hidden="true"></span>
    <span class="sr-only">{{ $title ?? $name }}</span>
</label>
