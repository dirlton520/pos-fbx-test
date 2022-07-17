-- ecommerce_performance_improvements --


CREATE TABLE `phppos_ecommerce_categories` (
  `ecommerce_category_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `category_id` int(11),
  CONSTRAINT `phppos_ecommerce_categories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `phppos_categories` (`id`),
  PRIMARY KEY (`ecommerce_category_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `phppos_ecommerce_tags` (
  `ecommerce_tag_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `tag_id` int(11),
  CONSTRAINT `phppos_ecommerce_tags_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES `phppos_tags` (`id`),
  PRIMARY KEY (`ecommerce_tag_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;