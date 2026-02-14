# ************************************************************
# Sequel Ace SQL dump
# Version 20096
#
# https://sequel-ace.com/
# https://github.com/Sequel-Ace/Sequel-Ace
#
# Host: 127.0.0.1 (MySQL 8.4.8)
# Database: beayar
# Generation Time: 2026-02-14 06:03:59 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE='NO_AUTO_VALUE_ON_ZERO', SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table activity_log
# ------------------------------------------------------------

CREATE TABLE `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint unsigned DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table admins
# ------------------------------------------------------------

CREATE TABLE `admins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'super_admin',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table bill_challans
# ------------------------------------------------------------

CREATE TABLE `bill_challans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` bigint unsigned NOT NULL,
  `challan_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bill_challans_bill_id_index` (`bill_id`),
  KEY `bill_challans_challan_id_index` (`challan_id`),
  CONSTRAINT `bill_challans_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bill_challans_challan_id_foreign` FOREIGN KEY (`challan_id`) REFERENCES `challans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table bill_items
# ------------------------------------------------------------

CREATE TABLE `bill_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bill_challan_id` bigint unsigned NOT NULL,
  `quotation_product_id` bigint unsigned NOT NULL,
  `challan_product_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `remaining_quantity` int NOT NULL DEFAULT '0',
  `unit_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `bill_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bill_items_challan_product_id_foreign` (`challan_product_id`),
  KEY `bill_items_bill_challan_id_index` (`bill_challan_id`),
  KEY `bill_items_quotation_product_id_index` (`quotation_product_id`),
  CONSTRAINT `bill_items_bill_challan_id_foreign` FOREIGN KEY (`bill_challan_id`) REFERENCES `bill_challans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bill_items_challan_product_id_foreign` FOREIGN KEY (`challan_product_id`) REFERENCES `challan_products` (`id`),
  CONSTRAINT `bill_items_quotation_product_id_foreign` FOREIGN KEY (`quotation_product_id`) REFERENCES `quotation_products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table bills
# ------------------------------------------------------------

