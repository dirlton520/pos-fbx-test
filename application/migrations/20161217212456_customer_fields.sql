-- Customer fields --

ALTER TABLE `phppos_customers` ADD `cc_ref_no` VARCHAR(255) NULL DEFAULT NULL AFTER `cc_token`, ADD `disable_loyalty` int(1) NOT NULL DEFAULT '0' AFTER `points`;
-- Customer Fields --