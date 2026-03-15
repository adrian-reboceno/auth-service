<?php

describe("Roles CRUD", function () {

    beforeEach(function () {
        $login = $this->postJson("/api/v1/auth/login", [
            "email"    => "admin@pharmacy.local",
            "password" => "Admin123!",
        ])->assertStatus(200);
        $this->token = $login->json("access_token");
    });

    it("GET /roles returns list", function () {
        $this->getJson("/api/v1/roles", [
            "Authorization" => "Bearer {$this->token}",
        ])->assertStatus(200)->assertJsonStructure(["data"]);
    });

    it("POST /roles creates role with description", function () {
        $this->postJson("/api/v1/roles", [
            "name" => "test_role", "description" => "Test role",
        ], ["Authorization" => "Bearer {$this->token}"])
        ->assertStatus(201)->assertJson(["message" => "role_created"]);
    });

    it("POST /roles returns error on duplicate name", function () {
        $this->postJson("/api/v1/roles", [
            "name" => "super_admin",
        ], ["Authorization" => "Bearer {$this->token}"])
        ->assertStatus(422)->assertJson(["error" => "role_already_exists"]);
    });

    it("PUT /roles/{id} updates description", function () {
        $this->postJson("/api/v1/roles", [
            "name" => "update_test_role", "description" => "Original",
        ], ["Authorization" => "Bearer {$this->token}"])->assertStatus(201);
        $roleId = \Illuminate\Support\Facades\DB::table("roles")->where("name", "update_test_role")->value("id");
        $this->putJson("/api/v1/roles/" . $roleId, [
            "name" => "update_test_role", "description" => "Updated",
        ], ["Authorization" => "Bearer {$this->token}"])
        ->assertStatus(200)->assertJson(["message" => "role_updated"]);
    });

    it("DELETE /roles/{id} deactivates role", function () {
        $this->postJson("/api/v1/roles", [
            "name" => "delete_test_role",
        ], ["Authorization" => "Bearer {$this->token}"])->assertStatus(201);
        $roleId = \Illuminate\Support\Facades\DB::table("roles")->where("name", "delete_test_role")->value("id");
        $this->deleteJson("/api/v1/roles/" . $roleId, [], [
            "Authorization" => "Bearer {$this->token}",
        ])->assertStatus(200)->assertJson(["message" => "role_deactivated"]);
    });

    it("system role cannot be deactivated", function () {
        $roleId = \Illuminate\Support\Facades\DB::table("roles")->where("name", "super_admin")->value("id");
        $this->deleteJson("/api/v1/roles/" . $roleId, [], [
            "Authorization" => "Bearer {$this->token}",
        ])->assertStatus(422)->assertJson(["error" => "system_role_protected"]);
    });
});
