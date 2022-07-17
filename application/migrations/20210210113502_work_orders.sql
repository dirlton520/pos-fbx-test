-- work_orders --

INSERT INTO `phppos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `icon`, `module_id`) VALUES
('module_work_orders', 'module_work_orders_desc', 71, 'ion-hammer', 'work_orders');

INSERT INTO `phppos_permissions` (`module_id`, `person_id`) (SELECT 'work_orders', person_id FROM phppos_permissions WHERE module_id = 'sales');


INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('edit', 'work_orders', 'work_orders_edit', 240);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'work_orders' and
action_id = 'edit'
order by module_id, person_id;

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('delete', 'work_orders', 'work_orders_delete', 241);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'work_orders' and
action_id = 'delete'
order by module_id, person_id;

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('search', 'work_orders', 'work_orders_search', 242);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'work_orders' and
action_id = 'search'
order by module_id, person_id;


INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('manage_statuses', 'work_orders', 'work_orders_manage_statuses', 243);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'work_orders' and
action_id = 'manage_statuses'
order by module_id, person_id;

CREATE TABLE `phppos_workorder_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `notify_by_email` TINYINT(1) DEFAULT '0',
  `notify_by_sms` TINYINT(1) DEFAULT '0',
  `color` text,
  `sort_order` int(11) DEFAULT '0',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;


INSERT INTO `phppos_workorder_statuses` (`id`, `name`, `color`, `sort_order`, `last_modified`) VALUES
(1, 'lang:work_orders_new', '#4594cc', 10, '2020-07-09 05:32:32'),
(2, 'lang:work_orders_in_progress', '#28a745', 20, '2020-07-09 05:32:47'),
(3, 'lang:work_orders_out_for_repair', '#f7ac08', 30, '2020-07-09 05:32:54'),
(4, 'lang:work_orders_waiting_on_customer', '#6a0dad', 40, '2020-07-09 05:33:01'),
(5, 'lang:work_orders_repaired', '#006400', 50, '2020-07-09 05:33:09'),
(6, 'lang:work_orders_complete', '#28a745', 60, '2020-07-09 05:33:17'),
(7, 'lang:work_orders_cancelled', '#fb5d5d', 70, '2020-07-09 05:33:34');


CREATE TABLE `phppos_sales_work_orders` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`sale_id` INT(10) NOT NULL,
	`status` INT(11) NULL DEFAULT '1',
	`employee_id` int(11) DEFAULT NULL,
	`estimated_repair_date` TIMESTAMP NULL DEFAULT NULL,
	`estimated_parts` DECIMAL(23,10) DEFAULT NULL,
	`estimated_labor` DECIMAL(23,10) DEFAULT NULL,
	`warranty` TINYINT(1) DEFAULT NULL,
	`custom_field_1_value` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
	`custom_field_2_value` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
	`custom_field_3_value` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
	`custom_field_4_value` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
	`custom_field_5_value` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
	`custom_field_6_value` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
	`custom_field_7_value` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
	`custom_field_8_value` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
	`custom_field_9_value` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
	`custom_field_10_value` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
	`comment` TEXT COLLATE utf8_unicode_ci NULL,
	`images` TEXT COLLATE utf8_unicode_ci,
	`deleted` INT(1) DEFAULT '0',
	  PRIMARY KEY (`id`),
	  KEY `custom_field_1_value` (`custom_field_1_value`),
	  KEY `custom_field_2_value` (`custom_field_2_value`),
	  KEY `custom_field_3_value` (`custom_field_3_value`),
	  KEY `custom_field_4_value` (`custom_field_4_value`),
	  KEY `custom_field_5_value` (`custom_field_5_value`),
	  KEY `custom_field_6_value` (`custom_field_6_value`),
	  KEY `custom_field_7_value` (`custom_field_7_value`),
	  KEY `custom_field_8_value` (`custom_field_8_value`),
	  KEY `custom_field_9_value` (`custom_field_9_value`),
	  KEY `custom_field_10_value` (`custom_field_10_value`),
	CONSTRAINT `phppos_sales_work_orders_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
	CONSTRAINT `phppos_sales_work_orders_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
	CONSTRAINT `phppos_sales_work_orders_ibfk_3` FOREIGN KEY (`status`) REFERENCES `phppos_workorder_statuses` (`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `phppos_sales_items_notes` (
note_id int(11) NOT NULL AUTO_INCREMENT,
note_timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
sale_id int(10) NOT NULL,
item_id int(10) NOT NULL,
line int(10) NOT NULL DEFAULT '0',
item_variation_id int(10) DEFAULT NULL,
note varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
detailed_notes text COLLATE utf8_unicode_ci,
internal tinyint(10) DEFAULT NULL,
employee_id int(10) NOT NULL,
images text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
PRIMARY KEY (note_id),
KEY phppos_sales_items_notes_ibfk_1 (sale_id),
KEY phppos_sales_items_notes_ibfk_2 (item_id),
KEY phppos_sales_items_notes_ibfk_3 (employee_id),
CONSTRAINT phppos_sales_items_notes_ibfk_1 FOREIGN KEY (sale_id) REFERENCES phppos_sales (sale_id),
CONSTRAINT phppos_sales_items_notes_ibfk_2 FOREIGN KEY (item_id) REFERENCES phppos_items (item_id),
CONSTRAINT phppos_sales_items_notes_ibfk_3 FOREIGN KEY (employee_id) REFERENCES phppos_employees (person_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
