-- link_returns_to_sale --
ALTER TABLE `phppos_sales` ADD COLUMN return_sale_id INT(10) NULL DEFAULT NULL,
ADD CONSTRAINT `phppos_sales_ibfk_11` FOREIGN KEY (`return_sale_id`) REFERENCES `phppos_sales` (`sale_id`);