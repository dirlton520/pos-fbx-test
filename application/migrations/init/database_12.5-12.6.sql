
ALTER TABLE  `phppos_items_taxes` ADD  `cumulative` INT( 1 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `phppos_item_kits_taxes` ADD  `cumulative` INT( 1 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `phppos_sales_items_taxes` ADD  `cumulative` INT( 1 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `phppos_sales_item_kits_taxes` ADD  `cumulative` INT( 1 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `phppos_sales_suspended_items_taxes` ADD  `cumulative` INT( 1 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `phppos_sales_suspended_item_kits_taxes` ADD  `cumulative` INT( 1 ) NOT NULL DEFAULT  '0';