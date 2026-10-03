@extends('layouts.app')

@section('title', 'الكتاب والشرح')
@section('heading', 'الكتاب والشرح')
@section('subheading', 'جهّزي شرح الآية حسب مستوى الطالب')

@section('content')
<div class="card">
    <p>اختاري الطالب والآية، ثم اضغطي «جهّزي الشرح» ليظهر نص الآية وشرح مناسب لمستواه.</p>
    @unless (\App\Models\Setting::aiEnabled())
        <div class="note-box mb-14">المساعد الذكي غير متاح حالياً. الشرح المحلي الجاهز يعمل بدون مفتاح.
            <a class="btn btn-ghost" href="{{ route('book.index') }}">إعادة المحاولة</a>
        </div>
    @endunless
    @include('partials.explain-form', ['source' => 'book'])
    @include('partials.explain-result')
</div>
@endsection
