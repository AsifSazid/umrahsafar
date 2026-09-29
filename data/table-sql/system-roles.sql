-- ─────────────────────────────────────────────────────────────
-- System Roles — manages the set of roles admins/users can hold.
-- `metadata` tracks who/when created & last updated this role row.
-- `data_json` is a full snapshot of the row, kept in sync on every
-- insert/update — a safety net so nothing is lost even if a
-- structured column is ever misread or dropped later.
--
-- Seed data (Super Admin / Guest / Client) is NOT in this file —
-- run data/server/seed-system-roles.php after this, so sys_id/uuid
-- come from the real generateSysIdAndUuid() generator (keeps
-- sys_id_counters in sync, instead of hardcoded values here going
-- out of sync with what the generator would produce next).
-- ─────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS system_roles;

CREATE TABLE system_roles (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    sys_id      VARCHAR(40) NOT NULL UNIQUE COMMENT 'e.g. 9-26-SYR-00001',
    uuid        VARCHAR(36) NOT NULL UNIQUE,
    name        VARCHAR(80) NOT NULL,
    role_alias  VARCHAR(60) NOT NULL UNIQUE COMMENT 'e.g. super-admin, guest',
    metadata    JSON DEFAULT NULL COMMENT '{"created_at":...,"created_by":...,"updated_at":...,"updated_by":...}',
    data_json   JSON DEFAULT NULL COMMENT 'full row snapshot, resynced on every insert/update'
) ENGINE=InnoDB;