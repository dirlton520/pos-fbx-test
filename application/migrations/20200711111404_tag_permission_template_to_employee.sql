-- tag_pemission_template_to_employee --
ALTER TABLE phppos_employees ADD COLUMN `template_id` INT(11) DEFAULT NULL;
ALTER TABLE phppos_employees ADD  INDEX template_id (`template_id`);