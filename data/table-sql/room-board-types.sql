-- ─────────────────────────────────────────────────────────────
-- Room Board Types — GLOBAL catalog (Room Only / Bed & Breakfast /
-- Half Board / Full Board, or any admin-defined text). Not tied to any
-- one room type or hotel — every room type's Prices modal offers the
-- same catalog, since these board options apply hotel-wide.
-- Seed data is NOT in this file — run data/seed/room-board-types.php
-- after this.
-- ─────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS room_board_types;

CREATE TABLE room_board_types (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    sys_id             VARCHAR(40) NOT NULL UNIQUE COMMENT 'e.g. US9-26-RBT-00001',
    uuid               VARCHAR(36) NOT NULL UNIQUE,
    name               VARCHAR(80) NOT NULL UNIQUE COMMENT 'e.g. Room Only, Bed & Breakfast, Half Board, Full Board',
    is_active          TINYINT(1) DEFAULT 1,
    sort_order         INT DEFAULT 0,
    metadata           JSON DEFAULT NULL COMMENT '{"created_at":...,"created_by":...,"updated_at":...,"updated_by":...}'
) ENGINE=InnoDB;