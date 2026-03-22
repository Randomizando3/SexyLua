<?php

declare(strict_types=1);

return [
    'name' => 'SexyLua',
    'tagline' => 'O despertar da sua metamorfose sexual',
    'base_url' => getenv('SEXYLUA_BASE_URL') ?: '',
    'timezone' => getenv('SEXYLUA_TIMEZONE') ?: 'America/Sao_Paulo',
    'demo_port' => (int) (getenv('SEXYLUA_PORT') ?: 8088),
    'storage_driver' => getenv('SEXYLUA_STORAGE_DRIVER') ?: 'json',
    'supports_postgresql' => true,
];
