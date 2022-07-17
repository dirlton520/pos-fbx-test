
ALTER TABLE `phppos_app_config` CHANGE `value` `value` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

ALTER TABLE `phppos_sales_payments`
ADD KEY (`sale_id`), 
DROP PRIMARY KEY,
ADD COLUMN `payment_id` int(10) NOT NULL AUTO_INCREMENT FIRST,
ADD PRIMARY KEY (`payment_id`),
ADD `payment_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

UPDATE `phppos_sales_payments` SET payment_date = (SELECT sale_time FROM `phppos_sales` WHERE sale_id = `phppos_sales_payments`.sale_id);

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`)     VALUES ('edit_sale', 'sales', 'module_edit_sale', 190);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'sales' and
action_id = 'edit_sale'
order by module_id, person_id;

ALTER TABLE `phppos_customers`  ADD `cc_token` VARCHAR(255) NULL AFTER `taxable`,  
ADD `cc_preview` VARCHAR(255) NULL AFTER `cc_token`,  ADD INDEX (`cc_token`);

REPLACE INTO `phppos_app_config` (`key`, `value`) VALUES ('version', '13.1');

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`)     VALUES ('give_discount', 'sales', 'module_give_discount', 180);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'sales' and
action_id = 'give_discount'
order by module_id, person_id;

ALTER TABLE  `phppos_employees` CHANGE  `username`  `username` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;