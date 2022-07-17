
SET foreign_key_checks = 0;
ALTER TABLE `phppos_modules_actions` CHANGE `action_id` `action_id` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `module_id` `module_id` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `action_name_key` `action_name_key` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `phppos_permissions_actions` CHANGE `module_id` `module_id` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `action_id` `action_id` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
SET foreign_key_checks = 1;