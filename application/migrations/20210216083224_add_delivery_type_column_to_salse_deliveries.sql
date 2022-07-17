-- add_delivery_type_column_to_salse_deliveries --
ALTER TABLE `phppos_sales_deliveries` ADD `delivery_type` VARCHAR(15) NULL AFTER `location_id`;