<?php

describe("GET /auth/me", function () {

    it("returns user payload from JWT", function () {
        $login = $this->postJson("/api/v1/auth/login", [
            "email" => "admin@pharmacy.local", "password" => "Admin123!",
        ])->assertStatus(200);
        $this->getJson("/api/v1/auth/me", [
            "Authorization" => "Bearer " . $login->json("access_token"),
        ])->assertStatus(200)->assertJsonStructure(["user_id", "roles", "permissions", "jti"]);
    });

    it("returns 401 without token", function () {
        $this->getJson("/api/v1/auth/me")->assertStatus(401);
    });

    it("returns 401 with expired token", function () {
        $expiredToken = $this->forgeJwtWithClaims(["exp" => now()->subMinute()->timestamp]);
        $this->getJson("/api/v1/auth/me", [
            "Authorization" => "Bearer " . $expiredToken,
        ])->assertStatus(401);
    });
});
