-- custom_suspended_sale_types_remove_quantity --
ALTER TABLE `phppos_sale_types` ADD COLUMN `remove_quantity` INT(1) DEFAULT '0';
UPDATE `phppos_sale_types` SET remove_quantity = 1 WHERE name = 'common_layaway' and system_sale_type = '1';