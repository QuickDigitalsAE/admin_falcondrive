<?php

return [
    'levels' => [
        'superadmin' => [
            'label' => 'Super Admin',
            'description' => 'Full unrestricted access across the admin panel.',
            'patterns' => ['*'],
        ],
        'admin' => [
            'label' => 'Admin',
            'description' => 'Operational control over dashboard, users, roles, and permissions.',
            'patterns' => [
                'Dashboard_View',
                'User_*',
                'Role_*',
            ],
        ],
        'manager' => [
            'label' => 'Manager',
            'description' => 'Management access with read-heavy visibility and limited user operations.',
            'patterns' => [
                'Dashboard_View',
                'User_*',
            ],
        ],
        'sales' => [
            'label' => 'Sales',
            'description' => 'Restricted front-office visibility focused on dashboard and basic user access.',
            'patterns' => [
                'Dashboard_View',
                'User_Menu',
                'User_View',
            ],
        ],
    ],
];
