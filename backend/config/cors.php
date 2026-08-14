<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | These settings allow the frontend application to access the backend API
    | from the development host at http://localhost:3000.
    |
    */

    'paths' => ['*'],

    'allowed_methods' => ['*'],

    // Durante desenvolvimento, permitir todas as origens evita bloqueios CORS
    // Em produção, restrinja para domínios confiáveis.
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
