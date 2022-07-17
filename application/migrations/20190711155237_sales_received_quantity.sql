-- sales_received_quantity --
ALTER TABLE `phppos_sales_items` ADD `quantity_received`decimal(23,10) NOT NULL DEFAULT '0.0000000000' AFTER `quantity_purchased`;

UPDATE `phppos_sales_items` INNER JOIN phppos_sales USING (sale_id) SET quantity_received = quantity_purchased WHERE suspended = 0;


ALTER TABLE `phppos_sales_item_kits` ADD `quantity_received`decimal(23,10) NOT NULL DEFAULT '0.0000000000' AFTER `quantity_purchased`;

UPDATE `phppos_sales_item_kits` INNER JOIN phppos_sales USING (sale_id) SET quantity_received = quantity_purchased WHERE suspended = 0;

ALTER TABLE `phppos_sales` ADD `total_quantity_received` decimal(23,10) NOT NULL DEFAULT '0.0000000000';
