-- add_auto_emai_receipt_field_to_customers --
ALTER TABLE `phppos_customers` ADD COLUMN `auto_email_receipt` INT(1) NOT NULL DEFAULT '0' AFTER `customer_info_popup`;