
REPLACE INTO `phppos_app_config` (`key`, `value`) VALUES ('version', '14.0');
REPLACE INTO `phppos_app_config` (`key`, `value`) VALUES ('sale_prefix', 'POS');
-- -------------------
-- For Store Accounts -
-- -------------------
ALTER TABLE  `phppos_customers` ADD  `balance` DECIMAL(23,10) NOT NULL DEFAULT '0.0000000000' AFTER  `company_name`;

CREATE TABLE `phppos_store_accounts` (
  `sno` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `transaction_amount` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `balance` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `comment` text NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sno`),
  KEY `deleted` (`deleted`),
  CONSTRAINT `phppos_store_accounts_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
  CONSTRAINT `phppos_store_accounts_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `phppos_customers` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ---------------------
-- Images for people ---
-- ---------------------
ALTER TABLE `phppos_people` ADD COLUMN `image_id` INT(10) NULL AFTER `comments`;
ALTER TABLE `phppos_people`
 ADD CONSTRAINT `phppos_people_ibfk_1` FOREIGN KEY (`image_id`) REFERENCES `phppos_app_files` (`file_id`);

-- ---------------------------
-- font-awesome for modules --
-- ---------------------------
ALTER TABLE  `phppos_modules` ADD  `icon` VARCHAR( 255 ) NOT NULL AFTER  `sort`;
UPDATE `phppos_modules` SET `icon` = 'cogs' WHERE `phppos_modules`.`module_id` = 'config';
UPDATE `phppos_modules` SET `icon` = 'group' WHERE `phppos_modules`.`module_id` = 'customers'; 
UPDATE `phppos_modules` SET `icon` = 'user' WHERE `phppos_modules`.`module_id` = 'employees'; 
UPDATE `phppos_modules` SET `icon` = 'credit-card' WHERE `phppos_modules`.`module_id` = 'giftcards'; 
UPDATE `phppos_modules` SET `icon` = 'inbox' WHERE `phppos_modules`.`module_id` = 'item_kits'; 
UPDATE `phppos_modules` SET `icon` = 'table' WHERE `phppos_modules`.`module_id` = 'items'; 
UPDATE `phppos_modules` SET `icon` = 'cloud-download' WHERE `phppos_modules`.`module_id` = 'receivings'; 
UPDATE `phppos_modules` SET `icon` = 'bar-chart-o' WHERE `phppos_modules`.`module_id` = 'reports'; 
UPDATE `phppos_modules` SET `icon` = 'shopping-cart' WHERE `phppos_modules`.`module_id` = 'sales'; 
UPDATE `phppos_modules` SET `icon` = 'download' WHERE `phppos_modules`.`module_id` = 'suppliers';

-- ---------------------------
-- phppos_items changes     --
-- ---------------------------
ALTER TABLE `phppos_items` ADD COLUMN `image_id` INT(10) NULL AFTER `is_serialized`;
ALTER TABLE `phppos_items` ADD COLUMN `is_service` INT(1) NOT NULL DEFAULT 0 AFTER `image_id`;
ALTER TABLE `phppos_items`
 ADD CONSTRAINT `phppos_items_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `phppos_app_files` (`file_id`);
ALTER TABLE `phppos_items` ADD COLUMN `override_default_tax` INT(1) NOT NULL DEFAULT 0 AFTER `image_id`;
UPDATE `phppos_items` SET `override_default_tax` = 1;

ALTER TABLE `phppos_items` CHANGE `start_date` `start_date` DATE NULL DEFAULT NULL, CHANGE  `end_date`  `end_date` DATE NULL DEFAULT NULL;
UPDATE phppos_items SET start_date = NULL WHERE start_date = '1969-01-01';
UPDATE phppos_items SET end_date = NULL WHERE end_date = '1969-01-01';

ALTER TABLE  `phppos_items` ADD  `product_id` VARCHAR( 255 ) NULL AFTER  `item_number` ,
ADD UNIQUE (`product_id`);

-- ---------------------------
-- module varchar length changes     --
-- ---------------------------
SET foreign_key_checks = 0;
ALTER TABLE  `phppos_modules` CHANGE  `module_id`  `module_id` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE  `phppos_permissions` CHANGE  `module_id`  `module_id` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
SET foreign_key_checks = 1;


-- ---------------------------
-- phppos_item_kits changes --
-- ---------------------------
ALTER TABLE `phppos_item_kits` ADD COLUMN `override_default_tax` INT(1) NOT NULL DEFAULT 0 AFTER `cost_price`;
UPDATE `phppos_item_kits` SET `override_default_tax` = 1;

ALTER TABLE  `phppos_item_kits` ADD  `product_id` VARCHAR( 255 ) NULL AFTER  `item_kit_number` ,
ADD UNIQUE (`product_id`);



-- --------------------------------------
-- Granular permissions for giftcards --
-- --------------------------------------
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`)
VALUES ('add_update', 'giftcards', 'module_action_add_update', 200),('delete', 'giftcards', 'module_action_delete', 210),('search', 'giftcards', 'module_action_search_giftcards', 220);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'giftcards' and
action_id = 'add_update'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'giftcards' and
action_id = 'delete'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'giftcards' and
action_id = 'search'
order by module_id, person_id;

-- --------------------------------------
-- Granular permissions for delete sale --
-- --------------------------------------
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('delete_sale', 'sales', 'module_action_delete_sale', 230);
INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'sales' and
action_id = 'delete_sale'
order by module_id, person_id;

-- ------------------------------------------
-- Little tweaks for  14.0 new features ---
-- ------------------------------------------
ALTER TABLE  `phppos_sales` ADD  `auth_code` VARCHAR(255) NULL DEFAULT '' AFTER `cc_ref_no`;
ALTER TABLE  `phppos_sales` ADD  `deleted_by` INT( 10 ) NULL DEFAULT  NULL AFTER  `auth_code`;
ALTER TABLE  `phppos_sales` ADD  `store_account_payment` INT( 1 ) NOT NULL DEFAULT  '0' AFTER  `suspended` ;
ALTER TABLE  `phppos_receivings` ADD  `deleted_by` INT( 10 ) NULL DEFAULT  NULL AFTER  `deleted`;
ALTER TABLE  `phppos_register_log` ADD  `deleted` INT( 1 ) NOT NULL DEFAULT  '0' AFTER  `cash_sales_amount`;
ALTER TABLE  `phppos_employees` ADD  `language` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `person_id`;

-- -------------------
-- For Locations ---
-- -------------------

INSERT INTO `phppos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `icon`, `module_id`) VALUES ('module_locations', 'module_locations_desc', 110, 'home', 'locations');
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('add_update', 'locations', 'module_action_add_update', 240),('delete', 'locations', 'module_action_delete', 250),('search', 'locations', 'module_action_search_locations', 260);

