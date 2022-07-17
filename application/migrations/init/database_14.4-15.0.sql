

CREATE TABLE `phppos_register_currency_denominations` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` decimal(23,10) NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `phppos_register_currency_denominations`
--

INSERT INTO `phppos_register_currency_denominations` (`id`, `name`, `value`) VALUES
(1, '100''s', 100.0000000000),
(2, '50''s', 50.0000000000),
(3, '20''s', 20.0000000000),
(4, '10''s', 10.0000000000),
(5, '5''s', 5.0000000000),
(6, '2''s', 2.0000000000),
(7, '1''s', 1.0000000000),
(8, 'Half Dollars', 0.5000000000),
(9, 'Quarters', 0.2500000000),
(10, 'Dimes', 0.1000000000),
(11, 'Nickels', 0.0500000000),
(12, 'Pennies', 0.0100000000);

ALTER TABLE `phppos_sales` ADD `was_layaway` INT(1) NOT NULL DEFAULT '0' AFTER `store_account_payment`, ADD `was_estimate` INT(1) NOT NULL DEFAULT '0' AFTER `was_layaway`,
ADD `signature_image_id` INT(10) DEFAULT NULL AFTER `tier_id`,
ADD CONSTRAINT `phppos_sales_ibfk_8` FOREIGN KEY (`signature_image_id`) REFERENCES `phppos_app_files` (`file_id`),
ADD INDEX (`was_layaway`), ADD INDEX(`was_estimate`) ;

UPDATE `phppos_sales` SET `was_layaway` = 1 WHERE suspended = 1;
UPDATE `phppos_sales` SET `was_estimate` = 1 WHERE suspended = 2;


CREATE TABLE `phppos_employees_time_clock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `clock_in` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `clock_out` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `clock_in_comment` text COLLATE utf8_unicode_ci NOT NULL,
  `clock_out_comment` text COLLATE utf8_unicode_ci NOT NULL,
  `hourly_pay_rate` DECIMAL(23,10) NOT NULL DEFAULT '0',
  CONSTRAINT `phppos_employees_time_clock_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_employees_time_clock_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_timeclock', 'reports', 'employees_timeclock', 280);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_timeclock'
order by module_id, person_id;

ALTER TABLE `phppos_employees` ADD `hourly_pay_rate` DECIMAL(23,10) NOT NULL DEFAULT '0' AFTER `commission_percent`;

ALTER TABLE `phppos_locations` ADD `color` TEXT NULL AFTER `email`;
ALTER TABLE `phppos_locations` ADD `return_policy` TEXT NULL AFTER `color`;

CREATE TABLE `phppos_inventory_counts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `count_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `employee_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  CONSTRAINT `phppos_inventory_counts_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_inventory_counts_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_inventory_counts_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inventory_counts_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `count` decimal(23,10) DEFAULT '0.0000000000',
  `actual_quantity` decimal(23,10) DEFAULT '0.0000000000',
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  CONSTRAINT `phppos_inventory_counts_items_ibfk_1` FOREIGN KEY (`inventory_counts_id`) REFERENCES `phppos_inventory_counts` (`id`),
  CONSTRAINT `phppos_inventory_counts_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('count_inventory', 'items', 'items_count_inventory', 65);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'items' and
action_id = 'count_inventory'
order by module_id, person_id;



INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('edit_quantity', 'items', 'items_edit_quantity', 62);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'items' and
action_id = 'edit_quantity'
order by module_id, person_id;


INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_tiers', 'reports', 'reports_tiers', 275);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_tiers'
order by module_id, person_id;

ALTER TABLE `phppos_sales` ADD `deleted_taxes` TEXT NULL DEFAULT NULL;

UPDATE phppos_app_config SET `value` = '' WHERE `key` = 'disable_subtraction_of_giftcard_amount_from_sales' and `value` = '1';
UPDATE phppos_app_config SET `value` = 'selling_giftcard' WHERE `key` = 'disable_subtraction_of_giftcard_amount_from_sales' and `value` = '0';
UPDATE phppos_app_config SET `key` = 'calculate_profit_for_giftcard_when' WHERE `key` = 'disable_subtraction_of_giftcard_amount_from_sales';

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_closeout', 'reports', 'reports_closeout', 105);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_closeout'
order by module_id, person_id;

UPDATE phppos_modules_actions SET `sort` = 106 WHERE module_id='reports' and action_id = 'view_commissions';

