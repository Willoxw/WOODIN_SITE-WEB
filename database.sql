CREATE DATABASE IF NOT EXISTS `woodin_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `woodin_db`;

CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image_url` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS `admins` (`id` int(11) NOT NULL AUTO_INCREMENT,`username` varchar(50) NOT NULL,`password` varchar(255) NOT NULL,PRIMARY KEY (`id`),UNIQUE KEY `username` (`username`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS `orders` (`id` int(11) NOT NULL AUTO_INCREMENT,`customer_name` varchar(255) NOT NULL,`customer_phone` varchar(255) NOT NULL,`total_amount` decimal(10,2) NOT NULL,`status` varchar(50) NOT NULL DEFAULT 'En attente',`created_at` timestamp NOT NULL DEFAULT current_timestamp(),PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS `order_items` (`id` int(11) NOT NULL AUTO_INCREMENT,`order_id` int(11) NOT NULL,`product_id` int(11) NOT NULL,`quantity` int(11) NOT NULL,`price` decimal(10,2) NOT NULL,PRIMARY KEY (`id`),KEY `order_id` (`order_id`),KEY `product_id` (`product_id`),CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `products` (`name`,`description`,`price`,`stock`,`image_url`) SELECT * FROM (SELECT 'Pagne Collection Succès 6 yards','Tissu wax 100% coton, motif exclusif Woodin.',39000.00,20,'assets/images/pagne_succes.jpg' UNION ALL SELECT 'Pagne Collection MaxiOr 6 yards','Collection vibrante et moderne, idéale pour toutes occasions.',29000.00,15,'assets/images/pagne_maxior.jpg' UNION ALL SELECT 'Pagne Collection Royal 6 yards','Élégance et prestige avec des motifs royaux.',29000.00,10,'assets/images/pagne_royal.jpg' UNION ALL SELECT 'Pagne du Ghana 4 yards','Motifs traditionnels du Ghana, parfait pour des tenues légères.',15000.00,30,'assets/images/pagne_ghana.jpg' UNION ALL SELECT 'Haut croisé en pagne','Prêt-à-porter chic et moderne, confectionné avec nos meilleurs tissus.',28000.00,25,'assets/images/haut_croise.jpg') AS seed WHERE NOT EXISTS (SELECT 1 FROM products LIMIT 1);
INSERT INTO `admins` (`username`,`password`) VALUES ('admin','$2y$10$EwTsA5aU0eSk.3xbQ3pSw.MyXs/3Z3xRNdafKEVhKpC.QGirhiEDa') ON DUPLICATE KEY UPDATE password=VALUES(password);
