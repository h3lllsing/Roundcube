<?php

namespace Tests\Unit;

use App\Services\SmtpAutoDiscover;
use Tests\TestCase;

class SmtpAutoDiscoverTest extends TestCase
{
    private SmtpAutoDiscover $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SmtpAutoDiscover;
    }

    public function test_invalid_email_returns_error(): void
    {
        $result = $this->service->discover('not-an-email');
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Invalid email address.', $result['error']);
    }

    public function test_empty_email_returns_error(): void
    {
        $result = $this->service->discover('');
        $this->assertArrayHasKey('error', $result);
    }

    public function test_email_without_domain_returns_error(): void
    {
        $result = $this->service->discover('user@');
        $this->assertArrayHasKey('error', $result);
    }

    public function test_discover_returns_structure_for_known_domain(): void
    {
        $result = $this->service->discover('test@gmail.com');

        if (isset($result['error'])) {
            $this->assertStringContainsString('No mail server', $result['error']);
        } else {
            $this->assertArrayHasKey('host', $result);
            $this->assertArrayHasKey('port', $result);
            $this->assertArrayHasKey('encryption', $result);
            $this->assertArrayHasKey('username', $result);
            $this->assertEquals(587, $result['port']);
            $this->assertEquals('tls', $result['encryption']);
            $this->assertEquals('test@gmail.com', $result['username']);
        }
    }

    public function test_discover_uses_email_as_username(): void
    {
        $email = 'admin@example.com';
        $result = $this->service->discover($email);

        if (!isset($result['error'])) {
            $this->assertEquals($email, $result['username']);
        }
    }

    public function test_extractDomain_returns_null_for_invalid_email(): void
    {
        $ref = new \ReflectionMethod($this->service, 'extractDomain');
        $ref->setAccessible(true);
        $this->assertNull($ref->invoke($this->service, 'not-valid'));
        $this->assertNull($ref->invoke($this->service, ''));
        $this->assertNull($ref->invoke($this->service, 'a@b'));
    }

    public function test_extractDomain_returns_domain(): void
    {
        $ref = new \ReflectionMethod($this->service, 'extractDomain');
        $ref->setAccessible(true);
        $this->assertEquals('gmail.com', $ref->invoke($this->service, 'test@gmail.com'));
        $this->assertEquals('example.org', $ref->invoke($this->service, 'user@example.org'));
    }
}