INSERT INTO `phppos_permissions` (`module_id`, `person_id`) (SELECT 'locations', person_id FROM phppos_permissions WHERE module_id = 'config');
INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'locations' and
action_id = 'add_update'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'locations' and
action_id = 'delete'
order by module_id, person_id;


INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'locations' and
action_id = 'search'
order by module_id, person_id;

-- -------------------------------------------------
-- Table structure for table `phppos_locations` ---
-- -------------------------------------------------

CREATE TABLE IF NOT EXISTS `phppos_locations` (
  `location_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8_unicode_ci,
  `address` text COLLATE utf8_unicode_ci,
  `phone` text COLLATE utf8_unicode_ci,
  `fax` text COLLATE utf8_unicode_ci,
  `email` text COLLATE utf8_unicode_ci,
  `receive_stock_alert` text COLLATE utf8_unicode_ci,
  `stock_alert_email` text COLLATE utf8_unicode_ci,
  `timezone` text COLLATE utf8_unicode_ci,
  `mailchimp_api_key` text COLLATE utf8_unicode_ci,
  `enable_credit_card_processing` text COLLATE utf8_unicode_ci,
  `merchant_id` text COLLATE utf8_unicode_ci,
  `merchant_password` text COLLATE utf8_unicode_ci,
  `default_tax_1_rate` text COLLATE utf8_unicode_ci,
  `default_tax_1_name` text COLLATE utf8_unicode_ci,
  `default_tax_2_rate` text COLLATE utf8_unicode_ci,
  `default_tax_2_name` text COLLATE utf8_unicode_ci,
  `default_tax_2_cumulative` text COLLATE utf8_unicode_ci,
  `default_tax_3_rate` text COLLATE utf8_unicode_ci,
  `default_tax_3_name` text COLLATE utf8_unicode_ci,
  `default_tax_4_rate` text COLLATE utf8_unicode_ci,
  `default_tax_4_name` text COLLATE utf8_unicode_ci,
  `default_tax_5_rate` text COLLATE utf8_unicode_ci,
  `default_tax_5_name` text COLLATE utf8_unicode_ci,
  `deleted` int(1) DEFAULT '0',
  PRIMARY KEY (`location_id`),
  KEY `deleted` (`deleted`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;
-- -------------------------------------------------
-- Migrate app config to location ---
-- -------------------------------------------------

INSERT INTO `phppos_locations` (`location_id`, `name`, `address`, `phone`, `fax`, `email`,
	`receive_stock_alert`, `stock_alert_email`, `timezone`, `mailchimp_api_key`,
	 `enable_credit_card_processing`, `merchant_id`, `merchant_password`, `default_tax_1_rate`,
	`default_tax_1_name`,`default_tax_2_rate`, `default_tax_2_name`, `default_tax_2_cumulative`) VALUES(
	1,
	'Default',
	(SELECT `value` FROM phppos_app_config WHERE `key` = 'address'),
	(SELECT `value` FROM phppos_app_config WHERE `key` = 'phone'),
	(SELECT `value` FROM phppos_app_config WHERE `key` = 'fax'),
	(SELECT `value` FROM phppos_app_config WHERE `key` = 'email'),
	(SELECT `value` FROM phppos_app_config WHERE `key` = 'receive_stock_alert'),
	(SELECT `value` FROM phppos_app_config WHERE `key` = 'stock_alert_email'),
	(SELECT `value` FROM phppos_app_config WHERE `key` = 'timezone'),
	(SELECT `value` FROM phppos_app_config WHERE `key` = 'mailchimp_api_key'),
	(SELECT `value` FROM phppos_app_config WHERE `key` = 'enable_credit_card_processing'),
	(SELECT `value` FROM phppos_app_config WHERE `key` = 'merchant_id'),
	(SELECT `value` FROM phppos_app_config WHERE `key` = 'merchant_password'),
	(SELECT `value` FROM phppos_app_config WHERE `key` = 'default_tax_1_rate'),
	(SELECT `value` FROM phppos_app_config WHERE `key` = 'default_tax_1_name'),
	(SELECT `value` FROM phppos_app_config WHERE `key` = 'default_tax_2_rate'),
	(SELECT `value` FROM phppos_app_config WHERE `key` = 'default_tax_2_name'),
	(SELECT `value` FROM phppos_app_config WHERE `key` = 'default_tax_2_cumulative')
);

DELETE FROM `phppos_app_config` WHERE `key` IN ('address', 'phone', 'fax','email','receive_stock_alert',
	'stock_alert_email', 'return_policy', 'timezone', 'mailchimp_api_key',  'enable_credit_card_processing', 
	'merchant_id', 'merchant_password');

-- -------------------------------------------------
-- Remove speed up search query option ---
-- -------------------------------------------------
DELETE FROM `phppos_app_config` WHERE `key` = 'speed_up_search_queries';


-- -----------------------------------------------------
-- Table structure for table `phppos_price_tiers` 	  --
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `phppos_price_tiers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

-- -----------------------------------------------------
-- Table structure for table `phppos_items_tier_prices` --
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `phppos_items_tier_prices` (
  `tier_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  `unit_price` decimal(23,10) NULL DEFAULT '0.0000000000',
  `percent_off` int(11) NULL,
  CONSTRAINT `phppos_items_tier_prices_ibfk_1` FOREIGN KEY (`tier_id`) REFERENCES `phppos_price_tiers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_items_tier_prices_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  PRIMARY KEY (`tier_id`, `item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- -----------------------------------------------------
-- Table structure for table `phppos_item_kits_tier_prices` --
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `phppos_item_kits_tier_prices` (
  `tier_id` int(10) NOT NULL,
  `item_kit_id` int(10) NOT NULL,
  `unit_price` decimal(23,10) NULL DEFAULT '0.0000000000',
  `percent_off` int(11) NULL,
  CONSTRAINT `phppos_item_kits_tier_prices_ibfk_1` FOREIGN KEY (`tier_id`) REFERENCES `phppos_price_tiers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_item_kits_tier_prices_ibfk_2` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`),
  PRIMARY KEY (`tier_id`, `item_kit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- -----------------------------------------------------
-- Table structure for table `phppos_location_items_tier_prices` --
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `phppos_location_items_tier_prices` (
  `tier_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  `location_id` int(10) NOT NULL,
  `unit_price` decimal(23,10) NULL DEFAULT '0.0000000000',
  `percent_off` int(11) NULL,
  CONSTRAINT `phppos_location_items_tier_prices_ibfk_1` FOREIGN KEY (`tier_id`) REFERENCES `phppos_price_tiers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_location_items_tier_prices_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_location_items_tier_prices_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  PRIMARY KEY (`tier_id`, `item_id`, `location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- -----------------------------------------------------
-- Table structure for table `phppos_location_item_kits_tier_prices` --
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `phppos_location_item_kits_tier_prices` (
  `tier_id` int(10) NOT NULL,
  `item_kit_id` int(10) NOT NULL,
  `location_id` int(10) NOT NULL,
  `unit_price` decimal(23,10) NULL DEFAULT '0.0000000000',
  `percent_off` int(11) NULL,
  CONSTRAINT `phppos_location_item_kits_tier_prices_ibfk_1` FOREIGN KEY (`tier_id`) REFERENCES `phppos_price_tiers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_location_item_kits_tier_prices_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_location_item_kits_tier_prices_ibfk_3` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`),
  PRIMARY KEY (`tier_id`, `item_kit_id`, `location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- -----------------------------------------------------
-- Table structure for table `phppos_location_items` --
-- -----------------------------------------------------

CREATE TABLE IF NOT EXISTS `phppos_location_items` (
  `location_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `location` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cost_price` decimal(23,10) NULL DEFAULT NULL,
  `unit_price` decimal(23,10) NULL DEFAULT NULL,
  `promo_price` decimal(23,10) NULL DEFAULT NULL,
  `start_date` date NULL DEFAULT NULL,
  `end_date` date NULL DEFAULT NULL,
  `quantity` decimal(23,10) NULL DEFAULT '0.0000000000',
  `reorder_level` decimal(23,10) NULL DEFAULT NULL,
  `override_default_tax` INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`location_id`, `item_id`),
  CONSTRAINT `phppos_location_items_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_location_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


-- -----------------------------------------------------
-- Transfer item quantity and location to new default phppos_location_items --
-- -----------------------------------------------------
INSERT INTO `phppos_location_items` (`location_id`, `item_id`, `quantity`, `location`) 
(SELECT 1, `item_id`, `quantity`,`location` FROM `phppos_items`);

-- -----------------------------------------------------
-- Remove phppos_items quantity and location columns --
-- -----------------------------------------------------
ALTER TABLE `phppos_items` DROP `quantity`;
ALTER TABLE `phppos_items` DROP `location`;

-- -----------------------------------------------------
-- Table structure for table `phppos_location_items_taxes` --
-- -----------------------------------------------------

CREATE TABLE `phppos_location_items_taxes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) NOT NULL,
  `item_id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(16,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tax` (`location_id`,`item_id`,`name`,`percent`),
  CONSTRAINT `phppos_location_items_taxes_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_location_items_taxes_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- -----------------------------------------------------
-- Table structure for table `phppos_location_item_kits` --
-- -----------------------------------------------------
CREATE TABLE `phppos_location_item_kits` (
  `location_id` int(11) NOT NULL,
  `item_kit_id` int(11) NOT NULL,
  `unit_price` decimal(23,10) DEFAULT NULL,
  `cost_price` decimal(23,10) DEFAULT NULL,
  `override_default_tax` INT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`location_id`,`item_kit_id`),
  CONSTRAINT `phppos_location_item_kits_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_location_item_kits_ibfk_2` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- -----------------------------------------------------
-- Table structure for table `phppos_location_item_kits_taxes` --
-- -----------------------------------------------------

CREATE TABLE `phppos_location_item_kits_taxes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) NOT NULL,
  `item_kit_id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(16,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tax` (`location_id`,`item_kit_id`,`name`,`percent`),
  CONSTRAINT `phppos_location_item_kits_taxes_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_location_item_kits_taxes_ibfk_2` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------
-- location id for inventory tracking table ---
-- --------------------------------------- ----
ALTER TABLE  `phppos_inventory` ADD  `location_id` INT( 11 ) NOT NULL AFTER  `trans_inventory`;
UPDATE `phppos_inventory` SET `location_id` = 1;
ALTER TABLE `phppos_inventory`
   ADD KEY (`location_id`),
   ADD CONSTRAINT `phppos_inventory_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`);


-- --------------------------------------
-- Permission for deleting suspended sale --
-- --------------------------------------
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`)     VALUES ('delete_suspended_sale', 'sales', 'module_action_delete_suspended_sale', 181);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'sales' and
action_id = 'delete_suspended_sale'
order by module_id, person_id;

-- --------------------------------------------
-- employee locations						---
-- --------------------------------------- ----
CREATE TABLE `phppos_employees_locations` (
  `employee_id` int(10) NOT NULL,
  `location_id` int(10) NOT NULL,
  PRIMARY KEY (`employee_id`,`location_id`),
  CONSTRAINT `phppos_employees_locations_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_employees_locations_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO `phppos_employees_locations` (employee_id, location_id) (
	SELECT person_id, 1 FROM phppos_employees
);

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`)  VALUES ('assign_all_locations', 'employees', 'module_action_assign_all_locations', 151);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'employees' and
action_id = 'assign_all_locations'
order by module_id, person_id;



-- --------------------------------------------
-- location id for sales ---
-- --------------------------------------- ----
ALTER TABLE phppos_sales ADD COLUMN location_id INT(11) NOT NULL;
UPDATE `phppos_sales` SET `location_id` = 1;

-- -------------------------------------------
-- Constraint additions for table `phppos_sales` ----
-- -------------------------------------------
ALTER TABLE `phppos_sales`
   ADD KEY (`location_id`),
	ADD CONSTRAINT `phppos_sales_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
	ADD CONSTRAINT `phppos_sales_ibfk_4` FOREIGN KEY (`deleted_by`) REFERENCES `phppos_employees` (`person_id`);
-- --------------------------------------------

-- --------------------------------------------
-- location id for receivings ---
-- --------------------------------------- ----
ALTER TABLE phppos_receivings ADD COLUMN location_id INT(11) NOT NULL;
UPDATE `phppos_receivings` SET `location_id` = 1;

-- -------------------------------------------
-- Constraint additions for table `phppos_receivings` ----
-- -------------------------------------------
ALTER TABLE `phppos_receivings`
   ADD KEY (`location_id`),
	ADD CONSTRAINT `phppos_receivings_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`);
