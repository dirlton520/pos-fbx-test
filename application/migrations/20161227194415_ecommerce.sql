-- ecommerce --


CREATE TABLE `phppos_ecommerce_products` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `product_quantity` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  UNIQUE KEY `product_id` (`product_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO `phppos_app_config` (`key`, `value`) VALUES ('ecommerce_cron_sync_operations', 'a:6:{i:0;s:24:"sync_phppos_item_changes";i:1;s:22:"sync_inventory_changes";i:2;s:34:"import_ecommerce_items_into_phppos";i:3;s:31:"export_phppos_tags_to_ecommerce";i:4;s:37:"export_phppos_categories_to_ecommerce";i:5;s:32:"export_phppos_items_to_ecommerce";}');