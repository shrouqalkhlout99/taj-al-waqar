@if (($lessonReminders ?? collect())->isNotEmpty())
    <div class="lesson-reminder" role="status">
        <p class="lesson-reminder-title">تذكير الحصة</p>
        <ul>
            @foreach ($lessonReminders as $reminder)
                @continue(! $reminder->student)
                <li>
                    <span>{{ $reminder->student->name }} · {{ $reminder->timeLabel() }}</span>
                    @if ($reminder->canStart())
                        <a class="btn btn-primary btn-start" href="{{ route('lessons.start', $reminder) }}">ابدئي الحصة</a>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif
