-- allow_sales_id_nullable_in_sales_deliveries_table --
ALTER TABLE `phppos_sales_deliveries` CHANGE `sale_id` `sale_id` INT(10) NULL;