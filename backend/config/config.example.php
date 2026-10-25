<?php
// Copy this file to config.php and fill in real values.
// config.php is listed in .gitignore and must never be committed.
return [
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'gym_app',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    'jwt' => [
        'secret'     => 'CHANGE_ME_TO_A_LONG_RANDOM_STRING',
        'issuer'     => 'gym-app',
        'ttl_seconds' => 7200,
    ],
    'external_api' => [
        // Exercise database used on the "Exercises" page (wger)
        'wger_base_url' => 'https://wger.de/api/v2',
        'wger_api_key'  => '',   // optional, leave empty if not needed
        'timeout'       => 5,    // seconds
    ],
    'logs' => [
        'info'  => __DIR__ . '/../logs/info.log',
        'error' => __DIR__ . '/../logs/error.log',
    ],
];
