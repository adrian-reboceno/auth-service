<?php

describe("Permissions CRUD", function () {

    beforeEach(function () {
        $login = $this->postJson("/api/v1/auth/login", [
            "email"    => "admin@pharmacy.local",
            "password" => "Admin123!",
        ])->assertStatus(200);
        $this->token = $login->json("access_token");
    });

    it("GET /permissions returns list with data key", function () {
        $this->getJson("/api/v1/permissions", [
            "Authorization" => "Bearer {$this->token}",
        ])->assertStatus(200)->assertJsonStructure(["data"]);
    });

    it("POST /permissions creates a new permission", function () {
        $response = $this->postJson("/api/v1/permissions", [
            "name"        => "reports:export",
            "description" => "Export reports",
        ], ["Authorization" => "Bearer {$this->token}"])
        ->assertStatus(201)->assertJson(["message" => "permission_created"]);
        expect($response->json("id"))->toBeString()->not->toBeEmpty();
    });

    it("GET /permissions/{id} returns permission", function () {
        $created = $this->postJson("/api/v1/permissions", [
            "name" => "reports:delete", "description" => "Delete reports",
        ], ["Authorization" => "Bearer {$this->token}"])->assertStatus(201);
        $this->getJson("/api/v1/permissions/" . $created->json("id"), [
            "Authorization" => "Bearer {$this->token}",
        ])->assertStatus(200)->assertJsonStructure(["id", "name", "description"]);
    });

    it("PUT /permissions/{id} updates description", function () {
        $created = $this->postJson("/api/v1/permissions", [
            "name" => "reports:archive", "description" => "Archive reports",
        ], ["Authorization" => "Bearer {$this->token}"])->assertStatus(201);
        $this->putJson("/api/v1/permissions/" . $created->json("id"), [
            "description" => "Updated description",
        ], ["Authorization" => "Bearer {$this->token}"])
        ->assertStatus(200)->assertJson(["message" => "permission_updated"]);
    });

    it("GET /permissions/{id} returns 404 for unknown id", function () {
        $this->getJson("/api/v1/permissions/00000000-0000-4000-a000-000000000000", [
            "Authorization" => "Bearer {$this->token}",
        ])->assertStatus(404);
    });
});
