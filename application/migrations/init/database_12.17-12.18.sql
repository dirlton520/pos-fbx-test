
ALTER TABLE  `phppos_sales` ADD  `suspended_sale_id` INT( 10 ) NOT NULL;
ALTER TABLE  `phppos_sales` ADD  `suspended` INT( 1 ) NOT NULL  DEFAULT '0';
INSERT INTO `phppos_sales` (`sale_time`, `customer_id`, `employee_id`, `comment`, `payment_type`, `deleted`, `suspended_sale_id`, `suspended`)  (SELECT `sale_time`, `customer_id`, `employee_id`, `comment`, `payment_type`, `deleted`, `sale_id`, '1' FROM phppos_sales_suspended);
INSERT INTO `phppos_sales_items` (`sale_id`, `item_id`, `description`, `serialnumber`, `line`, `quantity_purchased`, `item_cost_price`, `item_unit_price`, `discount_percent`) (SELECT phppos_sales.sale_id,`item_id`, `description`, `serialnumber`, `line`, `quantity_purchased`, `item_cost_price`, `item_unit_price`, `discount_percent` FROM phppos_sales_suspended_items JOIN phppos_sales ON phppos_sales.suspended_sale_id = phppos_sales_suspended_items.sale_id );
INSERT INTO `phppos_sales_items_taxes` (`sale_id`, `item_id`, `line`, `name`, `percent`, `cumulative`) (SELECT phppos_sales.sale_id,`item_id`, `line`, `name`, `percent`, `cumulative` FROM phppos_sales_suspended_items_taxes JOIN phppos_sales ON phppos_sales.suspended_sale_id = phppos_sales_suspended_items_taxes.sale_id );
INSERT INTO `phppos_sales_item_kits` (`sale_id`, `item_kit_id`, `description`, `line`, `quantity_purchased`, `item_kit_cost_price`, `item_kit_unit_price`, `discount_percent`) (SELECT phppos_sales.sale_id, `item_kit_id`, `description`, `line`, `quantity_purchased`, `item_kit_cost_price`, `item_kit_unit_price`, `discount_percent` FROM phppos_sales_suspended_item_kits JOIN phppos_sales ON phppos_sales.suspended_sale_id = phppos_sales_suspended_item_kits.sale_id );
INSERT INTO `phppos_sales_item_kits_taxes` (`sale_id`, `item_kit_id`, `line`, `name`, `percent`, `cumulative`) (SELECT phppos_sales.sale_id,`item_kit_id`, `line`, `name`, `percent`, `cumulative` FROM phppos_sales_suspended_item_kits_taxes JOIN phppos_sales ON phppos_sales.suspended_sale_id = phppos_sales_suspended_item_kits_taxes.sale_id );
INSERT INTO `phppos_sales_payments` (`sale_id`, `payment_type`, `payment_amount`) (SELECT phppos_sales.sale_id, phppos_sales_suspended_payments.`payment_type`, `payment_amount` FROM phppos_sales_suspended_payments JOIN phppos_sales ON phppos_sales.suspended_sale_id = phppos_sales_suspended_payments.sale_id );
ALTER TABLE `phppos_sales` DROP  `suspended_sale_id`;
DROP TABLE phppos_sales_suspended_items_taxes;
DROP TABLE phppos_sales_suspended_item_kits_taxes;
DROP TABLE phppos_sales_suspended_payments;
DROP TABLE phppos_sales_suspended_items;
DROP TABLE phppos_sales_suspended_item_kits;
DROP TABLE phppos_sales_suspended;