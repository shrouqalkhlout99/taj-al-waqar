<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\DefaultTeacherSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function publicPages(): array
    {
        return [
            'landing' => ['/'],
            'privacy' => ['/privacy'],
            'terms' => ['/terms'],
            'login' => ['/login'],
        ];
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function internalPages(): array
    {
        return [
            'dashboard' => ['/dashboard'],
            'students' => ['/students'],
            'lessons' => ['/lessons'],
            'appointments' => ['/appointments'],
            'reports' => ['/reports'],
            'assistant' => ['/assistant'],
            'book' => ['/book'],
            'settings' => ['/settings'],
            'notes' => ['/notes'],
        ];
    }

    public function test_login_page_renders_form_without_exposing_password_hash(): void
    {
        $user = $this->teacherUser([
            'email' => 'teacher@example.com',
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('تسجيل الدخول')
            ->assertSee('البريد الإلكتروني')
            ->assertSee('كلمة المرور')
            ->assertDontSee($user->getAuthPassword(), false);
    }

    public function test_valid_credentials_authenticate_and_redirect_to_dashboard(): void
    {
        $user = $this->teacherUser([
            'email' => 'teacher@example.com',
        ]);

        $this->from(route('login'))
            ->post(route('login'), [
                'email' => 'teacher@example.com',
                'password' => 'password',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_returns_guest_to_the_intended_internal_page(): void
    {
        $user = $this->teacherUser([
            'email' => 'teacher@example.com',
        ]);

        $this->get(route('students.index'))->assertRedirect(route('login'));

        $this->post(route('login'), [
            'email' => 'teacher@example.com',
            'password' => 'password',
        ])->assertRedirect(route('students.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->teacherUser([
            'email' => 'teacher@example.com',
        ]);

        $this->from(route('login'))
            ->post(route('login'), [
                'email' => 'teacher@example.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'بيانات الدخول غير صحيحة.']);

        $this->assertGuest();
    }

    public function test_login_validation_rejects_empty_payload(): void
    {
        $this->from(route('login'))
            ->post(route('login'), [])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
    }

    public function test_logout_clears_the_session(): void
    {
        $user = $this->teacherUser();

        $this->actingAsTeacher($user)
            ->from(route('dashboard'))
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $this->actingAsTeacher()
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));
    }

    #[DataProvider('internalPages')]
    public function test_guest_is_redirected_to_login_from_internal_pages(string $path): void
    {
        $this->get($path)->assertRedirect(route('login'));
    }

    #[DataProvider('internalPages')]
    public function test_authenticated_teacher_can_open_internal_pages(string $path): void
    {
        $this->actingAsTeacher()->get($path)->assertOk();
    }

    #[DataProvider('publicPages')]
    public function test_public_pages_remain_available_to_guests(string $path): void
    {
        $this->get($path)->assertOk();
    }

    public function test_guest_post_to_internal_route_redirects_to_login(): void
    {
        $this->post('/settings')->assertRedirect(route('login'));
    }

    public function test_guest_logout_redirects_to_login(): void
    {
        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_every_web_route_except_public_pages_requires_authentication(): void
    {
        $publicUris = ['/', 'privacy', 'terms', 'login', 'up'];

        foreach (Route::getRoutes() as $route) {
            $middleware = $route->gatherMiddleware();

            if (! in_array('web', $middleware, true)) {
                continue;
            }

            $uri = $route->uri();

            if (in_array($uri, $publicUris, true)) {
                $this->assertFalse(in_array('auth', $middleware, true), $uri);

                continue;
            }

            $this->assertContains('auth', $middleware, $uri);
        }
    }

    public function test_default_teacher_seeder_preserves_existing_students(): void
    {
        $student = Student::factory()->create([
            'name' => 'تحقق 4C',
        ]);

        $this->seed(DefaultTeacherSeeder::class);
        $this->seed(DefaultTeacherSeeder::class);

        $this->assertSame(1, User::query()->count());
        $this->assertSame('تحقق 4C', $student->fresh()->name);
        $this->assertDatabaseHas('users', [
            'email' => DefaultTeacherSeeder::EMAIL,
        ]);
    }

    public function test_default_teacher_seeder_uses_saved_teacher_email(): void
    {
        Setting::setValue('teacher_name', 'معلمة الاختبار');
        Setting::setValue('teacher_email', 'saved@example.com');

        $this->seed(DefaultTeacherSeeder::class);

        $this->assertDatabaseHas('users', [
            'name' => 'معلمة الاختبار',
            'email' => 'saved@example.com',
        ]);
    }
}
