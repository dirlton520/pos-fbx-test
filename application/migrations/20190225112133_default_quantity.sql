-- default_quantity --

ALTER TABLE `phppos_items` ADD `default_quantity` DECIMAL (23,10) NULL DEFAULT NULL;
ALTER TABLE `phppos_item_kits` ADD `default_quantity` DECIMAL (23,10) NULL DEFAULT NULL;