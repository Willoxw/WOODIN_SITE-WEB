USE `woodin_db`;

ALTER TABLE `customers`
  MODIFY `phone` varchar(30) NULL,
  MODIFY `password` varchar(255) NULL,
  ADD COLUMN `google_id` varchar(191) NULL,
  ADD COLUMN `apple_id` varchar(191) NULL,
  ADD UNIQUE KEY `customer_google_id` (`google_id`),
  ADD UNIQUE KEY `customer_apple_id` (`apple_id`);