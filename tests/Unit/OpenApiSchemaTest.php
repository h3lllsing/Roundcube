<?php

namespace Tests\Unit;

defined('L5_SWAGGER_CONST_HOST') or define('L5_SWAGGER_CONST_HOST', env('APP_URL', 'http://localhost'));

use App\OpenApi;
use App\OpenApiSchemas\ActivityLogData;
use App\OpenApiSchemas\AttachmentData;
use App\OpenApiSchemas\DashboardData;
use App\OpenApiSchemas\DomainData;
use App\OpenApiSchemas\DomainEmailData;
use App\OpenApiSchemas\ErrorResponse;
use App\OpenApiSchemas\ExpiryTrackerData;
use App\OpenApiSchemas\FeatureData;
use App\OpenApiSchemas\HostingData;
use App\OpenApiSchemas\LoginAuditData;
use App\OpenApiSchemas\LoginResponse;
use App\OpenApiSchemas\MessageResponse;
use App\OpenApiSchemas\ModuleData;
use App\OpenApiSchemas\NoteData;
use App\OpenApiSchemas\NotificationData;
use App\OpenApiSchemas\OtherServiceData;
use App\OpenApiSchemas\ReportData;
use App\OpenApiSchemas\ServiceProviderData;
use App\OpenApiSchemas\TaskCounts;
use App\OpenApiSchemas\TaskData;
use App\OpenApiSchemas\UserData;
use App\OpenApiSchemas\ValidationErrorResponse;
use App\OpenApiSchemas\VaultData;
use App\OpenApiSchemas\VaultRevealData;
use App\OpenApiSchemas\VoipData;
use App\OpenApiSchemas\VpsData;
use PHPUnit\Framework\TestCase;

class OpenApiSchemaTest extends TestCase
{
    public function openApiSchemaProvider(): array
    {
        return [
            [ActivityLogData::class],
            [AttachmentData::class],
            [DashboardData::class],
            [DomainData::class],
            [DomainEmailData::class],
            [ErrorResponse::class],
            [ExpiryTrackerData::class],
            [FeatureData::class],
            [HostingData::class],
            [LoginAuditData::class],
            [LoginResponse::class],
            [MessageResponse::class],
            [ModuleData::class],
            [NoteData::class],
            [NotificationData::class],
            [OtherServiceData::class],
            [ReportData::class],
            [ServiceProviderData::class],
            [TaskCounts::class],
            [TaskData::class],
            [UserData::class],
            [ValidationErrorResponse::class],
            [VaultData::class],
            [VaultRevealData::class],
            [VoipData::class],
            [VpsData::class],
            [OpenApi::class],
        ];
    }

    public function test_all_schema_classes_can_be_instantiated(): void
    {
        $classes = [
            ActivityLogData::class, AttachmentData::class, DashboardData::class,
            DomainData::class, DomainEmailData::class, ErrorResponse::class,
            ExpiryTrackerData::class, FeatureData::class, HostingData::class,
            LoginAuditData::class, LoginResponse::class, MessageResponse::class,
            ModuleData::class, NoteData::class, NotificationData::class,
            OtherServiceData::class, ReportData::class, ServiceProviderData::class,
            TaskCounts::class, TaskData::class, UserData::class,
            ValidationErrorResponse::class, VaultData::class, VaultRevealData::class,
            VoipData::class, VpsData::class, OpenApi::class,
        ];

        foreach ($classes as $class) {
            $instance = new $class;
            $this->assertInstanceOf($class, $instance);

            $reflection = new \ReflectionClass($class);
            foreach ($reflection->getAttributes() as $attribute) {
                $attribute->newInstance();
            }
        }
    }
}
