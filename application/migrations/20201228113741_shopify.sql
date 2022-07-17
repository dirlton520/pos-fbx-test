-- shopify --

UPDATE phppos_app_config SET `key` = 'sku_sync_field' WHERE `key` = 'woo_sku_sync_field';

ALTER TABLE phppos_items ADD COLUMN ecommerce_inventory_item_id VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE phppos_item_variations ADD COLUMN ecommerce_inventory_item_id VARCHAR(255) NULL DEFAULT NULL;

ALTER TABLE `phppos_items` ADD INDEX( `ecommerce_inventory_item_id`);
ALTER TABLE `phppos_item_variations` ADD INDEX( `ecommerce_inventory_item_id`);
