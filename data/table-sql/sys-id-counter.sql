-- ─────────────────────────────────────────────────────────────
-- sys_id_counters — backs generateSysId()/generateSysIdAndUuid() in
-- data/server/uuid_generator.php. One row per (table, year, month);
-- `counter` increments atomically for every sys_id generated in that
-- table/year/month. Resets naturally every month (and every year) —
-- no manual rollover needed, since a 5-digit base-36 serial gives
-- ~60 million possible values per table per month.
-- ─────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS sys_id_counters;

CREATE TABLE sys_id_counters (
    tag     VARCHAR(40) NOT NULL COMMENT 'the table name, e.g. custom_builds',
    year    CHAR(2) NOT NULL COMMENT 'two-digit year, e.g. 26',
    month   CHAR(1) NOT NULL COMMENT '1-9 for Jan-Sep, A=Oct, B=Nov, C=Dec',
    counter INT NOT NULL DEFAULT 0,
    PRIMARY KEY (tag, year, month)
) ENGINE=InnoDB;