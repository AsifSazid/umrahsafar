-- ─────────────────────────────────────────────────────────────
-- Room Prices — one row per room type, holding ALL of its board-type
-- prices together in one array (board_prices). Each entry can either
-- reference a global room_board_types catalog entry ("For All Rooms" —
-- board_type_sys_id set) or be a one-off entry typed just for this
-- room ("For This Room" — board_type_sys_id is null).
-- Seed data is NOT in this file — run data/seed/room-prices.php after
-- this (and after room-board-types.php).
-- ─────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS room_prices;

CREATE TABLE room_prices (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    sys_id             VARCHAR(40) NOT NULL UNIQUE COMMENT 'e.g. US9-26-RMP-00001',
    uuid               VARCHAR(36) NOT NULL UNIQUE,
    room_type_sys_id   VARCHAR(40) NOT NULL COMMENT 'one row per room type — naturally unique, no separate constraint needed',
    board_prices       JSON NOT NULL COMMENT '[{"board_type_sys_id":"US9-26-RBT-00001 or null","board_type_title":"Bed & Breakfast","valid_from":"2026-09-01","valid_to":"2026-09-30","price":150}] — price is in Saudi Arabian Riyals (SAR)',
    is_active          TINYINT(1) DEFAULT 1,
    metadata           JSON DEFAULT NULL COMMENT '{"created_at":...,"created_by":...,"updated_at":...,"updated_by":...}',
    FOREIGN KEY (room_type_sys_id) REFERENCES room_types(sys_id) ON DELETE CASCADE,
    INDEX idx_rmp_room_type (room_type_sys_id)
) ENGINE=InnoDB;