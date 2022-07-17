-- serial_number_cost_price --

ALTER TABLE `phppos_items_serial_numbers` ADD COLUMN `cost_price` DECIMAL(23,10) NULL DEFAULT NULL;