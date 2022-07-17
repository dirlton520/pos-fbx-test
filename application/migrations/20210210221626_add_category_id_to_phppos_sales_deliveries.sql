-- add_category_id_to_phppos_sales_deliveries --
ALTER TABLE `phppos_sales_deliveries` ADD `category_id` INT NULL AFTER `deleted`;