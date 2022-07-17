-- employee_permission_templates --

CREATE TABLE `phppos_permissions_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `deleted` INT(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `deleted` (`deleted`),
  KEY `name` (`name`),
  KEY `name_deleted` (`name`,`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `phppos_permissions_template` (
	`template_id` int(11) NOT NULL,
  `module_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`template_id`,`module_id`),
  CONSTRAINT `phppos_permissions_template_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `phppos_modules` (`module_id`),
  CONSTRAINT `phppos_permissions_template_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `phppos_permissions_templates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_permissions_template_actions` (
	`template_id` int(11) NOT NULL,
  `module_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `action_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`template_id`,`module_id`,`action_id`),
  KEY `phppos_permissions_template_actions_ibfk_2` (`action_id`),
  KEY `phppos_permissions_template_actions_ibfk_3` (`template_id`),
  CONSTRAINT `phppos_permissions_template_actions_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `phppos_modules` (`module_id`),
  CONSTRAINT `phppos_permissions_template_actions_ibfk_2` FOREIGN KEY (`action_id`) REFERENCES `phppos_modules_actions` (`action_id`),
  CONSTRAINT `phppos_permissions_template_actions_ibfk_3` FOREIGN KEY (`template_id`) REFERENCES `phppos_permissions_templates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_permissions_template_actions_locations` (
	`template_id` int(11) NOT NULL,
  `module_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `action_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `location_id` int(10) NOT NULL,
  PRIMARY KEY (`template_id`,`module_id`,`action_id`,`location_id`),
  KEY `phppos_permissions_template_actions_locations_ibfk_2` (`action_id`),
  KEY `phppos_permissions_template_actions_locations_ibfk_3` (`location_id`),
  KEY `phppos_permissions_template_actions_locations_ibfk_4` (`template_id`),
  CONSTRAINT `phppos_permissions_template_actions_locations_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `phppos_modules` (`module_id`),
  CONSTRAINT `phppos_permissions_template_actions_locations_ibfk_2` FOREIGN KEY (`action_id`) REFERENCES `phppos_modules_actions` (`action_id`),
  CONSTRAINT `phppos_permissions_template_actions_locations_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_permissions_template_actions_locations_ibfk_4` FOREIGN KEY (`template_id`) REFERENCES `phppos_permissions_templates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `phppos_permissions_template_locations` (
	`template_id` int(11) NOT NULL,
  `module_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `location_id` int(10) NOT NULL,
  PRIMARY KEY (`template_id`,`module_id`,`location_id`),
  KEY `phppos_permissions_template_locations_ibfk_2` (`location_id`),
  KEY `phppos_permissions_template_locations_ibfk_3` (`template_id`),
  CONSTRAINT `phppos_permissions_template_locations_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `phppos_modules` (`module_id`),
  CONSTRAINT `phppos_permissions_template_locations_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_permissions_template_locations_ibfk_3` FOREIGN KEY (`template_id`) REFERENCES `phppos_permissions_templates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;