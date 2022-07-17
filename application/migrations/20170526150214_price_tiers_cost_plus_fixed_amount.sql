-- price_tiers_cost_plus_fixed_amount --

ALTER TABLE `phppos_price_tiers` ADD `default_cost_plus_fixed_amount` decimal(23,10) NULL DEFAULT NULL;
ALTER TABLE `phppos_item_kits_tier_prices` ADD `cost_plus_fixed_amount` decimal(23,10) NULL DEFAULT NULL;
ALTER TABLE `phppos_items_tier_prices` ADD `cost_plus_fixed_amount`decimal(23,10) NULL DEFAULT NULL;
ALTER TABLE `phppos_location_item_kits_tier_prices` ADD `cost_plus_fixed_amount` decimal(23,10) NULL DEFAULT NULL;
ALTER TABLE `phppos_location_items_tier_prices` ADD `cost_plus_fixed_amount` decimal(23,10) NULL DEFAULT NULL;