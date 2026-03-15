<?php

describe("POST /auth/security-revoke", function () {

    it("revokes own session and returns security_revoked", function () {
        $login = $this->postJson("/api/v1/auth/login", [
            "email" => "admin@pharmacy.local", "password" => "Admin123!",
        ])->assertStatus(200);
        $this->postJson("/api/v1/auth/security-revoke", [], [
            "Authorization" => "Bearer " . $login->json("access_token"),
        ])->assertStatus(200)->assertJson(["message" => "security_revoked"]);
    });

    it("returns 401 after session is revoked", function () {
        $login = $this->postJson("/api/v1/auth/login", [
            "email" => "admin@pharmacy.local", "password" => "Admin123!",
        ])->assertStatus(200);
        $token = $login->json("access_token");
        $this->postJson("/api/v1/auth/security-revoke", [], [
            "Authorization" => "Bearer " . $token,
        ])->assertStatus(200);
        $this->getJson("/api/v1/auth/me", [
            "Authorization" => "Bearer " . $token,
        ])->assertStatus(401);
    });
});

describe("POST /auth/security-revoke-all", function () {

    it("returns bulk_security_revoked with users_revoked count", function () {
        $login = $this->postJson("/api/v1/auth/login", [
            "email" => "admin@pharmacy.local", "password" => "Admin123!",
        ])->assertStatus(200);
        $this->postJson("/api/v1/auth/security-revoke-all", [], [
            "Authorization" => "Bearer " . $login->json("access_token"),
        ])->assertStatus(200)->assertJsonStructure(["message", "users_revoked"]);
    });
});
