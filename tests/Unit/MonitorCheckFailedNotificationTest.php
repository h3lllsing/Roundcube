<?php

namespace Tests\Unit;

use App\Notifications\MonitorCheckFailed;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MonitorCheckFailedNotificationTest extends TestCase
{
    #[Test]
    public function via_returns_database_channel(): void
    {
        $notification = new MonitorCheckFailed('domain', 'Connection timeout', 'example.com');

        $this->assertSame(['database', 'mail'], $notification->via(new \stdClass));
    }

    #[Test]
    public function to_array_returns_correct_structure(): void
    {
        $notification = new MonitorCheckFailed('domain', 'Connection timeout', 'example.com');

        $result = $notification->toArray(new \stdClass);

        $this->assertSame('monitor_check_failed', $result['type']);
        $this->assertSame('domain', $result['resource_type']);
        $this->assertSame('example.com', $result['resource_name']);
        $this->assertSame('Connection timeout', $result['error']);
    }
}
