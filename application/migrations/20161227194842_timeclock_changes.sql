-- timeclock_changes --

ALTER TABLE `phppos_employees` ADD `not_required_to_clock_in` INT(1) NOT NULL DEFAULT '0' AFTER `hourly_pay_rate`;
ALTER TABLE `phppos_employees_time_clock` ADD `ip_address_clock_in` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL, ADD `ip_address_clock_out` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL;