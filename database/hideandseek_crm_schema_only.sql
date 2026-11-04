-- ============================================================
-- HIDEANDSEEK CRM - MySQL Database v2 (CHI CAU TRUC, KHONG CO DU LIEU MAU)
-- MVP scope:
--   1) Customer management
--   2) Membership & loyalty points
--   3) CRM transaction recording
--   4) RFM analysis (calculated from transaction data)
--   5) Dashboard data source
--   6) Product/category/style data for recommendation prototype
--
-- Target: MySQL 8.0.16+ / phpMyAdmin (CHECK constraints are enforced from 8.0.16)
--
-- v2 changes:
--   * Added CHECK constraints (price/amount >= 0, quantity > 0,
--     subtotal = quantity * unit_price, points_change <> 0, ...)
--   * Sample data regenerated so that it is fully consistent:
--       unit_price = products.price, subtotal = quantity * unit_price,
--       crm_transactions.total_amount = SUM(items.subtotal),
--       membership tier = highest tier with min_spending <= total completed spending,
--       points = FLOOR(total_amount / 100000 * point_rate of tier at that time),
--       customers.current_points = SUM(point_transactions.points_change)
--   * Point history now has created_at matching the transaction date
--   * Demo user password hash replaced by a valid bcrypt hash of 'password'
--
-- WARNING:
-- This script is designed to be re-runnable.
-- It DROPS the 9 tables below before recreating them.
-- Do not run it on a database containing production data you want to keep.
-- ============================================================

CREATE DATABASE IF NOT EXISTS `hideandseek_crm`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `hideandseek_crm`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `point_transactions`;
DROP TABLE IF EXISTS `crm_transaction_items`;
DROP TABLE IF EXISTS `crm_transactions`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `styles`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `membership_tiers`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 1. USERS
-- ============================================================
CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('manager', 'marketing') NOT NULL DEFAULT 'marketing',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_email` (`email`),
    KEY `idx_users_role` (`role`),
    KEY `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 2. MEMBERSHIP TIERS
-- ============================================================
CREATE TABLE `membership_tiers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `min_spending` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `point_rate` DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    `description` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    CONSTRAINT `chk_membership_tiers_values` CHECK (`min_spending` >= 0 AND `point_rate` > 0),
    UNIQUE KEY `uk_membership_tiers_name` (`name`),
    KEY `idx_membership_tiers_min_spending` (`min_spending`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 3. CUSTOMERS
-- ============================================================
CREATE TABLE `customers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_code` VARCHAR(20) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `email` VARCHAR(150) NULL,
    `gender` ENUM('male', 'female', 'other') NULL,
    `date_of_birth` DATE NULL,
    `address` VARCHAR(255) NULL,
    `membership_tier_id` BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `current_points` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    CONSTRAINT `chk_customers_points` CHECK (`current_points` >= 0),
    UNIQUE KEY `uk_customers_code` (`customer_code`),
    UNIQUE KEY `uk_customers_phone` (`phone`),
    UNIQUE KEY `uk_customers_email` (`email`),
    KEY `idx_customers_name` (`full_name`),
    KEY `idx_customers_membership_tier` (`membership_tier_id`),
    KEY `idx_customers_points` (`current_points`),
    CONSTRAINT `fk_customers_membership_tier`
        FOREIGN KEY (`membership_tier_id`)
        REFERENCES `membership_tiers` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 4. CATEGORIES
-- ============================================================
CREATE TABLE `categories` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_categories_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 5. STYLES
-- ============================================================
CREATE TABLE `styles` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_styles_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 6. PRODUCTS
-- ============================================================
CREATE TABLE `products` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_code` VARCHAR(30) NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `category_id` BIGINT UNSIGNED NOT NULL,
    `style_id` BIGINT UNSIGNED NOT NULL,
    `price` DECIMAL(15,2) NOT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    CONSTRAINT `chk_products_price` CHECK (`price` >= 0),
    UNIQUE KEY `uk_products_code` (`product_code`),
    KEY `idx_products_name` (`name`),
    KEY `idx_products_category` (`category_id`),
    KEY `idx_products_style` (`style_id`),
    KEY `idx_products_category_style` (`category_id`, `style_id`),
    CONSTRAINT `fk_products_category`
        FOREIGN KEY (`category_id`)
        REFERENCES `categories` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT `fk_products_style`
        FOREIGN KEY (`style_id`)
        REFERENCES `styles` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 7. CRM TRANSACTIONS
