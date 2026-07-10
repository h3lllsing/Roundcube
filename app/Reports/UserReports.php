<?php

namespace App\Reports;

use App\Models\User;

class UserReports extends ReportProvider
{
    public function slug(): string
    {
        return 'users';
    }

    public function label(): string
    {
        return 'Users';
    }

    public function description(): string
    {
        return 'User account reports for active users and activity summaries.';
    }

    public function icon(): ?string
    {
        return 'M12 4.354a4 4 0 110 5.292M7 20.75a7 7 0 0110 0';
    }

    public function reports(): array
    {
        return [
            'active' => [
                'slug' => 'active',
                'label' => 'Active Users',
                'description' => 'All active (non-suspended) user accounts.',
                'columns' => ['Name', 'Email', 'Roles', 'Last Login', 'Joined'],
                'query' => function (array $filters, $user, $accessibleIds) {
                    return User::whereNull('suspended_at')
                        ->when($filters['search'] ?? null, fn($q, $v) => $q->where(function ($q) use ($v) {
                            $q->where('name', 'like', "%{$v}%")->orWhere('email', 'like', "%{$v}%");
                        }))
                        ->orderBy('name')
                        ->get(['id', 'name', 'email', 'created_at']);
                },
            ],
        ];
    }
}
