-- allow_ecommerce_order_id_to_be_larger_int --

ALTER TABLE `phppos_sales` CHANGE `ecommerce_order_id` `ecommerce_order_id` BIGINT(20) NULL DEFAULT NULL;