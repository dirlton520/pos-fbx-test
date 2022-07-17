-- damaged_reason_comment_field --

ALTER TABLE phppos_damaged_items_log ADD COLUMN `damaged_reason_comment` VARCHAR(255) NULL DEFAULT NULL;