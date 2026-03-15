<?php

describe("Users CRUD", function () {

    beforeEach(function () {
        $login = $this->postJson("/api/v1/auth/login", [
            "email"    => "admin@pharmacy.local",
            "password" => "Admin123!",
        ])->assertStatus(200);
        $this->token = $login->json("access_token");
    });

    it("GET /users returns list", function () {
        $this->getJson("/api/v1/users", [
            "Authorization" => "Bearer {$this->token}",
        ])->assertStatus(200)->assertJsonStructure(["data"]);
    });

    it("POST /users creates a new user", function () {
        $this->postJson("/api/v1/users", [
            "full_name"    => "New User",
            "email"        => "newuser" . time() . "@pharmacy.local",
            "raw_password" => "Test1234x",
            "branch_id"    => 1,
            "locale"       => "en",
        ], ["Authorization" => "Bearer {$this->token}"])
        ->assertStatus(201)->assertJson(["message" => "user_created"]);
    });

    it("GET /users/{id} returns user", function () {
        $userId = \Illuminate\Support\Facades\DB::table("users")->where("email", "admin@pharmacy.local")->value("id");
        $this->getJson("/api/v1/users/" . $userId, [
            "Authorization" => "Bearer {$this->token}",
        ])->assertStatus(200)->assertJsonStructure(["id", "full_name", "email", "is_active"]);
    });

    it("PUT /users/{id} updates user", function () {
        $userId = \Illuminate\Support\Facades\DB::table("users")->where("email", "admin@pharmacy.local")->value("id");
        $this->putJson("/api/v1/users/" . $userId, [
            "full_name" => "System Administrator Updated",
        ], ["Authorization" => "Bearer {$this->token}"])
        ->assertStatus(200)->assertJson(["message" => "user_updated"]);
    });

    it("cannot grant permissions to self", function () {
        $userId = \Illuminate\Support\Facades\DB::table("users")->where("email", "admin@pharmacy.local")->value("id");
        $permId = \Illuminate\Support\Facades\DB::table("permissions")->where("name", "users:read")->value("id");
        $this->postJson("/api/v1/users/" . $userId . "/permissions", [
            "permission_id" => $permId,
        ], ["Authorization" => "Bearer {$this->token}"])
        ->assertStatus(422)->assertJson(["error" => "cannot_grant_permissions_to_self"]);
    });

    it("cannot assign roles to self", function () {
        $userId = \Illuminate\Support\Facades\DB::table("users")->where("email", "admin@pharmacy.local")->value("id");
        $roleId = \Illuminate\Support\Facades\DB::table("roles")->where("name", "super_admin")->value("id");
        $this->postJson("/api/v1/users/" . $userId . "/roles", [
            "role_id" => $roleId,
        ], ["Authorization" => "Bearer {$this->token}"])
        ->assertStatus(422)->assertJson(["error" => "cannot_assign_roles_to_self"]);
    });
});
