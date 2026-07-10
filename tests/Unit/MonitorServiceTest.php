<?php

namespace Tests\Unit;

use App\Services\MonitorService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MonitorServiceTest extends TestCase
{
    private MonitorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MonitorService;
    }

    public function test_ping_returns_success(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $result = $this->service->ping('https://example.com');

        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['status_code']);
        $this->assertNull($result['error']);
        $this->assertIsNumeric($result['response_time_ms']);
    }

    public function test_ping_returns_failure_on_exception(): void
    {
        Http::fake(['*' => fn () => throw new \Exception('Connection timeout')]);

        $result = $this->service->ping('https://invalid.example.com');

        $this->assertFalse($result['success']);
        $this->assertNull($result['status_code']);
        $this->assertEquals('Connection timeout', $result['error']);
    }

    public function test_ping_returns_failure_on_500(): void
    {
        Http::fake(['*' => Http::response(null, 500)]);

        $result = $this->service->ping('https://error.example.com');

        $this->assertFalse($result['success']);
        $this->assertEquals(500, $result['status_code']);
    }

    public function test_ssl_invalid_url(): void
    {
        $result = $this->service->checkSsl('not-a-url');

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid URL', $result['error']);
    }

    public function test_check_returns_combined_result(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $result = $this->service->check('https://example.com');

        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('ping', $result);
        $this->assertArrayHasKey('ssl', $result);
        $this->assertArrayHasKey('checked_at', $result);
        $this->assertEquals('https://example.com', $result['url']);
        $this->assertTrue($result['ping']['success']);
    }

    public function test_ssl_with_real_url_returns_cert_info(): void
    {
        $result = $this->service->checkSsl('https://example.com');

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('days_remaining', $result);
        $this->assertArrayHasKey('issuer', $result);
    }

    public function test_ssl_with_unreachable_host_returns_error(): void
    {
        $result = $this->service->checkSsl('https://192.0.2.1');

        $this->assertFalse($result['success']);
        $this->assertNotNull($result['error']);
    }

    public function test_ssl_handles_cert_parse_failure(): void
    {
        $mock = $this->getMockBuilder(MonitorService::class)
            ->onlyMethods(['parseSslCert'])
            ->getMock();

        $mock->expects($this->once())
            ->method('parseSslCert')
            ->willReturn(false);

        Http::fake(['*' => Http::response(['ok' => true], 200)]);
        $result = $mock->check('https://example.com');

        $this->assertFalse($result['ssl']['success']);
        $this->assertEquals('Failed to parse certificate', $result['ssl']['error']);
    }

    public function test_ssl_handles_exception_during_cert_parse(): void
    {
        $mock = $this->getMockBuilder(MonitorService::class)
            ->onlyMethods(['parseSslCert'])
            ->getMock();

        $mock->expects($this->once())
            ->method('parseSslCert')
            ->willThrowException(new \Exception('Parse error'));

        Http::fake(['*' => Http::response(['ok' => true], 200)]);
        $result = $mock->check('https://example.com');

        $this->assertFalse($result['ssl']['success']);
        $this->assertEquals('Parse error', $result['ssl']['error']);
    }
}
