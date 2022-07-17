-- bitrix_subscription --

CREATE TABLE `phppos_bitrix_subscription` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(10) NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `phppos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `icon`, `module_id`) VALUES ('module_bitrix', 'module_bitrix_desc', '25', 'ti-calendar', 'bitrix');

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('add', 'bitrix', 'bitrix_add', '47');

INSERT INTO `phppos_permissions` (`module_id`, `person_id`) VALUES ('bitrix', '1');

INSERT INTO `phppos_permissions_actions` (`module_id`, `person_id`, `action_id`) VALUES ('bitrix', '1', 'add');

ALTER TABLE `phppos_categories`  ADD `bitrix_section_id` INT(10) NULL  AFTER `parent_id`;

ALTER TABLE `phppos_categories`  ADD `bitrix_section_parent_id` INT(10) NULL AFTER `bitrix_section_id`;
