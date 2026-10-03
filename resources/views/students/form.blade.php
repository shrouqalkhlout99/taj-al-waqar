@extends('layouts.app')

@section('title', $student->exists ? 'تعديل ملف الطالب' : 'ملف طالب جديد')
@section('heading', $student->exists ? 'تعديل ملف الطالب' : 'ملف طالب جديد')
@section('subheading', 'الاسم، المستوى، وموضع الحفظ — بدون عمر')

@section('content')
<form class="card form-card" method="post" action="{{ $student->exists ? route('students.update', $student) : route('students.store') }}">
    @csrf
    @if ($student->exists) @method('PUT') @endif
    @if ($errors->any())
        <div class="note-box mb-14">{{ $errors->first() }}</div>
    @endif

    <p class="section-title">بيانات التواصل</p>
    <div class="form-grid">
        <div class="field">
            <label>الاسم <span class="req">مطلوب</span></label>
            <input name="name" required value="{{ old('name', $student->name) }}" class="{{ $errors->has('name') ? 'is-invalid' : '' }}">
            @error('name')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label>الجوال <span class="req">مطلوب</span></label>
            <input name="phone" required value="{{ old('phone', $student->phone) }}" class="{{ $errors->has('phone') ? 'is-invalid' : '' }}">
            @error('phone')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label>المستوى <span class="req">مطلوب</span></label>
            <select name="level" required class="{{ $errors->has('level') ? 'is-invalid' : '' }}">
                @foreach (config('quran.levels') as $level)
                    <option value="{{ $level }}" @selected(old('level', $student->level) === $level)>{{ $level }}</option>
                @endforeach
            </select>
            @error('level')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label>طريقة الشرح المناسبة <span class="req">مطلوب</span></label>
            <select name="style" required class="{{ $errors->has('style') ? 'is-invalid' : '' }}">
                @foreach (config('quran.styles') as $style)
                    <option value="{{ $style }}" @selected(old('style', $student->style) === $style)>{{ $style }}</option>
                @endforeach
            </select>
            @error('style')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field full">
            <label>رابط Google Meet</label>
            <input name="meet_link" value="{{ old('meet_link', $student->meet_link) }}" placeholder="https://meet.google.com/...">
        </div>
    </div>

    <p class="section-title">ولي الأمر</p>
    <p class="muted mb-14">اختياري. ولي واحد لكل طالب، بلا حساب دخول وبلا إشعارات.</p>
    <div class="form-grid">
        <div class="field">
            <label>اسم ولي الأمر</label>
            <input name="parent_name" value="{{ old('parent_name', $student->parentContact?->name) }}" class="{{ $errors->has('parent_name') ? 'is-invalid' : '' }}">
            @error('parent_name')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label>جوال ولي الأمر</label>
            <input name="parent_phone" value="{{ old('parent_phone', $student->parentContact?->phone) }}">
        </div>
        <div class="field">
            <label>صلة القرابة</label>
            <select name="parent_relation">
                <option value="">—</option>
                @foreach (config('quran.parent_relations') as $relation)
                    <option value="{{ $relation }}" @selected(old('parent_relation', $student->parentContact?->relation) === $relation)>{{ $relation }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <p class="section-title">موضع القرآن</p>
    <div class="form-grid" data-surah-controls>
        <div class="field">
            <label>آخر سورة وصل لها</label>
            <select name="current_surah" data-surah-select>
                @foreach (config('quran.surahs') as $surah)
                    <option value="{{ $surah }}" @selected(old('current_surah', $student->current_surah) === $surah)>{{ $surah }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label>آخر آية</label>
            @include('partials.ayah-select', [
                'name' => 'last_ayah',
                'value' => old('last_ayah', $student->last_ayah),
                'surah' => old('current_surah', $student->current_surah),
                'allowEmpty' => true,
            ])
        </div>
    </div>

    <p class="section-title">الحفظ الجديد</p>
    @include('partials.range-fields', ['prefix' => 'new_mem', 'range' => old() ? \App\Support\Quran::rangeFromRequest(old(), 'new_mem') : $student->new_mem])
    <p class="section-title">التسميع</p>
    @include('partials.range-fields', ['prefix' => 'recitation', 'range' => old() ? \App\Support\Quran::rangeFromRequest(old(), 'recitation') : $student->recitation])
    <p class="section-title">المراجعة</p>
    @include('partials.range-fields', ['prefix' => 'review', 'range' => old() ? \App\Support\Quran::rangeFromRequest(old(), 'review') : $student->review])

    <p class="section-title">ملاحظة خاصة</p>
    <div class="field full">
        <label>ملاحظة خاصة</label>
        <textarea name="last_note">{{ old('last_note', $student->last_note) }}</textarea>
        <p class="muted">ملاحظة خاصة للمعلمة. تظهر في الملف والجلسة وصفحة الملاحظات، ولا تُعرض في تقرير المشاركة.</p>
    </div>

    <div class="modal-actions">
        <a class="btn btn-outline" href="{{ route('students.index') }}">إلغاء</a>
        <div class="row-actions">
            @if ($student->exists)
                <button type="submit" form="delete-student" class="btn btn-danger" onclick="return confirm('حذف ملف الطالب؟')">حذف</button>
            @endif
            <button class="btn btn-primary" type="submit">حفظ الملف</button>
        </div>
    </div>
</form>
@if ($student->exists)
    <form id="delete-student" method="post" action="{{ route('students.destroy', $student) }}">
        @csrf
        @method('DELETE')
    </form>
@endif
@endsection
