-- ─────────────────────────────────────────────────────────────
-- username_counters — backs generateUsername() in
-- data/server/username_generator.php. One row per (type, year);
-- `counter` increments atomically for every username generated.
-- type = 'e' (employee/office/backend) or 'c' (client) — independent
-- counters, so the first employee and first client of a year both
-- start at 00001. Resets naturally every year.
-- ─────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS username_counters;

CREATE TABLE username_counters (
    type    CHAR(1) NOT NULL COMMENT 'e = employee/office, c = client',
    year    CHAR(2) NOT NULL COMMENT 'two-digit year, e.g. 26',
    counter INT NOT NULL DEFAULT 0,
    PRIMARY KEY (type, year)
) ENGINE=InnoDB;