-- ═══════════════════════════════════════════════════════════════
-- TravHub Umrah Platform — Schema v3
-- New Transport model: multi-stop routes (slug-based) + dynamic
-- vehicle_info JSON, and a proper Vehicle Type CRUD table.
-- Run AFTER schema.sql + schema_v2.sql.
-- Old transport_routes / transport_prices tables are left intact.
-- ═══════════════════════════════════════════════════════════════

-- ── 1. Vehicle Types (admin-managed list, e.g. Car, HiAce, Bus...) ──
CREATE TABLE IF NOT EXISTS vehicle_types (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    uuid         VARCHAR(36) NOT NULL UNIQUE,
    name         VARCHAR(60) NOT NULL UNIQUE,
    icon         VARCHAR(50) DEFAULT 'car',
    is_active    TINYINT(1) DEFAULT 1,
    sort_order   INT DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO vehicle_types (uuid, name, icon, sort_order) VALUES
(UUID(), 'Car',      'car',         1),
(UUID(), 'HiAce',    'truck',       2),
(UUID(), 'Bus',      'bus',         3),
(UUID(), 'Minibus',  'bus',         4),
(UUID(), 'Coaster',  'bus',         5),
(UUID(), 'GMC/JMC',  'car',         6),
(UUID(), 'H1',       'car',         7),
(UUID(), 'Staria',   'car',         8),
(UUID(), 'Train',    'train-front', 9);

-- ── 2. Transport Routes v2 (multi-stop, slug-based) ──────────────
-- to_json: JSON array of destinations after `from_loc`, e.g. ["Madina","Makka"]
--          (supports circuits like Makka -> Madina -> Makka)
-- vehicle_info: JSON array of { vehicle_type, price_sar }, dynamic length
CREATE TABLE IF NOT EXISTS transport_routes_v2 (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    uuid          VARCHAR(36) NOT NULL UNIQUE,
    sys_id        VARCHAR(40) NOT NULL UNIQUE COMMENT 'short human-friendly id, derived from slug',
    slug          VARCHAR(150) NOT NULL UNIQUE,
    from_loc      VARCHAR(100) NOT NULL,
    to_json       JSON NOT NULL COMMENT 'ordered array of destination stops after from_loc',
    vehicle_info  JSON NOT NULL COMMENT 'array of {vehicle_type, price_sar}',
    is_active     TINYINT(1) DEFAULT 1,
    sort_order    INT DEFAULT 0,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