-- --------------------------------------------


-- -------------------------------------------
-- Customers price tiers				  ----
-- -------------------------------------------
ALTER TABLE  `phppos_customers` ADD `tier_id` INT( 10 ) NULL AFTER  `cc_preview`,
	ADD CONSTRAINT `phppos_customers_ibfk_2` FOREIGN KEY (`tier_id`) REFERENCES `phppos_price_tiers` (`id`);


-- --------------------------------------------
-- Auto increment primiary keys for tax tables ---
-- --------------------------------------- ----
ALTER TABLE `phppos_items_taxes`
ADD UNIQUE KEY `unique_tax` (`item_id`, `name`, `percent`), 
DROP PRIMARY KEY,
ADD COLUMN `id` int(10) NOT NULL AUTO_INCREMENT FIRST,
ADD PRIMARY KEY (`id`);

ALTER TABLE `phppos_item_kits_taxes`
ADD UNIQUE KEY `unique_tax` (`item_kit_id`, `name`, `percent`), 
DROP PRIMARY KEY,
ADD COLUMN `id` int(10) NOT NULL AUTO_INCREMENT FIRST,
ADD PRIMARY KEY (`id`);

-- --------------------------------------------
-- Allow 4 decimal places for quantity fields for sales + receivings ---
-- --------------------------------------- ----

