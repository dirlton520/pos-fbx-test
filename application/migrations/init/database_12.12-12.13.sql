
ALTER TABLE  `phppos_giftcards` ADD  `customer_id` INT( 10 ) NULL DEFAULT NULL AFTER  `value`;
ALTER TABLE `phppos_giftcards`
  ADD CONSTRAINT `phppos_giftcards_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `phppos_customers` (`person_id`);

CREATE TABLE  `phppos_app_files` (
`file_id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`file_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
`file_data` LONGBLOB NOT NULL
) ENGINE = INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;