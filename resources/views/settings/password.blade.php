@extends('layouts.app')

@section('title', 'تغيير كلمة المرور')
@section('heading', 'تغيير كلمة المرور')
@section('subheading', 'لحماية حساب المعلمة على هذا الجهاز')

@section('content')
<form class="card settings-card" method="post" action="{{ route('password.update') }}">
    @csrf
    @method('PUT')
    <h3>تغيير كلمة المرور</h3>
    <p class="muted mb-14">أدخلي كلمة المرور الحالية ثم كلمة المرور الجديدة مرتين.</p>

    @if ($errors->any())
        <div class="note-box mb-14" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="field">
        <label for="current_password">كلمة المرور الحالية</label>
        <input id="current_password" name="current_password" type="password" autocomplete="current-password" required class="{{ $errors->has('current_password') ? 'is-invalid' : '' }}">
        @error('current_password')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field mt-12">
        <label for="password">كلمة المرور الجديدة</label>
        <input id="password" name="password" type="password" autocomplete="new-password" required class="{{ $errors->has('password') ? 'is-invalid' : '' }}">
        @error('password')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field mt-12">
        <label for="password_confirmation">تأكيد كلمة المرور الجديدة</label>
        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
    </div>

    <div class="settings-actions row-actions">
        <button type="submit" class="btn btn-primary">حفظ كلمة المرور</button>
        <a class="btn btn-outline" href="{{ route('settings.index', ['tab' => 'account']) }}">إلغاء</a>
    </div>
</form>
@endsection