ALTER TABLE  `phppos_item_kit_items` CHANGE  `quantity` `quantity` decimal(23,10) NOT NULL;
ALTER TABLE  `phppos_sales_item_kits` CHANGE `quantity_purchased` `quantity_purchased` decimal(23,10) NOT NULL DEFAULT '0.0000000000';
ALTER TABLE  `phppos_sales_items` CHANGE `quantity_purchased` `quantity_purchased` decimal(23,10) NOT NULL DEFAULT '0.0000000000';
ALTER TABLE  `phppos_receivings_items` CHANGE `quantity_purchased` `quantity_purchased` decimal(23,10) NOT NULL DEFAULT '0.0000000000';
ALTER TABLE  `phppos_inventory` CHANGE  `trans_inventory`  `trans_inventory` DECIMAL( 23,10 ) NOT NULL DEFAULT  '0.0000000000';
ALTER TABLE  `phppos_items` CHANGE  `reorder_level`  `reorder_level` DECIMAL( 23,10 ) NULL DEFAULT NULL;

-- --------------------------------------------
-- Allow 10 decimal places for price fields---
-- --------------------------------------- ----
ALTER TABLE  `phppos_inventory` CHANGE  `trans_inventory`  `trans_inventory` DECIMAL( 23,10 ) NOT NULL DEFAULT  '0.0000000000';
ALTER TABLE  `phppos_items` CHANGE  `cost_price`  `cost_price` DECIMAL( 23,10 ) NOT NULL ;
ALTER TABLE  `phppos_items` CHANGE  `unit_price`  `unit_price` DECIMAL( 23,10 ) NOT NULL ;
ALTER TABLE  `phppos_items` CHANGE  `promo_price`  `promo_price` DECIMAL( 23,10 ) NULL DEFAULT NULL;
ALTER TABLE  `phppos_item_kits` CHANGE  `unit_price`  `unit_price` DECIMAL( 23,10 ) NULL DEFAULT NULL ,
CHANGE  `cost_price`  `cost_price` DECIMAL( 23,10 ) NULL DEFAULT NULL ;
ALTER TABLE  `phppos_receivings_items` CHANGE  `item_cost_price`  `item_cost_price` DECIMAL( 23,10 ) NOT NULL ,
CHANGE  `item_unit_price`  `item_unit_price` DECIMAL( 23,10 ) NOT NULL ;
ALTER TABLE  `phppos_register_log` CHANGE  `open_amount`  `open_amount` DECIMAL( 23,10 ) NOT NULL ,
CHANGE  `close_amount`  `close_amount` DECIMAL( 23,10 ) NOT NULL ,
CHANGE  `cash_sales_amount`  `cash_sales_amount` DECIMAL( 23,10 ) NOT NULL ;
ALTER TABLE  `phppos_sales_items` CHANGE  `item_cost_price`  `item_cost_price` DECIMAL( 23,10 ) NOT NULL ,
CHANGE  `item_unit_price`  `item_unit_price` DECIMAL( 23,10 ) NOT NULL ;
ALTER TABLE  `phppos_sales_item_kits` CHANGE  `item_kit_cost_price`  `item_kit_cost_price` DECIMAL( 23,10 ) NOT NULL ,
CHANGE  `item_kit_unit_price`  `item_kit_unit_price` DECIMAL( 23,10 ) NOT NULL ;
ALTER TABLE  `phppos_sales_payments` CHANGE  `payment_amount`  `payment_amount` DECIMAL( 23,10 ) NOT NULL ;
ALTER TABLE  `phppos_giftcards` CHANGE  `value`  `value` DECIMAL( 23,10 ) NOT NULL ;

