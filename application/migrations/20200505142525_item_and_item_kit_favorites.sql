-- item_and_item_kit_favorites --
ALTER TABLE phppos_items ADD COLUMN `is_favorite` INT(1) DEFAULT '0';
ALTER TABLE phppos_items ADD  INDEX is_favorite_index (`is_favorite`);

ALTER TABLE phppos_item_kits ADD COLUMN `is_favorite` INT(1) DEFAULT '0';
ALTER TABLE phppos_item_kits ADD  INDEX is_favorite_index (`is_favorite`);

