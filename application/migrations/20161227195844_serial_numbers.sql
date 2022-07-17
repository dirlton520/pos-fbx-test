-- serial_numbers --

CREATE TABLE `phppos_items_serial_numbers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `item_id`  int(10) NOT NULL,
  `serial_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unit_price` DECIMAL(23,10) NULL DEFAULT NULL,
  UNIQUE KEY `serial_number` (`serial_number`),
  PRIMARY KEY (`id`),
  CONSTRAINT `phppos_items_serial_numbers_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