-- --------------------------------------------
-- promo price null for items without promo price---
-- --------------------------------------- ----
UPDATE phppos_items SET promo_price = NULL WHERE promo_price = 0 and start_date IS NULL and end_date IS NULL;


-- --------------------------------------------
-- Truncated CC field and card type---
-- --------------------------------------- ----
ALTER TABLE  `phppos_sales_payments` ADD  `truncated_card` VARCHAR(255) NULL DEFAULT '' AFTER `payment_amount`;
ALTER TABLE  `phppos_sales_payments` ADD  `card_issuer` VARCHAR(255) NULL DEFAULT '' AFTER `truncated_card`;
ALTER TABLE  `phppos_customers` ADD  `card_issuer` VARCHAR(255) NULL DEFAULT '' AFTER `cc_preview`;


-- --------------------------------------------
-- Tax included fields for items and item kits---
-- --------------------------------------- ----
ALTER TABLE  `phppos_items` ADD  `tax_included` INT( 1 ) NOT NULL DEFAULT  '0' AFTER  `description` ;
ALTER TABLE  `phppos_item_kits` ADD  `tax_included` INT( 1 ) NOT NULL DEFAULT  '0' AFTER  `category` ;


-- --------------------------------------------
-- Granular permissions for reports---
-- --------------------------------------- ----
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_categories', 'reports', 'reports_categories', 100);
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_sales_generator', 'reports', 'reports_sales_generator', 110);
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_customers', 'reports', 'reports_customers', 120);
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_deleted_sales', 'reports', 'reports_deleted_sales', 130);
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_discounts', 'reports', 'reports_discounts', 140);
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_employees', 'reports', 'reports_employees', 150);
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_giftcards', 'reports', 'reports_giftcards', 160);
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_inventory_reports', 'reports', 'reports_inventory_reports', 170);
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_item_kits', 'reports', 'module_item_kits', 180);
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_items', 'reports', 'reports_items', 190);
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_payments', 'reports', 'reports_payments', 200);
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_profit_and_loss', 'reports', 'reports_profit_and_loss', 210);
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_receivings', 'reports', 'reports_receivings', 220);
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_register_log', 'reports', 'reports_register_log_title', 230);
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_sales', 'reports', 'reports_sales', 240);
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_store_account', 'reports', 'reports_store_account', 250);
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_suppliers', 'reports', 'reports_suppliers', 260);
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_taxes', 'reports', 'reports_taxes', 270);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_categories'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_sales_generator'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_customers'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_deleted_sales'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_discounts'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_employees'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_giftcards'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_inventory_reports'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_item_kits'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_items'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_payments'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_profit_and_loss'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_receivings'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_register_log'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_sales'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_store_account'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_suppliers'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_taxes'
order by module_id, person_id;

ALTER TABLE `phppos_sales` ADD INDEX `sales_search` (`location_id`,`store_account_payment`,`sale_time`,`sale_id`);

-- --------------------------------------------
-- Allow null for giftcard numbers---
-- --------------------------------------- ----
ALTER TABLE  `phppos_giftcards` CHANGE  `giftcard_number`  `giftcard_number` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;