-- add_always_sms_receipt_field_to_customers --
ALTER TABLE `phppos_customers` ADD COLUMN `always_sms_receipt` INT(1) NOT NULL DEFAULT '0' AFTER `auto_email_receipt`;