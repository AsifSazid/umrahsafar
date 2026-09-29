-- ─────────────────────────────────────────────────────────────
-- Users — unified table for every logged-in identity (customers
-- AND admin/staff).
--   - `role_sys_ids`      → JSON array of every role this user holds,
--                           e.g. ["THR-ROLE-A1B2","THR-ROLE-C3D4"]
--                           (a user can have more than one role)
--   - `active_role_sys_id`→ which ONE of those roles is currently
--                           active/selected; this is what access
--                           checks actually use:
--     - role_alias = 'client'  → pages/user-dashboard.php only
--     - role_alias != 'client' → admin/index.php only (super-admin, etc.)
-- Replaces the old separate `admin_users` table entirely.
-- ─────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS admin_users;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    sys_id              VARCHAR(40) NOT NULL UNIQUE COMMENT 'e.g. THR-USR-A1B2C3D4',
    uuid                VARCHAR(36) NOT NULL UNIQUE,
    client_sys_id       VARCHAR(40) DEFAULT NULL COMMENT 'optional — links this user to a client record',
    role_sys_ids        JSON NOT NULL COMMENT 'every role this user holds — ["THR-ROLE-...","THR-ROLE-..."]',
    active_role_sys_id  VARCHAR(40) NOT NULL COMMENT 'FK → system_roles.sys_id — the currently-active one of role_sys_ids',
    name                VARCHAR(100) NOT NULL,
    username            VARCHAR(50) UNIQUE DEFAULT NULL COMMENT 'optional — admin/staff login alternative to email',
    email               VARCHAR(100) UNIQUE NOT NULL,
    phone               VARCHAR(25),
    password_hash       VARCHAR(255) NOT NULL,
    is_verified         TINYINT(1) DEFAULT 0,
    is_active           TINYINT(1) DEFAULT 1 COMMENT '0 = login blocked — shows "You are temporarily blocked! Please contact office."',
    metadata            JSON DEFAULT NULL COMMENT '{"created_at":...,"created_by":...,"updated_at":...,"updated_by":...}',
    last_login          TIMESTAMP NULL,
    INDEX idx_email               (email),
    INDEX idx_username             (username),
    INDEX idx_client_sys_id       (client_sys_id),
    INDEX idx_active_role_sys_id  (active_role_sys_id)
) ENGINE=InnoDB;

-- ── Seed: default Super Admin account ──────────────────────────
-- Run data/server/seed-super-admin.php once after this file (it calls
-- PHP's password_hash() live, so the hash is never hand-typed here).
-- Login: superadmin@travhub.com.bd / 11223344 — CHANGE IMMEDIATELY
-- after first login.