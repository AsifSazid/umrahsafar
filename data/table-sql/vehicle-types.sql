-- ─────────────────────────────────────────────────────────────
-- Vehicle Types — Car / HiAce / Bus / etc., used by transport routes
-- (each route's vehicle_info embeds one of these names + a price).
--
-- Seed data is NOT in this file — run data/seed/vehicle-types.php
-- after this, so sys_id/uuid come from the real generateSysIdAndUuid()
-- generator, same pattern as system_roles/users/service_levels/visa_types.
-- ─────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS vehicle_types;

CREATE TABLE vehicle_types (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    sys_id       VARCHAR(40) NOT NULL UNIQUE COMMENT 'e.g. US9-26-VHT-00001',
    uuid         VARCHAR(36) NOT NULL UNIQUE,
    name         VARCHAR(60) NOT NULL UNIQUE,
    icon         VARCHAR(50) DEFAULT 'car',
    capacities   JSON DEFAULT NULL COMMENT '{"seat":5,"luggage":4}',
    images       JSON DEFAULT NULL COMMENT 'array of storage paths, e.g. ["storage/vehicle-types/images/US9-26-VHT-00001-1.jpg"]',
    is_active    TINYINT(1) DEFAULT 1,
    sort_order   INT DEFAULT 0,
    metadata     JSON DEFAULT NULL COMMENT '{"created_at":...,"created_by":...,"updated_at":...,"updated_by":...}'
) ENGINE=InnoDB;