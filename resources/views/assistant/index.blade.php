@extends('layouts.app')

@section('title', 'المساعد الذكي')
@section('heading', 'المساعد الذكي — شرح الآية')
@section('subheading', $selected ? 'الطالب: '.$selected->name.' | السورة: '.$surah.' | الآية: '.$ayah : 'اختاري طالباً ثم الآية')

@section('content')
<div class="card">
    @unless (\App\Models\Setting::aiEnabled())
        <div class="note-box mb-14">المساعد الذكي غير متاح حالياً. فعّليه من <a class="plain-link" href="{{ route('settings.index', ['tab' => 'ai']) }}"><u>الإعدادات</u></a>. الشرح المحلي ما زال يعمل.
            <a class="btn btn-ghost" href="{{ route('assistant.index') }}">إعادة المحاولة</a>
        </div>
    @endunless
    @include('partials.explain-form', ['source' => 'assistant'])
    @include('partials.explain-result')
</div>

@if ($selected)
    <div class="card mt-14">
        <h3>صياغة للمعلمة</h3>
        <p class="muted mb-14">نصوص تُنسخ. الحصة لا تعتمد على هذه الصياغة، وإن تعذّر الاتصال يظهر النص المحلي.</p>
        <form class="form-grid" method="post" action="{{ route('assistant.compose') }}">
            @csrf
            <input type="hidden" name="student_id" value="{{ $selected->id }}">
            <div class="field">
                <label>المهمة</label>
                <select name="task" required>
                    <option value="parent" @selected(($compose['task'] ?? '') === 'parent')>رسالة ولي الأمر</option>
                    <option value="plan" @selected(($compose['task'] ?? '') === 'plan')>صياغة اقتراح الحصة</option>
                    <option value="followup" @selected(($compose['task'] ?? '') === 'followup')>نقاط متابعة</option>
                </select>
            </div>
            <div class="field">
                <label>&nbsp;</label>
                <button class="btn btn-primary" type="submit">جهّزي النص</button>
            </div>
        </form>
        @if (filled($compose['text'] ?? null))
            <div class="compose-result mt-14">
                <textarea id="compose-text" class="compose-text" readonly>{{ $compose['text'] }}</textarea>
                <button type="button" class="btn btn-ghost mt-12" data-copy-from="compose-text">نسخ</button>
            </div>
        @endif
    </div>
@endif
@endsection
