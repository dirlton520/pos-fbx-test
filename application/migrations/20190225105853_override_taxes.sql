-- override_taxes --

ALTER TABLE `phppos_sales` ADD `override_taxes` TEXT NULL DEFAULT NULL;
ALTER TABLE `phppos_receivings` ADD `override_taxes` TEXT NULL DEFAULT NULL;
