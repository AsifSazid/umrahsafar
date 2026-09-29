-- ══════════════════════════════════════════════════════════════
-- TravHub Umrah Platform — Full Schema (merged: v1 + v2 + v3)
-- Run this ONE file top to bottom for a fresh install.
-- All statements are idempotent (IF NOT EXISTS / INSERT IGNORE),
-- so it is also safe to re-run on an existing database.
-- ══════════════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS travhub_umrah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE travhub_umrah;

-- ─────────────────────────────────────────────────────────────
-- PART 1 — Core tables (formerly schema.sql)
-- ─────────────────────────────────────────────────────────────

-- Bookings
CREATE TABLE IF NOT EXISTS bookings (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    booking_ref         VARCHAR(20) UNIQUE NOT NULL,
    customer_name       VARCHAR(100) NOT NULL,
    customer_email      VARCHAR(100) NOT NULL,
    customer_phone      VARCHAR(25) NOT NULL,
    package_id          INT DEFAULT NULL,
    package_name        VARCHAR(200),
    departure_date      DATE DEFAULT NULL,
    return_date         DATE DEFAULT NULL,
    adults              INT DEFAULT 1,
    children            INT DEFAULT 0,
    infants             INT DEFAULT 0,
    makkah_nights       INT DEFAULT 0,
    madinah_nights      INT DEFAULT 0,
    hotel_category      VARCHAR(50),
    transport_type      VARCHAR(50),
    visa_type           VARCHAR(50),
    total_price_bdt     DECIMAL(12,2) DEFAULT 0,
    total_price_usd     DECIMAL(10,2) DEFAULT 0,
    total_price_sar     DECIMAL(10,2) DEFAULT 0,
    status              ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    notes               TEXT,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status     (status),
    INDEX idx_created    (created_at),
    INDEX idx_ref        (booking_ref)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────────
-- bookings.status — expand to the full status lifecycle.
-- Safe 3-step migration: widen to VARCHAR first (so old lowercase
-- values remain valid — an ENUM superset won't work here since the
-- default collation is case-insensitive and would reject 'confirmed'
-- and 'Confirmed' as duplicate labels), remap existing rows to their
-- new equivalents, then narrow to the final ENUM value set. Re-running
-- this block on an already-migrated DB is harmless — the UPDATEs
-- simply match nothing the second time.
-- ─────────────────────────────────────────────────────────────
ALTER TABLE bookings MODIFY COLUMN status VARCHAR(30) DEFAULT 'pending';

UPDATE bookings SET status = 'Submitted' WHERE status IN ('pending','draft','submitted');
UPDATE bookings SET status = 'Confirmed' WHERE status = 'confirmed';
UPDATE bookings SET status = 'Cancelled' WHERE status = 'cancelled';

ALTER TABLE bookings MODIFY COLUMN status ENUM(
    'Submitted','Contacted','Visa Processing','Visa Approved','Visa Rejected',
    'Hotel Processing','Hotel Confirmed','Flight Processing','Flight Confirmed',
    'Flight Date Changed','Payment Pending','Payment Received','Confirmed',
    'On Hold','Cancelled','Completed'
) DEFAULT 'Submitted';

-- Contact Inquiries
CREATE TABLE IF NOT EXISTS contact_inquiries (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(100),
    phone       VARCHAR(25),
    subject     VARCHAR(200),
    message     TEXT NOT NULL,
    ip_address  VARCHAR(45),
    is_read     TINYINT(1) DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB;

-- Admin Users
CREATE TABLE IF NOT EXISTS admin_users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(50) UNIQUE NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    email           VARCHAR(100),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login      TIMESTAMP NULL
) ENGINE=InnoDB;

-- Insert default admin (username: admin, password: TravHub@2026)
-- CHANGE THIS PASSWORD IMMEDIATELY AFTER FIRST LOGIN
INSERT IGNORE INTO admin_users (username, password_hash, email)
VALUES (
    'admin',
    '$2y$12$5XJ2Q3K7mP1N8W9oL6R4vu9f2YzXqA3CmD7eHgI0JkV5tWnBsOp6a',
    'admin@travhub.com.bd'
);
-- To generate a new hash in PHP: echo password_hash('YourNewPassword', PASSWORD_BCRYPT);

-- Exchange rate cache placeholder
-- (file-based cache used in production — see api/exchange-rates.php)


-- ─────────────────────────────────────────────────────────────
-- PART 2 — v2 additions (formerly schema_v2.sql)
-- ─────────────────────────────────────────────────────────────

-- ── 1. Site Settings (replaces all hardcoded config) ──────────
CREATE TABLE IF NOT EXISTS site_settings (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_val TEXT,
    label       VARCHAR(200),
    group_name  VARCHAR(50) DEFAULT 'general',
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO site_settings (setting_key, setting_val, label, group_name) VALUES
('whatsapp_number',   '+8801XXXXXXXXX',       'WhatsApp Number',        'contact'),
('admin_email',       'info@travhub.com.bd',  'Admin Email',            'contact'),
('bkash_number',      '01XXXXXXXXX',          'bKash Number',           'payment'),
('bkash_type',        'Personal',             'bKash Account Type',     'payment'),
('bank_name',         'Dutch-Bangla Bank',    'Bank Name',              'payment'),
('bank_account',      'XXXXXXXXXXXX',         'Bank Account Number',    'payment'),
('bank_holder',       'TravHub Ltd',          'Account Holder Name',    'payment'),
('exchange_api_key',  'YOUR_KEY_HERE',        'Exchange Rate API Key',  'api'),
('site_name',         'TravHub',              'Site Name',              'general'),
('site_url',          'https://travhub.com.bd','Site URL',              'general');

-- ── 2. Packages (moved from static PHP array) ─────────────────
CREATE TABLE IF NOT EXISTS packages (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(200) NOT NULL,
    slug         VARCHAR(200) UNIQUE NOT NULL,
    duration     INT DEFAULT 7,
    budget       ENUM('economy','premium','luxury') DEFAULT 'economy',
    stars        TINYINT DEFAULT 3,
    price_sar    DECIMAL(10,2) DEFAULT 0,
    badge        VARCHAR(50),
    rating       DECIMAL(3,1) DEFAULT 4.5,
    reviews      INT DEFAULT 0,
    city         VARCHAR(100) DEFAULT 'Makkah & Madinah',
    flight       VARCHAR(100),
    hotel        VARCHAR(200),
    transport    VARCHAR(100),
    includes_json TEXT COMMENT 'JSON array of included items',
    excludes_json TEXT COMMENT 'JSON array of excluded items',
    itinerary_json TEXT COMMENT 'JSON array of day objects',
    is_active    TINYINT(1) DEFAULT 1,
    sort_order   INT DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_budget (budget)
) ENGINE=InnoDB;

-- Seed default packages
INSERT IGNORE INTO packages (name, slug, duration, budget, stars, price_sar, badge, rating, reviews, city, flight, hotel, transport, includes_json, excludes_json, itinerary_json) VALUES
('Economy Essence', 'economy-essence', 7, 'economy', 3, 3500, 'Best Value', 4.2, 120, 'Makkah & Madinah', 'Economy Class', '3★ Hotel — 600m from Haram', 'Shared Bus',
 '["Umrah Visa","Return flight (economy)","3★ accommodation","Shared airport transfers","Group Ziyarah tour","Basic travel insurance"]',
 '["Personal shopping","Additional excursions","Tipping"]',
 '[{"day":1,"title":"Arrival Jeddah","desc":"Arrive Jeddah, group transfer to Makkah hotel"},{"day":2,"title":"Perform Umrah","desc":"Perform Tawaf, Sai and complete Umrah rites with group guide"},{"day":3,"title":"Ibadah & Rest","desc":"Free time for voluntary prayers and rest near Haram"},{"day":4,"title":"Makkah Ziyarah","desc":"Jabal al-Noor, Cave of Hira, Jabal Thawr, Arafat, Mina"},{"day":5,"title":"Transfer to Madinah","desc":"Coach to Madinah, check-in hotel near Masjid Nabawi"},{"day":6,"title":"Masjid al-Nabawi","desc":"Visit Rawdah, send salawat, Ziyarah of Madinah sites"},{"day":7,"title":"Departure","desc":"Breakfast, transfer to Jeddah airport, fly home"}]'),

('Premium Spiritual', 'premium-spiritual', 14, 'premium', 4, 5800, 'Most Popular', 4.8, 245, 'Makkah & Madinah', 'Direct Flight', '4★ Hotel — 200m from Haram', 'Private Car',
 '["Umrah Visa","Direct return flight","4★ accommodation","Private airport transfers","Private Ziyarah tours","Full travel insurance","Dedicated group imam"]',
 '["International departure tax (if applicable)","Personal expenses"]',
 '[{"day":1,"title":"VIP Arrival","desc":"Private meet & greet, direct transfer to 4★ Makkah hotel"},{"day":2,"title":"First Umrah","desc":"Perform Umrah rites with personal guide"},{"day":7,"title":"Transfer Madinah","desc":"Private car to Madinah, 4★ hotel check-in"},{"day":14,"title":"Departure","desc":"Transfer Madinah airport, direct flight home"}]'),

('Royal Sanctuary', 'royal-sanctuary', 21, 'luxury', 5, 9500, 'Ultimate Luxury', 5.0, 89, 'Makkah & Madinah', 'Business Class', '5★ Hotel — Adjacent to Haram', 'Luxury SUV',
 '["Umrah Visa","Business class flights","5★ luxury accommodation","Dedicated private driver","Personal spiritual guide","Full VIP Ziyarah","Premium insurance","Concierge service"]',
 '["Personal expenses"]',
 '[{"day":1,"title":"Royal Welcome","desc":"Business class arrival, VIP transfer to 5★ Makkah hotel"},{"day":2,"title":"First Umrah","desc":"Private Umrah with dedicated imam"},{"day":21,"title":"Farewell","desc":"VIP airport transfer, business class flight home"}]');

-- ── 3. Transport Routes & Pricing (v1 model — kept for reference/legacy) ──
-- NOTE: superseded by transport_routes_v2 in Part 3 below. Left in place
-- so /admin/migrate-transport.php has something to migrate from.
-- New installs can ignore these two tables and just use v2 directly.
CREATE TABLE IF NOT EXISTS transport_routes (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    route_name   VARCHAR(200) NOT NULL,
    from_loc     VARCHAR(100) NOT NULL,
    to_loc       VARCHAR(100) NOT NULL,
    is_active    TINYINT(1) DEFAULT 1,
    sort_order   INT DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transport_prices (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    route_id     INT NOT NULL,
    vehicle_type ENUM('Car','HiAce','Coaster','Bus','H1','GMC','Staria','Train') NOT NULL,
    price_sar    DECIMAL(10,2) DEFAULT 0,
    is_active    TINYINT(1) DEFAULT 1,
    FOREIGN KEY (route_id) REFERENCES transport_routes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_route_vehicle (route_id, vehicle_type)
) ENGINE=InnoDB;

-- Seed common Umrah routes (legacy v1 model)
INSERT IGNORE INTO transport_routes (id, route_name, from_loc, to_loc, sort_order) VALUES
(1, 'Jeddah Airport → Makkah',   'Jeddah Airport', 'Makkah',           1),
(2, 'Jeddah Airport → Madinah',  'Jeddah Airport', 'Madinah',          2),
(3, 'Makkah → Madinah',          'Makkah',         'Madinah',          3),
(4, 'Madinah → Jeddah Airport',  'Madinah',        'Jeddah Airport',   4),
(5, 'Makkah → Jeddah Airport',   'Makkah',         'Jeddah Airport',   5),
(6, 'Makkah Ziyarah Tour',       'Makkah',         'Makkah (Ziyarah)', 6),
(7, 'Madinah Ziyarah Tour',      'Madinah',        'Madinah (Ziyarah)',7);

-- Seed prices per vehicle per route (SAR)
INSERT IGNORE INTO transport_prices (route_id, vehicle_type, price_sar) VALUES
(1,'Car',250),(1,'HiAce',450),(1,'Coaster',700),(1,'Bus',1200),(1,'H1',350),(1,'GMC',500),(1,'Staria',600),(1,'Train',120),
(2,'Car',400),(2,'HiAce',700),(2,'Coaster',1100),(2,'Bus',2000),(2,'H1',550),(2,'GMC',800),(2,'Staria',950),(2,'Train',200),
(3,'Car',350),(3,'HiAce',600),(3,'Coaster',950),(3,'Bus',1700),(3,'H1',480),(3,'GMC',700),(3,'Staria',820),(3,'Train',160),
(4,'Car',400),(4,'HiAce',700),(4,'Coaster',1100),(4,'Bus',2000),(4,'H1',550),(4,'GMC',800),(4,'Staria',950),(4,'Train',200),
(5,'Car',250),(5,'HiAce',450),(5,'Coaster',700),(5,'Bus',1200),(5,'H1',350),(5,'GMC',500),(5,'Staria',600),(5,'Train',120),
(6,'Car',300),(6,'HiAce',500),(6,'Coaster',800),(6,'Bus',1400),(6,'H1',420),(6,'GMC',600),(6,'Staria',700),(6,'Train',0),
(7,'Car',280),(7,'HiAce',480),(7,'Coaster',750),(7,'Bus',1300),(7,'H1',400),(7,'GMC',570),(7,'Staria',670),(7,'Train',0);

-- ── 4. Moyallem Services ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS moyallem_services (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(200) NOT NULL,
    description  TEXT,
    price_sar    DECIMAL(10,2) DEFAULT 0,
    icon         VARCHAR(50) DEFAULT 'star',
    is_active    TINYINT(1) DEFAULT 1,
    sort_order   INT DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO moyallem_services (name, description, price_sar, icon, sort_order) VALUES
('Moyallem in Makkah',      'Certified spiritual guide for Umrah rites, Tawaf and Sa\'i assistance in Makkah',     850, 'mosque',        1),
('Moyallem in Madinah',     'Dedicated guide for Rawdah visit, 40-prayers program and Madinah Ziyarah',            650, 'map-pin',       2),
('Moyallem for Ziyarah',    'Expert guide for historical sites: Arafat, Mina, Muzdalifah, Jabal al-Noor etc.',     500, 'map',           3),
('Group Moyallem Package',  'One imam/guide covers full Umrah group — shared cost, maximum 15 pilgrims',           350, 'users',         4),
('VIP Private Moyallem',    'Dedicated personal scholar-guide for the entire journey, 24/7 availability',         1500, 'award',         5);

-- ── 5. Users (for optional account system) ───────────────────
CREATE TABLE IF NOT EXISTS users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    email           VARCHAR(100) UNIQUE NOT NULL,
    phone           VARCHAR(25),
    password_hash   VARCHAR(255),
    is_verified     TINYINT(1) DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login      TIMESTAMP NULL,
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- ── 6. Custom Builds (from Package Builder → DB) ─────────────
CREATE TABLE IF NOT EXISTS custom_builds (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    build_ref       VARCHAR(30) UNIQUE NOT NULL,
    user_id         INT DEFAULT NULL,
    customer_name   VARCHAR(100),
    customer_email  VARCHAR(100),
    customer_phone  VARCHAR(25),
    adults          INT DEFAULT 1,
    children        INT DEFAULT 0,
    infants         INT DEFAULT 0,
    visa_type       VARCHAR(100),
    package_level   VARCHAR(50),
    package_mode    VARCHAR(100) COMMENT 'Comma-separated list of selected "Your Choice" toggles, e.g. visa,flight,hotel',
    pdf_path        VARCHAR(255) COMMENT 'relative path to generated preview PDF, once created',
    flight_type     VARCHAR(100),
    duration        INT DEFAULT 10,
    makkah_nights   INT DEFAULT 6,
    madinah_nights  INT DEFAULT 4,
    hotel_category  VARCHAR(50),
    transport_route_id   INT DEFAULT NULL,
    transport_vehicle    VARCHAR(50),
    transport_price_sar  DECIMAL(10,2) DEFAULT 0,
    moyallem_ids    TEXT COMMENT 'JSON array of selected moyallem service IDs',
    moyallem_total_sar DECIMAL(10,2) DEFAULT 0,
    total_price_sar DECIMAL(12,2) DEFAULT 0,
    total_price_usd DECIMAL(10,2) DEFAULT 0,
    total_price_bdt DECIMAL(12,2) DEFAULT 0,
    build_data_json LONGTEXT COMMENT 'Full JSON snapshot of selections',
    status          ENUM('draft','submitted','confirmed','cancelled') DEFAULT 'submitted',
    notes           TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ref    (build_ref),
    INDEX idx_status (status),
    INDEX idx_user   (user_id)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────────
-- custom_builds.status — same expanded lifecycle as bookings.status,
-- same safe widen (VARCHAR) → remap → narrow (ENUM) migration.
-- ─────────────────────────────────────────────────────────────
ALTER TABLE custom_builds MODIFY COLUMN status VARCHAR(30) DEFAULT 'submitted';

UPDATE custom_builds SET status = 'Submitted' WHERE status IN ('draft','submitted');
UPDATE custom_builds SET status = 'Confirmed' WHERE status = 'confirmed';
UPDATE custom_builds SET status = 'Cancelled' WHERE status = 'cancelled';

ALTER TABLE custom_builds MODIFY COLUMN status ENUM(
    'Submitted','Contacted','Visa Processing','Visa Approved','Visa Rejected',
    'Hotel Processing','Hotel Confirmed','Flight Processing','Flight Confirmed',
    'Flight Date Changed','Payment Pending','Payment Received','Confirmed',
    'On Hold','Cancelled','Completed'
) DEFAULT 'Submitted';

-- ── 7. Add booking_type / user_id columns to existing bookings ──
-- Written as a safe-guard procedure instead of "ADD COLUMN IF NOT EXISTS"
-- because that syntax needs MySQL 8.0.29+ and isn't supported on many
-- MariaDB / older MySQL installs. This works everywhere.
--
-- IMPORTANT if running via phpMyAdmin's SQL tab: phpMyAdmin does NOT
-- let you change the statement delimiter, so the "DELIMITER $$" block
-- below will fail there. In that case, skip straight to running these
-- two lines instead (safe to ignore "Duplicate column" errors if the
-- columns already exist from a previous run):
--   ALTER TABLE bookings ADD COLUMN booking_type ENUM('package','custom') DEFAULT 'package' AFTER booking_ref;
--   ALTER TABLE bookings ADD COLUMN user_id INT DEFAULT NULL AFTER booking_type;
-- Everyone using the MySQL CLI, Adminer, or a DB GUI that supports
-- custom delimiters can run the block below as-is.
DELIMITER $$
CREATE PROCEDURE travhub_add_column_if_missing(
    IN tbl VARCHAR(64), IN col VARCHAR(64), IN def_sql TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND COLUMN_NAME = col
    ) THEN
        SET @ddl = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN ', def_sql);
        PREPARE stmt FROM @ddl;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

CALL travhub_add_column_if_missing('bookings', 'booking_type', "booking_type ENUM('package','custom') DEFAULT 'package' AFTER booking_ref");
CALL travhub_add_column_if_missing('bookings', 'user_id', "user_id INT DEFAULT NULL AFTER booking_type");

DROP PROCEDURE IF EXISTS travhub_add_column_if_missing;


-- ─────────────────────────────────────────────────────────────
-- PART 3 — v3 additions (formerly schema_v3.sql)
-- New Transport model: multi-stop routes (slug-based) + dynamic
-- vehicle_info JSON, and a proper Vehicle Type CRUD table.
-- ─────────────────────────────────────────────────────────────

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

-- ══════════════════════════════════════════════════════════════
-- PART 4 — Visa Types (admin CRUD, quick price-only edits)
-- ══════════════════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS visa_types (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    uuid         VARCHAR(36) NOT NULL UNIQUE,
    name         VARCHAR(80) NOT NULL UNIQUE,
    price_sar    DECIMAL(10,2) DEFAULT 0,
    description  VARCHAR(255),
    icon         VARCHAR(50) DEFAULT 'file-text',
    is_active    TINYINT(1) DEFAULT 1,
    sort_order   INT DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO visa_types (uuid, name, price_sar, description, icon, sort_order) VALUES
(UUID(), 'Umrah Visa',        450, 'Standard Umrah visa for pilgrims.',                 'file-text',     1),
(UUID(), 'Tourist Visa',      300, 'General tourist visa, valid for Umrah as well.',    'plane',         2),
(UUID(), 'Visa Not Required', 0,   'For eligible nationalities — verify before booking.','check-circle-2',3);

-- ─────────────────────────────────────────────────────────────
-- Service Levels (admin CRUD — Economy/Premium/Luxury package tiers)
-- ─────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS service_levels (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    uuid         VARCHAR(36) NOT NULL UNIQUE,
    name         VARCHAR(80) NOT NULL UNIQUE,
    price_sar    DECIMAL(10,2) DEFAULT 0,
    features     JSON DEFAULT NULL,
    is_active    TINYINT(1) DEFAULT 1,
    sort_order   INT DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO service_levels (uuid, name, price_sar, features, sort_order) VALUES
(UUID(), 'Economy', 1200, '["3-Star Hotels","Shared transfers","Ziyarah Tours"]', 1),
(UUID(), 'Premium', 2500, '["4-Star Hotels","Private transfers","Ziyarah Tours"]', 2),
(UUID(), 'Luxury',  4500, '["5-Star Hotels","Private car & guide","Ziyarah Tours"]', 3);


-- ══════════════════════════════════════════════════════════════
-- PART 5 — Flights (transport-route-style model)
-- Each record is one Fare Type ("Flexible"/"Fixed"), and inside it a
-- JSON array holds the per-connection-type prices, mirroring how
-- transport_routes_v2.vehicle_info works for vehicles:
--   flight_type_price: [{"type":"Direct","price_sar":500},
--                        {"type":"Connecting","price_sar":350}]
-- ══════════════════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS flights (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    uuid               VARCHAR(36) NOT NULL UNIQUE,
    sys_id             VARCHAR(40) NOT NULL UNIQUE COMMENT 'short human-friendly id',
    fare_type          VARCHAR(60) NOT NULL COMMENT 'e.g. Flexible, Fixed',
    flight_type_price  JSON NOT NULL COMMENT 'array of {type, price_sar} e.g. Direct/Connecting',
    note               VARCHAR(255) DEFAULT 'Price may differ based on season and availability.',
    is_active          TINYINT(1) DEFAULT 1,
    sort_order         INT DEFAULT 0,
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO flights (uuid, sys_id, fare_type, flight_type_price, sort_order) VALUES
(UUID(), 'FL-FLEX01', 'Flexible', '[{"type":"Direct","price_sar":650},{"type":"Connecting","price_sar":480}]', 1),
(UUID(), 'FL-FIXD01', 'Fixed',    '[{"type":"Direct","price_sar":500},{"type":"Connecting","price_sar":350}]', 2);


-- ══════════════════════════════════════════════════════════════
-- PART 6 — custom_builds: Package Mode + PDF preview columns
-- Version-safe (works on installs that already have custom_builds
-- from Part 2, and on brand-new installs where the CREATE TABLE
-- above already includes these columns — this simply no-ops then).
-- IMPORTANT if running via phpMyAdmin's SQL tab: phpMyAdmin cannot
-- change the statement delimiter, so the "DELIMITER $$" block below
-- will fail there. In that case, skip straight to running these two
-- lines instead (safe to ignore "Duplicate column" errors):
--   ALTER TABLE custom_builds ADD COLUMN package_mode VARCHAR(100) COMMENT 'Comma-separated selected choices, e.g. visa,flight,hotel' AFTER package_level;
--   ALTER TABLE custom_builds ADD COLUMN pdf_path VARCHAR(255) COMMENT 'relative path to generated preview PDF' AFTER package_mode;
-- ══════════════════════════════════════════════════════════════
DELIMITER $$
CREATE PROCEDURE travhub_add_column_if_missing_2(
    IN tbl VARCHAR(64), IN col VARCHAR(64), IN def_sql TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND COLUMN_NAME = col
    ) THEN
        SET @ddl = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN ', def_sql);
        PREPARE stmt FROM @ddl;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

CALL travhub_add_column_if_missing_2('custom_builds', 'package_mode', "package_mode VARCHAR(100) COMMENT 'Comma-separated selected choices, e.g. visa,flight,hotel' AFTER package_level");
CALL travhub_add_column_if_missing_2('custom_builds', 'pdf_path', "pdf_path VARCHAR(255) COMMENT 'relative path to generated preview PDF' AFTER package_mode");

DROP PROCEDURE IF EXISTS travhub_add_column_if_missing_2;

-- If your custom_builds.package_mode column already existed as the old
-- VARCHAR(20) (from an earlier version of this schema, before "Your Choice"
-- started storing a comma-separated list here), widen it now — ADD COLUMN
-- above only runs for a column that doesn't exist yet, it won't resize an
-- existing one. Safe to run every time; it's a no-op once already widened.
ALTER TABLE custom_builds MODIFY COLUMN package_mode VARCHAR(100) COMMENT 'Comma-separated selected choices, e.g. visa,flight,hotel';


-- ══════════════════════════════════════════════════════════════
-- PART 7 — Moyallem Services v2 (category-priced: General/Expert/VIP)
-- Same transport/flight-style model: each row is one named service,
-- with a JSON array of {category, price_sar} inside it. The legacy
-- flat-price moyallem_services table (Part 2 above) is left untouched
-- so Admin → Moyallem → "Migrate old data" has something to read from.
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS moyallem_services_v2 (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    uuid            VARCHAR(36) NOT NULL UNIQUE,
    sys_id          VARCHAR(40) NOT NULL UNIQUE COMMENT 'short human-friendly id',
    name            VARCHAR(200) NOT NULL,
    description     TEXT,
    icon            VARCHAR(50) DEFAULT 'star',
    category_price  JSON NOT NULL COMMENT 'array of {category, price_sar} e.g. General/Expert/VIP',
    is_active       TINYINT(1) DEFAULT 1,
    sort_order      INT DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO moyallem_services_v2 (uuid, sys_id, name, description, icon, category_price, sort_order) VALUES
(UUID(), 'MY-MAKKAH01', 'Moyallem in Makkah',      'Certified spiritual guide for Umrah rites, Tawaf and Sa\'i assistance in Makkah',
 '[{"category":"General","price_sar":350},{"category":"Expert","price_sar":650},{"category":"VIP","price_sar":1200}]', 'mosque', 1),
(UUID(), 'MY-MADINA01', 'Moyallem in Madinah',     'Dedicated guide for Rawdah visit, 40-prayers program and Madinah Ziyarah',
 '[{"category":"General","price_sar":300},{"category":"Expert","price_sar":550},{"category":"VIP","price_sar":1000}]', 'map-pin', 2),
(UUID(), 'MY-ZIYARAH01','Moyallem for Ziyarah',    'Expert guide for historical sites: Arafat, Mina, Muzdalifah, Jabal al-Noor etc.',
 '[{"category":"General","price_sar":250},{"category":"Expert","price_sar":450},{"category":"VIP","price_sar":900}]', 'map', 3),
(UUID(), 'MY-GROUP01',  'Group Moyallem Package',  'One imam/guide covers full Umrah group — shared cost, maximum 15 pilgrims',
 '[{"category":"General","price_sar":200},{"category":"Expert","price_sar":350},{"category":"VIP","price_sar":700}]', 'users', 4);


-- ══════════════════════════════════════════════════════════════
-- PART 8 — Ziarah (guided historical/spiritual site visits) — brand new
-- module, no legacy table to migrate from.
-- itinerary: ordered array of {title, content_html} — each item is one
--            stop/segment with a short rich-text description.
-- route_ids: array of transport_routes_v2.id this Ziarah can use — the
--            Mini/Full builder pre-selects one of these when the user
--            says they need transport for this Ziarah.
-- moyallem_enabled: whether this Ziarah offers a Moyallem add-on at all;
--            actual category (General/Expert/VIP) + price comes from
--            moyallem_services_v2 at booking time, not stored here.
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS ziarah (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    uuid                VARCHAR(36) NOT NULL UNIQUE,
    sys_id              VARCHAR(40) NOT NULL UNIQUE COMMENT 'short human-friendly id',
    name                VARCHAR(200) NOT NULL,
    short_description   VARCHAR(500),
    possible_duration   VARCHAR(100) COMMENT 'free text, e.g. "Half Day", "3-4 hours"',
    itinerary           JSON NOT NULL COMMENT 'array of {title, content_html}',
    route_ids           JSON NOT NULL COMMENT 'array of transport_routes_v2.id',
    moyallem_enabled    TINYINT(1) DEFAULT 0,
    price_sar           DECIMAL(10,2) DEFAULT 0,
    is_active           TINYINT(1) DEFAULT 1,
    sort_order          INT DEFAULT 0,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO ziarah (uuid, sys_id, name, short_description, possible_duration, itinerary, route_ids, moyallem_enabled, price_sar, sort_order) VALUES
(UUID(), 'ZR-MAKKAH01', 'Makkah Historical Ziyarah', 'Visit the key historical sites around Makkah connected to the life of the Prophet ﷺ.', 'Half Day (4-5 hours)',
 '[{"title":"Jabal al-Noor","content_html":"<p>Visit the <strong>Cave of Hira</strong>, where the first revelation was received.</p>"},{"title":"Jabal Thawr","content_html":"<p>See the cave where the Prophet ﷺ and Abu Bakr (RA) took refuge during the migration.</p>"},{"title":"Mina, Muzdalifah & Arafat","content_html":"<p>Drive through the sacred plains central to the Hajj rites.</p>"}]',
 '[]', 1, 250, 1),
(UUID(), 'ZR-MADINA01', 'Madinah Historical Ziyarah', 'Explore the mosques and historical landmarks of Madinah Munawwarah.', 'Half Day (3-4 hours)',
 '[{"title":"Quba Mosque","content_html":"<p>The first mosque built in Islam.</p>"},{"title":"Qiblatain Mosque","content_html":"<p>Where the Qibla direction was changed during prayer.</p>"},{"title":"Uhud Mountain & Martyrs Cemetery","content_html":"<p>Visit the site of the Battle of Uhud and pay respects at the martyrs\' graves.</p>"}]',
 '[]', 1, 220, 2);


-- ══════════════════════════════════════════════════════════════
-- PART 9 — Meals (flight-style single-group model)
-- One row per meal plan, with a JSON array of {tier, price_sar} for
-- Economy/Premium/Luxury — mirrors flights.flight_type_price.
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS meals (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    uuid                  VARCHAR(36) NOT NULL UNIQUE,
    sys_id                VARCHAR(40) NOT NULL UNIQUE COMMENT 'short human-friendly id',
    name                  VARCHAR(200) NOT NULL DEFAULT 'Meal Package',
    min_adults_required   INT DEFAULT 10,
    tier_price            JSON NOT NULL COMMENT 'array of {tier, price_sar} e.g. Economy/Premium/Luxury',
    is_active             TINYINT(1) DEFAULT 1,
    sort_order            INT DEFAULT 0,
    created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO meals (uuid, sys_id, name, min_adults_required, tier_price, sort_order) VALUES
(UUID(), 'ML-STD01', 'Meal Package', 10,
 '[{"tier":"Economy","price_sar":40},{"tier":"Premium","price_sar":75},{"tier":"Luxury","price_sar":140}]', 1);


-- ══════════════════════════════════════════════════════════════
-- PART 10 — Hotels (own DB — no more external API dependency)
-- Adapted from the TravHub "LFDP 1" master-data hotels design:
--   - country dropped entirely (Umrah hotels are always Saudi Arabia)
--   - city is now a predefined dropdown (ENUM) instead of a free country/
--     city lookup pair
--   - vendor/multi-tenant fields dropped (not needed for this project)
-- Three-level structure: hotels -> room_types -> room_rates
-- (date-ranged, meal plan + markup-based pricing).
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS hotels (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    uuid            VARCHAR(36) NOT NULL UNIQUE,
    sys_id          VARCHAR(30) NOT NULL UNIQUE,
    city            ENUM('Makkah','Madinah','Jeddah') NOT NULL DEFAULT 'Makkah',
    name            VARCHAR(160) NOT NULL,
    star_rating     TINYINT UNSIGNED DEFAULT NULL,
    address         VARCHAR(255) DEFAULT NULL,
    phone           VARCHAR(40) DEFAULT NULL,
    email           VARCHAR(120) DEFAULT NULL,
    description     TEXT DEFAULT NULL,
    amenities       JSON DEFAULT NULL COMMENT 'array of amenity strings, e.g. ["WiFi","Parking"]',
    images          JSON DEFAULT NULL COMMENT 'array of {url} objects, first image is the thumbnail',
    check_in_time   TIME DEFAULT NULL,
    check_out_time  TIME DEFAULT NULL,
    is_active       TINYINT(1) DEFAULT 1,
    sort_order      INT DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_hotels_city (city),
    INDEX idx_hotels_active (is_active)
) ENGINE=InnoDB;

INSERT IGNORE INTO hotels (uuid, sys_id, city, name, star_rating, address, description, amenities, images, check_in_time, check_out_time, sort_order) VALUES
(UUID(), 'HTL-MAKKAH01', 'Makkah', 'Hotel Al Arabia', 5, 'Near Haram, Makkah', 'A premium 5-star hotel with direct views of the Haram, offering a peaceful stay for pilgrims.', '["WiFi","Prayer Room","24-Hour Front Desk","Elevator"]', '[]', '14:00:00', '11:00:00', 1),
(UUID(), 'HTL-MADINA01', 'Madinah', 'Anwar Al Madinah Mövenpick', 5, 'Near Masjid an-Nabawi, Madinah', 'Elegant hotel just steps away from the Prophet''s Mosque, blending modern comfort with spiritual proximity.', '["WiFi","Restaurant","Prayer Room","Room Service"]', '[]', '14:00:00', '11:00:00', 2);


-- ── Room Types (per hotel) ──
CREATE TABLE IF NOT EXISTS room_types (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    uuid                VARCHAR(36) NOT NULL UNIQUE,
    sys_id              VARCHAR(50) NOT NULL UNIQUE,
    hotel_id            INT NOT NULL,
    room_name           VARCHAR(120) NOT NULL,
    description         VARCHAR(400) DEFAULT NULL,
    max_adults          TINYINT UNSIGNED NOT NULL DEFAULT 2,
    max_children        TINYINT UNSIGNED NOT NULL DEFAULT 0,
    standard_occupancy  TINYINT UNSIGNED NOT NULL DEFAULT 2,
    bed_config          VARCHAR(80) DEFAULT NULL,
    size_sqm            SMALLINT UNSIGNED DEFAULT NULL,
    is_active           TINYINT(1) DEFAULT 1,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    INDEX idx_rt_hotel (hotel_id),
    INDEX idx_rt_active (is_active)
) ENGINE=InnoDB;

INSERT IGNORE INTO room_types (uuid, sys_id, hotel_id, room_name, description, max_adults, max_children, standard_occupancy, bed_config, size_sqm) VALUES
(UUID(), 'RMT-MAKKAH01-01', 1, 'Deluxe Haram View', 'Spacious room with a direct view of the Haram.', 2, 1, 2, '1 King Bed', 32),
(UUID(), 'RMT-MAKKAH01-02', 1, 'Triple Room', 'Comfortable room for small families or groups.', 3, 1, 3, '1 King + 1 Single', 38),
(UUID(), 'RMT-MADINA01-01', 2, 'Standard Room', 'Cozy room close to Masjid an-Nabawi.', 2, 1, 2, '2 Twin Beds', 28);


-- ── Room Rates (per room_type, date-ranged, markup-based pricing) ──
CREATE TABLE IF NOT EXISTS room_rates (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    uuid                VARCHAR(36) NOT NULL UNIQUE,
    sys_id              VARCHAR(70) NOT NULL UNIQUE,
    room_type_id        INT NOT NULL,
    meal_plan           ENUM('room_only','bb','hb','fb','ai') NOT NULL DEFAULT 'bb',
    occupancy_basis     ENUM('per_room','single','double','triple','extra_bed') NOT NULL DEFAULT 'per_room',
    valid_from          DATE NOT NULL,
    valid_to            DATE NOT NULL,
    currency_code       VARCHAR(5) NOT NULL DEFAULT 'SAR',
    net_cost            DECIMAL(12,2) NOT NULL,
    markup_type         ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    markup_value        DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
    sell_price          DECIMAL(12,2) NOT NULL COMMENT 'net_cost + markup, calculated at save time',
    is_active           TINYINT(1) DEFAULT 1,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE CASCADE,
    INDEX idx_rr_room_type (room_type_id),
    INDEX idx_rr_period (room_type_id, valid_from, valid_to),
    INDEX idx_rr_active (is_active)
) ENGINE=InnoDB;

INSERT IGNORE INTO room_rates (uuid, sys_id, room_type_id, meal_plan, occupancy_basis, valid_from, valid_to, currency_code, net_cost, markup_type, markup_value, sell_price) VALUES
(UUID(), 'RMR-MAKKAH01-01-01', 1, 'bb', 'per_room', '2026-01-01', '2027-12-31', 'SAR', 500.00, 'percent', 15.0000, 575.00),
(UUID(), 'RMR-MAKKAH01-02-01', 2, 'bb', 'per_room', '2026-01-01', '2027-12-31', 'SAR', 700.00, 'percent', 15.0000, 805.00),
(UUID(), 'RMR-MADINA01-01-01', 3, 'bb', 'per_room', '2026-01-01', '2027-12-31', 'SAR', 350.00, 'percent', 15.0000, 402.50);


-- ══════════════════════════════════════════════════════════════
-- PART 7 — hotels: distance to a reference landmark (Masjid al-Haram
-- for Makkah, Masjid-e-Nabawi for Madinah, or a free-text landmark for
-- any other city, e.g. "Jeddah Airport").
-- Handles two cases safely: (a) a DB that already has the older
-- `al_haram_distance` column from an earlier version of this file gets
-- it renamed in place (data preserved), (b) a DB with neither column
-- gets `landmark_distance` added fresh. If running via phpMyAdmin's
-- SQL tab (no custom delimiter support), skip straight to:
--   ALTER TABLE hotels ADD COLUMN landmark_distance JSON COMMENT 'distance to a reference landmark — {"label":"Masjid al-Haram","unit":"km|m","value":2}' AFTER amenities;
-- (safe to ignore a "Duplicate column" error if it already exists)
-- ══════════════════════════════════════════════════════════════
DELIMITER $$
CREATE PROCEDURE travhub_migrate_landmark_distance()
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hotels' AND COLUMN_NAME = 'al_haram_distance'
    ) AND NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hotels' AND COLUMN_NAME = 'landmark_distance'
    ) THEN
        ALTER TABLE hotels CHANGE COLUMN al_haram_distance landmark_distance
            JSON COMMENT 'distance to a reference landmark — {"label":"Masjid al-Haram","unit":"km|m","value":2}';
    ELSEIF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hotels' AND COLUMN_NAME = 'landmark_distance'
    ) THEN
        ALTER TABLE hotels ADD COLUMN landmark_distance
            JSON COMMENT 'distance to a reference landmark — {"label":"Masjid al-Haram","unit":"km|m","value":2}' AFTER amenities;
    END IF;
END$$
DELIMITER ;

CALL travhub_migrate_landmark_distance();

DROP PROCEDURE IF EXISTS travhub_migrate_landmark_distance;

-- ══════════════════════════════════════════════════════════════
-- Done. Next steps:
--   1. Update includes/config.php with your DB credentials.
--   2. Log in to /admin (admin / TravHub@2026) and change the password.
--   3. Go to Admin → Transport → "Migrate old data" to copy the
--      legacy transport_routes/transport_prices rows above into
--      the new transport_routes_v2 multi-stop format.
--   4. Add/edit vehicle types under Admin → Vehicle Types.
--   5. Add/edit visa prices under Admin → Visa Types.
--   6. Add/edit flight fares under Admin → Flights.
--   7. Add/edit Moyallem services (General/Expert/VIP pricing) under
--      Admin → Moyallem — the new moyallem_services_v2 table.
--   8. Add/edit Ziarah tours under Admin → Ziarah — link each one to
--      real transport routes once you've created them.
--   9. Add/edit meal plan pricing (Economy/Premium/Luxury) under
--      Admin → Meals.
--  10. Add/edit hotels, room types, and room rates under Admin → Hotels.
-- ══════════════════════════════════════════════════════════════