CREATE TABLE `bills` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_company_id` bigint unsigned NOT NULL,
  `quotation_id` bigint unsigned NOT NULL,
  `quotation_revision_id` bigint unsigned DEFAULT NULL,
  `parent_bill_id` bigint unsigned DEFAULT NULL,
  `bill_type` enum('advance','regular','running') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'regular',
  `invoice_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bill_date` date NOT NULL,
  `payment_received_date` date DEFAULT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `bill_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `bill_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `due` double NOT NULL DEFAULT '0',
  `shipping` double NOT NULL DEFAULT '0',
  `discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('draft','issued','paid','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'issued',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bills_invoice_no_unique` (`invoice_no`),
  KEY `bills_tenant_company_id_foreign` (`tenant_company_id`),
  KEY `bills_parent_bill_id_foreign` (`parent_bill_id`),
  KEY `bills_quotation_id_index` (`quotation_id`),
  KEY `bills_quotation_revision_id_index` (`quotation_revision_id`),
  CONSTRAINT `bills_parent_bill_id_foreign` FOREIGN KEY (`parent_bill_id`) REFERENCES `bills` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bills_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `bills_quotation_revision_id_foreign` FOREIGN KEY (`quotation_revision_id`) REFERENCES `quotation_revisions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bills_tenant_company_id_foreign` FOREIGN KEY (`tenant_company_id`) REFERENCES `tenant_companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table brand_origins
# ------------------------------------------------------------

CREATE TABLE `brand_origins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_company_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_origins_tenant_company_id_foreign` (`tenant_company_id`),
  CONSTRAINT `brand_origins_tenant_company_id_foreign` FOREIGN KEY (`tenant_company_id`) REFERENCES `tenant_companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table cache
# ------------------------------------------------------------

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table cache_locks
# ------------------------------------------------------------

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table challan_products
# ------------------------------------------------------------

CREATE TABLE `challan_products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `challan_id` bigint unsigned NOT NULL,
  `quotation_product_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `challan_products_challan_id_foreign` (`challan_id`),
  KEY `challan_products_quotation_product_id_foreign` (`quotation_product_id`),
  CONSTRAINT `challan_products_challan_id_foreign` FOREIGN KEY (`challan_id`) REFERENCES `challans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `challan_products_quotation_product_id_foreign` FOREIGN KEY (`quotation_product_id`) REFERENCES `quotation_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table challans
# ------------------------------------------------------------

CREATE TABLE `challans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_company_id` bigint unsigned NOT NULL,
  `quotation_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `quotation_revision_id` bigint unsigned DEFAULT NULL,
  `challan_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `delivery_address` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `challans_tenant_company_id_foreign` (`tenant_company_id`),
  KEY `challans_quotation_id_foreign` (`quotation_id`),
  KEY `challans_customer_id_foreign` (`customer_id`),
  KEY `challans_quotation_revision_id_foreign` (`quotation_revision_id`),
  CONSTRAINT `challans_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `challans_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`),
  CONSTRAINT `challans_quotation_revision_id_foreign` FOREIGN KEY (`quotation_revision_id`) REFERENCES `quotation_revisions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `challans_tenant_company_id_foreign` FOREIGN KEY (`tenant_company_id`) REFERENCES `tenant_companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table company_members
# ------------------------------------------------------------

CREATE TABLE `company_members` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_company_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'employee',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_members_tenant_company_id_user_id_unique` (`tenant_company_id`,`user_id`),
  KEY `company_members_user_id_foreign` (`user_id`),
  CONSTRAINT `company_members_tenant_company_id_foreign` FOREIGN KEY (`tenant_company_id`) REFERENCES `tenant_companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `company_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table coupon_campaigns
# ------------------------------------------------------------

CREATE TABLE `coupon_campaigns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_company_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `coupon_campaigns_tenant_company_id_foreign` (`tenant_company_id`),
  CONSTRAINT `coupon_campaigns_tenant_company_id_foreign` FOREIGN KEY (`tenant_company_id`) REFERENCES `tenant_companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table coupon_customers
# ------------------------------------------------------------

CREATE TABLE `coupon_customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `coupon_customers_coupon_id_foreign` (`coupon_id`),
  KEY `coupon_customers_customer_id_foreign` (`customer_id`),
  CONSTRAINT `coupon_customers_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_customers_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table coupon_limits
# ------------------------------------------------------------

CREATE TABLE `coupon_limits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` bigint unsigned NOT NULL,
  `min_expenditure` decimal(15,2) NOT NULL DEFAULT '0.00',
  `max_expenditure` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `coupon_limits_coupon_id_foreign` (`coupon_id`),
  CONSTRAINT `coupon_limits_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table coupon_usage
# ------------------------------------------------------------

CREATE TABLE `coupon_usage` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `platform_invoice_id` bigint unsigned DEFAULT NULL,
  `discount_applied` decimal(10,2) NOT NULL,
  `used_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `coupon_usage_coupon_id_foreign` (`coupon_id`),
  KEY `coupon_usage_user_id_foreign` (`user_id`),
  CONSTRAINT `coupon_usage_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_usage_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table coupons
# ------------------------------------------------------------

CREATE TABLE `coupons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_company_id` bigint unsigned DEFAULT NULL,
  `campaign_id` bigint unsigned DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `discount_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `max_discount_amount` decimal(15,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupons_code_unique` (`code`),
  KEY `coupons_tenant_company_id_foreign` (`tenant_company_id`),
  KEY `coupons_campaign_id_foreign` (`campaign_id`),
  CONSTRAINT `coupons_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `coupon_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupons_tenant_company_id_foreign` FOREIGN KEY (`tenant_company_id`) REFERENCES `tenant_companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table customer_companies
# ------------------------------------------------------------

CREATE TABLE `customer_companies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_company_id` bigint unsigned NOT NULL,
  `company_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `bin_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_companies_tenant_company_id_foreign` (`tenant_company_id`),
  CONSTRAINT `customer_companies_tenant_company_id_foreign` FOREIGN KEY (`tenant_company_id`) REFERENCES `tenant_companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table customers
# ------------------------------------------------------------

CREATE TABLE `customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_company_id` bigint unsigned NOT NULL,
  `customer_company_id` bigint unsigned NOT NULL,
  `customer_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attention` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `designation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'individual',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customers_tenant_company_id_foreign` (`tenant_company_id`),
  KEY `customers_customer_company_id_foreign` (`customer_company_id`),
  CONSTRAINT `customers_customer_company_id_foreign` FOREIGN KEY (`customer_company_id`) REFERENCES `customer_companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customers_tenant_company_id_foreign` FOREIGN KEY (`tenant_company_id`) REFERENCES `tenant_companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table expense_categories
# ------------------------------------------------------------

CREATE TABLE `expense_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_company_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expense_categories_tenant_company_id_foreign` (`tenant_company_id`),
  CONSTRAINT `expense_categories_tenant_company_id_foreign` FOREIGN KEY (`tenant_company_id`) REFERENCES `tenant_companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table expenses
# ------------------------------------------------------------

CREATE TABLE `expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_company_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `receipt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_tenant_company_id_foreign` (`tenant_company_id`),
  KEY `expenses_category_id_foreign` (`category_id`),
  KEY `expenses_user_id_foreign` (`user_id`),
  CONSTRAINT `expenses_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `expense_categories` (`id`),
  CONSTRAINT `expenses_tenant_company_id_foreign` FOREIGN KEY (`tenant_company_id`) REFERENCES `tenant_companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expenses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table failed_jobs
# ------------------------------------------------------------

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table feature_flags
# ------------------------------------------------------------

CREATE TABLE `feature_flags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `feature_flags_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table images
# ------------------------------------------------------------

CREATE TABLE `images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_company_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `images_tenant_company_id_foreign` (`tenant_company_id`),
  KEY `images_name_index` (`name`),
  CONSTRAINT `images_tenant_company_id_foreign` FOREIGN KEY (`tenant_company_id`) REFERENCES `tenant_companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table job_batches
# ------------------------------------------------------------

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table jobs
# ------------------------------------------------------------

CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table migrations
# ------------------------------------------------------------

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table model_has_permissions
# ------------------------------------------------------------

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table model_has_roles
# ------------------------------------------------------------

CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table modules
# ------------------------------------------------------------

CREATE TABLE `modules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `modules_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table notifications
# ------------------------------------------------------------

CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table password_reset_tokens
# ------------------------------------------------------------

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table payments
# ------------------------------------------------------------

CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_company_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `bill_id` bigint unsigned DEFAULT NULL,
  `payment_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `reference` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_payment_no_unique` (`payment_no`),
  KEY `payments_tenant_company_id_foreign` (`tenant_company_id`),
  KEY `payments_customer_id_foreign` (`customer_id`),
  KEY `payments_bill_id_foreign` (`bill_id`),
  CONSTRAINT `payments_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`),
  CONSTRAINT `payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `payments_tenant_company_id_foreign` FOREIGN KEY (`tenant_company_id`) REFERENCES `tenant_companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table permissions
# ------------------------------------------------------------

CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table personal_access_tokens
# ------------------------------------------------------------

CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table plans
# ------------------------------------------------------------

CREATE TABLE `plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `base_price` decimal(10,2) NOT NULL,
  `billing_cycle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `limits` json DEFAULT NULL,
  `module_access` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plans_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table platform_invoices
# ------------------------------------------------------------

CREATE TABLE `platform_invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `subscription_id` bigint unsigned DEFAULT NULL,
  `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `due_date` date NOT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `platform_invoices_invoice_number_unique` (`invoice_number`),
  KEY `platform_invoices_user_id_foreign` (`user_id`),
  KEY `platform_invoices_subscription_id_foreign` (`subscription_id`),
  CONSTRAINT `platform_invoices_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`),
  CONSTRAINT `platform_invoices_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table platform_payments
# ------------------------------------------------------------

CREATE TABLE `platform_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `platform_invoice_id` bigint unsigned NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_method_details` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `platform_payments_transaction_id_unique` (`transaction_id`),
  KEY `platform_payments_platform_invoice_id_foreign` (`platform_invoice_id`),
  CONSTRAINT `platform_payments_platform_invoice_id_foreign` FOREIGN KEY (`platform_invoice_id`) REFERENCES `platform_invoices` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table products
# ------------------------------------------------------------

CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_company_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_id` bigint unsigned DEFAULT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_tenant_company_id_foreign` (`tenant_company_id`),
  KEY `products_image_id_foreign` (`image_id`),
  CONSTRAINT `products_image_id_foreign` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_tenant_company_id_foreign` FOREIGN KEY (`tenant_company_id`) REFERENCES `tenant_companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table quotation_products
# ------------------------------------------------------------

CREATE TABLE `quotation_products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `quotation_revision_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `brand_origin_id` bigint unsigned DEFAULT NULL,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specification_id` bigint unsigned DEFAULT NULL,
  `add_spec` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requision_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `foreign_currency_buying` decimal(15,2) DEFAULT NULL,
  `bdt_buying` decimal(15,2) DEFAULT NULL,
  `weight` decimal(10,2) DEFAULT NULL,
  `air_sea_freight_rate` decimal(10,2) DEFAULT NULL,
  `air_sea_freight` decimal(15,2) DEFAULT NULL,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `tax_percentage` decimal(5,2) DEFAULT NULL,
  `tax` decimal(15,2) DEFAULT NULL,
  `att_percentage` decimal(5,2) DEFAULT NULL,
  `att` decimal(15,2) DEFAULT NULL,
  `margin` decimal(5,2) DEFAULT NULL,
  `margin_value` decimal(15,2) DEFAULT NULL,
  `discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quotation_products_quotation_revision_id_foreign` (`quotation_revision_id`),
  KEY `quotation_products_product_id_foreign` (`product_id`),
  KEY `quotation_products_brand_origin_id_foreign` (`brand_origin_id`),
  KEY `quotation_products_specification_id_foreign` (`specification_id`),
  CONSTRAINT `quotation_products_brand_origin_id_foreign` FOREIGN KEY (`brand_origin_id`) REFERENCES `brand_origins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `quotation_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `quotation_products_quotation_revision_id_foreign` FOREIGN KEY (`quotation_revision_id`) REFERENCES `quotation_revisions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quotation_products_specification_id_foreign` FOREIGN KEY (`specification_id`) REFERENCES `specifications` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table quotation_revisions
