<?php

declare(strict_types=1);

return [
    'driver' => 'json',
    'postgresql' => [
        'dsn' => getenv('SEXYLUA_PG_DSN') ?: 'pgsql:host=127.0.0.1;port=5432;dbname=sexylua',
        'user' => getenv('SEXYLUA_PG_USER') ?: 'postgres',
        'password' => getenv('SEXYLUA_PG_PASSWORD') ?: '',
    ],
];
