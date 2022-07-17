-- can_change_inventory_comment_permission --

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('can_edit_inventory_comment', 'items', 'items_can_edit_inventory_comment', 500);
INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'items' and
action_id = 'can_edit_inventory_comment'
order by module_id, person_id;
