-- item_level_tiers_for_sales --

ALTER TABLE `phppos_sales_items` ADD `tier_id` int(10) NULL DEFAULT NULL;
ALTER TABLE `phppos_sales_item_kits` ADD `tier_id` int(10) NULL DEFAULT NULL;