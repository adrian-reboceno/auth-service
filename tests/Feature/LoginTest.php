<?php

describe('POST /auth/login', function () {

    it('returns access_token and refresh_token on valid credentials', function () {
        $this->postJson('/api/v1/auth/login', [
            'email'    => 'admin@pharmacy.local',
            'password' => 'Admin123!',
        ])
        ->assertStatus(200)
        ->assertJsonStructure([
            'access_token', 'token_type', 'refresh_token', 'expires_in',
            'user' => ['id', 'full_name', 'email', 'roles', 'branch_id', 'locale'],
        ]);
    });

    it('returns 401 on invalid password', function () {
        $this->postJson('/api/v1/auth/login', [
            'email'    => 'admin@pharmacy.local',
            'password' => 'WrongPassword1',
        ])->assertStatus(401)->assertJson(['error' => 'invalid_credentials']);
    });

    it('returns 401 on unknown email', function () {
        $this->postJson('/api/v1/auth/login', [
            'email'    => 'unknown@pharmacy.local',
            'password' => 'Admin123!',
        ])->assertStatus(401);
    });

    it('returns 422 when email is missing', function () {
        $this->postJson('/api/v1/auth/login', ['password' => 'Admin123!'])->assertStatus(422);
    });

    it('JWT payload contains permissions as names not UUIDs', function () {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'admin@pharmacy.local',
            'password' => 'Admin123!',
        ])->assertStatus(200);

        $token   = $response->json('access_token');
        $parts   = explode('.', $token);
        $payload = json_decode(base64_decode(str_pad($parts[1], strlen($parts[1]) + (4 - strlen($parts[1]) % 4) % 4, '=')), true);

        expect($payload['permissions'])->toBeArray()->not->toBeEmpty();
        foreach ($payload['permissions'] as $perm) {
            expect($perm)->toContain(':');
        }
    });
});
