-- update_sales_deliveries_delivery_type --
UPDATE `phppos_sales_deliveries` SET `delivery_type` = 'with_sales' WHERE `sale_id` IS NOT NULL;
UPDATE `phppos_sales_deliveries` SET `delivery_type` = 'without_sales' WHERE `sale_id` IS NULL;