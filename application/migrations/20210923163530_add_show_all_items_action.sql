-- add_show_all_items_action --
INSERT INTO phppos_modules_actions (action_id, module_id, action_name_key, sort) VALUES ('see_all_items', 'items', 'common_see_all_items', 504);
INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'items' and
action_id = 'see_all_items'
order by module_id, person_id;

INSERT INTO phppos_modules_actions (action_id, module_id, action_name_key, sort) VALUES ('see_all_item_kits', 'item_kits', 'common_see_all_item_kits', 505);
INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'item_kits' and
action_id = 'see_all_item_kits'
order by module_id, person_id;
