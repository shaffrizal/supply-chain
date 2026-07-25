<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        Http::fake([
            'api.open-meteo.com/*' => Http::response(['current' => []]),
            'open.er-api.com/*' => Http::response(['rates' => []]),
        ]);
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
