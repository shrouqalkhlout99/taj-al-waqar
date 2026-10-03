<?php

namespace Tests\Unit\Support;

use App\Support\Quran;
use Tests\TestCase;

class QuranTest extends TestCase
{
    public function test_every_surah_has_its_authentic_ayah_count(): void
    {
        $counts = Quran::ayahCounts();

        $this->assertSame(config('quran.surahs'), array_keys($counts));
        $this->assertCount(114, $counts);
        $this->assertSame(6236, array_sum($counts));
        $this->assertSame(7, Quran::ayahCount('الفاتحة'));
        $this->assertSame(286, Quran::ayahCount('البقرة'));
        $this->assertSame(120, Quran::ayahCount('المائدة'));
        $this->assertSame(6, Quran::ayahCount('الناس'));
        $this->assertSame(0, Quran::ayahCount(null));
        $this->assertSame(0, Quran::ayahCount('سورة غير موجودة'));
    }

    public function test_clamps_an_ayah_to_the_surah_length(): void
    {
        $this->assertSame(7, Quran::clampAyah('الفاتحة', 8));
        $this->assertSame(286, Quran::clampAyah('البقرة', 300));
        $this->assertSame(1, Quran::clampAyah('الفاتحة', 1));
        $this->assertNull(Quran::clampAyah('الفاتحة', ''));
        $this->assertSame(12, Quran::clampAyah('سورة غير موجودة', 12));
    }

    public function test_range_from_request_clamps_from_and_to_to_the_surah_length(): void
    {
        $range = Quran::rangeFromRequest([
            'recitation_surah' => 'الفاتحة',
            'recitation_from' => 1,
            'recitation_to' => 20,
        ], 'recitation');

        $this->assertSame([
            'surah' => 'الفاتحة',
            'from' => 1,
            'to' => 7,
        ], $range);
    }
}
