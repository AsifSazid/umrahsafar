-- ─────────────────────────────────────────────────────────────
-- Room Types — child of hotels, linked by sys_id (this project's
-- convention — FK is on the VARCHAR sys_id column, not the numeric
-- id, so hotels.sys_id must stay UNIQUE, which it already is).
-- Seed data is NOT in this file — run data/seed/room-types.php
-- after this (and after hotels.php).
-- ─────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS room_types;

CREATE TABLE room_types (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    sys_id             VARCHAR(40) NOT NULL UNIQUE COMMENT 'e.g. US9-26-RMT-00001',
    uuid               VARCHAR(36) NOT NULL UNIQUE,
    hotel_sys_id       VARCHAR(40) NOT NULL,
    name               VARCHAR(120) NOT NULL,
    description        VARCHAR(400) DEFAULT NULL,
    person_capacity    JSON DEFAULT NULL COMMENT '{"adults":2,"children":1} — max capacity; bed_config total capacity should not exceed this (enforced in the admin form with JS, not at the DB level)',
    size               JSON DEFAULT NULL COMMENT '{"unit":"sqr-m","value":300} — unit is one of sqr-m/sqr-cm/sqr-ft/sqr-in',
    bed_config         JSON DEFAULT NULL COMMENT 'array of {bed_type, quantity, capacity_per_bed}',
    amenities          JSON DEFAULT NULL COMMENT 'array of amenity strings',
    add_on             JSON DEFAULT NULL COMMENT '{"bed":0,"charge":0} — 1/0 whether an extra bed is offered, and its charge if so',
    view_info          JSON DEFAULT NULL COMMENT '{"enabled":0,"description":"..."} — Haram/Nawabi/generic view, label shown depends on the hotel''s city',
    image_urls         JSON DEFAULT NULL COMMENT 'external links, e.g. Facebook/Google photo URLs',
    images             JSON DEFAULT NULL COMMENT 'uploaded image paths',
    videos             JSON DEFAULT NULL COMMENT 'uploaded video paths',
    youtube_urls       JSON DEFAULT NULL COMMENT 'YouTube video links',
    is_active          TINYINT(1) DEFAULT 1,
    sort_order         INT DEFAULT 0,
    metadata           JSON DEFAULT NULL COMMENT '{"created_at":...,"created_by":...,"updated_at":...,"updated_by":...}',
    FOREIGN KEY (hotel_sys_id) REFERENCES hotels(sys_id) ON DELETE CASCADE,
    INDEX idx_rt_hotel (hotel_sys_id)
) ENGINE=InnoDB;