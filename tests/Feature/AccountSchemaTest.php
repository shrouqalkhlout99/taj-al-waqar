<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Appointment;
use App\Models\Lesson;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AccountSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_accounts_table_and_account_id_columns_exist_on_tenant_tables(): void
    {
        $this->assertTrue(Schema::hasTable('accounts'));
        $this->assertGreaterThan(0, Account::query()->count());

        foreach (['users', 'students', 'appointments', 'lessons', 'settings'] as $table) {
            $this->assertTrue(Schema::hasColumn($table, 'account_id'), $table.' missing account_id');
        }
    }

    public function test_parent_contacts_do_not_have_account_id(): void
    {
        $this->assertTrue(Schema::hasTable('parent_contacts'));
        $this->assertFalse(Schema::hasColumn('parent_contacts', 'account_id'));
    }

    public function test_noor_bayan_tables_and_student_columns_are_removed(): void
    {
        foreach ([
            'skills',
            'noor_lessons',
            'noor_lesson_skill',
            'skill_activities',
            'noor_sessions',
            'student_skill_profiles',
            'skill_assessments',
            'homework_items',
        ] as $table) {
            $this->assertFalse(Schema::hasTable($table), $table.' should be dropped');
        }

        foreach (['tracks_noor_bayan', 'arabic_score', 'noor_score', 'current_noor_lesson_id'] as $column) {
            $this->assertFalse(Schema::hasColumn('students', $column), 'students.'.$column.' should be dropped');
        }
    }

    public function test_factory_user_is_linked_to_an_account(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->account_id);
        $this->assertTrue(Account::query()->whereKey($user->account_id)->exists());
    }

    public function test_student_appointment_and_lesson_share_the_student_account(): void
    {
        $student = Student::factory()->create();
        $appointment = Appointment::factory()->for($student)->create();
        $lesson = Lesson::factory()->for($student)->create([
            'appointment_id' => $appointment->id,
        ]);

        $this->assertNotNull($student->account_id);
        $this->assertSame($student->account_id, $appointment->account_id);
        $this->assertSame($student->account_id, $lesson->account_id);
        $this->assertSame(0, DB::table('students')->whereNull('account_id')->count());
        $this->assertSame(0, DB::table('appointments')->whereNull('account_id')->count());
        $this->assertSame(0, DB::table('lessons')->whereNull('account_id')->count());
        $this->assertSame(0, DB::table('users')->whereNull('account_id')->count());
        $this->assertSame(0, DB::table('settings')->whereNull('account_id')->count());
    }

    public function test_settings_persist_against_the_authenticated_account(): void
    {
        $this->actingAsTeacher();

        Setting::setValue('teacher_city', 'جدة');

        $this->assertSame('جدة', Setting::getValue('teacher_city'));
        $this->assertDatabaseHas('settings', [
            'account_id' => auth()->user()->account_id,
            'key' => 'teacher_city',
            'value' => 'جدة',
        ]);
    }

    public function test_settings_reject_duplicate_key_for_the_same_account(): void
    {
        $account = Account::factory()->create();

        DB::table('settings')->insert([
            'account_id' => $account->id,
            'key' => 'theme',
            'value' => 'light',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        DB::table('settings')->insert([
            'account_id' => $account->id,
            'key' => 'theme',
            'value' => 'dark',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_settings_allow_the_same_key_on_two_accounts(): void
    {
        $first = Account::factory()->create();
        $second = Account::factory()->create();

        DB::table('settings')->insert([
            [
                'account_id' => $first->id,
                'key' => 'theme',
                'value' => 'light',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account_id' => $second->id,
                'key' => 'theme',
                'value' => 'dark',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->assertSame(2, DB::table('settings')->where('key', 'theme')->count());
    }

    public function test_database_seeder_links_teacher_settings(): void
    {
        $this->seed();

        $user = User::query()->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->account_id);
        $this->assertSame(0, Setting::query()->whereNull('account_id')->count());
        $this->assertTrue(
            Setting::query()->where('account_id', $user->account_id)->exists()
        );
    }

    public function test_creating_a_student_without_an_account_fails_clearly(): void
    {
        DB::table('accounts')->delete();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('لا يوجد حساب صالح لتعيين account_id.');

        Student::factory()->create();
    }

    public function test_new_student_without_account_id_uses_the_authenticated_account(): void
    {
        $this->actingAsTeacher();

        $student = Student::query()->create([
            'name' => 'نورة تعيين كتابة ٩أ',
            'level' => 'مبتدئ',
            'style' => 'شرح مبسط',
            'color' => '#6b4bd6',
        ]);

        $this->assertSame(auth()->user()->account_id, $student->account_id);
    }

    public function test_authenticated_student_factory_uses_the_teacher_account(): void
    {
        $this->actingAsTeacher();

        $student = Student::factory()->create();

        $this->assertSame(auth()->user()->account_id, $student->account_id);
    }

    public function test_student_lists_hide_students_from_another_account(): void
    {
        $this->actingAsTeacher();
        $other = Account::factory()->create();
        $foreign = Student::factory()->create([
            'account_id' => $other->id,
            'name' => 'طالبة حساب آخر ٩ب',
        ]);

        $this->get(route('students.index'))
            ->assertOk()
            ->assertDontSee('طالبة حساب آخر ٩ب');

        $this->get(route('students.edit', $foreign))->assertNotFound();
    }

    public function test_account_with_students_cannot_be_deleted(): void
    {
        $student = Student::factory()->create();

        $this->expectException(QueryException::class);

        Account::query()->whereKey($student->account_id)->delete();
    }
}
