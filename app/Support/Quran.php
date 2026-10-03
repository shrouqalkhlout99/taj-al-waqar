<?php

namespace App\Support;

class Quran
{
    public static function surahs(): array
    {
        return config('quran.surahs');
    }

    /**
     * @return array<string, int>
     */
    public static function ayahCounts(): array
    {
        return config('quran.ayah_counts');
    }

    public static function ayahCount(?string $surah): int
    {
        if ($surah === null || $surah === '') {
            return 0;
        }

        return (int) (self::ayahCounts()[$surah] ?? 0);
    }

    public static function clampAyah(?string $surah, mixed $ayah): ?int
    {
        $ayah = self::nullableInt($ayah);

        if ($ayah === null) {
            return null;
        }

        $max = self::ayahCount($surah);

        if ($max < 1) {
            return $ayah > 0 ? $ayah : null;
        }

        return max(1, min($ayah, $max));
    }

    public static function surahNumber(string $name): ?int
    {
        $index = array_search($name, self::surahs(), true);

        return $index === false ? null : $index + 1;
    }

    public static function rangeText(?array $range): string
    {
        if (! $range || empty($range['surah'])) {
            return '—';
        }

        $from = $range['from'] ?? '';
        $to = $range['to'] ?? '';

        return trim($range['surah'].' '.$from.($to !== '' && $to !== null ? '–'.$to : ''));
    }

    public static function gradeLabel(?string $id): string
    {
        return config('quran.grades')[$id] ?? '—';
    }

    public static function statusLabel(?string $id): string
    {
        return config('quran.statuses')[$id] ?? $id ?? '—';
    }

    public static function greeting(): string
    {
        $hour = now()->hour;

        return $hour < 12 ? 'صباح الخير' : 'مساء الخير';
    }

    public static function arabicDate(?\DateTimeInterface $date = null): string
    {
        return ($date ? now()->setTimestamp($date->getTimestamp()) : now())
            ->locale('ar')
            ->translatedFormat('l، j F Y');
    }

    public static function nextColor(int $count): string
    {
        $colors = config('quran.colors');

        return $colors[$count % count($colors)];
    }

    public static function rangeFromRequest(array $input, string $prefix): array
    {
        $surah = $input["{$prefix}_surah"] ?? '';
        $surahName = is_string($surah) ? $surah : '';

        return [
            'surah' => $surah,
            'from' => self::clampAyah($surahName, $input["{$prefix}_from"] ?? null),
            'to' => self::clampAyah($surahName, $input["{$prefix}_to"] ?? null),
        ];
    }

    public static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
