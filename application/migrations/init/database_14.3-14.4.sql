
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_suspended_sales', 'reports', 'reports_suspended_sales', 261);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_suspended_sales'
order by module_id, person_id;

CREATE TABLE `phppos_registers` (
  `register_id`int(11) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`register_id`),
  KEY `deleted` (`deleted`),
  CONSTRAINT `phppos_registers_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create a default register for each location
INSERT INTO `phppos_registers` (`location_id`, `name`)  (SELECT location_id, 'Default' FROM `phppos_locations`);
ALTER TABLE `phppos_sales` ADD COLUMN `register_id`  INT(11) NULL DEFAULT NULL AFTER `location_id`;
ALTER TABLE `phppos_sales`ADD CONSTRAINT `phppos_sales_ibfk_7` FOREIGN KEY (`register_id`) REFERENCES `phppos_registers` (`register_id`);

ALTER TABLE `phppos_register_log` ADD COLUMN `register_id`  INT(11) NULL DEFAULT NULL AFTER `employee_id`;
ALTER TABLE `phppos_register_log`ADD CONSTRAINT `phppos_register_log_ibfk_2` FOREIGN KEY (`register_id`) REFERENCES `phppos_registers` (`register_id`);
ALTER TABLE phppos_register_log DROP FOREIGN KEY phppos_register_log_ibfk_1;
ALTER TABLE  `phppos_register_log` CHANGE  `employee_id`  `employee_id_open` INT( 10 ) NOT NULL ;
ALTER TABLE `phppos_register_log`ADD CONSTRAINT `phppos_register_log_ibfk_1` FOREIGN KEY (`employee_id_open`) REFERENCES `phppos_employees` (`person_id`);

ALTER TABLE `phppos_register_log` ADD COLUMN `employee_id_close`  INT(11) NULL DEFAULT NULL AFTER `employee_id_open`;

ALTER TABLE `phppos_register_log`ADD CONSTRAINT `phppos_register_log_ibfk_3` FOREIGN KEY (`employee_id_close`) REFERENCES `phppos_employees` (`person_id`);

UPDATE `phppos_register_log` SET employee_id_close = employee_id_open;

UPDATE `phppos_register_log` SET register_id = (SELECT register_id FROM phppos_registers INNER JOIN phppos_employees_locations ON  phppos_registers.location_id = phppos_employees_locations.location_id WHERE employee_id = employee_id_open ORDER BY phppos_employees_locations.location_id LIMIT 1);

CREATE TABLE `phppos_additional_item_numbers` (
  `item_id`int(11) NOT NULL AUTO_INCREMENT,
  `item_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`item_id`, `item_number`),
  UNIQUE KEY `item_number` (`item_number`),
  CONSTRAINT `phppos_additional_item_numbers_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `phppos_customers`
ADD COLUMN `id` int(10) NOT NULL AUTO_INCREMENT FIRST,
ADD PRIMARY KEY (`id`);

ALTER TABLE `phppos_employees`
ADD COLUMN `id` int(10) NOT NULL AUTO_INCREMENT FIRST,
ADD PRIMARY KEY (`id`);

ALTER TABLE `phppos_suppliers`
ADD COLUMN `id` int(10) NOT NULL AUTO_INCREMENT FIRST,
ADD PRIMARY KEY (`id`);

ALTER TABLE `phppos_sales` ADD  `sold_by_employee_id` INT( 10 ) NULL DEFAULT  NULL AFTER `employee_id`;
ALTER TABLE `phppos_sales` ADD CONSTRAINT `phppos_sales_ibfk_6` FOREIGN KEY (`sold_by_employee_id`) REFERENCES `phppos_employees` (`person_id`);
UPDATE `phppos_sales` SET sold_by_employee_id=employee_id;

ALTER TABLE `phppos_customers`
ADD COLUMN `credit_limit` decimal(23,10) DEFAULT NULL AFTER `balance`;

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_commissions', 'reports', 'reports_commission', 111);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_commissions'
order by module_id, person_id;

ALTER TABLE  `phppos_items` ADD  `commission_percent` DECIMAL( 23, 10 ) NULL DEFAULT  '0' AFTER  `is_service` ,
ADD  `commission_fixed` DECIMAL( 23, 10 ) NULL DEFAULT  '0' AFTER  `commission_percent` ;

ALTER TABLE  `phppos_item_kits` ADD  `commission_percent` DECIMAL( 23, 10 ) NULL DEFAULT  '0' AFTER  `override_default_tax` ,
ADD  `commission_fixed` DECIMAL( 23, 10 ) NULL DEFAULT  '0' AFTER  `commission_percent` ;

ALTER TABLE  `phppos_employees` ADD  `commission_percent` DECIMAL( 23, 10 ) NULL DEFAULT  '0' AFTER  `language`;

ALTER TABLE  `phppos_sales_items` ADD  `commission` DECIMAL( 23, 10 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `phppos_sales_item_kits` ADD  `commission` DECIMAL( 23, 10 ) NOT NULL DEFAULT  '0';

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('see_cost_price', 'item_kits', 'module_see_cost_price', 91);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'item_kits' and
action_id = 'see_cost_price'
order by module_id, person_id;

ALTER TABLE  `phppos_receivings` ADD  `transfer_to_location_id` INT( 11 ) NULL DEFAULT NULL ;
ALTER TABLE `phppos_receivings` ADD KEY (`transfer_to_location_id`);
ALTER TABLE `phppos_receivings` ADD CONSTRAINT `phppos_receivings_ibfk_4` FOREIGN KEY (`transfer_to_location_id`) REFERENCES `phppos_locations` (`location_id`);

REPLACE INTO `phppos_app_config` (`key`, `value`) VALUES ('version', '14.4');