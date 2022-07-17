-- modifers --

CREATE TABLE `phppos_modifiers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
	`sort_order` INT(10) NOT NULL DEFAULT '0',
	`name` VARCHAR(255) NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `name` (`name`),
  PRIMARY KEY (`id`),
  KEY `sort_index` (`deleted`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `phppos_modifier_items` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
	`sort_order` INT(10) NOT NULL DEFAULT '0',
	`modifier_id` INT(10) NOT NULL,
	`name` VARCHAR(255) NOT NULL,
  `cost_price` decimal(23,10) NOT NULL DEFAULT 0,
  `unit_price` decimal(23,10) NOT NULL DEFAULT 0,
  `deleted` int(1) NOT NULL DEFAULT '0',
	CONSTRAINT `phppos_modifier_items_ibfk_1` FOREIGN KEY (`modifier_id`) REFERENCES `phppos_modifiers` (`id`),
  PRIMARY KEY (`id`),
  KEY `sort_index` (`deleted`, `modifier_id`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `phppos_items_modifiers` (
	`item_id` INT(10) NOT NULL,
	`modifier_id` INT(10) NOT NULL,
	CONSTRAINT `phppos_items_modifiers_ibfk_1` FOREIGN KEY (`modifier_id`) REFERENCES `phppos_modifiers` (`id`),
	CONSTRAINT `phppos_items_modifiers_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  PRIMARY KEY (`item_id`,`modifier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_item_kits_modifiers` (
	`item_kit_id` INT(10) NOT NULL,
	`modifier_id` INT(10) NOT NULL,
	CONSTRAINT `phppos_item_kits_modifiers_ibfk_1` FOREIGN KEY (`modifier_id`) REFERENCES `phppos_modifiers` (`id`),
	CONSTRAINT `phppos_item_kits_modifiers_ibfk_2` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`),
  PRIMARY KEY (`item_kit_id`,`modifier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_sales_items_modifier_items` (
	`item_id` INT(10) NOT NULL,
	`sale_id` INT(10) NOT NULL,
	`line` INT(10) NOT NULL,
	`modifier_item_id` INT(10) NOT NULL,
  `cost_price` decimal(23,10) NOT NULL DEFAULT 0,
  `unit_price` decimal(23,10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`item_id`,`sale_id`,`line`,`modifier_item_id`),
	CONSTRAINT `phppos_sales_items_modifier_items_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
	CONSTRAINT `phppos_sales_items_modifier_items_ibfk_2` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
	CONSTRAINT `phppos_sales_items_modifier_items_ibfk_3` FOREIGN KEY (`modifier_item_id`) REFERENCES `phppos_modifier_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `phppos_sales_item_kits_modifier_items` (
	`item_kit_id` INT(10) NOT NULL,
	`sale_id` INT(10) NOT NULL,
	`line` INT(10) NOT NULL,
	`modifier_item_id` INT(10) NOT NULL,
  `cost_price` decimal(23,10) NOT NULL DEFAULT 0,
  `unit_price` decimal(23,10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`item_kit_id`,`sale_id`,`line`,`modifier_item_id`),
	CONSTRAINT `phppos_sales_item_kits_modifier_items_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`),
	CONSTRAINT `phppos_sales_item_kits_modifier_items_ibfk_2` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
	CONSTRAINT `phppos_sales_item_kits_modifier_items_ibfk_3` FOREIGN KEY (`modifier_item_id`) REFERENCES `phppos_modifier_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
