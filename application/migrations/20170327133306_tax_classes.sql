-- tax_classes --

CREATE TABLE `phppos_tax_classes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `order` int(10) NOT NULL DEFAULT '0',
  `location_id` int(10) NULL DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  CONSTRAINT `phppos_tax_classes_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_tax_classes_taxes` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`order` int(10) NOT NULL DEFAULT '0',
	`tax_class_id` int(10) NOT NULL,
	`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	`percent` decimal(15,3) NOT NULL,
	`cumulative` int(1) NOT NULL DEFAULT '0',
	CONSTRAINT `phppos_tax_classes_taxes_ibfk_1` FOREIGN KEY (`tax_class_id`) REFERENCES `phppos_tax_classes` (`id`),
	UNIQUE KEY `unique_tax` (`tax_class_id`, `name`, `percent`), 
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER table phppos_items ADD `tax_class_id` int(10) NULL DEFAULT NULL,
ADD CONSTRAINT `phppos_items_ibfk_6` FOREIGN KEY (`tax_class_id`) REFERENCES `phppos_tax_classes` (`id`);

ALTER table phppos_item_kits ADD `tax_class_id` int(10) NULL DEFAULT NULL,
ADD CONSTRAINT `phppos_item_kits_ibfk_3` FOREIGN KEY (`tax_class_id`) REFERENCES `phppos_tax_classes` (`id`);

ALTER table phppos_location_items ADD `tax_class_id` int(10) NULL DEFAULT NULL,
ADD CONSTRAINT `phppos_location_items_ibfk_3` FOREIGN KEY (`tax_class_id`) REFERENCES `phppos_tax_classes` (`id`);

ALTER table phppos_location_item_kits ADD `tax_class_id` int(10) NULL DEFAULT NULL,
ADD CONSTRAINT `phppos_location_item_kits_ibfk_3` FOREIGN KEY (`tax_class_id`) REFERENCES `phppos_tax_classes` (`id`);

ALTER table phppos_locations ADD `tax_class_id` int(10) NULL DEFAULT NULL,
ADD CONSTRAINT `phppos_locations_ibfk_2` FOREIGN KEY (`tax_class_id`) REFERENCES `phppos_tax_classes` (`id`);

ALTER table phppos_customers ADD `tax_class_id` int(10) NULL DEFAULT NULL,
ADD CONSTRAINT `phppos_customers_ibfk_3` FOREIGN KEY (`tax_class_id`) REFERENCES `phppos_tax_classes` (`id`);

ALTER table phppos_suppliers ADD `tax_class_id` int(10) NULL DEFAULT NULL,
ADD CONSTRAINT `phppos_suppliers_ibfk_2` FOREIGN KEY (`tax_class_id`) REFERENCES `phppos_tax_classes` (`id`);