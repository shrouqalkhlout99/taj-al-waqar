@extends('layouts.auth')

@section('title', 'تسجيل الدخول')

@section('content')
<form class="card auth-card" method="post" action="{{ route('login') }}">
    @csrf
    <div class="auth-brand">
        <img class="brand-logo" src="{{ asset('images/brand/logo-mark.svg') }}" alt="{{ config('app.name', 'تاج الوقار') }}">
        <div>
            <h1>{{ config('app.name', 'تاج الوقار') }}</h1>
            <p class="muted">دخول المعلمة إلى النظام المحلي</p>
        </div>
    </div>

    <h3>تسجيل الدخول</h3>
    <p class="muted mb-14">أدخلي بريدك وكلمة المرور المحفوظة على هذا الجهاز.</p>

    @if ($errors->any())
        <div class="note-box mb-14">{{ $errors->first() }}</div>
    @endif

    <div class="field">
        <label for="email">البريد الإلكتروني</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus class="{{ $errors->has('email') ? 'is-invalid' : '' }}">
        @error('email')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field mt-12">
        <label for="password">كلمة المرور</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required class="{{ $errors->has('password') ? 'is-invalid' : '' }}">
        @error('password')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="auth-actions">
        <button type="submit" class="btn btn-primary">دخول</button>
    </div>
</form>
@endsection
