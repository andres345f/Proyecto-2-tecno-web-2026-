<?php

return [
    'base_url' => env('PAGOFACIL_BASE_URL', 'https://masterqr.pagofacil.com.bo/api/services/v2'),
    'token_service' => env('PAGOFACIL_TOKEN_SERVICE'),
    'token_secret' => env('PAGOFACIL_TOKEN_SECRET'),
    'currency' => env('PAGOFACIL_CURRENCY', 2), // 2 = BOB
    'callback_url' => env('PAGOFACIL_CALLBACK_URL'),
    'timeout' => env('PAGOFACIL_TIMEOUT', 30),
    'enable_logs' => env('PAGOFACIL_ENABLE_LOGS', true),
];
