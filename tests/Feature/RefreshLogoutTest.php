<?php

describe("POST /auth/refresh", function () {

    it("returns new access_token and refresh_token", function () {
        $login = $this->postJson("/api/v1/auth/login", [
            "email" => "admin@pharmacy.local", "password" => "Admin123!",
        ])->assertStatus(200);
        $this->postJson("/api/v1/auth/refresh", [
            "refresh_token" => $login->json("refresh_token"),
        ])->assertStatus(200)->assertJsonStructure(["access_token", "token_type", "refresh_token", "expires_in"]);
    });

    it("returns 422 when refresh_token is missing", function () {
        $this->postJson("/api/v1/auth/refresh", [])->assertStatus(422);
    });

    it("returns error on invalid refresh_token", function () {
        $this->postJson("/api/v1/auth/refresh", [
            "refresh_token" => "invalid-token-xyz",
        ])->assertStatus(401);
    });
});

describe("POST /auth/logout", function () {

    it("returns logged_out message", function () {
        $login = $this->postJson("/api/v1/auth/login", [
            "email" => "admin@pharmacy.local", "password" => "Admin123!",
        ])->assertStatus(200);
        $this->postJson("/api/v1/auth/logout", [], [
            "Authorization" => "Bearer " . $login->json("access_token"),
        ])->assertStatus(200)->assertJson(["message" => "logged_out"]);
    });

    it("returns 401 without token", function () {
        $this->postJson("/api/v1/auth/logout")->assertStatus(401);
    });
});
