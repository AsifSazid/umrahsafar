-- ══════════════════════════════════════════════════════════════
-- TravHub Umrah Platform — Schema v2 (Migration / Additions)
-- Run AFTER original schema.sql
-- ══════════════════════════════════════════════════════════════

USE travhub_umrah;

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

-- ── 3. Transport Routes & Pricing ────────────────────────────
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

-- Seed common Umrah routes
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

-- ── 7. Add booking_type / user_id columns to existing bookings ──
-- NOTE: "ADD COLUMN IF NOT EXISTS" needs MySQL 8.0.29+ and isn't
-- supported on many MariaDB / older MySQL installs. If this plain
-- ALTER fails because the columns already exist, that's fine — it
-- just means this file has already been run before; ignore the error.
-- (See schema_full.sql for a version-safe procedure-based version.)
ALTER TABLE bookings ADD COLUMN booking_type ENUM('package','custom') DEFAULT 'package' AFTER booking_ref;
ALTER TABLE bookings ADD COLUMN user_id INT DEFAULT NULL AFTER booking_type;
