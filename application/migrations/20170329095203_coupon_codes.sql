-- coupon_codes --


ALTER TABLE `phppos_price_rules` ADD `coupon_code` VARCHAR(255) NULL AFTER `num_times_to_apply`,
ADD `description` text COLLATE utf8_unicode_ci DEFAULT '' AFTER `coupon_code`,
ADD `show_on_receipt` int(1) NOT NULL DEFAULT '0' AFTER `description`;

CREATE TABLE `phppos_sales_coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) NOT NULL,
  `rule_id` int(11) NOT NULL,
	CONSTRAINT `phppos_sales_coupons_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
	CONSTRAINT `phppos_sales_coupons_ibfk_2` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;