-- -----------------------
--  Messages           ---
-- -----------------------

CREATE TABLE `phppos_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `sender_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` INT(1) NOT NULL DEFAULT '0',
  CONSTRAINT `phppos_messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `phppos_employees` (`person_id`),
  PRIMARY KEY (`id`),
  KEY `phppos_messages_key_1` (`deleted`, `created_at`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_message_receiver` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message_read` INT(1) NOT NULL DEFAULT '0',
  CONSTRAINT `phppos_message_receiver_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `phppos_messages` (`id`),
  CONSTRAINT `phppos_message_receiver_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `phppos_employees` (`person_id`),
  PRIMARY KEY (`id`),
  KEY `phppos_message_receiver_key_1` (`message_id`, `receiver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO `phppos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `icon`, `module_id`) VALUES ('module_messages', 'module_messages_desc', 120, 'email', 'messages');
INSERT INTO `phppos_permissions` (`module_id`, `person_id`) (SELECT 'messages', person_id FROM phppos_employees);

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('send_message', 'messages', 'employees_send_message', 350);
INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'messages' and
action_id = 'send_message'
order by module_id, person_id;


ALTER TABLE `phppos_register_log` ADD `notes` VARCHAR(255) NULL DEFAULT NULL AFTER `cash_sales_amount`;

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_all_employee_commissions', 'reports', 'reports_view_all_employee_commissions', 107);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_all_employee_commissions'
order by module_id, person_id;

-- --------------------------
--  ionicons for modules  ---
-- --------------------------
UPDATE `phppos_modules` SET `icon` = 'settings' WHERE `phppos_modules`.`module_id` = 'config';
UPDATE `phppos_modules` SET `icon` = 'user' WHERE `phppos_modules`.`module_id` = 'customers';
UPDATE `phppos_modules` SET `icon` = 'id-badge' WHERE `phppos_modules`.`module_id` = 'employees';
UPDATE `phppos_modules` SET `icon` = 'harddrives' WHERE `phppos_modules`.`module_id` = 'item_kits';
UPDATE `phppos_modules` SET `icon` = 'harddrive' WHERE `phppos_modules`.`module_id` = 'items';
UPDATE `phppos_modules` SET `icon` = 'cloud-down' WHERE `phppos_modules`.`module_id` = 'receivings';
UPDATE `phppos_modules` SET `icon` = 'bar-chart' WHERE `phppos_modules`.`module_id` = 'reports';

-- Decimal Discount + tier percent off

ALTER TABLE `phppos_sales_items` CHANGE `discount_percent` `discount_percent` DECIMAL(15,3) NOT NULL DEFAULT '0';
ALTER TABLE `phppos_sales_item_kits` CHANGE `discount_percent` `discount_percent` DECIMAL(15,3) NOT NULL DEFAULT '0';

ALTER TABLE `phppos_items_tier_prices` CHANGE `percent_off` `percent_off` DECIMAL(15,3) NULL DEFAULT NULL;
ALTER TABLE `phppos_item_kits_tier_prices` CHANGE `percent_off` `percent_off` DECIMAL(15,3) NULL DEFAULT NULL;

ALTER TABLE `phppos_location_item_kits_tier_prices` CHANGE `percent_off` `percent_off` DECIMAL(15,3) NULL DEFAULT NULL;
ALTER TABLE `phppos_location_items_tier_prices` CHANGE `percent_off` `percent_off` DECIMAL(15,3) NULL DEFAULT NULL;

ALTER TABLE `phppos_receivings_items` CHANGE `discount_percent` `discount_percent` DECIMAL(15,3) NOT NULL DEFAULT '0';


-- Line should be bigger int
ALTER TABLE `phppos_receivings_items` CHANGE `line` `line` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `phppos_sales_item_kits` CHANGE `line` `line` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `phppos_sales_item_kits_taxes` CHANGE `line` `line` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `phppos_sales_items` CHANGE `line` `line` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `phppos_sales_items_taxes` CHANGE `line` `line` int(11) NOT NULL DEFAULT '0';

-- Receivings now can support taxes
CREATE TABLE `phppos_receivings_items_taxes` (
  `receiving_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  `line` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(15,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`receiving_id`,`item_id`,`line`,`name`,`percent`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `phppos_receivings_items_taxes_ibfk_1` FOREIGN KEY (`receiving_id`) REFERENCES `phppos_receivings` (`receiving_id`),
  CONSTRAINT `phppos_receivings_items_taxes_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `phppos_receivings` ADD `deleted_taxes` TEXT NULL DEFAULT NULL;
ALTER TABLE `phppos_receivings` ADD `is_po` INT(1) NOT NULL DEFAULT '0';

ALTER TABLE `phppos_receivings_items` ADD `quantity_received`decimal(23,10) NOT NULL DEFAULT '0.0000000000' AFTER `quantity_purchased`;

UPDATE `phppos_receivings_items` INNER JOIN phppos_receivings USING (receiving_id) SET quantity_received = quantity_purchased WHERE suspended = 0;

ALTER TABLE `phppos_receivings_items` ADD `expire_date` date DEFAULT NULL;

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`)
VALUES ('delete_taxes', 'receivings', 'module_action_delete_taxes', 300);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'receivings' and
action_id = 'delete_taxes'
order by module_id, person_id;

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`)
VALUES ('edit_receiving', 'receivings', 'module_action_edit_receiving', 303);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'receivings' and
action_id = 'edit_receiving'
order by module_id, person_id;

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`)
VALUES ('delete_receiving', 'receivings', 'module_action_delete_receiving', 306);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'receivings' and
action_id = 'delete_receiving'
order by module_id, person_id;

ALTER TABLE `phppos_employees` ADD `force_password_change` INT(1) NOT NULL DEFAULT '0' AFTER `password`;

CREATE TABLE `phppos_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NULL,
  KEY `deleted` (`deleted`),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag_name` (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `phppos_items_tags` (
  `item_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`item_id`, `tag_id`),
  CONSTRAINT `phppos_items_tags_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_items_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `phppos_tags` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `phppos_item_kits_tags` (
  `item_kit_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`item_kit_id`, `tag_id`),
  CONSTRAINT `phppos_item_kits_tags_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`),
  CONSTRAINT `phppos_item_kits_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `phppos_tags` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



CREATE TABLE `phppos_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `hide_from_grid` int(1) NOT NULL DEFAULT '0',
  `parent_id` int (11) NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  KEY `deleted` (`deleted`),
  CONSTRAINT `phppos_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `phppos_categories` (`id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------
