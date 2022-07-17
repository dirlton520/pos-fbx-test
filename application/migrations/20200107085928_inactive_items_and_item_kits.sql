-- inactive_items_and_item_kits --

ALTER TABLE `phppos_items` ADD COLUMN `item_inactive` INT(1) DEFAULT '0', ADD INDEX `item_inactive_index` (`item_inactive`);
ALTER TABLE `phppos_item_kits` ADD COLUMN `item_kit_inactive` INT(1) DEFAULT '0', ADD INDEX `item_kit_inactive_index` (`item_kit_inactive`);
