-- improve_speed_inventory_past_date --
create index phppos_inventory_custom on phppos_inventory(trans_items, location_id, trans_date, item_variation_id, trans_id);