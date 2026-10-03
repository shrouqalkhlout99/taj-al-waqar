@php
    $todayKey = now()->toDateString();
    $grouped = [
        'اليوم' => $appointments->filter(fn ($item) => $item->scheduled_date?->toDateString() === $todayKey),
        'القادمة' => $appointments->filter(fn ($item) => $item->scheduled_date?->toDateString() > $todayKey),
        'السابقة' => $appointments->filter(fn ($item) => $item->scheduled_date?->toDateString() < $todayKey),
    ];
@endphp
@extends('layouts.app')

@section('title', 'المواعيد')
@section('heading', 'المواعيد')
@section('subheading', 'كل حصة باسم الطالب ووقتها، حتى لو تغيّر الموعد')

@section('content')
<div class="toolbar">
    <a class="btn btn-primary" href="{{ route('appointments.create') }}">إضافة موعد</a>
</div>
<div class="card">
    @if ($appointments->isEmpty())
        <x-empty-state
            title="لا توجد مواعيد بعد"
            description="أضيفي أول موعد ليظهر في جدول اليوم ويمكن بدء الحصة منه."
            :action="route('appointments.create')"
            action-label="إضافة موعد"
        />
    @else
        @foreach ($grouped as $label => $items)
            @continue($items->isEmpty())
            <h3 class="section-title">{{ $label }}</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>الوقت</th>
                            <th>الطالب</th>
                            <th>الحالة</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $appointment)
                            <tr>
                                <td>{{ $appointment->scheduled_date?->locale('ar')->translatedFormat('j F Y') }}</td>
                                <td>{{ $appointment->timeLabel() }}</td>
                                <td>{{ $appointment->student?->name ?? 'طالب محذوف' }}</td>
                                <td><span class="badge {{ $appointment->status }}">{{ $appointment->statusLabel() }}</span></td>
                                <td class="row-actions col-actions">
                                    @if ($allowReschedule ?? true)
                                        <a class="btn btn-ghost" href="{{ route('appointments.edit', $appointment) }}">تعديل</a>
                                    @endif
                                    @if ($appointment->status !== 'cancelled' && $appointment->status !== 'done')
                                        <button
                                            type="button"
                                            class="btn btn-danger"
                                            data-open-modal="cancel-appointment-modal"
                                            data-cancel-action="{{ route('appointments.cancel', $appointment) }}"
                                        >إلغاء</button>
                                    @endif
                                    @if ($appointment->canStart())
                                        <a class="btn btn-primary" href="{{ route('lessons.start', $appointment) }}">ابدئي الحصة</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif
</div>

<form id="cancel-appointment-form" method="post" class="hidden">
    @csrf
</form>
<x-confirm-modal id="cancel-appointment-modal" title="إلغاء الموعد؟" body="سيتم إلغاء هذا الموعد. لن تُحذف الحصة السابقة إن وُجدت، وسيظهر الموعد كملغى فقط.">
    <x-slot:actions>
        <button type="submit" form="cancel-appointment-form" class="btn btn-danger">نعم، ألغِ الموعد</button>
    </x-slot>
</x-confirm-modal>
@endsection

@push('scripts')
<script>
    const cancelForm = document.getElementById('cancel-appointment-form');
    document.querySelectorAll('[data-open-modal]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const action = btn.getAttribute('data-cancel-action');
            if (cancelForm && action) {
                cancelForm.setAttribute('action', action);
            }
            document.getElementById(btn.getAttribute('data-open-modal'))?.classList.remove('hidden');
        });
    });
    document.querySelectorAll('[data-close-modal]').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.getElementById(btn.getAttribute('data-close-modal'))?.classList.add('hidden');
        });
    });
</script>
@endpush
