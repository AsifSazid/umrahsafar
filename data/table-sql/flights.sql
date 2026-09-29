-- ─────────────────────────────────────────────────────────────
-- Flights — fare types (Flexible/Fixed etc.), each priced by flight
-- type (Direct/Connecting), shown on the Flight step of both builders.
--
-- Seed data is NOT in this file — run data/seed/flights.php after
-- this, so sys_id/uuid come from the real generateSysIdAndUuid()
-- generator, same pattern as every other table in this project.
-- ─────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS flights;

CREATE TABLE flights (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    sys_id         VARCHAR(40) NOT NULL UNIQUE COMMENT 'e.g. US9-26-FLT-00001',
    uuid           VARCHAR(36) NOT NULL UNIQUE,
    fare_type      VARCHAR(60) NOT NULL COMMENT 'e.g. Flexible, Fixed',
    type_prices    JSON NOT NULL COMMENT '[{"type":"Direct","price":650}] — price is in Saudi Arabian Riyals (SAR), plain number, not multi-currency',
    note           VARCHAR(255) DEFAULT 'Price may differ based on season and availability.',
    is_active      TINYINT(1) DEFAULT 1,
    sort_order     INT DEFAULT 0,
    metadata       JSON DEFAULT NULL COMMENT '{"created_at":...,"created_by":...,"updated_at":...,"updated_by":...}'
) ENGINE=InnoDB;