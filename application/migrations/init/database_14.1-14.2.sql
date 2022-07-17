
REPLACE INTO `phppos_app_config` (`key`, `value`) VALUES ('version', '14.2');
UPDATE phppos_sales SET `deleted_by` = NULL WHERE deleted = 0;
UPDATE phppos_receivings SET `deleted_by` = NULL WHERE deleted = 0;

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`)
VALUES ('delete_taxes', 'sales', 'module_action_delete_taxes', 182);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'sales' and
action_id = 'delete_taxes'
order by module_id, person_id;

UPDATE phppos_app_config SET `key` = 'hide_layaways_sales_in_reports' WHERE `key` = 'hide_suspended_sales_in_reports';
ALTER TABLE  `phppos_items` ADD  `size` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `description`;