<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_homepage_is_a_full_page_livewire_component(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('The world,')
            ->assertSee('wire:snapshot', false);
    }
}
