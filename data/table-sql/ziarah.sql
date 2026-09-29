-- ─────────────────────────────────────────────────────────────
-- Ziarah — historical/religious site tours (Makkah/Madinah), shown
-- on the Ziarah step of both builders, optionally linked to transport
-- routes and a Moyallem guide.
--
-- Seed data is NOT in this file — run data/seed/ziarah.php after
-- this, so sys_id/uuid come from the real generateSysIdAndUuid()
-- generator, same pattern as every other table in this project.
-- ─────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS ziarah;

CREATE TABLE ziarah (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    sys_id              VARCHAR(40) NOT NULL UNIQUE COMMENT 'e.g. US9-26-ZIR-00001',
    uuid                VARCHAR(36) NOT NULL UNIQUE,
    name                VARCHAR(200) NOT NULL,
    description         VARCHAR(500) COMMENT 'plain text, no HTML',
    possible_duration    VARCHAR(100) COMMENT 'free text, e.g. "Half Day", "3-4 hours"',
    itinerary           JSON NOT NULL COMMENT 'array of {title, description} — description supports HTML tags',
    route_sys_ids       JSON NOT NULL COMMENT 'array of transport_routes.sys_id',
    moyallem_enabled    TINYINT(1) DEFAULT 0,
    price               DECIMAL(10,2) DEFAULT 0 COMMENT 'Saudi Arabian Riyals (SAR)',
    is_active           TINYINT(1) DEFAULT 1,
    sort_order          INT DEFAULT 0,
    metadata            JSON DEFAULT NULL COMMENT '{"created_at":...,"created_by":...,"updated_at":...,"updated_by":...}'
) ENGINE=InnoDB;