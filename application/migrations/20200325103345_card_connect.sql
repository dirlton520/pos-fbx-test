-- card_connect --

ALTER TABLE `phppos_locations` ADD `card_connect_mid` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `phppos_locations` ADD `card_connect_rest_username` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `phppos_locations` ADD `card_connect_rest_password` VARCHAR(255) NULL DEFAULT NULL;

ALTER TABLE `phppos_registers` ADD `card_connect_hsn` VARCHAR(255) NULL DEFAULT NULL;

ALTER TABLE `phppos_customers` ADD `cc_expire` VARCHAR(255) NULL DEFAULT NULL AFTER `cc_token`;
