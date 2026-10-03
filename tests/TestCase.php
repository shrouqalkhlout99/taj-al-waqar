<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Authenticate as the default teacher before each test in the class.
     */
    protected bool $authenticateAsTeacher = false;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->authenticateAsTeacher) {
            $this->actingAsTeacher();
        }
    }

    /**
     * Create a teacher user for tests that need authentication.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function teacherUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    /**
     * Authenticate as a teacher so tests stay ready for the 7B app lock.
     */
    protected function actingAsTeacher(?User $user = null): static
    {
        return $this->actingAs($user ?? $this->teacherUser());
    }

    protected function assertNamedSelectAllowsAyah(string $html, string $name, int $max): void
    {
        $this->assertSame(1, preg_match(
            '/<select[^>]*name="'.preg_quote($name, '/').'"[^>]*>.*?<\/select>/s',
            $html,
            $matches
        ), "Missing select [{$name}].");

        $markup = $matches[0];
        $this->assertStringContainsString('value="'.$max.'"', $markup);
        $this->assertStringNotContainsString('value="'.($max + 1).'"', $markup);
    }
}
