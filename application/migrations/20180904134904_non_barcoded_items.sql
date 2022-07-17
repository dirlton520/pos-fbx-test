-- non_barcoded_items --

ALTER TABLE phppos_items ADD COLUMN is_barcoded INT(1) NOT NULL DEFAULT '1';
ALTER TABLE phppos_item_kits ADD COLUMN is_barcoded INT(1) NOT NULL DEFAULT '1';