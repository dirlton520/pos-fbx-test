-- pricing_history --
CREATE TABLE `phppos_items_pricing_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `on_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `employee_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_variation_id` int(11) NULL DEFAULT NULL,
  `location_id` int(11) NULL DEFAULT NULL,
  `unit_price` decimal(23,10) DEFAULT NULL,
  `cost_price` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `phppos_items_pricing_history_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_items_pricing_history_ibfk_2` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`),
  CONSTRAINT `phppos_items_pricing_history_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_items_pricing_history_ibfk_4` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `phppos_item_kits_pricing_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `on_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `employee_id` int(11) NOT NULL,
  `item_kit_id` int(11) NOT NULL,
  `location_id` int(11) NULL DEFAULT NULL,
  `unit_price` decimal(23,10) DEFAULT NULL,
  `cost_price` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `phppos_item_kits_pricing_history_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`),
  CONSTRAINT `phppos_item_kits_pricing_history_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_item_kits_pricing_history_ibfk_3` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;