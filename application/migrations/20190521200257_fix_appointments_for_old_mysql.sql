-- fix_appointments_for_old_mysql --

ALTER TABLE `phppos_appointments` CHANGE `start_time` `start_time` TIMESTAMP NULL DEFAULT NULL, CHANGE `end_time` `end_time` TIMESTAMP NULL DEFAULT NULL;