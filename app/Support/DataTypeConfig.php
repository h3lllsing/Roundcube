<?php

namespace App\Support;

use App\Models\Feature;
use App\Models\LoginAudit;
use App\Models\Module;
use App\Models\Privilege;
use App\Models\Role;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

class DataTypeConfig
{
    public static function exportTypes(): array
    {
        return [
            'features' => [
                'model' => Feature::class, 'columns' => ['name', 'slug', 'description', 'icon', 'is_active', 'created_at'], 'admin' => true,
            ],
            'modules' => [
                'model' => Module::class, 'columns' => ['name', 'feature_id', 'created_at'], 'admin' => true,
            ],
            'activity-logs' => [
                'model' => Activity::class, 'columns' => ['log_name', 'description', 'subject_type', 'subject_id', 'causer_type', 'causer_id', 'event', 'created_at'], 'admin' => true,
            ],
            'login-audits' => [
                'model' => LoginAudit::class, 'columns' => ['user_id', 'email', 'ip_address', 'user_agent', 'event', 'created_at'], 'admin' => true,
            ],
            'users' => [
                'model' => User::class, 'columns' => ['name', 'email'], 'admin' => true,
            ],
            'roles' => [
                'model' => Role::class, 'columns' => ['id', 'name', 'slug', 'created_at'], 'admin' => true,
            ],
            'privileges' => [
                'model' => Privilege::class, 'columns' => ['id', 'name', 'slug', 'description', 'created_at'], 'admin' => true,
            ],
        ];
    }

    public static function importTypes(): array
    {
        return [
            'features' => Feature::class,
            'modules' => Module::class,
            'users' => User::class,
            'roles' => Role::class,
            'privileges' => Privilege::class,
            'activity-logs' => Activity::class,
            'login-audits' => LoginAudit::class,
        ];
    }

    public static function importTypeModuleMapping(): array
    {
        return [
            'users' => 'users',
            'roles' => 'roles',
            'privileges' => 'privileges',
            'activity-logs' => 'activity-logs',
            'login-audits' => 'login-audits',
        ];
    }
}
