<?php

namespace App\Support;

use App\Models\Lesson;
use App\Models\ParentContact;
use App\Models\Setting;

class ParentLessonShare
{
    public function __construct(
        public ParentContact $parent,
        public string $message,
        public string $whatsappUrl,
    ) {}

    public static function for(Lesson $lesson): ?self
    {
        if (! Setting::bool('parent_send_summary')) {
            return null;
        }

        $parent = $lesson->student?->parentContact;
        if ($parent === null || ! filled(trim((string) $parent->phone))) {
            return null;
        }

        $base = Setting::whatsappLink($parent->phone);
        if ($base === null) {
            return null;
        }

        $message = self::messageFor($lesson);

        return new self(
            parent: $parent,
            message: $message,
            whatsappUrl: $base.'?text='.rawurlencode($message),
        );
    }

    public static function messageFor(Lesson $lesson): string
    {
        $name = $lesson->student?->name ?: 'الطالبة';
        $date = Quran::arabicDate($lesson->session_date);

        $lines = [
            'السلام عليكم،',
            'ملخص حصة '.$name,
            'التاريخ: '.$date,
            '',
            'التسميع: '.$lesson->recitationText().self::gradeSuffix($lesson->recitation_grade),
            'الحفظ: '.$lesson->newMemText().self::gradeSuffix($lesson->new_mem_grade),
            'المراجعة: '.$lesson->reviewText(),
            '',
            'بارك الله فيكم.',
        ];

        return implode("\n", $lines);
    }

    private static function gradeSuffix(?string $grade): string
    {
        if (! filled($grade)) {
            return '';
        }

        $label = Quran::gradeLabel($grade);
        if ($label === '—') {
            return '';
        }

        return ' ('.$label.')';
    }
}
