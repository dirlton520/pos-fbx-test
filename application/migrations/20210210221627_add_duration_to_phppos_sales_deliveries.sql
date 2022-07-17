-- add_duration_to_phppos_sales_deliveries --
ALTER TABLE `phppos_sales_deliveries` ADD `duration` INT NULL AFTER `deleted`;