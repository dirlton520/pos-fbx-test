-- damaged_items_outside_of_returns --

CREATE TABLE `phppos_damaged_items_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `damaged_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`damaged_qty` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `item_id` int(10) NOT NULL,
  `item_variation_id` int(10) NULL DEFAULT NULL,
  `sale_id` int(10) NULL,
  `location_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
	CONSTRAINT `phppos_damaged_items_log_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
	CONSTRAINT `phppos_damaged_items_log_ibfk_2` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`),
	CONSTRAINT `phppos_damaged_items_log_ibfk_3` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
	CONSTRAINT `phppos_damaged_items_log_ibfk_4` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO phppos_damaged_items_log (`damaged_date`,`damaged_qty`,`item_id`,`item_variation_id`,`sale_id`,`location_id`)
SELECT phppos_sales.sale_time, phppos_sales_items.damaged_qty,phppos_sales_items.item_id,phppos_sales_items.item_variation_id,phppos_sales.sale_id,phppos_sales.location_id
FROM phppos_sales INNER JOIN phppos_sales_items USING (sale_id) WHERE damaged_qty !=0;