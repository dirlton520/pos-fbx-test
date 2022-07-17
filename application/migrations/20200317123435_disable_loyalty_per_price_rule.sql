-- disable_loyalty_per_price_rule --
ALTER TABLE `phppos_price_rules` ADD `disable_loyalty_for_rule` int(1) NOT NULL DEFAULT '0';