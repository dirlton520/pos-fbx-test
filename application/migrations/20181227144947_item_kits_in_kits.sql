-- item_kits_in_kits --

CREATE TABLE `phppos_item_kit_item_kits` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `item_kit_id` int(11) NOT NULL,
  `item_kit_item_kit` int(11) NOT NULL,
  `quantity` decimal(23,10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_item_kit_item_kits_ibfk_1` (`item_kit_id`),
  KEY `phppos_item_kit_item_kits_ibfk_2` (`item_kit_item_kit`),
  CONSTRAINT `phppos_item_kit_item_kits_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_item_kit_item_kits_ibfk_2` FOREIGN KEY (`item_kit_item_kit`) REFERENCES `phppos_item_kits` (`item_kit_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;