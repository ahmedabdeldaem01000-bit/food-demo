<?php

return [
    'enabled' => env('DEMO_DATA_ENABLED', false),

    'users' => [
        'admin' => [
            'name' => env('DEMO_ADMIN_NAME', 'Foodify Local Admin'),
            'email' => env('DEMO_ADMIN_EMAIL'),
            'phone' => env('DEMO_ADMIN_PHONE'),
            'password' => env('DEMO_ADMIN_PASSWORD'),
        ],
        'employee' => [
            'name' => env('DEMO_EMPLOYEE_NAME', 'Foodify Local Employee'),
            'email' => env('DEMO_EMPLOYEE_EMAIL'),
            'phone' => env('DEMO_EMPLOYEE_PHONE'),
            'password' => env('DEMO_EMPLOYEE_PASSWORD'),
        ],
    ],
];
