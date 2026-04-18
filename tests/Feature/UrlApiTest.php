<?php

namespace Tests\Feature;

use App\Models\Url;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrlApiTest extends TestCase
{
    use RefreshDatabase;

    // ==================== POST /api/shorten ====================

    public function test_shorten_endpoint_returns_201(): void
    {
        $response = $this->postJson('/api/shorten', [
            'url' => 'https://www.google.com'
        ]);

        $response->assertStatus(201);
    }

    public function test_shorten_endpoint_returns_correct_structure(): void
    {
        $response = $this->postJson('/api/shorten', [
            'url' => 'https://www.google.com'
        ]);

        $response->assertJsonStructure([
            'short_code',
            'short_url',
            'original_url',
        ]);
    }

    public function test_shorten_endpoint_saves_to_database(): void
    {
        $this->postJson('/api/shorten', [
            'url' => 'https://www.google.com'
        ]);

        $this->assertDatabaseCount('urls', 1);
    }

    public function test_shorten_endpoint_rejects_invalid_url(): void
    {
        $response = $this->postJson('/api/shorten', [
            'url' => 'not-a-valid-url'
        ]);

        $response->assertStatus(422);
    }

    public function test_shorten_endpoint_rejects_empty_url(): void
    {
        $response = $this->postJson('/api/shorten', [
            'url' => ''
        ]);

        $response->assertStatus(422);
    }

    // ==================== GET /api/stats/{shortCode} ====================

    public function test_stats_endpoint_returns_200_for_valid_code(): void
    {
        $url = Url::create([
            'original_url' => 'https://www.google.com',
            'short_code'   => 'abc123',
            'visit_count'  => 5,
        ]);

        $response = $this->getJson('/api/stats/abc123');

        $response->assertStatus(200);
    }

    public function test_stats_endpoint_returns_correct_data(): void
    {
        Url::create([
            'original_url' => 'https://www.google.com',
            'short_code'   => 'abc123',
            'visit_count'  => 5,
        ]);

        $response = $this->getJson('/api/stats/abc123');

        $response->assertJson([
            'short_code'   => 'abc123',
            'original_url' => 'https://www.google.com',
            'visit_count'  => 5,
        ]);
    }

    public function test_stats_endpoint_returns_404_for_invalid_code(): void
    {
        $response = $this->getJson('/api/stats/nonexistent');

        $response->assertStatus(404);
    }

    // ==================== GET /{shortCode} ====================

    public function test_redirect_endpoint_redirects_to_original_url(): void
    {
        Url::create([
            'original_url' => 'https://www.google.com',
            'short_code'   => 'abc123',
            'visit_count'  => 0,
        ]);

        $response = $this->get('/abc123');

        $response->assertRedirect('https://www.google.com');
    }

    public function test_redirect_endpoint_returns_404_for_invalid_code(): void
    {
        $response = $this->getJson('/nonexistent999');

        $response->assertStatus(404);
    }

    public function test_redirect_increments_visit_count(): void
    {
        Url::create([
            'original_url' => 'https://www.google.com',
            'short_code'   => 'abc123',
            'visit_count'  => 0,
        ]);

        $this->get('/abc123');

        $this->assertDatabaseHas('urls', [
            'short_code'  => 'abc123',
            'visit_count' => 1,
        ]);
    }
}