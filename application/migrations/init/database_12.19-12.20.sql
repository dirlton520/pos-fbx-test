
CREATE TABLE `phppos_modules_actions` (
`action_id` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
`module_id` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
`action_name_key` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
`sort` INT NOT NULL ,
PRIMARY KEY (  `action_id` ,  `module_id` )
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES
('add_update', 'customers', 'module_action_add_update', 1),
('add_update', 'employees', 'module_action_add_update', 130),
('add_update', 'item_kits', 'module_action_add_update', 70),
('add_update', 'items', 'module_action_add_update', 40),
('add_update', 'suppliers', 'module_action_add_update', 100),
('delete', 'customers', 'module_action_delete', 20),
('delete', 'employees', 'module_action_delete', 140),
('delete', 'item_kits', 'module_action_delete', 80),
('delete', 'items', 'module_action_delete', 50),
('delete', 'suppliers', 'module_action_delete', 110),
('search', 'customers', 'module_action_search_customers', 30),
('search', 'employees', 'module_action_search_employees', 150),
('search', 'item_kits', 'module_action_search_item_kits', 90),
('search', 'items', 'module_action_search_items', 60),
('search', 'suppliers', 'module_action_search_suppliers', 120),
('see_cost_price', 'items', 'module_see_cost_price', 61),
('edit_sale_price', 'sales', 'module_edit_sale_price', 170);

CREATE TABLE `phppos_permissions_actions` (
`module_id` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
`person_id` INT NOT NULL ,
`action_id` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
PRIMARY KEY (  `module_id` ,  `person_id` ,  `action_id` )
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

ALTER TABLE `phppos_modules_actions`
	ADD CONSTRAINT `phppos_modules_actions_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `phppos_modules` (`module_id`);

ALTER TABLE `phppos_permissions_actions`
  ADD CONSTRAINT `phppos_permissions_actions_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `phppos_modules` (`module_id`),
  ADD CONSTRAINT `phppos_permissions_actions_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `phppos_employees` (`person_id`),
  ADD CONSTRAINT `phppos_permissions_actions_ibfk_3` FOREIGN KEY (`action_id`) REFERENCES `phppos_modules_actions` (`action_id`);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
order by module_id, person_id;