--  Migrate categories  ---
-- --------------------------

INSERT INTO phppos_categories (name) 
SELECT DISTINCT category FROM phppos_items 
UNION 
SELECT DISTINCT category FROM phppos_item_kits;

ALTER TABLE phppos_items
    ADD COLUMN category_id int(11) NULL AFTER `name`,
    ADD CONSTRAINT `phppos_items_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `phppos_categories` (`id`);

ALTER TABLE phppos_item_kits
     ADD COLUMN category_id int(11) NULL AFTER `name`,
     ADD CONSTRAINT `phppos_item_kits_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `phppos_categories` (`id`);

UPDATE `phppos_categories` SET name = 'BLANK' WHERE name = '';

-- --------------------------
--  Need this for mysql 5.6+ so the queries don't take forever for large tables  ---
-- --------------------------
ALTER TABLE phppos_items FORCE;
ALTER TABLE phppos_item_kits FORCE;
ALTER TABLE phppos_categories FORCE;

UPDATE phppos_items as i INNER JOIN phppos_categories as c ON c.name = i.category SET category_id = c.id;
UPDATE phppos_item_kits as i INNER JOIN phppos_categories as c ON c.name = i.category SET category_id = c.id; 

ALTER TABLE phppos_items
    DROP COLUMN category;

ALTER TABLE phppos_item_kits
    DROP COLUMN category;

 INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_tags', 'reports', 'common_tags', 264);

 INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
 SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
 from phppos_permissions
 inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
 WHERE phppos_permissions.module_id = 'reports' and
 action_id = 'view_tags'
 order by module_id, person_id;

ALTER TABLE `phppos_items` ADD `expire_days` int(10) NULL DEFAULT NULL AFTER `reorder_level`;

ALTER TABLE `phppos_register_log` ADD `total_cash_additions` DECIMAL(23,10) NOT NULL DEFAULT '0' AFTER `cash_sales_amount`, ADD `total_cash_subtractions` DECIMAL(23,10) NOT NULL DEFAULT '0' AFTER `total_cash_additions`;

