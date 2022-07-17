-- add_receipt_line_sort_order_field --
ALTER TABLE `phppos_sales_items` ADD `receipt_line_sort_order` INT(11) NULL DEFAULT NULL AFTER `loyalty_multiplier`;
ALTER TABLE `phppos_sales_item_kits` ADD `receipt_line_sort_order` INT(11) NULL DEFAULT NULL AFTER `loyalty_multiplier`;
ALTER TABLE `phppos_receivings_items` ADD `receipt_line_sort_order` INT(11) NULL DEFAULT NULL AFTER `items_quantity_units_id`;
