-- price_rules --

-- Price Rules --

CREATE TABLE `phppos_price_rules` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name`  varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `added_on` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `active`  int(1) NOT NULL DEFAULT '1',
	`deleted` int(1) NOT NULL DEFAULT '0',
  `type`  varchar(255) COLLATE utf8_unicode_ci NOT NULL,	
  `items_to_buy`  decimal(23,10) NULL DEFAULT NULL,
  `items_to_get`  decimal(23,10) NULL DEFAULT NULL,
  `percent_off`  decimal(23,10) NULL DEFAULT NULL,
  `fixed_off`  decimal(23,10) NULL DEFAULT NULL,
  `spend_amount`  decimal(23,10) NULL DEFAULT NULL,
	`num_times_to_apply` int(10) NOT NULL,
  KEY `name` (`name`),
  KEY `start_date` (`start_date`),
  KEY `end_date` (`end_date`),
  KEY `type` (`type`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_price_rules_price_breaks` (
`id` int(10) NOT NULL AUTO_INCREMENT,
`rule_id` int(10) NOT NULL,
`item_qty_to_buy` decimal(23,10) DEFAULT NULL,
`discount_per_unit_fixed` decimal(23,10) DEFAULT NULL,
`discount_per_unit_percent` decimal(23,10) DEFAULT NULL,
KEY `phppos_price_rules_custom_ibfk_1` (`rule_id`),
CONSTRAINT `phppos_price_rules_price_breaks_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`),
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_price_rules_items` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_price_rules_items_ibfk_1` (`rule_id`),
  KEY `phppos_price_rules_items_ibfk_2` (`item_id`),
	CONSTRAINT `phppos_price_rules_items_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`),
	CONSTRAINT `phppos_price_rules_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `phppos_price_rules_item_kits` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rule_id`  int(10) NOT NULL,
  `item_kit_id`  int(10) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `phppos_price_rules_item_kits_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`),
  CONSTRAINT `phppos_price_rules_item_kits_ibfk_2` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `phppos_price_rules_categories` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rule_id`  int(10) NOT NULL,
  `category_id`  int(10) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `phppos_price_rules_categories_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`),
  CONSTRAINT `phppos_price_rules_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `phppos_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `phppos_price_rules_tags` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rule_id`  int(10) NOT NULL,
  `tag_id`  int(10) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `phppos_price_rules_tags_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`),
  CONSTRAINT `phppos_price_rules_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `phppos_tags` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
  
 INSERT INTO `phppos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `icon`, `module_id`) VALUES
 ('module_price_rules', 'module_item_price_rules_desc', 35, 'money', 'price_rules');

 INSERT INTO `phppos_permissions` (`module_id`, `person_id`) (SELECT 'price_rules', person_id FROM phppos_permissions WHERE module_id = 'items');


 INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES
 ('add_update', 'price_rules', 'module_action_add_update', 400),
 ('delete', 'price_rules', 'module_action_delete', 405),
 ('search', 'price_rules', 'module_action_search_price_rules', 415);

 INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
 SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
 from phppos_permissions
 inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
 WHERE phppos_permissions.module_id = 'price_rules' and
 action_id = 'add_update'
 order by module_id, person_id;


 INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
 SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
 from phppos_permissions
 inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
 WHERE phppos_permissions.module_id = 'price_rules' and
 action_id = 'delete'
 order by module_id, person_id;


 INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
 SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
 from phppos_permissions
 inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
 WHERE phppos_permissions.module_id = 'price_rules' and
 action_id = 'search'
 order by module_id, person_id;