# ------------------------------------------------------------

CREATE TABLE `quotation_revisions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `quotation_id` bigint unsigned NOT NULL,
  `revision_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'R0',
  `date` date NOT NULL,
  `validity` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BDT',
  `exchange_rate` decimal(10,4) NOT NULL DEFAULT '1.0000',
  `type` enum('normal','via') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `shipping` decimal(15,2) NOT NULL DEFAULT '0.00',
  `discount_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `shipping_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `vat_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `vat_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `terms_conditions` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','sent','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `saved_as` enum('draft','quotation') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quotation_revisions_quotation_id_foreign` (`quotation_id`),
  KEY `quotation_revisions_created_by_foreign` (`created_by`),
  CONSTRAINT `quotation_revisions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `quotation_revisions_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table quotation_statuses
# ------------------------------------------------------------

CREATE TABLE `quotation_statuses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_company_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'gray',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quotation_statuses_tenant_company_id_foreign` (`tenant_company_id`),
  CONSTRAINT `quotation_statuses_tenant_company_id_foreign` FOREIGN KEY (`tenant_company_id`) REFERENCES `tenant_companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table quotations
# ------------------------------------------------------------

CREATE TABLE `quotations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_company_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `status_id` bigint unsigned NOT NULL,
  `regular_billing_locked` tinyint(1) NOT NULL DEFAULT '0',
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quotation_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `po_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `po_date` date DEFAULT NULL,
  `ship_to` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quotations_reference_no_unique` (`reference_no`),
  UNIQUE KEY `quotations_quotation_no_unique` (`quotation_no`),
  KEY `quotations_tenant_company_id_foreign` (`tenant_company_id`),
  KEY `quotations_customer_id_foreign` (`customer_id`),
  KEY `quotations_user_id_foreign` (`user_id`),
  KEY `quotations_status_id_foreign` (`status_id`),
  CONSTRAINT `quotations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `quotations_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `quotation_statuses` (`id`),
  CONSTRAINT `quotations_tenant_company_id_foreign` FOREIGN KEY (`tenant_company_id`) REFERENCES `tenant_companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quotations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table received_bills
