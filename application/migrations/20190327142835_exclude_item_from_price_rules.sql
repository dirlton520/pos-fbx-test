-- exclude_item_from_price_rules --
ALTER table phppos_items ADD COLUMN `disable_from_price_rules` INT(1) DEFAULT '0';
ALTER table phppos_item_kits ADD COLUMN `disable_from_price_rules` INT(1) DEFAULT '0';