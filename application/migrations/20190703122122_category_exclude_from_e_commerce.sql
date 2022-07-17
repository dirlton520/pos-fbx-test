-- category_exclude_from_e_commerce --
ALTER TABLE `phppos_categories` ADD COLUMN `exclude_from_e_commerce` INT(1) NOT NULL DEFAULT '0';