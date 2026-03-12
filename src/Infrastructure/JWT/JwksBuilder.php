<?php

namespace Infrastructure\JWT;

final class JwksBuilder
{
    public function build(): array
    {
        $publicKeyPath = config('jwt.public_key_path');
        $kid           = config('jwt.current_kid');

        $keyResource = openssl_pkey_get_public(file_get_contents($publicKeyPath));
        $details     = openssl_pkey_get_details($keyResource);

        return [
            'keys' => [
                [
                    'kty' => 'RSA',
                    'use' => 'sig',
                    'alg' => 'RS256',
                    'kid' => $kid,
                    'n'   => rtrim(strtr(base64_encode($details['rsa']['n']), '+/', '-_'), '='),
                    'e'   => rtrim(strtr(base64_encode($details['rsa']['e']), '+/', '-_'), '='),
                ],
            ],
        ];
    }
}
