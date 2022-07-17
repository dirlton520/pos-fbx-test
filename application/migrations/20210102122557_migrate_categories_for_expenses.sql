-- migrate_categories_for_expenses --
SET FOREIGN_KEY_CHECKS = 0;
INSERT INTO `phppos_expenses_categories` (id,deleted,parent_id,name) SELECT id,deleted,parent_id,name FROM `phppos_categories`;
SET FOREIGN_KEY_CHECKS = 1;