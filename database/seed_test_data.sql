-- =============================================================================
-- TicketMaster.LT — Test / Demo Data
-- =============================================================================
-- Prerequisites:
--   1. schema.sql already imported (or `php spark migrate` run)
--   2. Run `php spark db:seed DatabaseSeeder` first to create the two default
--      users (admin@ticketmaster.lt / Admin@1234 and user@ticketmaster.lt / User@1234).
--      This script does NOT insert users to avoid bcrypt hash issues.
--
-- Import:
--   mysql -u <user> -p <database> < seed_test_data.sql
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ── Quarries ─────────────────────────────────────────────────────────────────
TRUNCATE TABLE `task_tickets`;
TRUNCATE TABLE `invoice_tickets`;
TRUNCATE TABLE `tasks`;
TRUNCATE TABLE `invoices`;
TRUNCATE TABLE `trucks`;
TRUNCATE TABLE `quarries`;

INSERT INTO `quarries` (`id`, `nombre_cantera`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Cantera El Norte',   1, NOW(), NOW()),
(2, 'Cantera Sur',        1, NOW(), NOW()),
(3, 'Cantera Central',    1, NOW(), NOW()),
(4, 'Cantera del Río',    1, NOW(), NOW()),
(5, 'Cantera La Cumbre',  0, NOW(), NOW());

-- ── Trucks ───────────────────────────────────────────────────────────────────
INSERT INTO `trucks` (`id`, `no_camion`, `nombre_chofer`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'T-001', 'Juan García',       1, NOW(), NOW()),
(2, 'T-002', 'Pedro López',       1, NOW(), NOW()),
(3, 'T-003', 'Miguel Rodríguez',  1, NOW(), NOW()),
(4, 'T-004', 'Carlos Martínez',   1, NOW(), NOW()),
(5, 'T-005', 'Antonio Sánchez',   0, NOW(), NOW());

-- ── Invoices ─────────────────────────────────────────────────────────────────
-- INV-2026-001: Cantera El Norte  — 5 tickets × $350 = $1,750  (paid)
-- INV-2026-002: Cantera Sur       — 4 tickets × $350 = $1,400  (received)
-- INV-2026-003: Cantera Central   — 3 tickets × $350 = $1,050  (pending)
-- INV-2026-004: Cantera El Norte  — 4 tickets × $350 = $1,400  (paid)
-- INV-2026-005: Cantera del Río   — 3 tickets × $320 = $960    (received)
-- INV-2026-006: Cantera Sur       — 2 tickets × $380 = $760    (pending)
INSERT INTO `invoices`
    (`id`, `no_factura`,    `fecha`,        `cantera_id`, `due_date`,    `fecha_recibida`, `monto_total`, `status`,    `created_at`, `updated_at`) VALUES
(1,  'INV-2026-001',  '2026-01-05',  1,            '2026-02-05',  '2026-01-10',     1750.00,      'paid',      NOW(), NOW()),
(2,  'INV-2026-002',  '2026-01-12',  2,            '2026-02-12',  '2026-01-18',     1400.00,      'received',  NOW(), NOW()),
(3,  'INV-2026-003',  '2026-02-03',  3,            '2026-03-03',  NULL,             1050.00,      'pending',   NOW(), NOW()),
(4,  'INV-2026-004',  '2026-02-10',  1,            '2026-03-10',  '2026-02-15',     1400.00,      'paid',      NOW(), NOW()),
(5,  'INV-2026-005',  '2026-02-20',  4,            '2026-03-20',  '2026-02-25',      960.00,      'received',  NOW(), NOW()),
(6,  'INV-2026-006',  '2026-03-05',  2,            '2026-04-05',  NULL,              760.00,      'pending',   NOW(), NOW());

-- ── Invoice Tickets ───────────────────────────────────────────────────────────
-- INV-2026-001 (invoice_id=1, cantera_id=1): tickets 101–105
INSERT INTO `invoice_tickets`
    (`invoice_id`, `no_ticket`, `fecha`,       `tipo_trabajo`,  `cantera_id`, `direccion`,            `rate`,  `created_at`, `updated_at`) VALUES
(1, '101', '2026-01-02', 'Hauling',   1, '123 Main St, Springfield',   350.00, NOW(), NOW()),
(1, '102', '2026-01-02', 'Hauling',   1, '123 Main St, Springfield',   350.00, NOW(), NOW()),
(1, '103', '2026-01-03', 'Hauling',   1, '456 Oak Ave, Springfield',   350.00, NOW(), NOW()),
(1, '104', '2026-01-03', 'Delivery',  1, '456 Oak Ave, Springfield',   350.00, NOW(), NOW()),
(1, '105', '2026-01-04', 'Delivery',  1, '789 Pine Rd, Springfield',   350.00, NOW(), NOW()),
-- INV-2026-002 (invoice_id=2, cantera_id=2): tickets 201–204
(2, '201', '2026-01-10', 'Hauling',   2, '10 River Blvd, Riverside',   350.00, NOW(), NOW()),
(2, '202', '2026-01-10', 'Hauling',   2, '10 River Blvd, Riverside',   350.00, NOW(), NOW()),
(2, '203', '2026-01-11', 'Hauling',   2, '22 South Ave, Riverside',    350.00, NOW(), NOW()),
(2, '204', '2026-01-11', 'Delivery',  2, '22 South Ave, Riverside',    350.00, NOW(), NOW()),
-- INV-2026-003 (invoice_id=3, cantera_id=3): tickets 301–303
(3, '301', '2026-02-01', 'Hauling',   3, '5 Center St, Midtown',       350.00, NOW(), NOW()),
(3, '302', '2026-02-01', 'Hauling',   3, '5 Center St, Midtown',       350.00, NOW(), NOW()),
(3, '303', '2026-02-02', 'Delivery',  3, '18 West Blvd, Midtown',      350.00, NOW(), NOW()),
-- INV-2026-004 (invoice_id=4, cantera_id=1): tickets 106–109
(4, '106', '2026-02-08', 'Hauling',   1, '123 Main St, Springfield',   350.00, NOW(), NOW()),
(4, '107', '2026-02-08', 'Hauling',   1, '123 Main St, Springfield',   350.00, NOW(), NOW()),
(4, '108', '2026-02-09', 'Delivery',  1, '789 Pine Rd, Springfield',   350.00, NOW(), NOW()),
(4, '109', '2026-02-09', 'Delivery',  1, '789 Pine Rd, Springfield',   350.00, NOW(), NOW()),
-- INV-2026-005 (invoice_id=5, cantera_id=4): tickets 401–403
(5, '401', '2026-02-18', 'Hauling',   4, '30 North Rd, Northside',     320.00, NOW(), NOW()),
(5, '402', '2026-02-18', 'Hauling',   4, '30 North Rd, Northside',     320.00, NOW(), NOW()),
(5, '403', '2026-02-19', 'Delivery',  4, '55 East Dr, Northside',      320.00, NOW(), NOW()),
-- INV-2026-006 (invoice_id=6, cantera_id=2): tickets 205–206
(6, '205', '2026-03-03', 'Hauling',   2, '10 River Blvd, Riverside',   380.00, NOW(), NOW()),
(6, '206', '2026-03-03', 'Hauling',   2, '22 South Ave, Riverside',    380.00, NOW(), NOW());

-- ── Tasks ─────────────────────────────────────────────────────────────────────
-- TASK 1: T-001/Juan García   — 5 tickets (3 matched, 2 unmatched) — open
-- TASK 2: T-002/Pedro López   — 4 tickets (4 matched)              — delivered
-- TASK 3: T-003/Miguel Rodríguez — 3 tickets (3 matched)           — open
-- TASK 4: T-001/Juan García   — 4 tickets (3 matched, 1 unmatched) — delivered
-- TASK 5: T-004/Carlos Martínez — 3 tickets (0 matched)            — open
INSERT INTO `tasks`
    (`id`, `truck_id`, `nombre_chofer`,     `periodo`,         `monto_total`, `status`,    `delivered_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Juan García',       'January 2026',    1750.00, 'open',      NULL,                  '2026-01-05 08:00:00', NOW()),
(2, 2, 'Pedro López',       'January 2026',    1400.00, 'delivered', '2026-01-28 17:00:00', '2026-01-12 08:00:00', NOW()),
(3, 3, 'Miguel Rodríguez',  'February 2026',   1050.00, 'open',      NULL,                  '2026-02-03 08:00:00', NOW()),
(4, 1, 'Juan García',       'February 2026',   1400.00, 'delivered', '2026-02-28 17:00:00', '2026-02-10 08:00:00', NOW()),
(5, 4, 'Carlos Martínez',   'March 2026',       960.00, 'open',      NULL,                  '2026-03-01 08:00:00', NOW());

-- ── Task Tickets ──────────────────────────────────────────────────────────────
-- Key for Phase 8 demo:
--   '00101' → CAST = 101 → matches invoice ticket '101' (cantera_id=1) ✓
--   '102'   → CAST = 102 → matches invoice ticket '102' (cantera_id=1) ✓
--   '103'   → CAST = 103 → matches invoice ticket '103' (cantera_id=1) ✓
--   '888'   → CAST = 888 → NO match (cantera_id=1)                     ✗ UNMATCHED
--   '889'   → CAST = 889 → NO match (cantera_id=1)                     ✗ UNMATCHED
--   '106'   → matches INV-004 ticket '106' (cantera_id=1)              ✓
--   '107'   → matches INV-004 ticket '107' (cantera_id=1)              ✓
--   '108'   → matches INV-004 ticket '108' (cantera_id=1)              ✓
--   '777'   → CAST = 777 → NO match (cantera_id=1)                     ✗ UNMATCHED
--   '501'-'503' → NO matching invoice tickets (cantera_id=4)           ✗ UNMATCHED ×3
INSERT INTO `task_tickets`
    (`task_id`, `no_ticket`, `fecha`,       `tipo_trabajo`,  `cantera_id`, `direccion`,                  `rate`,  `created_at`, `updated_at`) VALUES
-- TASK 1 — Juan García (truck T-001) — Jan 2026
(1, '00101', '2026-01-02', 'Hauling',   1, '123 Main St, Springfield',   350.00, NOW(), NOW()),  -- ✓ matches INV-001/101
(1, '102',   '2026-01-02', 'Hauling',   1, '123 Main St, Springfield',   350.00, NOW(), NOW()),  -- ✓ matches INV-001/102
(1, '103',   '2026-01-03', 'Hauling',   1, '456 Oak Ave, Springfield',   350.00, NOW(), NOW()),  -- ✓ matches INV-001/103
(1, '888',   '2026-01-03', 'Delivery',  1, '456 Oak Ave, Springfield',   350.00, NOW(), NOW()),  -- ✗ UNMATCHED
(1, '889',   '2026-01-04', 'Delivery',  1, '789 Pine Rd, Springfield',   350.00, NOW(), NOW()),  -- ✗ UNMATCHED
-- TASK 2 — Pedro López (truck T-002) — Jan 2026
(2, '201',   '2026-01-10', 'Hauling',   2, '10 River Blvd, Riverside',   350.00, NOW(), NOW()),  -- ✓ matches INV-002/201
(2, '202',   '2026-01-10', 'Hauling',   2, '10 River Blvd, Riverside',   350.00, NOW(), NOW()),  -- ✓ matches INV-002/202
(2, '203',   '2026-01-11', 'Hauling',   2, '22 South Ave, Riverside',    350.00, NOW(), NOW()),  -- ✓ matches INV-002/203
(2, '204',   '2026-01-11', 'Delivery',  2, '22 South Ave, Riverside',    350.00, NOW(), NOW()),  -- ✓ matches INV-002/204
-- TASK 3 — Miguel Rodríguez (truck T-003) — Feb 2026
(3, '301',   '2026-02-01', 'Hauling',   3, '5 Center St, Midtown',       350.00, NOW(), NOW()),  -- ✓ matches INV-003/301
(3, '302',   '2026-02-01', 'Hauling',   3, '5 Center St, Midtown',       350.00, NOW(), NOW()),  -- ✓ matches INV-003/302
(3, '303',   '2026-02-02', 'Delivery',  3, '18 West Blvd, Midtown',      350.00, NOW(), NOW()),  -- ✓ matches INV-003/303
-- TASK 4 — Juan García (truck T-001) — Feb 2026
(4, '106',   '2026-02-08', 'Hauling',   1, '123 Main St, Springfield',   350.00, NOW(), NOW()),  -- ✓ matches INV-004/106
(4, '107',   '2026-02-08', 'Hauling',   1, '123 Main St, Springfield',   350.00, NOW(), NOW()),  -- ✓ matches INV-004/107
(4, '108',   '2026-02-09', 'Delivery',  1, '789 Pine Rd, Springfield',   350.00, NOW(), NOW()),  -- ✓ matches INV-004/108
(4, '777',   '2026-02-09', 'Delivery',  1, '789 Pine Rd, Springfield',   350.00, NOW(), NOW()),  -- ✗ UNMATCHED
-- TASK 5 — Carlos Martínez (truck T-004) — Mar 2026  (all unmatched)
(5, '501',   '2026-03-01', 'Hauling',   4, '30 North Rd, Northside',     320.00, NOW(), NOW()),  -- ✗ UNMATCHED
(5, '502',   '2026-03-01', 'Hauling',   4, '30 North Rd, Northside',     320.00, NOW(), NOW()),  -- ✗ UNMATCHED
(5, '503',   '2026-03-02', 'Delivery',  4, '55 East Dr, Northside',      320.00, NOW(), NOW());  -- ✗ UNMATCHED

-- Summary: 6 unmatched task tickets (888, 889, 777, 501, 502, 503)
-- After running Phase 8 "Match": 777→109 or 888/889→104/105 can be corrected

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- Expected dashboard counts after import + seeder:
--   Invoices: 6   |  Tasks: 5   |  Quarries: 4 (active)  |  Unmatched: 6
-- =============================================================================
