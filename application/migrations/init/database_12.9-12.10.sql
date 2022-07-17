
ALTER TABLE  `phppos_giftcards` CHANGE  `value`  `value` DECIMAL( 15, 2 ) NOT NULL;
ALTER TABLE  `phppos_inventory` CHANGE  `trans_inventory`  `trans_inventory` DECIMAL( 15, 2 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `phppos_items` CHANGE  `cost_price`  `cost_price` DECIMAL( 15, 2 ) NOT NULL ,
CHANGE  `unit_price`  `unit_price` DECIMAL( 15, 2 ) NOT NULL ,
CHANGE  `quantity`  `quantity` DECIMAL( 15, 2 ) NOT NULL DEFAULT  '0.00',
CHANGE  `reorder_level`  `reorder_level` DECIMAL( 15, 2 ) NOT NULL DEFAULT  '0.00';
ALTER TABLE  `phppos_items_taxes` CHANGE  `percent`  `percent` DECIMAL( 15, 3 ) NOT NULL;
ALTER TABLE  `phppos_item_kits` CHANGE  `unit_price`  `unit_price` DECIMAL( 15, 2 ) NULL DEFAULT NULL ,
CHANGE  `cost_price`  `cost_price` DECIMAL( 15, 2 ) NULL DEFAULT NULL;
ALTER TABLE  `phppos_item_kits_taxes` CHANGE  `percent`  `percent` DECIMAL( 15, 3 ) NOT NULL;
ALTER TABLE  `phppos_item_kit_items` CHANGE  `quantity`  `quantity` DECIMAL( 15, 2 ) NOT NULL;
ALTER TABLE  `phppos_receivings_items` CHANGE  `item_unit_price`  `item_unit_price` DECIMAL( 15, 2 ) NOT NULL;
ALTER TABLE  `phppos_sales_items` CHANGE  `quantity_purchased`  `quantity_purchased` DECIMAL( 15, 2 ) NOT NULL DEFAULT  '0.00',
CHANGE  `item_unit_price`  `item_unit_price` DECIMAL( 15, 2 ) NOT NULL;
ALTER TABLE  `phppos_sales_items_taxes` CHANGE  `percent`  `percent` DECIMAL( 15, 3 ) NOT NULL;
ALTER TABLE  `phppos_sales_item_kits` CHANGE  `quantity_purchased`  `quantity_purchased` DECIMAL( 15, 2 ) NOT NULL DEFAULT  '0.00',
CHANGE  `item_kit_unit_price`  `item_kit_unit_price` DECIMAL( 15, 2 ) NOT NULL;
ALTER TABLE  `phppos_sales_item_kits_taxes` CHANGE  `percent`  `percent` DECIMAL( 15, 3 ) NOT NULL;
ALTER TABLE  `phppos_sales_suspended_items` CHANGE  `quantity_purchased`  `quantity_purchased` DECIMAL( 15, 2 ) NOT NULL DEFAULT  '0.00',
CHANGE  `item_unit_price`  `item_unit_price` DECIMAL( 15, 2 ) NOT NULL;
ALTER TABLE  `phppos_sales_suspended_items_taxes` CHANGE  `percent`  `percent` DECIMAL( 15, 3 ) NOT NULL;
ALTER TABLE  `phppos_sales_suspended_item_kits` CHANGE  `quantity_purchased`  `quantity_purchased` DECIMAL( 15, 2 ) NOT NULL DEFAULT  '0.00',
CHANGE  `item_kit_unit_price`  `item_kit_unit_price` DECIMAL( 15, 2 ) NOT NULL;
ALTER TABLE  `phppos_sales_suspended_item_kits_taxes` CHANGE  `percent`  `percent` DECIMAL( 15, 3 ) NOT NULL;
ALTER TABLE  `phppos_item_kits` ADD  `item_kit_number` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `item_kit_id` ,
ADD UNIQUE (
`item_kit_number`
);
ALTER TABLE  `phppos_sales_suspended` ADD  `deleted` INT( 1 ) NOT NULL DEFAULT  '0',
ADD INDEX (  `deleted` );