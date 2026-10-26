<?php

return [
    'admin_email' => env('ADMIN_EMAIL'),
    'admin_password' => env('ADMIN_PASSWORD'),
    'maintenance_enabled' => (bool) env('BINA_MAINTENANCE_ENABLED', false),
    'maintenance_token' => env('BINA_MAINTENANCE_TOKEN'),
];
