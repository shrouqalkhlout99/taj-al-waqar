@php
    $levels = [
        ['قرآن', $student->quran_score ?? 0],
        ['تجويد', $student->tajweed_score ?? 0],
        ['التقدم العام', $student->overall_score ?? 0],
    ];
@endphp
<div class="level-bars">
    @foreach ($levels as [$label, $value])
        <div class="level-bar">
            <div class="level-bar-head">
                <small>{{ $label }}</small>
                <b>{{ $value }}%</b>
            </div>
            <div class="meter"><span style="width: {{ $value }}%"></span></div>
        </div>
    @endforeach
</div>
