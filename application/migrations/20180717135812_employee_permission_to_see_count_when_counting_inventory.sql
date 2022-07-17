-- employee_permission_to_see_count_when_counting_inventory --

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('see_count_when_count_inventory', 'items', 'items_see_count_when_count_inventory', 66);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'items' and
action_id = 'see_count_when_count_inventory'
order by module_id, person_id;
