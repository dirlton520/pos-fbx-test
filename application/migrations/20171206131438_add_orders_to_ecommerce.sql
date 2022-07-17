-- add_orders_to_ecommerce --

ALTER TABLE `phppos_sales` ADD `is_ecommerce` INT(1) NOT NULL DEFAULT '0' AFTER `suspended`,
ADD `ecommerce_order_id` INT(10) NULL DEFAULT NULL AFTER `is_ecommerce`,
ADD `ecommerce_status` VARCHAR(255) NOT NULL DEFAULT '' AFTER `ecommerce_order_id`;