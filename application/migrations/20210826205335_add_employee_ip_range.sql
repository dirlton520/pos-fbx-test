-- add_employee_ip_range --
ALTER TABLE `phppos_employees` ADD `allowed_ip_address` TEXT NULL DEFAULT NULL AFTER `override_price_adjustments`;
