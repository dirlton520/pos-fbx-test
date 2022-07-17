
REPLACE INTO `phppos_app_config` (`key`, `value`) VALUES ('version', '14.3');
DROP TABLE `phppos_sessions`;

ALTER TABLE  `phppos_sales` ADD  `tier_id` INT( 10 ) NULL DEFAULT NULL;
ALTER TABLE `phppos_sales` ADD CONSTRAINT `phppos_sales_ibfk_5` FOREIGN KEY (`tier_id`) REFERENCES `phppos_price_tiers` (`id`);

-- --------------------------------------------
-- Granular permissions for reports---
-- --------------------------------------- ----
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('show_profit', 'reports', 'reports_show_profit', 280);
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('show_cost_price', 'reports', 'reports_show_cost_price', 290);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'show_profit'
order by module_id, person_id;

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'show_cost_price'
order by module_id, person_id;

ALTER TABLE  `phppos_receivings` ADD  `suspended` INT( 1 ) NOT NULL DEFAULT  '0' AFTER  `deleted_by`;

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('edit_store_account_balance', 'customers', 'customers_edit_store_account_balance', 31);
INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'customers' and
action_id = 'edit_store_account_balance'
order by module_id, person_id;

DELETE FROM `phppos_store_accounts` WHERE deleted = 1;
ALTER TABLE `phppos_store_accounts` DROP COLUMN `deleted`;