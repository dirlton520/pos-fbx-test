-- price_rules_exclude_tiers --

CREATE TABLE `phppos_price_rules_tiers_exclude` (
  `price_rule_id` int(10) NOT NULL,
  `tier_id` int(10) NOT NULL,
  CONSTRAINT `phppos_price_rules_tiers_ibfk_1` FOREIGN KEY (`price_rule_id`) REFERENCES `phppos_price_rules` (`id`),
  CONSTRAINT `phppos_price_rules_tiers_ibfk_2` FOREIGN KEY (`tier_id`) REFERENCES `phppos_price_tiers` (`id`),
  PRIMARY KEY (`price_rule_id`,`tier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
