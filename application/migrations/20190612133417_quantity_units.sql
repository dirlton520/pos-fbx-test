-- quantity_units --

CREATE TABLE `phppos_items_quantity_units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `unit_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	`unit_quantity` DECIMAL(23,10) NOT NULL,
  `unit_price` decimal(23,10) DEFAULT NULL,
  `cost_price` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `phppos_items_quantity_units_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `phppos_sales_items` ADD COLUMN `unit_quantity` DECIMAL(23,10) NULL, 
ADD COLUMN items_quantity_units_id int(11), 
ADD CONSTRAINT `phppos_sales_items_ibfk_6` FOREIGN KEY (`items_quantity_units_id`) REFERENCES `phppos_items_quantity_units` (`id`);

ALTER TABLE `phppos_receivings_items` ADD COLUMN `unit_quantity` DECIMAL(23,10) NULL, 
ADD COLUMN items_quantity_units_id int(11), 
ADD CONSTRAINT `phppos_receivings_items_ibfk_4` FOREIGN KEY (`items_quantity_units_id`) REFERENCES `phppos_items_quantity_units` (`id`);