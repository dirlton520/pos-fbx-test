-- allow_for_employee_display_column_customization_for_all_manage_tables --


CREATE TABLE `phppos_employees_app_config` (
  `employee_id` int(11) NOT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  CONSTRAINT `phppos_employees_app_config_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  PRIMARY KEY (`employee_id`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
