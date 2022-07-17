-- timeclock_enhancements --

CREATE TABLE `phppos_employees_time_off` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `approved` int(1) NOT NULL DEFAULT '0',
  `start_day` date DEFAULT NULL,
  `end_day` date DEFAULT NULL,
  `hours_requested` DECIMAL(23,10) DEFAULT 0,
  `is_paid` int(1) NOT NULL DEFAULT '0',
  `reason` VARCHAR(255) NULL DEFAULT NULL,
  `employee_requested_person_id` int(10) NULL DEFAULT NULL,
  `employee_requested_location_id` int(10) NULL DEFAULT NULL,
  `employee_approved_person_id` int(10) NULL DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  CONSTRAINT `phppos_employees_time_off_ibfk_1` FOREIGN KEY (`employee_requested_person_id`) REFERENCES `phppos_people` (`person_id`),
  CONSTRAINT `phppos_employees_time_off_ibfk_2` FOREIGN KEY (`employee_approved_person_id`) REFERENCES `phppos_people` (`person_id`),
  CONSTRAINT `phppos_employees_time_off_ibfk_3` FOREIGN KEY (`employee_requested_location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;