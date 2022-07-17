-- add_index_for_ecommerce_order_id --

ALTER TABLE `phppos_sales` ADD INDEX (`ecommerce_order_id`);