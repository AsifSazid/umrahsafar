-- ══════════════════════════════════════════
-- TravHub Umrah Platform — MySQL Schema
-- Run once to set up the database
-- ══════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS travhub_umrah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE travhub_umrah;

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
