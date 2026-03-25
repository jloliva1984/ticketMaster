-- =============================================================================
-- TicketMaster.LT — Complete Database Schema
-- Compatible with: MySQL 5.7+ / MariaDB 10.3+
-- Character set: utf8mb4 / utf8mb4_unicode_ci
-- Generated for: CodeIgniter 4 migrations
--
-- Usage:
--   mysql -u <user> -p <database> < schema.sql
--
-- After import, run seeders:
--   php spark db:seed DatabaseSeeder
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;

-- -----------------------------------------------------------------------------
-- Drop tables in reverse dependency order
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `task_tickets`;
DROP TABLE IF EXISTS `invoice_tickets`;
DROP TABLE IF EXISTS `tasks`;
DROP TABLE IF EXISTS `invoices`;
DROP TABLE IF EXISTS `trucks`;
DROP TABLE IF EXISTS `quarries`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `ci_sessions`;
DROP TABLE IF EXISTS `migrations`;

-- -----------------------------------------------------------------------------
-- Table: users
-- -----------------------------------------------------------------------------
CREATE TABLE `users` (
    `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100)     NOT NULL,
    `email`      VARCHAR(150)     NOT NULL,
    `password`   VARCHAR(255)     NOT NULL,
    `role`       ENUM('admin','user') NOT NULL DEFAULT 'user',
    `is_active`  TINYINT(1)       NOT NULL DEFAULT 1,
    `last_login` DATETIME         DEFAULT NULL,
    `created_at` DATETIME         DEFAULT NULL,
    `updated_at` DATETIME         DEFAULT NULL,
    `deleted_at` DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`),
    KEY `idx_users_role`      (`role`),
    KEY `idx_users_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Table: quarries  (Canteras)
-- -----------------------------------------------------------------------------
CREATE TABLE `quarries` (
    `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre_cantera` VARCHAR(150)     NOT NULL,
    `is_active`      TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`     DATETIME         DEFAULT NULL,
    `updated_at`     DATETIME         DEFAULT NULL,
    `deleted_at`     DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_quarries_nombre` (`nombre_cantera`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Table: trucks  (Camiones)
-- -----------------------------------------------------------------------------
CREATE TABLE `trucks` (
    `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `no_camion`     VARCHAR(20)      NOT NULL,
    `nombre_chofer` VARCHAR(100)     NOT NULL,
    `is_active`     TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`    DATETIME         DEFAULT NULL,
    `updated_at`    DATETIME         DEFAULT NULL,
    `deleted_at`    DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_trucks_no_camion` (`no_camion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Table: invoices  (Facturas)
-- -----------------------------------------------------------------------------
CREATE TABLE `invoices` (
    `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `no_factura`     VARCHAR(50)      NOT NULL,
    `fecha`          DATE             NOT NULL,
    `cantera_id`     INT(11) UNSIGNED NOT NULL,
    `due_date`       DATE             DEFAULT NULL,
    `fecha_recibida` DATE             DEFAULT NULL,
    `pdf_file`       VARCHAR(255)     DEFAULT NULL,
    `monto_total`    DECIMAL(12,2)    NOT NULL DEFAULT 0.00,
    `status`         ENUM('pending','received','paid') NOT NULL DEFAULT 'pending',
    `notes`          TEXT             DEFAULT NULL,
    `created_at`     DATETIME         DEFAULT NULL,
    `updated_at`     DATETIME         DEFAULT NULL,
    `deleted_at`     DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_invoices_no_factura` (`no_factura`),
    KEY `idx_invoices_cantera_id` (`cantera_id`),
    KEY `idx_invoices_fecha`      (`fecha`),
    KEY `idx_invoices_status`     (`status`),
    CONSTRAINT `fk_invoices_cantera`
        FOREIGN KEY (`cantera_id`) REFERENCES `quarries` (`id`)
        ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Table: invoice_tickets
-- -----------------------------------------------------------------------------
CREATE TABLE `invoice_tickets` (
    `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `invoice_id`   INT(11) UNSIGNED NOT NULL,
    `no_ticket`    VARCHAR(50)      NOT NULL,
    `fecha`        DATE             NOT NULL,
    `tipo_trabajo` VARCHAR(100)     DEFAULT NULL,
    `cantera_id`   INT(11) UNSIGNED DEFAULT NULL,
    `direccion`    VARCHAR(255)     DEFAULT NULL,
    `rate`         DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    `created_at`   DATETIME         DEFAULT NULL,
    `updated_at`   DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_it_invoice_id`  (`invoice_id`),
    KEY `idx_it_no_ticket`   (`no_ticket`),
    KEY `idx_it_cantera_id`  (`cantera_id`),
    CONSTRAINT `fk_it_invoice`
        FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_it_cantera`
        FOREIGN KEY (`cantera_id`) REFERENCES `quarries` (`id`)
        ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Table: tasks  (Tareas)
-- -----------------------------------------------------------------------------
CREATE TABLE `tasks` (
    `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `truck_id`      INT(11) UNSIGNED NOT NULL,
    `nombre_chofer` VARCHAR(100)     NOT NULL COMMENT 'Stored at creation to preserve history',
    `periodo`       VARCHAR(100)     DEFAULT NULL COMMENT 'e.g. March 2026',
    `monto_total`   DECIMAL(12,2)    NOT NULL DEFAULT 0.00,
    `status`        ENUM('open','delivered') NOT NULL DEFAULT 'open',
    `delivered_at`  DATETIME         DEFAULT NULL,
    `notes`         TEXT             DEFAULT NULL,
    `created_at`    DATETIME         DEFAULT NULL,
    `updated_at`    DATETIME         DEFAULT NULL,
    `deleted_at`    DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_tasks_truck_id` (`truck_id`),
    KEY `idx_tasks_status`   (`status`),
    CONSTRAINT `fk_tasks_truck`
        FOREIGN KEY (`truck_id`) REFERENCES `trucks` (`id`)
        ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Table: task_tickets
-- -----------------------------------------------------------------------------
CREATE TABLE `task_tickets` (
    `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `task_id`      INT(11) UNSIGNED NOT NULL,
    `no_ticket`    VARCHAR(50)      NOT NULL,
    `fecha`        DATE             NOT NULL,
    `tipo_trabajo` VARCHAR(100)     DEFAULT NULL,
    `cantera_id`   INT(11) UNSIGNED DEFAULT NULL,
    `direccion`    VARCHAR(255)     DEFAULT NULL,
    `rate`         DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    `created_at`   DATETIME         DEFAULT NULL,
    `updated_at`   DATETIME         DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_tt_task_id`    (`task_id`),
    KEY `idx_tt_no_ticket`  (`no_ticket`),
    KEY `idx_tt_cantera_id` (`cantera_id`),
    CONSTRAINT `fk_tt_task`
        FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tt_cantera`
        FOREIGN KEY (`cantera_id`) REFERENCES `quarries` (`id`)
        ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Table: ci_sessions  (CodeIgniter file sessions are used; table not needed)
-- Leave commented unless switching to DatabaseHandler:
-- -----------------------------------------------------------------------------
-- CREATE TABLE `ci_sessions` (
--     `id`         VARCHAR(128) NOT NULL,
--     `ip_address` VARCHAR(45)  NOT NULL,
--     `timestamp`  INT(10) UNSIGNED NOT NULL DEFAULT 0,
--     `data`       BLOB         NOT NULL,
--     PRIMARY KEY (`id`),
--     KEY `ci_sessions_timestamp` (`timestamp`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- Table: migrations  (CodeIgniter tracks migrations here)
-- -----------------------------------------------------------------------------
CREATE TABLE `migrations` (
    `id`        BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `version`   VARCHAR(255)        NOT NULL,
    `class`     TEXT                NOT NULL,
    `group`     VARCHAR(255)        NOT NULL,
    `namespace` VARCHAR(255)        NOT NULL,
    `time`      INT(11)             NOT NULL,
    `batch`     INT(11) UNSIGNED    NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
