-- ─────────────────────────────────────────────────────────────
-- Hotels — top of the hotels → room_types → room_board_types →
-- room_prices chain. Seed data is NOT in this file — run
-- data/seed/hotels.php after this.
-- ─────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS hotels;

CREATE TABLE hotels (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    sys_id             VARCHAR(40) NOT NULL UNIQUE COMMENT 'e.g. US9-26-HTL-00001',
    uuid               VARCHAR(36) NOT NULL UNIQUE,
    city               VARCHAR(60) NOT NULL DEFAULT 'Makkah',
    star_rating        TINYINT UNSIGNED DEFAULT NULL,
    name               VARCHAR(160) NOT NULL,
    checkin_schedule   JSON DEFAULT NULL COMMENT '{"in":"14:00","out":"12:00"}',
    distance_info      JSON DEFAULT NULL COMMENT 'Makkah/Madinah: {"landmark":"Masjid al-Haram","gate_no":"1","unit":"m","value":450,"walking":{"enabled":true,"minutes":6}} — other cities: same shape minus gate_no',
    age_ranges         JSON DEFAULT NULL COMMENT '{"child":{"min":2,"max":11},"infant":{"min":0,"max":1}}',
    description        TEXT DEFAULT NULL,
    address            VARCHAR(255) DEFAULT NULL,
    phone              VARCHAR(40) DEFAULT NULL,
    email              VARCHAR(120) DEFAULT NULL,
    image_urls         JSON DEFAULT NULL COMMENT 'external links, e.g. Facebook/Google photo URLs',
    images             JSON DEFAULT NULL COMMENT 'uploaded image paths',
    videos             JSON DEFAULT NULL COMMENT 'uploaded video paths',
    youtube_urls       JSON DEFAULT NULL COMMENT 'YouTube video links',
    is_active          TINYINT(1) DEFAULT 1,
    sort_order         INT DEFAULT 0,
    metadata           JSON DEFAULT NULL COMMENT '{"created_at":...,"created_by":...,"updated_at":...,"updated_by":...}'
) ENGINE=InnoDB;