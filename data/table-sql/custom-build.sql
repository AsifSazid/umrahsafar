-- ─────────────────────────────────────────────────────────────
-- Custom Builds — one row per Package Builder submission (both the
-- homepage mini-builder and pages/package-builder.php write here).
--
-- Every price-bearing JSON column follows the same convention:
--   - each individual item inside carries a plain SAR `price` number
--   - the section's own `total_prices` (and the top-level `total_prices`)
--     carry {"sar":[amount,rate],"bdt":[amount,rate],"usd":[amount,rate]}
--     — the exchange rate is frozen at submission time alongside the
--     converted amount, so a booking's historical rate is always
--     recoverable later even if today's rate has since changed.
-- ─────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS custom_builds;

CREATE TABLE custom_builds (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    sys_id              VARCHAR(40) NOT NULL UNIQUE COMMENT 'e.g. US9-26-CBS-00001',
    uuid                VARCHAR(36) NOT NULL UNIQUE,

    customer_infos      JSON NOT NULL COMMENT '{"name":"Jane Webster","email":"jane@x.com","phone":"01751906710"}',
    persons             JSON NOT NULL COMMENT '{"adults":2,"children":1,"infants":0}',

    visa_infos          JSON DEFAULT NULL COMMENT '{"required":1,"visa_type_sys_ids":["US9-26-VST-00001"],"total_prices":{"sar":[450,1],"bdt":[15300,34],"usd":[119.88,0.2664]}}',
    service_infos       JSON DEFAULT NULL COMMENT '{"sys_id":"US9-26-SVC-00002","name":"Premium","price":1200,"total_prices":{"sar":[12000,1],"bdt":[390000,32.5],"usd":[3196.8,0.2664]}} — price is per-traveler (multiplied by adult count)',
    choices             VARCHAR(150) DEFAULT NULL COMMENT 'comma-separated selected modules, e.g. visa,flight,hotel,transport,meal',

    flight_infos        JSON DEFAULT NULL COMMENT '{"connection_type":"Direct"} — minimal for now; price/date/fare_type added once flight booking is confirmed with the customer directly',
    days                JSON DEFAULT NULL COMMENT '{"makkah":6,"madinah":4,"total":10}',

    hotel_infos         JSON DEFAULT NULL COMMENT '{"category":"5-Star","selections":[{"hotel_sys_id":"...","hotel_name":"...","room_type_sys_id":"...","room_name":"...","board_type_sys_id":"...","board_name":"...","price":500}],"total_prices":{"sar":[500,1],"bdt":[17000,34],"usd":[133.20,0.2664]}}',
    meal_infos          JSON DEFAULT NULL COMMENT '{"meal_sys_id":"...","meal_name":"Meal Package","tier":"Premium","price":75,"total_prices":{"sar":[75,1],"bdt":[2550,34],"usd":[19.98,0.2664]}}',
    transport_infos     JSON DEFAULT NULL COMMENT '{"legs":[{"route_sys_id":"...","route_name":"...","vehicle_type_sys_id":"...","vehicle_name":"...","price":250}],"total_prices":{"sar":[250,1],"bdt":[8500,34],"usd":[66.60,0.2664]}}',
    moyallem_infos      JSON DEFAULT NULL COMMENT '{"services":[{"sys_id":"...","service_name":"...","category":"Expert","price":650}],"total_prices":{"sar":[650,1],"bdt":[22100,34],"usd":[173.29,0.2664]}}',
    ziarah_infos        JSON DEFAULT NULL COMMENT '{"selections":[{"sys_id":"...","name":"Makkah Historical Ziyarah","price":250,"transport_route_sys_id":"...","moyallem_category":"General","moyallem_price":300}],"total_prices":{"sar":[550,1],"bdt":[17875,34],"usd":[144.1,0.2664]}}',

    total_prices        JSON NOT NULL COMMENT 'grand total across every section — {"sar":[1925,1],"bdt":[65450,34],"usd":[512.95,0.2664]}',
    markups             JSON DEFAULT NULL COMMENT 'reserved for future use — nothing written here yet',
    data_json           JSON NOT NULL COMMENT 'full raw snapshot of the mini-builder/package-builder state as submitted — safety net if a structured column is ever misread or dropped later',

    pdf_path            VARCHAR(255) DEFAULT NULL,
    status              ENUM('Submitted','Contacted','Visa Processing','Visa Approved','Visa Rejected',
                              'Hotel Processing','Hotel Confirmed','Flight Processing','Flight Confirmed',
                              'Flight Date Changed','Payment Pending','Payment Received','Confirmed',
                              'On Hold','Cancelled','Completed') DEFAULT 'Submitted',

    metadata            JSON DEFAULT NULL COMMENT '{"created_at":...,"created_by":"US9-26-USR-00001 or null","updated_at":...,"updated_by":"..."} — created_by/updated_by hold the client user''s sys_id, or null if not logged in',

    INDEX idx_sys_id (sys_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;