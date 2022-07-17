-- location_variation_prices --
ALTER TABLE phppos_location_item_variations ADD COLUMN `unit_price` decimal(23,10) DEFAULT NULL,
ADD COLUMN `cost_price` decimal(23,10) DEFAULT NULL;