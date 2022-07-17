-- expenses_categories --
CREATE TABLE `phppos_expenses_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `last_modified` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted` int(1) NOT NULL DEFAULT 0,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted` (`deleted`),
  KEY `phppos_expenses_categories_ibfk_1` (`parent_id`),
  KEY `name` (`name`),
  CONSTRAINT `phppos_expenses_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `phppos_expenses_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci