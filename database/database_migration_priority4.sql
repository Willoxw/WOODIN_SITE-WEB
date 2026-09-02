USE `woodin_db`;

ALTER TABLE `admins` ADD COLUMN `role` enum('super_admin','gestionnaire') NOT NULL DEFAULT 'gestionnaire', ADD COLUMN `is_active` tinyint(1) NOT NULL DEFAULT 1;
UPDATE `admins` SET `role` = 'super_admin' WHERE `username` = 'admin';

ALTER TABLE `messages` ADD COLUMN `is_read` tinyint(1) NOT NULL DEFAULT 0;

ALTER TABLE `admins` ADD COLUMN `last_login` timestamp NULL DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `customers` (`id` int(11) NOT NULL AUTO_INCREMENT,`full_name` varchar(255) NOT NULL,`email` varchar(191) NOT NULL,`phone` varchar(30) DEFAULT NULL,`password` varchar(255) DEFAULT NULL,`city` varchar(100) NOT NULL DEFAULT '',`google_id` varchar(191) DEFAULT NULL,`apple_id` varchar(191) DEFAULT NULL,`created_at` timestamp NOT NULL DEFAULT current_timestamp(),PRIMARY KEY (`id`),UNIQUE KEY `customer_email` (`email`),UNIQUE KEY `customer_phone` (`phone`),UNIQUE KEY `customer_google_id` (`google_id`),UNIQUE KEY `customer_apple_id` (`apple_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS `customer_login_attempts` (`id` int(11) NOT NULL AUTO_INCREMENT,`identifier` varchar(255) NOT NULL,`ip_address` varchar(45) NOT NULL,`attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),`locked_until` timestamp NULL DEFAULT NULL,PRIMARY KEY (`id`),KEY `customer_login_lookup` (`identifier`,`ip_address`,`attempted_at`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS `password_resets` (`id` int(11) NOT NULL AUTO_INCREMENT,`customer_id` int(11) NOT NULL,`token_hash` char(64) NOT NULL,`expires_at` datetime NOT NULL,`used_at` datetime NULL DEFAULT NULL,`created_at` timestamp NOT NULL DEFAULT current_timestamp(),PRIMARY KEY (`id`),UNIQUE KEY `password_reset_token` (`token_hash`),KEY `password_reset_customer` (`customer_id`),CONSTRAINT `password_reset_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE `orders` ADD COLUMN `customer_id` int(11) DEFAULT NULL, ADD INDEX `order_customer` (`customer_id`);
ALTER TABLE `orders` ADD CONSTRAINT `orders_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;
ALTER TABLE `orders` ADD COLUMN `invoice_token` varchar(32) DEFAULT NULL, ADD UNIQUE KEY `invoice_token` (`invoice_token`);
UPDATE `orders` SET `invoice_token` = REPLACE(UUID(), '-', '') WHERE `invoice_token` IS NULL;
CREATE TABLE IF NOT EXISTS `discounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `type` enum('percentage','fixed') NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `min_purchase_amount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `usage_count` int(11) NOT NULL DEFAULT 0,
  `valid_from` date NOT NULL,
  `valid_until` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY discount_code (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `product_promotions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `discount_percentage` decimal(5,2) NOT NULL,
  `starts_at` date NOT NULL,
  `ends_at` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY promotion_product (`product_id`),
  CONSTRAINT promotion_product_fk FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `discount_usage` (`id` int(11) NOT NULL AUTO_INCREMENT,`customer_id` int(11) NOT NULL,`discount_id` int(11) NOT NULL,`order_id` int(11) NOT NULL,`used_at` timestamp NOT NULL DEFAULT current_timestamp(),PRIMARY KEY (`id`),KEY discount_usage_customer (`customer_id`),KEY discount_usage_discount (`discount_id`),UNIQUE KEY discount_usage_order (`order_id`),CONSTRAINT discount_usage_customer_fk FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,CONSTRAINT discount_usage_discount_fk FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`) ON DELETE RESTRICT,CONSTRAINT discount_usage_order_fk FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE `orders` ADD COLUMN `discount_amount` decimal(10,2) NOT NULL DEFAULT 0;
ALTER TABLE `products` ADD COLUMN `category_id` int(11) DEFAULT NULL, ADD INDEX `stock` (`stock`), ADD INDEX `category_id` (`category_id`);
ALTER TABLE `products` ADD CONSTRAINT `products_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
CREATE TABLE IF NOT EXISTS `product_images` (`id` int(11) NOT NULL AUTO_INCREMENT, `product_id` int(11) NOT NULL, `image_url` varchar(255) NOT NULL, `is_main` tinyint(1) NOT NULL DEFAULT 0, PRIMARY KEY (`id`), KEY `product_images_product` (`product_id`), CONSTRAINT `product_images_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS `order_status_history` (`id` int AUTO_INCREMENT PRIMARY KEY,`order_id` int NOT NULL,`old_status` varchar(50) NULL,`new_status` varchar(50) NOT NULL,`changed_by` int NOT NULL COMMENT 'admin_id',`changed_at` timestamp DEFAULT CURRENT_TIMESTAMP,`note` text NULL,FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,FOREIGN KEY (`changed_by`) REFERENCES `admins` (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS `stock_movements` (`id` int(11) NOT NULL AUTO_INCREMENT, `product_id` int(11) NOT NULL, `quantity_change` int(11) NOT NULL, `reason` enum('vente','réapprovisionnement','correction','annulation_commande') NOT NULL, `created_at` timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (`id`), KEY `stock_movements_product` (`product_id`), CONSTRAINT `stock_movements_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE `stock_movements` MODIFY `reason` enum('vente','réapprovisionnement','correction','annulation_commande') NOT NULL;

INSERT INTO `categories` (`name`, `slug`) VALUES ('4 yards', '4-yards'), ('6 yards', '6-yards'), ('Prêt-à-porter', 'pret-a-porter') ON DUPLICATE KEY UPDATE name=VALUES(name);
UPDATE `products` SET `category_id` = (SELECT `id` FROM `categories` WHERE `slug` = CASE WHEN `name` LIKE '%4 yards%' THEN '4-yards' WHEN `name` LIKE '%6 yards%' THEN '6-yards' ELSE 'pret-a-porter' END LIMIT 1) WHERE `category_id` IS NULL;
