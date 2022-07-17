-- new_price_tier_options --

ALTER TABLE `phppos_price_tiers` ADD `default_percent_off` decimal(15,3) NULL DEFAULT NULL AFTER `name`;

ALTER TABLE `phppos_price_tiers` ADD `default_cost_plus_percent` decimal(15,3) NULL DEFAULT NULL AFTER `default_percent_off`;
ALTER TABLE `phppos_item_kits_tier_prices` ADD `cost_plus_percent`decimal(15,3) NULL DEFAULT NULL AFTER `percent_off`;
ALTER TABLE `phppos_items_tier_prices` ADD `cost_plus_percent`decimal(15,3) NULL DEFAULT NULL AFTER `percent_off`;
ALTER TABLE `phppos_location_item_kits_tier_prices` ADD `cost_plus_percent`decimal(15,3) NULL DEFAULT NULL AFTER `percent_off`;
ALTER TABLE `phppos_location_items_tier_prices` ADD `cost_plus_percent`decimal(15,3) NULL DEFAULT NULL AFTER `percent_off`;