# ------------------------------------------------------------

CREATE TABLE `received_bills` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` bigint unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `received_date` date NOT NULL,
  `details` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `received_bills_bill_id_foreign` (`bill_id`),
  CONSTRAINT `received_bills_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table role_has_permissions
# ------------------------------------------------------------

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table roles
# ------------------------------------------------------------

CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table sale_targets
# ------------------------------------------------------------

CREATE TABLE `sale_targets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_company_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `month` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_amount` decimal(15,2) NOT NULL,
  `achieved_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_targets_tenant_company_id_foreign` (`tenant_company_id`),
  KEY `sale_targets_user_id_foreign` (`user_id`),
  CONSTRAINT `sale_targets_tenant_company_id_foreign` FOREIGN KEY (`tenant_company_id`) REFERENCES `tenant_companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_targets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table sessions
# ------------------------------------------------------------

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table specifications
# ------------------------------------------------------------

CREATE TABLE `specifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `specifications_product_id_foreign` (`product_id`),
  CONSTRAINT `specifications_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table subscription_usage
# ------------------------------------------------------------

CREATE TABLE `subscription_usage` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subscription_id` bigint unsigned NOT NULL,
  `metric` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `used` int NOT NULL DEFAULT '0',
  `limit` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscription_usage_subscription_id_foreign` (`subscription_id`),
  CONSTRAINT `subscription_usage_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table subscriptions
