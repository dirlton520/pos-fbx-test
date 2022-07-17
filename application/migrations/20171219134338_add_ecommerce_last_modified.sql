-- add_ecommerce_last_modified --


ALTER TABLE `phppos_items` ADD `ecommerce_last_modified` timestamp NULL DEFAULT NULL AFTER `last_modified`;
ALTER TABLE `phppos_item_variations` ADD `ecommerce_last_modified` timestamp NULL DEFAULT NULL AFTER `last_modified`;