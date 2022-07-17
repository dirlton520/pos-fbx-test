
SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE  `phppos_app_config` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE  `phppos_app_config` CHANGE  `key`  `key` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,CHANGE  `value`  `value` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

ALTER TABLE  `phppos_customers` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE  `phppos_customers` CHANGE  `account_number`  `account_number` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

ALTER TABLE  `phppos_employees` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE  `phppos_employees` CHANGE  `username`  `username` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL , CHANGE  `password`  `password` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

ALTER TABLE  `phppos_inventory` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE  `phppos_inventory` CHANGE  `trans_comment`  `trans_comment` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

ALTER TABLE  `phppos_items` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE  `phppos_items` CHANGE  `name`  `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL , CHANGE  `category`  `category` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL , CHANGE  `item_number`  `item_number` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL , CHANGE  `description`  `description` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL , CHANGE  `location`  `location` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

ALTER TABLE  `phppos_items_taxes` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE  `phppos_items_taxes` CHANGE  `name`  `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

ALTER TABLE  `phppos_item_kits` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE  `phppos_item_kits` CHANGE  `name`  `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL , CHANGE  `description`  `description` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

ALTER TABLE  `phppos_item_kit_items` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

ALTER TABLE  `phppos_permissions` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

ALTER TABLE  `phppos_modules` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE  `phppos_modules` CHANGE  `name_lang_key`  `name_lang_key` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL , CHANGE  `desc_lang_key`  `desc_lang_key` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL , CHANGE  `module_id`  `module_id` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

ALTER TABLE  `phppos_people` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `phppos_people` CHANGE `first_name` `first_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `last_name` `last_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `phone_number` `phone_number` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `email` `email` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `address_1` `address_1` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `address_2` `address_2` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `city` `city` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `state` `state` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `zip` `zip` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `country` `country` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `comments` `comments` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

ALTER TABLE  `phppos_permissions` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE  `phppos_permissions` CHANGE  `module_id`  `module_id` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

ALTER TABLE  `phppos_receivings` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `phppos_receivings` CHANGE `comment` `comment` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `payment_type` `payment_type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

ALTER TABLE  `phppos_receivings_items` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `phppos_receivings_items` CHANGE `description` `description` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, CHANGE `serialnumber` `serialnumber` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

ALTER TABLE  `phppos_sales` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `phppos_sales` CHANGE `comment` `comment` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `payment_type` `payment_type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

ALTER TABLE  `phppos_sales_items` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `phppos_sales_items` CHANGE `description` `description` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, CHANGE `serialnumber` `serialnumber` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

ALTER TABLE  `phppos_sales_items_taxes` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE  `phppos_sales_items_taxes` CHANGE  `name`  `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

ALTER TABLE  `phppos_sales_payments` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE  `phppos_sales_payments` CHANGE  `payment_type`  `payment_type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

ALTER TABLE  `phppos_sales_suspended` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `phppos_sales_suspended` CHANGE `comment` `comment` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `payment_type` `payment_type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

ALTER TABLE  `phppos_sales_suspended_items` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `phppos_sales_suspended_items` CHANGE `description` `description` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, CHANGE `serialnumber` `serialnumber` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

ALTER TABLE  `phppos_sales_suspended_items_taxes` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE  `phppos_sales_suspended_items_taxes` CHANGE  `name`  `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

ALTER TABLE  `phppos_sales_suspended_payments` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE  `phppos_sales_suspended_payments` CHANGE  `payment_type`  `payment_type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

ALTER TABLE  `phppos_sessions` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `phppos_sessions` CHANGE `session_id` `session_id` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0', CHANGE `ip_address` `ip_address` VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0', CHANGE `user_agent` `user_agent` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `user_data` `user_data` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

ALTER TABLE  `phppos_suppliers` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `phppos_suppliers` CHANGE `company_name` `company_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `account_number` `account_number` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

SET FOREIGN_KEY_CHECKS=1;