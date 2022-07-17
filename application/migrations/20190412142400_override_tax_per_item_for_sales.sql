-- override_tax_per_item_for_sales --

ALTER TABLE `phppos_sales_items` ADD `override_taxes` TEXT NULL DEFAULT NULL;
ALTER TABLE `phppos_sales_item_kits` ADD `override_taxes` TEXT NULL DEFAULT NULL;