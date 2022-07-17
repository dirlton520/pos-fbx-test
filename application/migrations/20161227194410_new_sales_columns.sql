-- new_sales_columns --


-- phppos_sales
ALTER TABLE `phppos_sales` ADD `rule_id` INT(10) NULL DEFAULT NULL AFTER `sale_id`, ADD `rule_discount` DECIMAL(23,10) NULL DEFAULT NULL AFTER `rule_id`, ADD CONSTRAINT `phppos_sales_ibfk_9` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules`(`id`), 
ADD `discount_reason` text COLLATE utf8_unicode_ci NOT NULL AFTER `comment`, 
ADD `total_quantity_purchased` decimal(23,10) NOT NULL DEFAULT '0.0000000000', ADD `subtotal` decimal(23,10) NOT NULL DEFAULT '0.0000000000', ADD `tax` decimal(23,10) NOT NULL DEFAULT '0.0000000000', ADD `total` decimal(23,10) NOT NULL DEFAULT '0.0000000000', ADD `profit` decimal(23,10) NOT NULL DEFAULT '0.0000000000', 
ADD aaatex_qb_imported int(1) DEFAULT 0 AFTER `profit`, 
DROP INDEX `sales_search`, 
ADD KEY `sales_search` (`location_id`,`deleted`,`sale_time`,`suspended`,`store_account_payment`,`total_quantity_purchased`);


-- phppos_sales_items
ALTER TABLE `phppos_sales_items` ADD `rule_id` INT(10) NULL DEFAULT NULL AFTER `item_id`, ADD `rule_discount` DECIMAL(23,10) NULL DEFAULT NULL AFTER `rule_id`,ADD CONSTRAINT `phppos_sales_items_ibfk_3` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`), ADD `is_bogo` BOOLEAN NOT NULL DEFAULT FALSE AFTER `commission`, 
ADD `regular_item_unit_price_at_time_of_sale` DECIMAL(23,10) NULL DEFAULT NULL AFTER `item_unit_price`,
ADD `subtotal` decimal(23,10) NOT NULL DEFAULT '0.0000000000', ADD `tax` decimal(23,10) NOT NULL DEFAULT '0.0000000000', ADD `total` decimal(23,10) NOT NULL DEFAULT '0.0000000000', ADD `profit` decimal(23,10) NOT NULL DEFAULT '0.0000000000';


-- phppos_sales_item_kits
ALTER TABLE `phppos_sales_item_kits` ADD `subtotal` decimal(23,10) NOT NULL DEFAULT '0.0000000000', ADD `tax` decimal(23,10) NOT NULL DEFAULT '0.0000000000', ADD `total` decimal(23,10) NOT NULL DEFAULT '0.0000000000', ADD `profit` decimal(23,10) NOT NULL DEFAULT '0.0000000000', ADD `rule_id` INT(10) NULL DEFAULT NULL AFTER `item_kit_id`, ADD `rule_discount` DECIMAL(23,10) NULL DEFAULT NULL AFTER `rule_id`, ADD CONSTRAINT `phppos_sales_item_kits_ibfk_3` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules`(`id`),ADD `is_bogo` BOOLEAN NOT NULL DEFAULT FALSE AFTER `commission`, ADD `regular_item_kit_unit_price_at_time_of_sale` DECIMAL(23,10) NULL DEFAULT NULL AFTER `item_kit_unit_price`;

-- phppos_sales_payments

ALTER TABLE `phppos_sales_payments` ADD `ebt_auth_code` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT '',ADD `ebt_voucher_no` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT '',ADD INDEX(`payment_date`);