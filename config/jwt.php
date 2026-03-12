<?php

return [
    'public_key_path'  => env('JWT_PUBLIC_KEY_PATH', storage_path('keys/public.pem')),
    'private_key_path' => env('JWT_PRIVATE_KEY_PATH', storage_path('keys/private.pem')),
    'current_kid'      => env('JWT_CURRENT_KID', 'key-2024-01'),
    'ttl_seconds'      => (int) env('ACCESS_TOKEN_TTL_SECONDS', 300),
    'issuer'           => env('JWT_ISSUER', 'pharmacy-auth-service'),
];
