-- ─────────────────────────────────────────────────────────────
-- Transport Routes — replaces the old v1 `transport_routes` +
-- `transport_prices` pivot table, AND the old `transport_routes_v2`
-- table, with a single final `transport_routes` table.
--
-- `vehicle_options` is a JSON array of {vehicle_type_sys_id, price} —
-- price is a plain Saudi Arabia Riyal (SAR) number (this is the
-- master/reference price;
-- multi-currency conversion happens only at booking-snapshot time in
-- custom_builds, not here). vehicle_type_sys_id references
-- vehicle_types.sys_id, so renaming a vehicle type never breaks the
-- link (unlike the old version, which embedded the vehicle name as a
-- plain string with no relational link at all).
--
-- Seed data is NOT in this file — run data/seed/transport-routes.php
-- after this, so sys_id/uuid come from the real generateSysIdAndUuid()
-- generator, same pattern as every other table in this project.
-- ─────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS transport_routes;   -- this file's own table, for the fresh re-create below

CREATE TABLE transport_routes (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    sys_id            VARCHAR(40) NOT NULL UNIQUE COMMENT 'e.g. US9-26-TRS-00001',
    uuid              VARCHAR(36) NOT NULL UNIQUE,
    slug              VARCHAR(150) NOT NULL UNIQUE,
    origin            VARCHAR(100) NOT NULL,
    destinations      JSON NOT NULL COMMENT 'ordered array of destination stops after origin',
    vehicle_options   JSON NOT NULL COMMENT '[{"vehicle_type_sys_id":"US9-26-VHT-00001","price":100}] — price is in Saudi Arabia Riyal (SAR), plain number, not multi-currency',
    is_active         TINYINT(1) DEFAULT 1,
    sort_order        INT DEFAULT 0,
    metadata          JSON DEFAULT NULL COMMENT '{"created_at":...,"created_by":...,"updated_at":...,"updated_by":...}'
) ENGINE=InnoDB;