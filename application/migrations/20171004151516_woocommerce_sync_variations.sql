-- woocommerce_sync_variations --

CREATE TABLE `phppos_ecommerce_product_variations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `variation_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `product_quantity` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  UNIQUE KEY `product_variation` (`variation_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `phppos_attributes` ADD `ecommerce_attribute_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER `id`;

ALTER TABLE `phppos_attribute_values` ADD `ecommerce_attribute_term_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER `id`;

ALTER TABLE `phppos_item_variations` ADD `ecommerce_variation_id` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `id`,
ADD CONSTRAINT `phppos_item_variations_ibfk_2` FOREIGN KEY (`ecommerce_variation_id`) REFERENCES `phppos_ecommerce_product_variations` (`variation_id`);