-- remove_is_bogo_from_database --

ALTER TABLE `phppos_sales_items` DROP `is_bogo`;
ALTER TABLE `phppos_sales_item_kits` DROP `is_bogo`;