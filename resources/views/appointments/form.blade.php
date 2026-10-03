@extends('layouts.app')

@section('title', $appointment->exists ? 'تعديل الموعد' : 'موعد جديد')
@section('heading', $appointment->exists ? 'تعديل الموعد' : 'موعد جديد')
@section('subheading', 'حدّدي الطالب والتاريخ والساعة بوضوح')

@section('content')
<form class="card form-card" method="post" action="{{ $appointment->exists ? route('appointments.update', $appointment) : route('appointments.store') }}">
    @csrf
    @if ($appointment->exists) @method('PUT') @endif
    @if ($errors->any())
        <div class="note-box mb-14">{{ $errors->first() }}</div>
    @endif
    <div class="form-grid">
        <div class="field">
            <label>الطالب</label>
            <select name="student_id" required class="{{ $errors->has('student_id') ? 'is-invalid' : '' }}">
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected((int) old('student_id', $appointment->student_id) === $student->id)>{{ $student->name }}</option>
                @endforeach
            </select>
            @error('student_id')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label>التاريخ</label>
            <input type="date" name="scheduled_date" required class="{{ $errors->has('scheduled_date') ? 'is-invalid' : '' }}" value="{{ old('scheduled_date', optional($appointment->scheduled_date)->toDateString() ?? $appointment->scheduled_date) }}">
            @error('scheduled_date')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label>الوقت</label>
            <input type="time" name="scheduled_time" required class="{{ $errors->has('scheduled_time') ? 'is-invalid' : '' }}" value="{{ old('scheduled_time', $appointment->exists ? $appointment->timeLabel() : $appointment->scheduled_time) }}">
            @error('scheduled_time')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field full">
            <p class="muted">المدة الافتراضية من الإعدادات: {{ $lessonMinutes ?? 45 }} دقيقة، مع فاصل بين الحصص إن كان مفعّلاً.</p>
        </div>
    </div>
    <div class="modal-actions">
        <a class="btn btn-outline" href="{{ route('appointments.index') }}">إلغاء</a>
        <button class="btn btn-primary">حفظ الموعد</button>
    </div>
</form>
@endsection