-- Note: This is CRM transaction history, NOT a full POS module.
-- ============================================================
CREATE TABLE `crm_transactions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `transaction_code` VARCHAR(30) NOT NULL,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `transaction_date` DATETIME NOT NULL,
    `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('completed', 'cancelled') NOT NULL DEFAULT 'completed',
    `created_by` BIGINT UNSIGNED NOT NULL,
    `note` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    CONSTRAINT `chk_crm_transactions_total` CHECK (`total_amount` >= 0),
    UNIQUE KEY `uk_crm_transactions_code` (`transaction_code`),
    KEY `idx_crm_transactions_customer` (`customer_id`),
    KEY `idx_crm_transactions_date` (`transaction_date`),
    KEY `idx_crm_transactions_customer_date` (`customer_id`, `transaction_date`),
    KEY `idx_crm_transactions_status` (`status`),
    KEY `idx_crm_transactions_created_by` (`created_by`),
    CONSTRAINT `fk_crm_transactions_customer`
        FOREIGN KEY (`customer_id`)
        REFERENCES `customers` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT `fk_crm_transactions_created_by`
        FOREIGN KEY (`created_by`)
        REFERENCES `users` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 8. CRM TRANSACTION ITEMS
-- ============================================================
CREATE TABLE `crm_transaction_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `transaction_id` BIGINT UNSIGNED NOT NULL,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `quantity` INT UNSIGNED NOT NULL,
    `unit_price` DECIMAL(15,2) NOT NULL,
    `subtotal` DECIMAL(15,2) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    CONSTRAINT `chk_transaction_items_values` CHECK (`quantity` > 0 AND `unit_price` >= 0 AND `subtotal` = `quantity` * `unit_price`),
    UNIQUE KEY `uk_transaction_product` (`transaction_id`, `product_id`),
    KEY `idx_transaction_items_transaction` (`transaction_id`),
    KEY `idx_transaction_items_product` (`product_id`),
    CONSTRAINT `fk_transaction_items_transaction`
        FOREIGN KEY (`transaction_id`)
        REFERENCES `crm_transactions` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT `fk_transaction_items_product`
        FOREIGN KEY (`product_id`)
        REFERENCES `products` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 9. POINT TRANSACTIONS
-- points_change > 0 : add points
-- points_change < 0 : subtract points
-- ============================================================
CREATE TABLE `point_transactions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `points_change` INT NOT NULL,
    `reason` VARCHAR(255) NOT NULL,
    `reference_transaction_id` BIGINT UNSIGNED NULL,
    `created_by` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    CONSTRAINT `chk_point_transactions_change` CHECK (`points_change` <> 0),
    KEY `idx_point_transactions_customer` (`customer_id`),
    KEY `idx_point_transactions_customer_date` (`customer_id`, `created_at`),
    KEY `idx_point_transactions_reference` (`reference_transaction_id`),
    KEY `idx_point_transactions_created_by` (`created_by`),
    CONSTRAINT `fk_point_transactions_customer`
        FOREIGN KEY (`customer_id`)
        REFERENCES `customers` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT `fk_point_transactions_reference`
        FOREIGN KEY (`reference_transaction_id`)
        REFERENCES `crm_transactions` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT `fk_point_transactions_created_by`
        FOREIGN KEY (`created_by`)
        REFERENCES `users` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

