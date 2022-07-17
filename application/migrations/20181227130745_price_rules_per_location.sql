-- price_rules_per_location --
CREATE TABLE `phppos_price_rules_locations` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rule_id`  int(10) NOT NULL,
  `location_id`  int(10) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `phppos_price_rules_locations_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`),
  CONSTRAINT `phppos_price_rules_locations_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;