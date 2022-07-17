-- woo_commerce_long_description --

ALTER TABLE `phppos_items` ADD COLUMN `long_description` longtext COLLATE utf8_unicode_ci NOT NULL;
UPDATE `phppos_items` SET long_description = `description`;