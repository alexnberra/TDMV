<?php

return [
    'seed_mode' => env('TDMV_SEED_MODE', 'demo'),

    'demo' => [
        'tribes' => (int) env('TDMV_DEMO_TRIBES', 2),
        'staff_per_tribe' => (int) env('TDMV_DEMO_STAFF_PER_TRIBE', 3),
        'members_per_tribe' => (int) env('TDMV_DEMO_MEMBERS_PER_TRIBE', 10),
        'vehicles_per_member_min' => (int) env('TDMV_DEMO_VEHICLES_MIN', 1),
        'vehicles_per_member_max' => (int) env('TDMV_DEMO_VEHICLES_MAX', 3),
        'applications_per_vehicle_min' => (int) env('TDMV_DEMO_APPLICATIONS_MIN', 1),
        'applications_per_vehicle_max' => (int) env('TDMV_DEMO_APPLICATIONS_MAX', 2),
    ],
];