# ------------------------------------------------------------

CREATE TABLE `subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `plan_id` bigint unsigned DEFAULT NULL,
  `plan_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'free',
  `company_limit` int NOT NULL DEFAULT '1',
  `user_limit_per_company` int NOT NULL DEFAULT '1',
  `quotation_limit_per_month` int NOT NULL DEFAULT '10',
  `module_access` json DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `custom_limits` json DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscriptions_user_id_foreign` (`user_id`),
  KEY `subscriptions_plan_id_foreign` (`plan_id`),
  KEY `subscriptions_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `subscriptions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`),
  CONSTRAINT `subscriptions_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table system_settings
# ------------------------------------------------------------

CREATE TABLE `system_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table tenants
# ------------------------------------------------------------

CREATE TABLE `tenants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenants_user_id_foreign` (`user_id`),
  CONSTRAINT `tenants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table tenant_companies
# ------------------------------------------------------------

CREATE TABLE `tenant_companies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned DEFAULT NULL,
  `owner_id` bigint unsigned NOT NULL,
  `parent_company_id` bigint unsigned DEFAULT NULL,
  `organization_type` enum('independent','holding','subsidiary') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'independent',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `bin_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_companies_owner_id_foreign` (`owner_id`),
  KEY `tenant_companies_parent_company_id_foreign` (`parent_company_id`),
  KEY `tenant_companies_organization_type_index` (`organization_type`),
  KEY `tenant_companies_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `tenant_companies_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tenant_companies_parent_company_id_foreign` FOREIGN KEY (`parent_company_id`) REFERENCES `tenant_companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tenant_companies_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table users
# ------------------------------------------------------------

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_tenant_company_id` bigint unsigned DEFAULT NULL,
  `current_scope` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'personal',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_current_tenant_company_id_index` (`current_tenant_company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
