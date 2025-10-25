<?php

return [
    'groups' => [
        [
            'label' => 'Platform',
            'items' => [
                [
                    'title' => 'Dashboard',
                    'href' => '/dashboard',
                    'icon' => 'LayoutGrid',
                    'permission' => null,
                ],
                [
                    'title' => 'Download Center',
                    'href' => '/exports',
                    'icon' => 'Download',
                    'permission' => null,
                ],
                [
                    'title' => 'Activity Logs',
                    'href' => '/activity-logs',
                    'icon' => 'ScrollText',
                    'permission' => 'activity-logs.view',
                ],
            ],
        ],
        [
            'label' => 'Team',
            'items' => [
                [
                    'title' => 'Staff',
                    'href' => '/staff',
                    'icon' => 'Users',
                    'permission' => 'staff.view',
                ],
                [
                    'title' => 'Users',
                    'href' => '/users',
                    'icon' => 'UserCog',
                    'permission' => 'users.manage',
                ],
                [
                    'title' => 'Roles',
                    'href' => '/roles',
                    'icon' => 'Shield',
                    'permission' => 'roles.manage',
                ],
            ],
        ],
    ],
];
