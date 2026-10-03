<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StudentsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsTeacher = true;

    public function test_creates_a_student_from_valid_payload(): void
    {
        $response = $this->from(route('students.create'))
            ->post(route('students.store'), $this->studentPayload([
                'name' => 'هند الزهراني',
            ]));

        $student = Student::query()->first();
        $this->assertNotNull($student);
        $response->assertRedirect(route('students.index', ['id' => $student->id]))
            ->assertSessionHas('success');

        $this->assertSame('هند الزهراني', $student->name);
        $this->assertSame('0501234567', $student->phone);
        $this->assertSame('مبتدئ', $student->level);
        $this->assertSame('شرح مبسط', $student->style);
        $this->assertSame('الفاتحة', $student->current_surah);
        $this->assertSame(7, $student->last_ayah);
        $this->assertSame(['surah' => 'البقرة', 'from' => 1, 'to' => 5], $student->new_mem);
        $this->assertSame(['surah' => 'الفاتحة', 'from' => 1, 'to' => 7], $student->recitation);
        $this->assertSame(8, $student->quran_score);
        $this->assertSame(0, $student->tajweed_score);
        $this->assertSame(4, $student->overall_score);
        $this->assertSame('ملاحظة تجريبية', $student->last_note);
        $this->assertSame(now()->toDateString(), $student->last_note_date?->toDateString());
    }

    public function test_rejects_student_without_a_name(): void
    {
        $this->from(route('students.create'))
            ->post(route('students.store'), $this->studentPayload([
                'name' => '',
            ]))
            ->assertRedirect(route('students.create'))
            ->assertSessionHasErrors(['name']);

        $this->assertSame(0, Student::query()->count());
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function requiredStudentFields(): array
    {
        return [
            'phone' => ['phone', 'حقل الجوال مطلوب.'],
            'level' => ['level', 'حقل المستوى مطلوب.'],
            'style' => ['style', 'حقل طريقة الشرح مطلوب.'],
        ];
    }

    #[DataProvider('requiredStudentFields')]
    public function test_rejects_student_when_a_required_field_is_empty(string $field, string $message): void
    {
        $this->from(route('students.create'))
            ->post(route('students.store'), $this->studentPayload([
                $field => '',
            ]))
            ->assertRedirect(route('students.create'))
            ->assertSessionHasErrors([$field => $message]);

        $this->assertSame(0, Student::query()->count());
    }

    public function test_create_form_marks_name_phone_level_and_style_required(): void
    {
        $html = $this->get(route('students.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('name="name" required', $html);
        $this->assertStringContainsString('name="phone" required', $html);
        $this->assertStringContainsString('name="level" required', $html);
        $this->assertStringContainsString('name="style" required', $html);
        $this->assertStringContainsString('الاسم <span class="req">مطلوب</span>', $html);
        $this->assertStringContainsString('الجوال <span class="req">مطلوب</span>', $html);
        $this->assertStringContainsString('المستوى <span class="req">مطلوب</span>', $html);
        $this->assertStringContainsString('طريقة الشرح المناسبة <span class="req">مطلوب</span>', $html);
    }

    public function test_create_form_limits_last_ayah_options_to_the_surah_length(): void
    {
        $html = $this->get(route('students.create'))
            ->assertOk()
            ->getContent();

        $this->assertNamedSelectAllowsAyah($html, 'last_ayah', 7);
        $this->assertStringNotContainsString('name="last_ayah" type="number"', $html);
    }

    public function test_edit_form_limits_last_ayah_options_to_the_students_surah(): void
    {
        $student = Student::factory()->create([
            'current_surah' => 'البقرة',
            'last_ayah' => 30,
        ]);

        $html = $this->get(route('students.edit', $student))
            ->assertOk()
            ->getContent();

        $this->assertNamedSelectAllowsAyah($html, 'last_ayah', 286);
    }

    public function test_rejects_last_ayah_beyond_the_current_surah_length(): void
    {
        $this->from(route('students.create'))
            ->post(route('students.store'), $this->studentPayload([
                'last_ayah' => 8,
            ]))
            ->assertRedirect(route('students.create'))
            ->assertSessionHasErrors(['last_ayah' => 'رقم الآية يتجاوز عدد آيات هذه السورة.']);

        $this->assertSame(0, Student::query()->count());
    }

    public function test_clamps_range_ayahs_to_the_surah_length(): void
    {
        $this->from(route('students.create'))
            ->post(route('students.store'), $this->studentPayload([
                'recitation_to' => 20,
            ]))
            ->assertSessionHasNoErrors();

        $student = Student::query()->first();
        $this->assertNotNull($student);
        $this->assertSame(['surah' => 'الفاتحة', 'from' => 1, 'to' => 7], $student->recitation);
    }

    public function test_updates_an_existing_student(): void
    {
        $student = Student::factory()->create([
            'name' => 'طالب قديم',
            'last_note' => 'ملاحظة سابقة',
        ]);

        $this->from(route('students.edit', $student))
            ->put(route('students.update', $student), $this->studentPayload([
                'name' => 'طالب محدّث',
                'level' => 'متوسط',
                'last_note' => 'ملاحظة جديدة',
            ]))
            ->assertRedirect(route('students.index', ['id' => $student->id]))
            ->assertSessionHas('success');

        $student->refresh();

        $this->assertSame('طالب محدّث', $student->name);
        $this->assertSame('متوسط', $student->level);
        $this->assertSame('ملاحظة جديدة', $student->last_note);
        $this->assertSame(now()->toDateString(), $student->last_note_date?->toDateString());
    }

    public function test_keeps_the_follow_up_note_date_when_the_note_does_not_change(): void
    {
        $student = Student::factory()->create([
            'name' => 'طالب قديم',
            'last_note' => 'ملاحظة ثابتة',
            'last_note_date' => '2026-08-01',
        ]);

        $this->from(route('students.edit', $student))
            ->put(route('students.update', $student), $this->studentPayload([
                'name' => 'طالب محدّث',
                'last_note' => 'ملاحظة ثابتة',
            ]))
            ->assertRedirect(route('students.index', ['id' => $student->id]));

        $student->refresh();

        $this->assertSame('طالب محدّث', $student->name);
        $this->assertSame('ملاحظة ثابتة', $student->last_note);
        $this->assertSame('2026-08-01', $student->last_note_date?->toDateString());
    }

    public function test_clears_the_follow_up_note_date_when_the_note_is_removed(): void
    {
        $student = Student::factory()->create([
            'last_note' => 'ملاحظة سابقة',
            'last_note_date' => '2026-08-01',
        ]);

        $this->from(route('students.edit', $student))
            ->put(route('students.update', $student), $this->studentPayload([
                'last_note' => '',
            ]))
            ->assertRedirect(route('students.index', ['id' => $student->id]));

        $student->refresh();

        $this->assertNull($student->last_note);
        $this->assertNull($student->last_note_date);
    }

    public function test_deletes_a_student(): void
    {
        $student = Student::factory()->create([
            'name' => 'طالب للحذف',
        ]);

        $this->from(route('students.edit', $student))
            ->delete(route('students.destroy', $student))
            ->assertRedirect(route('students.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($student);
    }

    public function test_student_card_shows_the_phone_number(): void
    {
        $student = Student::factory()->create([
            'name' => 'هند الزهراني',
            'phone' => '0501234567',
        ]);

        $this->get(route('students.index', ['id' => $student->id]))
            ->assertOk()
            ->assertSee('هند الزهراني')
            ->assertSee('0501234567')
            ->assertDontSee('ولي الأمر');
    }

    public function test_searches_students_by_name(): void
    {
        Student::factory()->create(['name' => 'هند الزهراني']);
        Student::factory()->create(['name' => 'خالد العمري']);

        $this->get(route('students.index', ['q' => 'هند']))
            ->assertOk()
            ->assertSee('هند الزهراني')
            ->assertDontSee('خالد العمري');
    }

    public function test_creates_a_student_with_a_parent_contact(): void
    {
        $response = $this->from(route('students.create'))
            ->post(route('students.store'), $this->studentPayload([
                'name' => 'هند الزهراني',
                'parent_name' => 'نورة والدة هند',
                'parent_phone' => '0551234567',
                'parent_relation' => 'أم',
            ]));

        $student = Student::query()->first();
        $this->assertNotNull($student);
        $response->assertRedirect(route('students.index', ['id' => $student->id]));

        $this->assertDatabaseCount('parent_contacts', 1);
        $this->assertDatabaseHas('parent_contacts', [
            'student_id' => $student->id,
            'name' => 'نورة والدة هند',
            'phone' => '0551234567',
            'relation' => 'أم',
        ]);
    }

    public function test_creates_a_student_without_a_parent_contact(): void
    {
        $this->from(route('students.create'))
            ->post(route('students.store'), $this->studentPayload())
            ->assertRedirect();

        $this->assertDatabaseCount('parent_contacts', 0);
    }

    public function test_updates_the_same_parent_contact_instead_of_creating_another(): void
    {
        $student = Student::factory()->create();
        $student->parentContact()->create([
            'name' => 'ولي قديم',
            'phone' => '0500000001',
            'relation' => 'أب',
        ]);

        $this->from(route('students.edit', $student))
            ->put(route('students.update', $student), $this->studentPayload([
                'name' => $student->name,
                'parent_name' => 'ولي محدّث',
                'parent_phone' => '0500000002',
                'parent_relation' => 'أم',
            ]))
            ->assertRedirect(route('students.index', ['id' => $student->id]));

        $this->assertDatabaseCount('parent_contacts', 1);
        $this->assertDatabaseHas('parent_contacts', [
            'student_id' => $student->id,
            'name' => 'ولي محدّث',
            'phone' => '0500000002',
            'relation' => 'أم',
        ]);
    }

    public function test_clears_parent_fields_and_deletes_the_contact(): void
    {
        $student = Student::factory()->create();
        $student->parentContact()->create([
            'name' => 'ولي للحذف',
            'phone' => '0500000009',
            'relation' => 'أب',
        ]);

        $this->from(route('students.edit', $student))
            ->put(route('students.update', $student), $this->studentPayload([
                'name' => $student->name,
                'parent_name' => '',
                'parent_phone' => '',
                'parent_relation' => '',
            ]))
            ->assertRedirect(route('students.index', ['id' => $student->id]));

        $this->assertDatabaseCount('parent_contacts', 0);
    }

    public function test_keeps_the_parent_contact_when_parent_fields_are_omitted(): void
    {
        $student = Student::factory()->create();
        $student->parentContact()->create([
            'name' => 'ولي ثابت',
            'phone' => '0500000011',
            'relation' => 'جد',
        ]);

        $this->from(route('students.edit', $student))
            ->put(route('students.update', $student), $this->studentPayload([
                'name' => 'اسم محدّث',
            ]))
            ->assertRedirect(route('students.index', ['id' => $student->id]));

        $this->assertDatabaseHas('parent_contacts', [
            'student_id' => $student->id,
            'name' => 'ولي ثابت',
            'phone' => '0500000011',
            'relation' => 'جد',
        ]);
    }

    public function test_requires_a_parent_name_when_a_parent_phone_is_present(): void
    {
        $this->from(route('students.create'))
            ->post(route('students.store'), $this->studentPayload([
                'parent_name' => '',
                'parent_phone' => '0551234567',
            ]))
            ->assertRedirect(route('students.create'))
            ->assertSessionHasErrors(['parent_name']);

        $this->assertSame(0, Student::query()->count());
        $this->assertDatabaseCount('parent_contacts', 0);
    }

    public function test_rejects_an_unknown_parent_relation(): void
    {
        $this->from(route('students.create'))
            ->post(route('students.store'), $this->studentPayload([
                'parent_name' => 'نورة والدة هند',
                'parent_relation' => 'جار',
            ]))
            ->assertRedirect(route('students.create'))
            ->assertSessionHasErrors(['parent_relation']);

        $this->assertDatabaseCount('parent_contacts', 0);
    }

    public function test_student_card_shows_the_parent_contact(): void
    {
        $student = Student::factory()->create([
            'name' => 'هند الزهراني',
        ]);
        $student->parentContact()->create([
            'name' => 'نورة والدة هند',
            'phone' => '0551234567',
            'relation' => 'أم',
        ]);

        $this->get(route('students.index', ['id' => $student->id]))
            ->assertOk()
            ->assertSee('ولي الأمر')
            ->assertSee('نورة والدة هند')
            ->assertSee('0551234567')
            ->assertSee('أم')
            ->assertSee('tel:0551234567', false);
    }

    public function test_searches_students_by_parent_name_and_phone(): void
    {
        $hind = Student::factory()->create(['name' => 'هند الزهراني']);
        $hind->parentContact()->create([
            'name' => 'نورة والدة هند',
            'phone' => '0551234567',
            'relation' => 'أم',
        ]);
        Student::factory()->create(['name' => 'خالد العمري']);

        $this->get(route('students.index', ['q' => 'نورة']))
            ->assertOk()
            ->assertSee('هند الزهراني')
            ->assertDontSee('خالد العمري');

        $this->get(route('students.index', ['q' => '0551234567']))
            ->assertOk()
            ->assertSee('هند الزهراني')
            ->assertDontSee('خالد العمري');
    }

    public function test_deleting_a_student_cascades_the_parent_contact(): void
    {
        $student = Student::factory()->create([
            'name' => 'طالب للحذف',
        ]);
        $student->parentContact()->create([
            'name' => 'ولي للحذف',
            'phone' => '0500000008',
            'relation' => 'أب',
        ]);

        $this->from(route('students.edit', $student))
            ->delete(route('students.destroy', $student))
            ->assertRedirect(route('students.index'));

        $this->assertModelMissing($student);
        $this->assertDatabaseCount('parent_contacts', 0);
    }

    public function test_student_file_groups_the_card_into_sections(): void
    {
        $student = Student::factory()->create([
            'name' => 'هند الزهراني',
            'last_note' => 'ملاحظة خاصة للملف',
        ]);

        $this->get(route('students.index', ['id' => $student->id]))
            ->assertOk()
            ->assertSee('البيانات الأساسية')
            ->assertSee('الحفظ')
            ->assertSee('التسميع والمراجعة')
            ->assertSee('ملاحظة خاصة')
            ->assertSee('للمعلمة فقط')
            ->assertSee('ملاحظة خاصة للملف')
            ->assertSee('سجل الحصص');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function studentPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'هند الزهراني',
            'phone' => '0501234567',
            'level' => 'مبتدئ',
            'style' => 'شرح مبسط',
            'meet_link' => 'https://meet.google.com/abc-defg-hij',
            'current_surah' => 'الفاتحة',
            'last_ayah' => 7,
            'last_note' => 'ملاحظة تجريبية',
            'new_mem_surah' => 'البقرة',
            'new_mem_from' => 1,
            'new_mem_to' => 5,
            'recitation_surah' => 'الفاتحة',
            'recitation_from' => 1,
            'recitation_to' => 7,
            'review_surah' => 'الفاتحة',
            'review_from' => 1,
            'review_to' => 7,
        ], $overrides);
    }
}
