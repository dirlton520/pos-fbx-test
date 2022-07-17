-- last_edited_columns --

ALTER TABLE `phppos_items` ADD `last_edited` timestamp NULL DEFAULT NULL;
UPDATE `phppos_items` SET last_edited = last_modified;