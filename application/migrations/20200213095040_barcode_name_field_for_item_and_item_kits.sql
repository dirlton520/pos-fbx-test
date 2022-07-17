-- barcode_name_field_for_item_and_item_kits --

ALTER TABLE `phppos_items` ADD COLUMN `barcode_name` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `phppos_item_kits` ADD COLUMN `barcode_name` VARCHAR(255) NOT NULL DEFAULT '';