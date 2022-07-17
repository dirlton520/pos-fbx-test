-- delivery_item_kits --
CREATE TABLE `phppos_delivery_item_kits` (
  `delivery_item_kits_id` int(11) NOT NULL AUTO_INCREMENT,
  `delivery_id` int(11) DEFAULT NULL,
  `item_kit_id` int(11) DEFAULT NULL,
  `quantity` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`delivery_item_kits_id`),
  KEY `delivery_id` (`delivery_id`),
  KEY `item_kit_id` (`item_kit_id`),
  CONSTRAINT `phppos_delivery_item_kits_ibfk_1` FOREIGN KEY (`delivery_id`) REFERENCES `phppos_sales_deliveries` (`id`),
  CONSTRAINT `phppos_delivery_item_kits_ibfk_2` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;