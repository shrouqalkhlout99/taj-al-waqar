@php
    $range = $range ?? [];
@endphp
<div class="form-grid range-grid" data-surah-controls>
    <div class="field">
        <label>السورة</label>
        <select name="{{ $prefix }}_surah" data-surah-select>
            <option value="">—</option>
            @foreach (config('quran.surahs') as $surahOption)
                <option value="{{ $surahOption }}" @selected(($range['surah'] ?? '') === $surahOption)>{{ $surahOption }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label>من آية</label>
        @include('partials.ayah-select', [
            'name' => $prefix.'_from',
            'value' => $range['from'] ?? '',
            'surah' => $range['surah'] ?? '',
            'allowEmpty' => true,
        ])
    </div>
    <div class="field">
        <label>إلى آية</label>
        @include('partials.ayah-select', [
            'name' => $prefix.'_to',
            'value' => $range['to'] ?? '',
            'surah' => $range['surah'] ?? '',
            'allowEmpty' => true,
        ])
    </div>
</div>
