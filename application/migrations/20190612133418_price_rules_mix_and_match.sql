-- price_rules_mix_and_match --
SET SESSION sql_mode="NO_AUTO_CREATE_USER";

ALTER TABLE `phppos_price_rules` ADD `mix_and_match` int(1) NOT NULL DEFAULT '0';