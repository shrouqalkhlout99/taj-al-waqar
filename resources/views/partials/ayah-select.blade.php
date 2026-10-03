@php
    $name = $name ?? 'ayah';
    $surah = is_string($surah ?? null) ? $surah : '';
    $allowEmpty = $allowEmpty ?? true;
    $required = $required ?? false;
    $max = \App\Support\Quran::ayahCount($surah);
    $selected = \App\Support\Quran::clampAyah($surah, $value ?? null);
@endphp
<select name="{{ $name }}" data-ayah-select @if ($allowEmpty) data-ayah-empty="1" @endif @if ($required) required @endif>
    @if ($allowEmpty || $max < 1)
        <option value="">—</option>
    @endif
    @if ($max > 0)
        @for ($n = 1; $n <= $max; $n++)
            <option value="{{ $n }}" @selected($selected === $n)>{{ $n }}</option>
        @endfor
    @endif
</select>
