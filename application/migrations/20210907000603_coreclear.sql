-- coreclear --
ALTER TABLE phppos_locations 
ADD `blockchyp_api_key` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
ADD `blockchyp_bearer_token` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
ADD `blockchyp_signing_key` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
ADD `blockchyp_test_mode` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL;

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_edit_transaction_history', 'sales', 'common_view_edit_transaction_history', 400);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'sales' and
action_id = 'view_edit_transaction_history'
order by module_id, person_id;