<?php

describe('GET /auth/jwks', function () {

    it('returns 200 with no authentication', function () {
        $this->getJson('/api/v1/auth/jwks')->assertStatus(200);
    });

    it('returns a valid JWK Set structure', function () {
        $this->getJson('/api/v1/auth/jwks')
            ->assertStatus(200)
            ->assertJsonStructure(['keys' => [['kty', 'use', 'alg', 'kid', 'n', 'e']]]);
    });

    it('includes Cache-Control header in response', function () {
        $response = $this->getJson('/api/v1/auth/jwks');
        $response->assertStatus(200);
        expect($response->headers->has('Cache-Control'))->toBeTrue();
    });

});
