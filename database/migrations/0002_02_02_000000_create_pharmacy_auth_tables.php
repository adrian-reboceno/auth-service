<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            -- CORE IDENTITY
            CREATE TABLE users (
                id            CHAR(36)      PRIMARY KEY,
                full_name     VARCHAR(120)  NOT NULL,
                email         VARCHAR(254)  NOT NULL UNIQUE,
                password_hash VARCHAR(255)  NOT NULL,
                branch_id     BIGINT        NOT NULL DEFAULT 1,
                locale        ENUM('es','en') NOT NULL DEFAULT 'es',
                is_active     TINYINT(1)    NOT NULL DEFAULT 1,
                password_changed_at TIMESTAMP NULL,
                created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                updated_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );

            -- RBAC — PERMISSIONS AND ROLES
            CREATE TABLE permissions (
                id          CHAR(36)     PRIMARY KEY,
                name        VARCHAR(80)  NOT NULL UNIQUE,
                resource    VARCHAR(40)  NOT NULL,
                action      VARCHAR(40)  NOT NULL,
                description VARCHAR(255),
                created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_resource_action (resource, action)
            );

            CREATE TABLE roles (
                id          CHAR(36)     PRIMARY KEY,
                name        VARCHAR(60)  NOT NULL UNIQUE,
                description VARCHAR(255),
                is_active   TINYINT(1)   NOT NULL DEFAULT 1,
                is_system   TINYINT(1)   NOT NULL DEFAULT 0,
                created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );

            CREATE TABLE role_permissions (
                role_id       CHAR(36)   NOT NULL REFERENCES roles(id)       ON DELETE RESTRICT,
                permission_id CHAR(36)   NOT NULL REFERENCES permissions(id) ON DELETE RESTRICT,
                granted_at    TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP,
                created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (role_id, permission_id)
            );

            -- RBAC — USER ↔ ROLE ASSIGNMENTS
            CREATE TABLE user_roles (
                id       BIGINT   PRIMARY KEY AUTO_INCREMENT,
                user_id  CHAR(36) NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                role_id  CHAR(36) NOT NULL REFERENCES roles(id) ON DELETE RESTRICT,
                created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_user_role (user_id, role_id)
            );

            -- RBAC — DIRECT USER PERMISSIONS
            CREATE TABLE user_permissions (
                id            BIGINT    PRIMARY KEY AUTO_INCREMENT,
                user_id       CHAR(36)  NOT NULL REFERENCES users(id)       ON DELETE CASCADE,
                permission_id CHAR(36)  NOT NULL REFERENCES permissions(id) ON DELETE RESTRICT,
                is_active     TINYINT(1) NOT NULL DEFAULT 1,
                granted_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                expires_at    TIMESTAMP NULL,
                UNIQUE KEY uq_user_permission (user_id, permission_id)
            );

            -- AUDIT — ASSIGNMENT AUTHORSHIP LOG
            CREATE TABLE assignment_log (
                id                   BIGINT      PRIMARY KEY AUTO_INCREMENT,
                action               VARCHAR(60) NOT NULL,
                target_user_id       CHAR(36)    NULL,
                target_role_id       CHAR(36)    NULL,
                target_permission_id CHAR(36)    NULL,
                performed_by         CHAR(36)    NOT NULL,
                detail_json          JSON,
                ip_address           VARCHAR(45),
                created_at           TIMESTAMP   DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TRIGGER trg_assignment_log_no_update
            BEFORE UPDATE ON assignment_log FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'assignment_log is append-only: UPDATE not permitted';
            END;

            CREATE TRIGGER trg_assignment_log_no_delete
            BEFORE DELETE ON assignment_log FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'assignment_log is append-only: DELETE not permitted';
            END;

            -- TOKENS
            CREATE TABLE refresh_tokens (
                id         BIGINT    PRIMARY KEY AUTO_INCREMENT,
                user_id    CHAR(36)  NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                token_hash CHAR(64)  NOT NULL UNIQUE,
                status     ENUM('active','revoked','expired','used') NOT NULL DEFAULT 'active',
                expires_at TIMESTAMP NOT NULL,
                ip_address VARCHAR(45),
                user_agent VARCHAR(512),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE active_tokens (
                jti        CHAR(36)  PRIMARY KEY,
                user_id    CHAR(36)  NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                expires_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE jti_blacklist (
                jti        CHAR(36)   PRIMARY KEY,
                user_id    CHAR(36)   NOT NULL,
                reason     VARCHAR(120),
                expires_at TIMESTAMP  NOT NULL,
                created_at TIMESTAMP  DEFAULT CURRENT_TIMESTAMP
            );

            -- AUDIT LOG — IDENTITY EVENTS
            CREATE TABLE auth_audit_log (
                id          BIGINT      PRIMARY KEY AUTO_INCREMENT,
                user_id     CHAR(36),
                action      VARCHAR(80) NOT NULL,
                detail_json JSON,
                ip_address  VARCHAR(45),
                created_at  TIMESTAMP   DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TRIGGER trg_auth_audit_log_no_update
            BEFORE UPDATE ON auth_audit_log FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'auth_audit_log is append-only: UPDATE not permitted';
            END;

            CREATE TRIGGER trg_auth_audit_log_no_delete
            BEFORE DELETE ON auth_audit_log FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'auth_audit_log is append-only: DELETE not permitted';
            END;
            CREATE TABLE sessions (
                id VARCHAR(255) PRIMARY KEY,
                user_id CHAR(36) NULL,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                payload LONGTEXT NOT NULL,
                last_activity INTEGER NOT NULL,
                INDEX idx_sessions_user_id (user_id),
                INDEX idx_sessions_last_activity (last_activity)
            );

            -- CACHE MANAGEMENT (Used for Rate Limiting and Session metadata)
            CREATE TABLE cache (
                `key`   VARCHAR(255) PRIMARY KEY,
                `value` MEDIUMTEXT   NOT NULL,
                expiration INTEGER   NOT NULL
            );

            CREATE TABLE cache_locks (
                `key`      VARCHAR(255) PRIMARY KEY,
                owner      VARCHAR(255) NOT NULL,
                expiration INTEGER      NOT NULL
            );

            -- INDEXES
            CREATE INDEX idx_user_roles_user          ON user_roles(user_id);
            CREATE INDEX idx_user_roles_role          ON user_roles(role_id);
            CREATE INDEX idx_user_permissions_user    ON user_permissions(user_id, is_active);
            CREATE INDEX idx_user_permissions_expiry  ON user_permissions(expires_at, is_active);
            CREATE INDEX idx_role_permissions_role    ON role_permissions(role_id);
            CREATE INDEX idx_permissions_resource     ON permissions(resource, action);
            CREATE INDEX idx_refresh_tokens_hash      ON refresh_tokens(token_hash);
            CREATE INDEX idx_refresh_tokens_user      ON refresh_tokens(user_id, status);
            CREATE INDEX idx_active_tokens_user       ON active_tokens(user_id);
            CREATE INDEX idx_active_tokens_expiry     ON active_tokens(expires_at);
            CREATE INDEX idx_jti_blacklist_user       ON jti_blacklist(user_id);
            CREATE INDEX idx_jti_blacklist_expiry     ON jti_blacklist(expires_at);
            CREATE INDEX idx_assignment_log_user      ON assignment_log(target_user_id, created_at);
            CREATE INDEX idx_auth_audit_user          ON auth_audit_log(user_id, created_at);
            CREATE INDEX idx_auth_audit_action        ON auth_audit_log(action, created_at);
        ");
    }

    public function down(): void
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_auth_audit_log_no_delete;
            DROP TRIGGER IF EXISTS trg_auth_audit_log_no_update;
            DROP TABLE IF EXISTS auth_audit_log;
            
            DROP TABLE IF EXISTS jti_blacklist;
            DROP TABLE IF EXISTS active_tokens;
            DROP TABLE IF EXISTS refresh_tokens;
            
            DROP TRIGGER IF EXISTS trg_assignment_log_no_delete;
            DROP TRIGGER IF EXISTS trg_assignment_log_no_update;
            DROP TABLE IF EXISTS assignment_log;
            
            DROP TABLE IF EXISTS user_permissions;
            DROP TABLE IF EXISTS user_roles;
            DROP TABLE IF EXISTS role_permissions;
            
            DROP TABLE IF EXISTS permissions;
            DROP TABLE IF EXISTS roles;
            DROP TABLE IF EXISTS users;
        ");
    }
};
