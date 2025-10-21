<?php

namespace App\Support\Exports;

use Illuminate\Support\Str;

class ExportConfig
{
    public static function brandHeader(string $documentTitle): array
    {
        $logoPath = public_path('images/asset-logo.svg');

        return [
            'logo' => file_exists($logoPath) ? $logoPath : null,
            'organization_name' => config('app.name', 'Asset Management'),
            'document_title' => $documentTitle,
        ];
    }

    public static function staff(): array
    {
        return [
            'label' => 'Staff Directory',
            'type' => 'staff',
            'filename_prefix' => 'staff-directory',

            'csv' => [
                'headers' => [
                    '#',
                    'Full Name',
                    'Email',
                    'Phone',
                    'Job Title',
                    'Status',
                    'Linked User',
                ],
                'fields' => [
                    'index',
                    'full_name',
                    'email',
                    'phone',
                    'job_title',
                    [
                        'field' => 'status',
                        'transform' => fn ($value) => Str::of($value ?? '')->replace('_', ' ')->title(),
                        'default' => 'Inactive',
                    ],
                    [
                        'field' => 'user.name',
                        'default' => '—',
                    ],
                ],
                'with_relations' => ['user:id,name'],
                'filename_prefix' => 'staff-directory',
            ],

            'pdf' => [
                'view' => 'pdf-layout',
                'document_title' => 'Staff Directory - Full Export',
                'filename_prefix' => 'staff-directory',
                'orientation' => 'landscape',
                'with_relations' => ['user:id,name'],
                'columns' => [
                    ['key' => 'index', 'label' => '#'],
                    ['key' => 'full_name', 'label' => 'Full Name'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'phone', 'label' => 'Phone'],
                    ['key' => 'job_title', 'label' => 'Job Title'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'user.name', 'label' => 'Linked User'],
                ],
            ],

            'current_page' => [
                'view' => 'pdf-layout',
                'document_title' => 'Staff Directory - Current View',
                'filename_prefix' => 'staff-current-view',
                'orientation' => 'landscape',
                'with_relations' => ['user:id,name'],
                'columns' => [
                    ['key' => 'index', 'label' => '#'],
                    ['key' => 'full_name', 'label' => 'Full Name'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'job_title', 'label' => 'Job Title'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'user.name', 'label' => 'Linked User'],
                ],
            ],

            'single_record' => [
                'view' => 'pdf-layout',
                'document_title' => 'Staff Profile Summary',
                'filename_prefix' => 'staff-profile',
                'with_relations' => ['user:id,name,email'],
                'columns' => [
                    ['key' => 'full_name', 'label' => 'Full Name'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'phone', 'label' => 'Phone'],
                    ['key' => 'job_title', 'label' => 'Job Title'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'hire_date', 'label' => 'Hire Date'],
                    ['key' => 'user.name', 'label' => 'Linked User'],
                ],
            ],
        ];
    }

    public static function users(): array
    {
        return [
            'label' => 'User Roster',
            'type' => 'users',
            'filename_prefix' => 'users',

            'csv' => [
                'headers' => [
                    '#',
                    'Name',
                    'Email',
                    'Roles',
                    'Direct Permissions',
                    'Has 2FA',
                    'Linked Staff',
                    'Staff Status',
                ],
                'fields' => [
                    'index',
                    'name',
                    'email',
                    [
                        'field' => 'roles',
                        'transform' => fn ($value) => $value instanceof \Illuminate\Support\Collection
                            ? $value->implode(', ')
                            : (is_array($value) ? implode(', ', $value) : $value),
                        'default' => '—',
                    ],
                    [
                        'field' => 'permissions',
                        'transform' => fn ($value) => $value instanceof \Illuminate\Support\Collection
                            ? $value->implode(', ')
                            : (is_array($value) ? implode(', ', $value) : $value),
                        'default' => 'Inherited',
                    ],
                    [
                        'field' => 'has_two_factor',
                        'transform' => fn ($value) => $value ? 'Yes' : 'No',
                    ],
                    ['field' => 'staff.full_name', 'default' => '—'],
                    [
                        'field' => 'staff.status',
                        'transform' => fn ($value) => $value ? Str::of($value)->headline() : '—',
                        'default' => '—',
                    ],
                ],
                'with_relations' => ['roles:id,name', 'permissions:id,name', 'staff:id,first_name,last_name,status,user_id'],
            ],

            'pdf' => [
                'view' => 'pdf-layout',
                'document_title' => 'User Accounts - Full Export',
                'filename_prefix' => 'users-roster',
                'orientation' => 'landscape',
                'with_relations' => ['roles:id,name', 'permissions:id,name', 'staff:id,first_name,last_name,status,user_id'],
                'columns' => [
                    ['key' => 'index', 'label' => '#'],
                    ['key' => 'name', 'label' => 'Name'],
                    ['key' => 'email', 'label' => 'Email'],
                    [
                        'key' => 'roles',
                        'label' => 'Roles',
                        'transform' => fn ($model) => $model->roles?->pluck('name')->join(', ') ?: '—',
                    ],
                    [
                        'key' => 'permissions',
                        'label' => 'Direct Permissions',
                        'transform' => fn ($model) => $model->permissions?->pluck('name')->join(', ') ?: 'Inherited',
                    ],
                    [
                        'key' => 'has_two_factor',
                        'label' => '2FA',
                        'transform' => fn ($model) => $model->two_factor_secret ? 'Yes' : 'No',
                    ],
                    [
                        'key' => 'staff.full_name',
                        'label' => 'Staff',
                        'transform' => fn ($model) => $model->staff?->full_name ?? '—',
                    ],
                    [
                        'key' => 'staff.status',
                        'label' => 'Staff Status',
                        'transform' => fn ($model) => $model->staff?->status ? Str::of($model->staff->status)->headline() : '—',
                    ],
                ],
            ],

            'current_page' => [
                'view' => 'pdf-layout',
                'document_title' => 'User Accounts - Current View',
                'filename_prefix' => 'users-current-view',
                'orientation' => 'landscape',
                'with_relations' => ['roles:id,name', 'permissions:id,name', 'staff:id,first_name,last_name,status,user_id'],
                'columns' => [
                    ['key' => 'index', 'label' => '#'],
                    ['key' => 'name', 'label' => 'Name'],
                    ['key' => 'email', 'label' => 'Email'],
                    [
                        'key' => 'roles',
                        'label' => 'Roles',
                        'transform' => fn ($model) => $model->roles?->pluck('name')->join(', ') ?: '—',
                    ],
                    [
                        'key' => 'has_two_factor',
                        'label' => '2FA',
                        'transform' => fn ($model) => $model->two_factor_secret ? 'Yes' : 'No',
                    ],
                    [
                        'key' => 'staff.full_name',
                        'label' => 'Staff',
                        'transform' => fn ($model) => $model->staff?->full_name ?? '—',
                    ],
                ],
            ],
        ];
    }
}
