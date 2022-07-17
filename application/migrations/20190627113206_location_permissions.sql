-- location_permissions --

CREATE TABLE `phppos_permissions_locations` (
  `module_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `person_id` int(10) NOT NULL,
  `location_id` int (10) NOT NULL,
  PRIMARY KEY (`module_id`,`person_id`,`location_id`),
  CONSTRAINT `phppos_permissions_locations_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_permissions_locations_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `phppos_modules` (`module_id`),
  CONSTRAINT `phppos_permissions_locations_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `phppos_permissions_actions_locations` (
  `module_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `person_id` int(11) NOT NULL,
  `action_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `location_id` int (10) NOT NULL,
  PRIMARY KEY (`module_id`,`person_id`,`action_id`,`location_id`),
  CONSTRAINT `phppos_permissions_actions_locations_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `phppos_modules` (`module_id`),
  CONSTRAINT `phppos_permissions_actions_locations_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_permissions_actions_locations_ibfk_3` FOREIGN KEY (`action_id`) REFERENCES `phppos_modules_actions` (`action_id`),
  CONSTRAINT `phppos_permissions_actions_locations_ibfk_4` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;