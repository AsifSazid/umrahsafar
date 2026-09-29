-- ─────────────────────────────────────────────────────────────
-- Meals — meal plans shown on the Meal step of both builders, gated
-- by a minimum adult count (min_adults_required).
--
-- Seed data is NOT in this file — run data/seed/meals.php after this,
-- so sys_id/uuid come from the real generateSysIdAndUuid() generator,
-- same pattern as every other table in this project.
-- ─────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS meals;

CREATE TABLE meals (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    sys_id                VARCHAR(40) NOT NULL UNIQUE COMMENT 'e.g. US9-26-MLL-00001',
    uuid                  VARCHAR(36) NOT NULL UNIQUE,
    name                  VARCHAR(200) NOT NULL DEFAULT 'Meal Package',
    min_adults_required   INT DEFAULT 10,
    price_tiers           JSON NOT NULL COMMENT '[{"tier":"Economy","price":40}] — price is in Saudi Arabian Riyals (SAR), plain number, not multi-currency',
    is_active             TINYINT(1) DEFAULT 1,
    sort_order            INT DEFAULT 0,
    metadata              JSON DEFAULT NULL COMMENT '{"created_at":...,"created_by":...,"updated_at":...,"updated_by":...}'
) ENGINE=InnoDB;