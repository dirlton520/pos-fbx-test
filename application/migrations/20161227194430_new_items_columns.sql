-- new_items_columns --

ALTER TABLE `phppos_items` ADD `disable_loyalty` int(1) NOT NULL DEFAULT '0' AFTER `change_cost_price`,
ADD `manufacturer_id` int(11) DEFAULT NULL AFTER `supplier_id`,ADD  CONSTRAINT `phppos_items_ibfk_4` FOREIGN KEY (`manufacturer_id`) REFERENCES `phppos_manufacturers` (`id`),
ADD `is_ebt_item` int(1) NOT NULL DEFAULT '0' AFTER `is_service`,
CHANGE `description` `description` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, ADD `ecommerce_product_id` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL  AFTER `product_id`,  ADD `is_ecommerce` INT(1) DEFAULT 1 AFTER `override_default_tax`, ADD CONSTRAINT `phppos_items_ibfk_5` FOREIGN KEY (`ecommerce_product_id`) REFERENCES `phppos_ecommerce_products` (`product_id`),
ADD INDEX `description` (`description`(255)),
ADD INDEX `size` (`size`),
ADD INDEX `reorder_level` (`reorder_level`),
ADD INDEX `cost_price` (`cost_price`),
ADD INDEX `unit_price` (`unit_price`),
ADD INDEX `promo_price` (`promo_price`),
ADD aaatex_qb_item_name VARCHAR(255) DEFAULT '',
ADD `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD INDEX `last_modified` (`last_modified`);