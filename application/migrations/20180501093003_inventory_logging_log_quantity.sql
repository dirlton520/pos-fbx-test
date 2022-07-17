-- inventory_logging_log_quantity --

ALTER TABLE `phppos_inventory` ADD COLUMN trans_current_quantity decimal(23,10) NULL DEFAULT NULL;