-- item_attibutes --


CREATE TABLE `phppos_attributes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name`  varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `name` (`name`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_attribute_values` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `attribute_id` int(10) NOT NULL,
  `name`  varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `name_attribute_id` (`name`,`attribute_id`),
  CONSTRAINT `phppos_attribute_values_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `phppos_attributes` (`id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_item_attributes` (
  `attribute_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  CONSTRAINT `phppos_item_attributes_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_item_attributes_ibfk_2` FOREIGN KEY (`attribute_id`) REFERENCES `phppos_attributes` (`id`),
  PRIMARY KEY (`attribute_id`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_item_attribute_values` (
  `item_id` int(10) NOT NULL,
  `attribute_value_id` int(10) NOT NULL,
  CONSTRAINT `phppos_item_attribute_values_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_item_attribute_values_ibfk_2` FOREIGN KEY (`attribute_value_id`) REFERENCES `phppos_attribute_values` (`id`),
  PRIMARY KEY (`attribute_value_id`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_item_variations` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `item_id` int(10) NOT NULL,
  `reorder_level` decimal(23,10) DEFAULT NULL,
  `replenish_level` decimal(23,10) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `item_number` varchar(255) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `unit_price` decimal(23,10) DEFAULT NULL,
  `cost_price` decimal(23,10) DEFAULT NULL,
  `promo_price` decimal(23,10) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `phppos_item_variations_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  UNIQUE KEY `item_number` (`item_number`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_item_variation_attribute_values` (
 `attribute_value_id` int(10) NOT NULL,
 `item_variation_id` int(10) NOT NULL,
 CONSTRAINT `phppos_item_variation_attribute_values_ibfk_1` FOREIGN KEY (`attribute_value_id`) REFERENCES `phppos_attribute_values` (`id`) ON DELETE CASCADE,
 CONSTRAINT `phppos_item_variation_attribute_values_ibfk_2` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`) ON DELETE CASCADE,
 PRIMARY KEY (`attribute_value_id`,`item_variation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_location_item_variations` (
  `item_variation_id` int(10) NOT NULL,
  `location_id` int(10) NOT NULL,
  `quantity` int(1) NULL DEFAULT NULL,
  `reorder_level` decimal(23,10) DEFAULT NULL,
  `replenish_level` decimal(23,10) DEFAULT NULL,
  CONSTRAINT `phppos_item_attribute_location_values_ibfk_1` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`),
  CONSTRAINT `phppos_item_attribute_location_values_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  PRIMARY KEY (`item_variation_id`,`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `phppos_sales_items`
	ADD `item_variation_id` int(10) NULL DEFAULT NULL AFTER `item_id`,
	ADD  CONSTRAINT `phppos_sales_items_ibfk_4` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`);

ALTER TABLE `phppos_receivings_items`
	ADD `item_variation_id` int(10) NULL DEFAULT NULL AFTER `item_id`,
	ADD  CONSTRAINT `phppos_receivings_items_ibfk_3` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`);
	
ALTER TABLE `phppos_inventory`
	ADD `item_variation_id` int(10) NULL DEFAULT NULL AFTER `trans_items`,
	ADD  CONSTRAINT `phppos_inventory_ibfk_4` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`);

ALTER TABLE `phppos_inventory_counts_items`
	ADD `item_variation_id` int(10) NULL DEFAULT NULL AFTER `item_id`,
	ADD  CONSTRAINT `inventory_counts_items_ibfk_3` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`);
				
SET unique_checks=0; SET foreign_key_checks=0;

ALTER TABLE `phppos_item_kit_items` 
DROP FOREIGN KEY `phppos_item_kit_items_ibfk_1`,
DROP FOREIGN KEY `phppos_item_kit_items_ibfk_2`,
DROP INDEX `phppos_item_kit_items_ibfk_2`;

ALTER TABLE `phppos_item_kit_items` DROP PRIMARY KEY,
ADD COLUMN `id` int(10) NOT NULL AUTO_INCREMENT FIRST,
ADD PRIMARY KEY (`id`),
ADD CONSTRAINT `phppos_item_kit_items_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`) ON DELETE CASCADE,
ADD CONSTRAINT `phppos_item_kit_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`) ON DELETE CASCADE;

SET unique_checks=1; SET foreign_key_checks=1;

ALTER TABLE `phppos_item_kit_items`
	ADD `item_variation_id` int(10) NULL DEFAULT NULL AFTER `item_id`,
	ADD  CONSTRAINT `phppos_item_kit_items_ibfk_3` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`);
	
ALTER TABLE `phppos_item_images`
	ADD `item_variation_id` int(10) NULL DEFAULT NULL AFTER `item_id`,
	ADD  CONSTRAINT `phppos_item_images_ibfk_3` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`);