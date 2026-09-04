USE `woodin_db`;

-- Idempotent fixtures for the complete local Playwright QA campaign.
INSERT INTO `categories` (`name`, `slug`) VALUES
  ('4 yards', '4-yards'),
  ('6 yards', '6-yards'),
  ('Prêt-à-porter', 'pret-a-porter'),
  ('Accessoires', 'accessoires')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT INTO `products` (`name`, `description`, `price`, `stock`, `image_url`, `category_id`)
SELECT seed.`name`, seed.`description`, seed.`price`, seed.`stock`, seed.`image_url`, categories.`id`
FROM (
  SELECT 'Pagne QA Ébène 4 yards' AS `name`, 'Fixture QA tissu wax 100% coton.' AS `description`, 18000.00 AS `price`, 40 AS `stock`, 'assets/images/produits/pagne_ebene_qa.jpg' AS `image_url`, '4-yards' AS `slug`
  UNION ALL SELECT 'Pagne QA Indigo 6 yards', 'Fixture QA collection premium.', 42000.00, 25, 'assets/images/produits/pagne_indigo_qa.jpg', '6-yards'
  UNION ALL SELECT 'Chemise QA Kente', 'Fixture QA prêt-à-porter.', 32000.00, 12, 'assets/images/produits/chemise_kente_qa.jpg', 'pret-a-porter'
  UNION ALL SELECT 'Sac QA Tissé', 'Fixture QA accessoire textile.', 14500.00, 8, 'assets/images/produits/sac_tisse_qa.jpg', 'accessoires'
  UNION ALL SELECT 'Pagne QA Rupture', 'Fixture QA produit sans stock.', 22000.00, 0, 'assets/images/produits/pagne_rupture_qa.jpg', '4-yards'
) AS seed
INNER JOIN `categories` ON `categories`.`slug` = seed.`slug`
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `products`.`name` = seed.`name`);

INSERT INTO `customers` (`full_name`, `email`, `phone`, `password`, `city`)
SELECT 'Client QA Principal', 'qa.customer@woodin.test', '699123450', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCqDeXr9XjQXQ0f9QwO2', 'Yaoundé'
WHERE NOT EXISTS (SELECT 1 FROM `customers` WHERE `email` = 'qa.customer@woodin.test');

INSERT INTO `customers` (`full_name`, `email`, `phone`, `password`, `city`)
SELECT 'Client QA Secondaire', 'qa.customer2@woodin.test', '699123451', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCqDeXr9XjQXQ0f9QwO2', 'Douala'
WHERE NOT EXISTS (SELECT 1 FROM `customers` WHERE `email` = 'qa.customer2@woodin.test');

INSERT INTO `discounts` (`code`, `type`, `value`, `min_purchase_amount`, `usage_limit`, `valid_from`, `valid_until`, `is_active`)
VALUES
  ('QA10', 'percentage', 10.00, 10000.00, 100, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 365 DAY), 1),
  ('QA5000', 'fixed', 5000.00, 20000.00, 100, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 365 DAY), 1)
ON DUPLICATE KEY UPDATE `is_active` = 1, `valid_until` = DATE_ADD(CURDATE(), INTERVAL 365 DAY);

INSERT INTO `product_promotions` (`product_id`, `discount_percentage`, `starts_at`, `ends_at`)
SELECT `products`.`id`, 15.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 90 DAY)
FROM `products`
WHERE `products`.`name` = 'Pagne QA Ébène 4 yards'
  AND NOT EXISTS (
    SELECT 1 FROM `product_promotions` AS promotions
    WHERE promotions.`product_id` = `products`.`id` AND promotions.`ends_at` >= CURDATE()
  );

INSERT INTO `stock_movements` (`product_id`, `quantity_change`, `reason`)
SELECT `products`.`id`, `products`.`stock`, 'réapprovisionnement'
FROM `products`
WHERE `products`.`name` IN ('Pagne QA Ébène 4 yards', 'Pagne QA Indigo 6 yards', 'Chemise QA Kente', 'Sac QA Tissé')
  AND NOT EXISTS (
    SELECT 1 FROM `stock_movements` AS movements
    WHERE movements.`product_id` = `products`.`id` AND movements.`reason` = 'réapprovisionnement'
  );
