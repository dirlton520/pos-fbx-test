-- woo_multi_location --

CREATE TABLE `phppos_ecommerce_locations` (
  `location_id` int(10) NOT NULL,
  PRIMARY KEY (`location_id`),
	CONSTRAINT `phppos_ecommerce_locations_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
