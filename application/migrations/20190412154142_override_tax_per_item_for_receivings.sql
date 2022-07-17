-- override_tax_per_item_for_receivings --
ALTER TABLE `phppos_receivings_items` ADD `override_taxes` TEXT NULL DEFAULT NULL;
