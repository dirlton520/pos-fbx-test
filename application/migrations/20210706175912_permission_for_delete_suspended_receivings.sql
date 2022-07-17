-- permission_for_delete_suspended_receivings --
INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`)     VALUES ('delete_suspended_receiving', 'receivings', 'module_action_delete_suspended_receiving', 181);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
FROM phppos_permissions
INNER JOIN phppos_modules_actions ON phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'receivings' AND
action_id = 'delete_suspended_receiving'
ORDER BY module_id, person_id;