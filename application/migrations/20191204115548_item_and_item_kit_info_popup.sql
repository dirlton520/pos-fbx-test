-- item_and_item_kit_info_popup --

ALTER TABLE `phppos_items` ADD COLUMN `info_popup` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `phppos_item_kits` ADD COLUMN `info_popup` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

