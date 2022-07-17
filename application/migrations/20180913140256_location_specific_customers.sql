-- location_specific_customers --


ALTER TABLE  `phppos_customers` ADD  `location_id` INT( 11 ) NULL DEFAULT NULL,
ADD CONSTRAINT `phppos_customers_ibfk_4` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`);