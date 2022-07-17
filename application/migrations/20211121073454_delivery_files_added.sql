-- delivery_files_added --

CREATE TABLE `phppos_delivery_files` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `file_id` int(11) NOT NULL,
  `delivery_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`),
  KEY `delivery_id` (`delivery_id`),
  CONSTRAINT `phppos_delivery_files_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `phppos_app_files` (`file_id`),
  CONSTRAINT `phppos_delivery_files_ibfk_2` FOREIGN KEY (`delivery_id`) REFERENCES `phppos_sales_deliveries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
