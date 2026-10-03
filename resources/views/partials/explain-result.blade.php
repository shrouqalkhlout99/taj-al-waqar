@if ($result)
    <div class="card result-card">
        <div class="level">شرح مناسب لـ {{ $selected->name }} · {{ $selected->style }}</div>
        <div class="verse">{{ $result['verse'] ? '﴿'.$result['verse'].'﴾' : $surah.' '.$ayah }}</div>
        <div class="ai-box">{{ $result['body'] }}</div>
        <div class="point-box"><b>نقطة مهمة لـ {{ $selected->name }}:</b><br>{{ $result['point'] }}</div>
    </div>
@endif
