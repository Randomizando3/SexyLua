<?php

declare(strict_types=1);

$stunUrls = array_values(array_filter(array_map(
    static fn (string $value): string => trim($value),
    explode(',', getenv('SEXYLUA_RTC_STUN_URLS') ?: 'stun:stun.l.google.com:19302,stun:stun1.l.google.com:19302')
)));

$turnUrls = array_values(array_filter(array_map(
    static fn (string $value): string => trim($value),
    explode(',', getenv('SEXYLUA_RTC_TURN_URLS') ?: (getenv('SEXYLUA_RTC_TURN_URL') ?: ''))
)));

$iceServers = [];

if ($stunUrls !== []) {
    $iceServers[] = [
        'urls' => $stunUrls,
    ];
}

if ($turnUrls !== []) {
    $turnServer = [
        'urls' => $turnUrls,
    ];

    $turnUsername = trim((string) (getenv('SEXYLUA_RTC_TURN_USERNAME') ?: ''));
    $turnCredential = trim((string) (getenv('SEXYLUA_RTC_TURN_CREDENTIAL') ?: ''));

    if ($turnUsername !== '') {
        $turnServer['username'] = $turnUsername;
    }

    if ($turnCredential !== '') {
        $turnServer['credential'] = $turnCredential;
    }

    $iceServers[] = $turnServer;
}

$iceTransportPolicy = strtolower((string) (getenv('SEXYLUA_RTC_ICE_TRANSPORT_POLICY') ?: 'all'));

return [
    'name' => 'SexyLua',
    'tagline' => 'O despertar da sua metamorfose sexual',
    'base_url' => getenv('SEXYLUA_BASE_URL') ?: '',
    'timezone' => getenv('SEXYLUA_TIMEZONE') ?: 'America/Sao_Paulo',
    'demo_port' => (int) (getenv('SEXYLUA_PORT') ?: 8088),
    'storage_driver' => getenv('SEXYLUA_STORAGE_DRIVER') ?: 'json',
    'supports_postgresql' => true,
    'rtc_ice_servers' => $iceServers,
    'rtc_ice_transport_policy' => in_array($iceTransportPolicy, ['all', 'relay'], true) ? $iceTransportPolicy : 'all',
];
