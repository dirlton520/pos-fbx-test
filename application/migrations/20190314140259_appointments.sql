-- appointments --
	
INSERT INTO `phppos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `icon`, `module_id`) VALUES
('module_appointments', 'module_appointments_desc', 75, 'ti-calendar', 'appointments');

INSERT INTO `phppos_permissions` (`module_id`, `person_id`) (SELECT 'appointments', person_id FROM phppos_permissions WHERE module_id = 'sales');

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('add', 'appointments', 'appointments_add', 240);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'appointments' and
action_id = 'add'
order by module_id, person_id;

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('edit', 'appointments', 'appointments_edit', 245);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'appointments' and
action_id = 'edit'
order by module_id, person_id;


INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('delete', 'appointments', 'appointments_delete', 250);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'appointments' and
action_id = 'delete'
order by module_id, person_id;

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('search', 'appointments', 'appointments_search', 255);

INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'appointments' and
action_id = 'search'
order by module_id, person_id;

CREATE TABLE `phppos_appointment_types` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `phppos_appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_id` int(10) NOT NULL,
  `customer_id` int(10) NULL DEFAULT NULL,
  `employee_id` int(10) NULL DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `appointments_type_id` int(10) NOT NULL,
  `notes` text NOT NULL COLLATE utf8_unicode_ci DEFAULT '',
  `deleted` int(1) NOT NULL DEFAULT '0',
	CONSTRAINT `phppos_appointments_ibfk_1` FOREIGN KEY (`appointments_type_id`) REFERENCES `phppos_appointment_types` (`id`),
	CONSTRAINT `phppos_appointments_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `phppos_customers` (`person_id`),
	CONSTRAINT `phppos_appointments_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
	CONSTRAINT `phppos_appointments_ibfk_4` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `phppos_modules_actions` (`action_id`, `module_id`, `action_name_key`, `sort`) VALUES ('view_appointments', 'reports', 'reports_appointments', 95);
INSERT INTO phppos_permissions_actions (module_id, person_id, action_id)
SELECT DISTINCT phppos_permissions.module_id, phppos_permissions.person_id, action_id
from phppos_permissions
inner join phppos_modules_actions on phppos_permissions.module_id = phppos_modules_actions.module_id
WHERE phppos_permissions.module_id = 'reports' and
action_id = 'view_appointments'
order by module_id, person_id;
