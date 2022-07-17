-- add_loyalty_multiplier_to_items_and_item_kits --
ALTER TABLE phppos_items ADD COLUMN loyalty_multiplier DECIMAL(23,10) NULL DEFAULT NULL;

ALTER TABLE phppos_item_kits ADD COLUMN loyalty_multiplier DECIMAL(23,10) NULL DEFAULT NULL;