<?php

namespace Tests\Unit;

use App\Models\Url;
use App\Services\UrlShortenerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrlShortenerServiceTest extends TestCase
{
    use RefreshDatabase;

    private UrlShortenerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UrlShortenerService();
    }

    // ==================== generateCode ====================

    public function test_generate_code_returns_string(): void
    {
        $code = $this->service->generateCode();
        $this->assertIsString($code);
    }

    public function test_generate_code_default_length_is_six(): void
    {
        $code = $this->service->generateCode();
        $this->assertEquals(6, strlen($code));
    }

    public function test_generate_code_custom_length(): void
    {
        $code = $this->service->generateCode(10);
        $this->assertEquals(10, strlen($code));
    }

    public function test_generate_code_is_alphanumeric(): void
    {
        $code = $this->service->generateCode();
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $code);
    }

    // ==================== isValidUrl ====================

    public function test_valid_url_returns_true(): void
    {
        $this->assertTrue($this->service->isValidUrl('https://www.google.com'));
    }

    public function test_valid_url_with_http_returns_true(): void
    {
        $this->assertTrue($this->service->isValidUrl('http://example.com'));
    }

    public function test_invalid_url_returns_false(): void
    {
        $this->assertFalse($this->service->isValidUrl('not-a-url'));
    }

    public function test_empty_string_is_invalid_url(): void
    {
        $this->assertFalse($this->service->isValidUrl(''));
    }

    public function test_url_without_scheme_is_invalid(): void
    {
        $this->assertFalse($this->service->isValidUrl('www.google.com'));
    }

    // ==================== shorten ====================

    public function test_shorten_creates_url_record(): void
    {
        $url = $this->service->shorten('https://www.google.com');
        $this->assertDatabaseHas('urls', ['original_url' => 'https://www.google.com']);
    }

    public function test_shorten_returns_url_model(): void
    {
        $url = $this->service->shorten('https://www.google.com');
        $this->assertInstanceOf(Url::class, $url);
    }

    public function test_shorten_generates_short_code(): void
    {
        $url = $this->service->shorten('https://www.google.com');
        $this->assertNotEmpty($url->short_code);
    }

    public function test_shorten_sets_visit_count_to_zero(): void
    {
        $url = $this->service->shorten('https://www.google.com');
        $this->assertEquals(0, $url->visit_count);
    }

    // ==================== resolve ====================

    public function test_resolve_returns_url_for_valid_code(): void
    {
        $created = $this->service->shorten('https://www.google.com');
        $resolved = $this->service->resolve($created->short_code);
        $this->assertNotNull($resolved);
        $this->assertEquals($created->short_code, $resolved->short_code);
    }

    public function test_resolve_returns_null_for_invalid_code(): void
    {
        $result = $this->service->resolve('invalid123');
        $this->assertNull($result);
    }

    // ==================== recordVisit ====================

    public function test_record_visit_increments_visit_count(): void
    {
        $url = $this->service->shorten('https://www.google.com');
        $this->service->recordVisit($url);
        $this->assertEquals(1, $url->fresh()->visit_count);
    }

    public function test_record_visit_multiple_times(): void
    {
        $url = $this->service->shorten('https://www.google.com');
        $this->service->recordVisit($url);
        $this->service->recordVisit($url);
        $this->service->recordVisit($url);
        $this->assertEquals(3, $url->fresh()->visit_count);
    }

    // ==================== getStats ====================

    public function test_get_stats_returns_array_for_valid_code(): void
    {
        $url = $this->service->shorten('https://www.google.com');
        $stats = $this->service->getStats($url->short_code);
        $this->assertIsArray($stats);
    }

    public function test_get_stats_returns_null_for_invalid_code(): void
    {
        $stats = $this->service->getStats('invalid123');
        $this->assertNull($stats);
    }

    public function test_get_stats_contains_required_keys(): void
    {
        $url = $this->service->shorten('https://www.google.com');
        $stats = $this->service->getStats($url->short_code);
        $this->assertArrayHasKey('short_code', $stats);
        $this->assertArrayHasKey('original_url', $stats);
        $this->assertArrayHasKey('visit_count', $stats);
        $this->assertArrayHasKey('created_at', $stats);
    }
}