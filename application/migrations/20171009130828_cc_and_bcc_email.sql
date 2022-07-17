-- cc_and_bcc_email --


ALTER TABLE `phppos_locations` ADD `cc_email` TEXT NULL AFTER `email`;
ALTER TABLE `phppos_locations` ADD `bcc_email` TEXT NULL AFTER `cc_email`;