-- delivery_items --
CREATE TABLE `phppos_delivery_items` (
  `delivery_items_id` int(11) NOT NULL AUTO_INCREMENT,
  `delivery_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `item_variation_id` int(11) DEFAULT NULL,
  `quantity` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`delivery_items_id`),
  KEY `delivery_id` (`delivery_id`),
  KEY `item_id` (`item_id`),
  KEY `item_variation_id` (`item_variation_id`),
  CONSTRAINT `phppos_delivery_items_ibfk_1` FOREIGN KEY (`delivery_id`) REFERENCES `phppos_sales_deliveries` (`id`),
  CONSTRAINT `phppos_delivery_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_delivery_items_ibfk_3` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
