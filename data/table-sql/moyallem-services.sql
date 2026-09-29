-- ─────────────────────────────────────────────────────────────
-- Moyallem Services — spiritual guide services (Makkah/Madinah),
-- priced by category (General/Expert/VIP etc.), shown on the
-- Moyallem step of both builders.
--
-- Seed data is NOT in this file — run data/seed/moyallem-services.php
-- after this, so sys_id/uuid come from the real generateSysIdAndUuid()
-- generator, same pattern as every other table in this project.
-- ─────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS moyallem_services;

CREATE TABLE moyallem_services (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    sys_id            VARCHAR(40) NOT NULL UNIQUE COMMENT 'e.g. US9-26-MYL-00001',
    uuid              VARCHAR(36) NOT NULL UNIQUE,
    name              VARCHAR(200) NOT NULL,
    description       TEXT,
    icon              VARCHAR(50) DEFAULT 'star',
    category_prices   JSON NOT NULL COMMENT '[{"category":"General","price":850}] — price is in Saudi Arabian Riyals (SAR), plain number, not multi-currency',
    is_active         TINYINT(1) DEFAULT 1,
    sort_order        INT DEFAULT 0,
    metadata          JSON DEFAULT NULL COMMENT '{"created_at":...,"created_by":...,"updated_at":...,"updated_by":...}'
) ENGINE=InnoDB;