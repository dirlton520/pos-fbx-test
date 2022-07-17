-- prices_rules_manufacturer --

CREATE TABLE `phppos_price_rules_manufacturers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) NOT NULL,
  `manufacturer_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_price_rules_manufacturers_ibfk_1` (`rule_id`),
  KEY `phppos_price_rules_manufacturers_ibfk_2` (`manufacturer_id`),
	CONSTRAINT `phppos_price_rules_manufacturers_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`),
	CONSTRAINT `phppos_price_rules_manufacturers_ibfk_2` FOREIGN KEY (`manufacturer_id`) REFERENCES `phppos_manufacturers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;