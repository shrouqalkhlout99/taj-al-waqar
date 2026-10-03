<form class="form-grid" method="post" action="{{ route('assistant.explain') }}" data-surah-controls>
    @csrf
    <input type="hidden" name="source" value="{{ $source }}">
    <div class="field">
        <label>الطالب</label>
        <select name="student_id" required>
            @foreach ($students as $student)
                <option value="{{ $student->id }}" @selected($selected && $selected->id === $student->id)>{{ $student->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label>السورة</label>
        <select name="surah" data-surah-select>
            @foreach (config('quran.surahs') as $item)
                <option value="{{ $item }}" @selected($surah === $item)>{{ $item }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label>الآية</label>
        @include('partials.ayah-select', [
            'name' => 'ayah',
            'value' => $ayah,
            'surah' => $surah,
            'allowEmpty' => false,
            'required' => true,
        ])
    </div>
    <div class="field">
        <label>&nbsp;</label>
        <button class="btn btn-primary" type="submit">جهّزي الشرح</button>
    </div>
</form>
