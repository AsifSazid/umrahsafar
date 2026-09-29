-- ─────────────────────────────────────────────────────────────
-- Service Levels — Economy / Premium / Luxury package tiers, shown
-- on the Travelers step of both builders (pages/index.php and
-- pages/package-builder.php).
--
-- Seed data is NOT in this file — run data/seed/service-levels.php
-- after this (once written), so sys_id/uuid come from the real
-- generateSysIdAndUuid() generator, same pattern as system_roles/users.
-- ─────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS service_levels;

CREATE TABLE service_levels (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    sys_id       VARCHAR(40) NOT NULL UNIQUE COMMENT 'e.g. US9-26-SVC-00001',
    uuid         VARCHAR(36) NOT NULL UNIQUE,
    name         VARCHAR(80) NOT NULL UNIQUE,
    price        DECIMAL(10,2) DEFAULT 0 COMMENT 'Saudi Arabia Riyals / SAR',
    features     JSON DEFAULT NULL COMMENT 'array of short bullet strings shown on the card',
    is_active    TINYINT(1) DEFAULT 1,
    sort_order   INT DEFAULT 0,
    metadata     JSON DEFAULT NULL COMMENT '{"created_at":...,"created_by":...,"updated_at":...,"updated_by":...}'
) ENGINE=InnoDB;