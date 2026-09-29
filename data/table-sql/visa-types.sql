-- ─────────────────────────────────────────────────────────────
-- Visa Types — Umrah / Tourist / etc., shown on the Choices/Visa
-- step of both builders (pages/index.php and pages/package-builder.php).
--
-- Seed data is NOT in this file — run data/seed/visa-types.php after
-- this, so sys_id/uuid come from the real generateSysIdAndUuid()
-- generator, same pattern as system_roles/users/service_levels.
-- ─────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS visa_types;

CREATE TABLE visa_types (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    sys_id       VARCHAR(40) NOT NULL UNIQUE COMMENT 'e.g. US9-26-VST-00001',
    uuid         VARCHAR(36) NOT NULL UNIQUE,
    name         VARCHAR(80) NOT NULL UNIQUE,
    price        DECIMAL(10,2) DEFAULT 0 COMMENT 'Saudi Arabia Riyals / SAR',
    for_umrah    TINYINT(1) DEFAULT 0 COMMENT '1 = usable for Umrah trips, 0 = not',
    description  VARCHAR(255),
    icon         VARCHAR(50) DEFAULT 'file-text',
    is_active    TINYINT(1) DEFAULT 1,
    sort_order   INT DEFAULT 0,
    metadata     JSON DEFAULT NULL COMMENT '{"created_at":...,"created_by":...,"updated_at":...,"updated_by":...}'
) ENGINE=InnoDB;