-- secondary_categories --

CREATE TABLE `phppos_items_secondary_categories` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `item_id` int(10) NOT NULL,
  `category_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_category` (`item_id`,`category_id`),
  KEY `phppos_items_secondary_categories_ibfk_1` (`item_id`),
  KEY `phppos_items_secondary_categories_ibfk_2` (`category_id`),
  CONSTRAINT `phppos_items_secondary_categories_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_items_secondary_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `phppos_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `phppos_item_kits_secondary_categories` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `item_kit_id` int(10) NOT NULL,
  `category_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_kit_category` (`item_kit_id`,`category_id`),
  KEY `phppos_item_kits_secondary_categories_ibfk_1` (`item_kit_id`),
  KEY `phppos_item_kits_secondary_categories_ibfk_2` (`category_id`),
  CONSTRAINT `phppos_item_kits_secondary_categories_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`),
  CONSTRAINT `phppos_item_kits_secondary_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `phppos_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;