CREATE TABLE `phppos_register_log_audit` ( 
	`id` INT(11) NOT NULL AUTO_INCREMENT , 
   `register_log_id` int(10) NOT NULL,
   `employee_id` int(10) NOT NULL,
	`date` TIMESTAMP NOT NULL , 
	`amount` DECIMAL(23,10) NOT NULL DEFAULT '0' ,
	`note` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
   PRIMARY KEY (`id`),
   CONSTRAINT `register_log_audit_ibfk_1` FOREIGN KEY (`register_log_id`) REFERENCES `phppos_register_log` (`register_log_id`),
   CONSTRAINT `register_log_audit_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `phppos_expenses` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `location_id` int(10) NOT NULL,
  `category_id` int(11) NULL,
  `expense_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `expense_description` text COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `expense_reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `expense_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expense_amount` decimal(23,10) NOT NULL,
  `expense_tax` decimal(23,10) NOT NULL,
  `expense_note`varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `employee_id` int(10) NOT NULL,
  `approved_employee_id` int(10) NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `location_id` (`location_id`),
  KEY `employee_id` (`employee_id`),
  KEY `approved_employee_id` (`approved_employee_id`),
  KEY `category_id` (`category_id`),
  KEY `deleted` (`deleted`),
  CONSTRAINT `phppos_expenses_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_expenses_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_expenses_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `phppos_categories` (`id`),
  CONSTRAINT `phppos_expenses_ibfk_4` FOREIGN KEY (`approved_employee_id`) REFERENCES `phppos_employees` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `phppos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `icon`, `module_id`) VALUES ('module_expenses', 'module_expenses_desc', 75, 'money', 'expenses');
INSERT INTO `phppos_permissions` (`module_id`, `person_id`) (SELECT 'expenses', person_id FROM phppos_permissions WHERE module_id = 'sales');

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('search', 'expenses', 'module_expenses_search', 310);
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('add_update', 'expenses', 'module_expenses_add_update', 315);
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('delete', 'expenses', 'module_expenses_delete', 330);

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_expenses', 'reports', 'module_expenses_report', 155);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'expenses' and
action_id = 'search'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'expenses' and
action_id = 'add_update'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'expenses' and
action_id = 'delete'
order by module_id, person_id;


INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_expenses'
order by module_id, person_id;

ALTER TABLE `phppos_locations` CHANGE `merchant_id` `hosted_checkout_merchant_id` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `phppos_locations` CHANGE `merchant_password` `hosted_checkout_merchant_password` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

ALTER TABLE `phppos_locations` ADD `credit_card_processor` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `enable_credit_card_processing`;
UPDATE `phppos_locations` SET credit_card_processor = 'mercury' WHERE enable_credit_card_processing = '1';
ALTER TABLE `phppos_locations` ADD `emv_merchant_id` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `hosted_checkout_merchant_password`, ADD `listener_port` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `emv_merchant_id`, ADD `com_port` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `listener_port`, ADD `stripe_public` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `com_port`, ADD `stripe_private` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `stripe_public`, ADD `stripe_currency_code` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `stripe_private`, ADD `braintree_merchant_id` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `stripe_currency_code`, ADD `braintree_public_key` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `braintree_merchant_id`, ADD `braintree_private_key` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `braintree_public_key`;

ALTER TABLE `phppos_customers` ADD `tax_certificate` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' AFTER `taxable`;


INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('manage_categories', 'items', 'items_manage_categories', 70);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'items' and
action_id = 'manage_categories'
order by module_id, person_id;


INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('manage_tags', 'items', 'items_manage_tags', 75);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'items' and
action_id = 'manage_tags'
order by module_id, person_id;

ALTER TABLE `phppos_employees` ADD `inactive` INT(1) NOT NULL DEFAULT '0' AFTER `hourly_pay_rate`, ADD `reason_inactive` TEXT NULL DEFAULT NULL AFTER `inactive`, ADD `hire_date` DATE NULL DEFAULT NULL AFTER `reason_inactive`, ADD `employee_number` VARCHAR(255) NULL DEFAULT NULL AFTER `hire_date`, ADD `birthday` DATE NULL DEFAULT NULL AFTER `employee_number`, ADD `termination_date` DATE NULL DEFAULT NULL AFTER `birthday`;

ALTER TABLE `phppos_employees` ADD UNIQUE(`employee_number`);

CREATE TABLE `phppos_giftcards_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `giftcard_id` int(11) NOT NULL,
  `transaction_amount` decimal(23,10) NOT NULL,
  `log_message` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `phppos_giftcards_log_ibfk_1` FOREIGN KEY (`giftcard_id`) REFERENCES `phppos_giftcards` (`giftcard_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `phppos_giftcards` ADD `inactive` INT(1) NOT NULL DEFAULT '0' AFTER `customer_id`;
ALTER TABLE `phppos_giftcards` ADD `description` text COLLATE utf8_unicode_ci NOT NULL AFTER `giftcard_number`;

CREATE TABLE `phppos_sessions` (
  `id` varchar(40) NOT NULL,
  `ip_address` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` int(10) unsigned DEFAULT 0 NOT NULL,
  `data` LONGBLOB NOT NULL,
  PRIMARY KEY (id),
  KEY `phppos_sessions_timestamp` (`timestamp`)
)  ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_employees_reset_password` ( 
	`id` INT(10) NOT NULL AUTO_INCREMENT , 
	`key` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL , 
	`employee_id` INT NOT NULL , 
   `expire` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  	CONSTRAINT `phppos_employees_reset_password_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
	PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_inventory_at_all_locations', 'reports', 'reports_view_inventory_at_all_locations', 300);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_inventory_at_all_locations'
order by module_id, person_id;



CREATE TABLE `phppos_registers_cart` ( 
	`id` INT(10) NOT NULL AUTO_INCREMENT , 
	`register_id` INT NOT NULL , 
	`data` LONGBLOB NOT NULL,
   UNIQUE KEY `register_id` (register_id),
   CONSTRAINT `phppos_registers_cart_ibfk_1` FOREIGN KEY (`register_id`) REFERENCES `phppos_registers` (`register_id`),
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `phppos_sales_payments` ADD `auth_code` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT '' AFTER `payment_amount`;
ALTER TABLE `phppos_sales_payments` ADD `ref_no` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT '' AFTER `auth_code`;
ALTER TABLE `phppos_sales_payments` ADD `cc_token` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT '' AFTER `ref_no`;
ALTER TABLE `phppos_sales_payments` ADD `acq_ref_data` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT '' AFTER `cc_token`;
ALTER TABLE `phppos_sales_payments` ADD `process_data` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT '' AFTER `acq_ref_data`;
ALTER TABLE `phppos_sales_payments` ADD `entry_method` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT '' AFTER `process_data`;
ALTER TABLE `phppos_sales_payments` ADD `aid` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT '' AFTER `entry_method`;
ALTER TABLE `phppos_sales_payments` ADD `tvr` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT '' AFTER `aid`;
ALTER TABLE `phppos_sales_payments` ADD `iad` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT '' AFTER `tvr`;
ALTER TABLE `phppos_sales_payments` ADD `tsi` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT '' AFTER `iad`;
ALTER TABLE `phppos_sales_payments` ADD `arc` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT '' AFTER `tsi`;
ALTER TABLE `phppos_sales_payments` ADD `cvm` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT '' AFTER `arc`;
ALTER TABLE `phppos_sales_payments` ADD `tran_type` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT '' AFTER `cvm`;
ALTER TABLE `phppos_sales_payments` ADD `application_label` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT '' AFTER `tran_type`;


ALTER TABLE `phppos_items` ADD `commission_percent_type`  VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT '' AFTER `commission_percent`;
ALTER TABLE `phppos_item_kits` ADD `commission_percent_type` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT '' AFTER `commission_percent`;
ALTER TABLE `phppos_employees` ADD `commission_percent_type` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT '' AFTER `commission_percent`;

UPDATE `phppos_items` SET commission_percent_type = 'selling_price' WHERE commission_percent IS NOT NULL;
UPDATE `phppos_item_kits` SET commission_percent_type = 'selling_price' WHERE commission_percent IS NOT NULL;
UPDATE `phppos_employees` SET commission_percent_type = 'selling_price' WHERE commission_percent IS NOT NULL;


ALTER TABLE `phppos_items` ADD `change_cost_price` INT(1) NOT NULL DEFAULT '0' AFTER `commission_fixed`;
ALTER TABLE `phppos_item_kits` ADD `change_cost_price` INT(1) NOT NULL DEFAULT '0' AFTER `commission_fixed`;


INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`)
VALUES ('edit_sale_cost_price', 'sales', 'module_edit_sale_cost_price', 175);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'sales' and
action_id = 'edit_sale_cost_price'
order by module_id, person_id;


ALTER TABLE `phppos_price_tiers` ADD `order` INT(10) NOT NULL DEFAULT '0' AFTER `id`;

UPDATE `phppos_price_tiers` SET `order` = `id`;


ALTER TABLE `phppos_suppliers` ADD `override_default_tax` int(1) NOT NULL DEFAULT '0' AFTER `account_number`;

CREATE TABLE `phppos_suppliers_taxes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(15,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tax` (`supplier_id`,`name`,`percent`),
  CONSTRAINT `phppos_suppliers_taxes_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `phppos_suppliers` (`person_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `phppos_customers` ADD `override_default_tax` int(1) NOT NULL DEFAULT '0' AFTER `account_number`;

CREATE TABLE `phppos_customers_taxes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(15,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tax` (`customer_id`,`name`,`percent`),
  CONSTRAINT `phppos_customers_taxes_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `phppos_customers` (`person_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `phppos_customers` ADD `points` decimal(23,10) NOT NULL DEFAULT '0.0000000000' AFTER `credit_limit`;
ALTER TABLE `phppos_customers` ADD `current_spend_for_points` decimal(23,10) NOT NULL DEFAULT '0.0000000000' AFTER `points`;
ALTER TABLE `phppos_customers` ADD `current_sales_for_discount` int(10) NOT NULL DEFAULT '0' AFTER `current_spend_for_points`;


INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`)
VALUES ('edit_customer_points', 'customers', 'module_edit_customer_points', 35);


INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'customers' and
action_id = 'edit_customer_points'
order by module_id, person_id;


ALTER TABLE `phppos_sales` ADD `points_used` decimal(23,10) NOT NULL DEFAULT '0.0000000000' AFTER `tier_id`;
ALTER TABLE `phppos_sales` ADD `points_gained` decimal(23,10) NOT NULL DEFAULT '0.0000000000' AFTER `points_used`;
ALTER TABLE `phppos_sales` ADD `did_redeem_discount` int(1) NOT NULL DEFAULT '0' AFTER `points_gained`;


--
-- `Giftcard detection has changed to support both track 1 and track 2; so we need to remove leading ; if giftcard detection is enabled so swiping still works
--
UPDATE phppos_giftcards SET giftcard_number = TRIM(LEADING ';' FROM giftcard_number) 
WHERE ( SELECT count(*) FROM phppos_app_config 
        WHERE `key` = 'disable_giftcard_detection' and `value` = '0'
      ) = 1;

-- Allow for larger payment type field based on new span tag + replace old html char
ALTER TABLE `phppos_sales` CHANGE `payment_type` `payment_type` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
UPDATE phppos_sales SET payment_type = REPLACE(payment_type, '&#8209;','<span style="white-space:nowrap;">-</span>');

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`)
VALUES ('edit_giftcard_value', 'giftcards', 'module_edit_giftcard_value', 205);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'giftcards' and
action_id = 'edit_giftcard_value'
order by module_id, person_id;

REPLACE INTO `phppos_app_config` (`key`, `value`) VALUES ('legacy_search_method', '1');
REPLACE INTO `phppos_app_config` (`key`, `value`) VALUES ('disable_quick_complete_sale', '1');	
REPLACE INTO `phppos_app_config` (`key`, `value`) VALUES ('hide_test_mode_home', '1');	
REPLACE INTO `phppos_app_config` (`key`, `value`) VALUES ('version', '15.0');

/*!50604	ALTER TABLE `phppos_expenses` ADD FULLTEXT INDEX `full_search` (`expense_type`,`expense_description`,`expense_reason`,`expense_note`)*/;	
/*!50604	ALTER TABLE `phppos_expenses` ADD FULLTEXT INDEX `expense_type_search` (`expense_type`)*/;
/*!50604	ALTER TABLE `phppos_expenses` ADD FULLTEXT INDEX `expense_description_search` (`expense_description`)*/;
/*!50604	ALTER TABLE `phppos_expenses` ADD FULLTEXT INDEX `expense_reason_search` (`expense_reason`)*/;
/*!50604	ALTER TABLE `phppos_expenses` ADD FULLTEXT INDEX `expense_note_search` (`expense_note`)*/;

-- Full text indexes --
/*!50604	ALTER TABLE `phppos_locations` ADD FULLTEXT INDEX full_search (`name`, `address`, `phone`, `email`)*/;	
/*!50604	ALTER TABLE `phppos_locations` ADD FULLTEXT INDEX name_search (`name`)*/;
/*!50604	ALTER TABLE `phppos_locations` ADD FULLTEXT INDEX address_search (`address`)*/;
/*!50604	ALTER TABLE `phppos_locations` ADD FULLTEXT INDEX phone_search (`phone`)*/;
/*!50604	ALTER TABLE `phppos_locations` ADD FULLTEXT INDEX email_search (`email`)*/;

/*!50604	ALTER TABLE `phppos_tags` ADD FULLTEXT INDEX name_search (`name`)*/;		

/*!50604	ALTER TABLE `phppos_item_kits` DROP INDEX `name`*/;
/*!50604	ALTER TABLE `phppos_item_kits` ADD FULLTEXT INDEX full_search (`name`, `item_kit_number`, `product_id`, `description`)*/;	
/*!50604	ALTER TABLE `phppos_item_kits` ADD FULLTEXT INDEX name_search (`name`)*/;
/*!50604	ALTER TABLE `phppos_item_kits` ADD FULLTEXT INDEX item_kit_number_search (`item_kit_number`)*/;
/*!50604	ALTER TABLE `phppos_item_kits` ADD FULLTEXT INDEX product_id_search (`product_id`)*/;
/*!50604	ALTER TABLE `phppos_item_kits` ADD FULLTEXT INDEX description_search (`description`)*/;


/*!50604 ALTER TABLE `phppos_categories` ADD FULLTEXT INDEX name_search (`name`)*/;

/*!50604 ALTER TABLE `phppos_additional_item_numbers` ADD FULLTEXT INDEX item_number_search (`item_number`)*/;		

/*!50604	ALTER TABLE `phppos_items` DROP INDEX `name`*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX full_search (`name`, `item_number`, `product_id`, `description`)*/;	
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX name_search (`name`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX item_number_search (`item_number`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX product_id_search (`product_id`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX description_search (`description`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX size_search (`size`)*/;

/*!50604	ALTER TABLE `phppos_suppliers` ADD FULLTEXT INDEX full_search (`account_number`, `company_name`)*/;	
/*!50604	ALTER TABLE `phppos_suppliers` ADD FULLTEXT INDEX company_name_search (`company_name`)*/;
/*!50604	ALTER TABLE `phppos_suppliers` ADD FULLTEXT INDEX account_number_search (`account_number`)*/;

/*!50604	ALTER TABLE `phppos_giftcards` ADD FULLTEXT INDEX giftcard_number_search (`description`,`giftcard_number`)*/;

/*!50604	ALTER TABLE `phppos_people` DROP INDEX `first_name`*/;
/*!50604	ALTER TABLE `phppos_people` DROP INDEX `last_name`*/;
/*!50604	ALTER TABLE `phppos_people` DROP INDEX `email`*/;
/*!50604	ALTER TABLE `phppos_people` ADD FULLTEXT INDEX full_search (`first_name`, `last_name`, `email`, `phone_number`)*/;	
/*!50604	ALTER TABLE `phppos_people` ADD FULLTEXT INDEX first_name_search (`first_name`)*/;
/*!50604	ALTER TABLE `phppos_people` ADD FULLTEXT INDEX last_name_search (`last_name`)*/;
/*!50604	ALTER TABLE `phppos_people` ADD FULLTEXT INDEX full_name_search (`first_name`, `last_name`)*/;
/*!50604	ALTER TABLE `phppos_people` ADD FULLTEXT INDEX email_search (`email`)*/;
/*!50604	ALTER TABLE `phppos_people` ADD FULLTEXT INDEX phone_number_search (`phone_number`)*/;

/*!50604	ALTER TABLE `phppos_customers` ADD FULLTEXT INDEX full_search (`account_number`, `company_name`, `tax_certificate`)*/;	
/*!50604	ALTER TABLE `phppos_customers` ADD FULLTEXT INDEX account_number_search (`account_number`)*/;
/*!50604	ALTER TABLE `phppos_customers` ADD FULLTEXT INDEX company_name_search (`company_name`)*/;
/*!50604	ALTER TABLE `phppos_customers` ADD FULLTEXT INDEX tax_certificate_search(`tax_certificate`)*/;

/*!50604 ALTER TABLE `phppos_employees` ADD FULLTEXT INDEX username_search (`username`)*/;

/*!50604 REPLACE INTO `phppos_app_config` (`key`, `value`) VALUES ('supports_full_text', '1')*/;