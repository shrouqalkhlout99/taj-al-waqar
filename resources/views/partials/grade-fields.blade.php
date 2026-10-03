<div class="grades">
    @foreach (config('quran.grades') as $id => $label)
        <label>
            <input type="radio" name="{{ $name }}" value="{{ $id }}" @checked(($value ?? 'good') === $id)>
            <span>{{ $label }}</span>
        </label>
    @endforeach
</div>
