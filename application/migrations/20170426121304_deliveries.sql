-- deliveries --

	
INSERT INTO `phppos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `icon`, `module_id`) VALUES
('module_deliveries', 'module_deliveries_desc', 71, 'ion-android-car', 'deliveries');

INSERT INTO `phppos_permissions` (`module_id`, `person_id`) (SELECT 'deliveries', person_id FROM phppos_permissions WHERE module_id = 'sales');

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('add_update', 'deliveries', 'deliveries_add_update', 240);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'deliveries' and
action_id = 'add_update'
order by module_id, person_id;

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('edit', 'deliveries', 'deliveries_edit', 245);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'deliveries' and
action_id = 'edit'
order by module_id, person_id;

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('delete', 'deliveries', 'deliveries_delete', 250);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'deliveries' and
action_id = 'delete'
order by module_id, person_id;

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('search', 'deliveries', 'deliveries_search', 255);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'deliveries' and
action_id = 'search'
order by module_id, person_id;

CREATE TABLE `phppos_shipping_providers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `order` int(10) NOT NULL DEFAULT '0',
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_shipping_methods` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`shipping_provider_id` int(10) NOT NULL,
	`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	`fee` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
	`fee_tax_class_id` INT(10) NULL DEFAULT NULL,
	`time_in_days` int NULL DEFAULT NULL,
	`has_tracking_number` int(1) NOT NULL DEFAULT '0',
	`is_default` int(1) NOT NULL DEFAULT '0',
	`deleted` int(1) NOT NULL DEFAULT '0',
	CONSTRAINT `phppos_shipping_methods_ibfk_1` FOREIGN KEY (`shipping_provider_id`) REFERENCES `phppos_shipping_providers` 	(`id`),
	CONSTRAINT `phppos_shipping_methods_ibfk_2` FOREIGN KEY (`fee_tax_class_id`) REFERENCES `phppos_tax_classes` 	(`id`),
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_shipping_zones` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	`fee` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `tax_class_id` int(10) NULL DEFAULT NULL,
  `order` int(10) NOT NULL DEFAULT '0',
	`deleted` int(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	CONSTRAINT `phppos_shipping_zones_ibfk_1` FOREIGN KEY (`tax_class_id`) REFERENCES `phppos_tax_classes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_zips` (
	`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	`shipping_zone_id` int(10) NULL,
  `order` int(10) NOT NULL DEFAULT '0',
	`deleted` int(1) NOT NULL DEFAULT '0',
	CONSTRAINT `phppos_zips_ibfk_1` FOREIGN KEY (`shipping_zone_id`) REFERENCES `phppos_shipping_zones` (`id`),
	UNIQUE KEY `name` (`name`), 
	PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_sales_deliveries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(10) NOT NULL,
	`shipping_address_person_id` int(10) NOT NULL,
	`shipping_method_id` int(10) NULL DEFAULT NULL,
	`shipping_zone_id` int(10) NULL DEFAULT NULL,
	`tax_class_id` int(10) NULL DEFAULT NULL,
	`status` varchar(30) NOT NULL COLLATE utf8_unicode_ci,
	`estimated_shipping_date` timestamp NULL,
	`actual_shipping_date` timestamp NULL,
	`estimated_delivery_or_pickup_date` timestamp NULL,
	`actual_delivery_or_pickup_date` timestamp NULL,
	`is_pickup` int(1) NOT NULL DEFAULT 0,
	`tracking_number` varchar(255) COLLATE utf8_unicode_ci ,
	`comment` text NOT NULL COLLATE utf8_unicode_ci DEFAULT '',
	 KEY `search_index` (`status`,`shipping_address_person_id`),		
	CONSTRAINT `phppos_sales_deliveries_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
	CONSTRAINT `phppos_sales_deliveries_ibfk_2` FOREIGN KEY (`shipping_address_person_id`) REFERENCES `phppos_people` (`person_id`),
	CONSTRAINT `phppos_sales_deliveries_ibfk_3` FOREIGN KEY (`shipping_method_id`) REFERENCES `phppos_shipping_methods` (`id`),
	CONSTRAINT `phppos_sales_deliveries_ibfk_4` FOREIGN KEY (`shipping_zone_id`) REFERENCES `phppos_shipping_zones` (`id`),
	CONSTRAINT `phppos_sales_deliveries_ibfk_5` FOREIGN KEY (`tax_class_id`) REFERENCES `phppos_tax_classes` (`id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('search', 'sales', 'module_action_search_sales', 235);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'sales' and
action_id = 'search'
order by module_id, person_id;