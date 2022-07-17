-- add_loyalty_multiplier_to_sale_items_and_sale_item_kits_table --
ALTER TABLE phppos_sales_item_kits ADD COLUMN loyalty_multiplier DECIMAL(23,10) NULL DEFAULT NULL;

ALTER TABLE phppos_sales_items ADD COLUMN loyalty_multiplier DECIMAL(23,10) NULL DEFAULT NULL;