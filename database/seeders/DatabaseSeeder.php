<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Appointment;
use App\Models\ParentContact;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Account::currentId();

        Setting::setValue('teacher_name', Setting::getValue('teacher_name', 'معلمة قرآن'));
        Setting::setValue('openai_model', Setting::getValue('openai_model', 'gpt-4o-mini'));

        $this->call(DefaultTeacherSeeder::class);

        if (! Student::withoutGlobalScopes()->exists()) {

            $students = [
                [
                    'name' => 'أحمد محمد',
                    'phone' => '0500000001',
                    'level' => 'متوسط',
                    'style' => 'قصة مبسطة',
                    'meet_link' => 'https://meet.google.com/abc-defg-hij',
                    'color' => '#6b4bd6',
                    'current_surah' => 'البقرة',
                    'last_ayah' => 30,
                    'new_mem' => ['surah' => 'البقرة', 'from' => 31, 'to' => 35],
                    'recitation' => ['surah' => 'البقرة', 'from' => 1, 'to' => 20],
                    'review' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
                    'last_note' => 'خلط بين الآيتين 18 و19، يحتاج تثبيت التجويد في الغنة.',
                    'last_note_date' => now()->subDays(5)->toDateString(),
                ],
                [
                    'name' => 'سارة علي',
                    'phone' => '0500000002',
                    'level' => 'مبتدئ',
                    'style' => 'شرح مبسط',
                    'meet_link' => 'https://meet.google.com/sara-meet-xyz',
                    'color' => '#1f9d6a',
                    'current_surah' => 'الفاتحة',
                    'last_ayah' => 7,
                    'new_mem' => ['surah' => 'البقرة', 'from' => 1, 'to' => 5],
                    'recitation' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
                    'review' => ['surah' => 'الفاتحة', 'from' => 1, 'to' => 7],
                    'last_note' => 'نطق جيد مع بطء بسيط. شجعيها على التكرار اليومي.',
                    'last_note_date' => now()->subDays(3)->toDateString(),
                ],
                [
                    'name' => 'مريم أحمد',
                    'phone' => '0500000003',
                    'level' => 'متقدم',
                    'style' => 'شرح تفصيلي',
                    'meet_link' => 'https://meet.google.com/mariam-quran',
                    'color' => '#d97706',
                    'current_surah' => 'آل عمران',
                    'last_ayah' => 40,
                    'new_mem' => ['surah' => 'آل عمران', 'from' => 41, 'to' => 50],
                    'recitation' => ['surah' => 'آل عمران', 'from' => 20, 'to' => 40],
                    'review' => ['surah' => 'البقرة', 'from' => 255, 'to' => 257],
                    'last_note' => 'حفظ متين، ركّزي على الوقف والابتداء.',
                    'last_note_date' => now()->subDays(2)->toDateString(),
                ],
                [
                    'name' => 'محمد خالد',
                    'phone' => '0500000004',
                    'level' => 'متوسط',
                    'style' => 'أسئلة ونقاش',
                    'meet_link' => 'https://meet.google.com/mk-quran-class',
                    'color' => '#db2777',
                    'current_surah' => 'يس',
                    'last_ayah' => 12,
                    'new_mem' => ['surah' => 'يس', 'from' => 13, 'to' => 27],
                    'recitation' => ['surah' => 'يس', 'from' => 1, 'to' => 12],
                    'review' => ['surah' => 'الملك', 'from' => 1, 'to' => 10],
                    'last_note' => 'يحتاج مراجعة مخارج الحروف الحلقية.',
                    'last_note_date' => now()->subDay()->toDateString(),
                ],
            ];

            $times = ['16:00', '17:00', '18:00', '19:00'];

            foreach ($students as $i => $data) {
                $student = Student::create($data);

                if ($i === 0) {
                    ParentContact::query()->create([
                        'student_id' => $student->id,
                        'name' => 'محمد والد أحمد',
                        'phone' => '0501112233',
                        'relation' => 'أب',
                    ]);
                }

                Appointment::create([
                    'student_id' => $student->id,
                    'scheduled_date' => now()->toDateString(),
                    'scheduled_time' => $times[$i],
                    'status' => 'upcoming',
                ]);
            }
        }
    }
}
