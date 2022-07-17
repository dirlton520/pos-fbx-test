-- login_time_restrictions --
ALTER TABLE `phppos_employees` ADD COLUMN `login_start_time` TIME DEFAULT NULL;
ALTER TABLE `phppos_employees` ADD COLUMN `login_end_time` TIME DEFAULT NULL;