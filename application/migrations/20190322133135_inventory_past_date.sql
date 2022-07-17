-- inventory_past_date --
SET FOREIGN_KEY_CHECKS = 0;
INSERT INTO phppos_inventory (trans_items,trans_user,trans_inventory,location_id,trans_current_quantity)
(SELECT item_id,1,0,location_id,quantity FROM phppos_location_items);

INSERT INTO phppos_inventory (trans_items,item_variation_id,trans_user,trans_inventory,location_id,trans_current_quantity)
(SELECT item_id,item_variation_id,1,0,location_id,quantity FROM phppos_location_item_variations INNER JOIN phppos_item_variations on phppos_item_variations.id = item_variation_id);

REPLACE INTO phppos_app_config (`key`,`value`) VALUES ('past_inventory_date',date(now()));
SET FOREIGN_KEY_CHECKS = 1;