-- link_2500_support_tran_cloud --

ALTER TABLE `phppos_registers` ADD `emv_pinpad_ip` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `phppos_registers` ADD `emv_pinpad_port` VARCHAR(255) NULL DEFAULT NULL;