<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AppPagesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsTeacher = true;

    /**
     * @return array<string, array{0: string}>
     */
    public static function essentialPages(): array
    {
        return [
            'landing' => ['/'],
            'dashboard' => ['/dashboard'],
            'students' => ['/students'],
            'appointments' => ['/appointments'],
            'lessons' => ['/lessons'],
            'notes' => ['/notes'],
            'book' => ['/book'],
            'assistant' => ['/assistant'],
            'reports' => ['/reports'],
            'settings' => ['/settings'],
        ];
    }

    #[DataProvider('essentialPages')]
    public function test_essential_page_loads(string $path): void
    {
        $this->get($path)->assertOk();
    }
}
