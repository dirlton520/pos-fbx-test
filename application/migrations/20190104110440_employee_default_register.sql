-- employee_default_register --
CREATE TABLE `phppos_employee_registers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `register_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_employee_registers_ibfk_1` (`employee_id`),
  KEY `phppos_employee_registers_ibfk_2` (`register_id`),
  CONSTRAINT `phppos_employee_registers_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_employee_registers_ibfk_2` FOREIGN KEY (`register_id`) REFERENCES `phppos_registers` (`register_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;