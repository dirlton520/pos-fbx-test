-- count_other_payment_types --


CREATE TABLE `phppos_register_log_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `register_log_id` int(10) NOT NULL,
  `payment_type` varchar(255) NOT NULL,
  `open_amount` decimal(23,10) NOT NULL,
  `close_amount` decimal(23,10) NOT NULL,
  `payment_sales_amount` decimal(23,10) NOT NULL,
  `total_payment_additions` decimal(23,10) NOT NULL,
  `total_payment_subtractions` decimal(23,10) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `phppos_register_log_payments_ibfk_1` FOREIGN KEY (`register_log_id`) REFERENCES `phppos_register_log` (`register_log_id`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `phppos_register_log_payments` (`register_log_id`,`payment_type`,`open_amount`,`close_amount`,`payment_sales_amount`,`total_payment_additions`,`total_payment_subtractions`) SELECT register_log_id,'common_cash',open_amount, close_amount,cash_sales_amount,total_cash_additions,total_cash_subtractions FROM phppos_register_log;
  
ALTER TABLE `phppos_register_log_audit` ADD COLUMN `payment_type` varchar(255) NOT NULL;
UPDATE `phppos_register_log_audit` SET payment_type = 'common_cash';

ALTER TABLE `phppos_register_log` DROP COLUMN `open_amount`;
ALTER TABLE `phppos_register_log` DROP COLUMN `close_amount`;
ALTER TABLE `phppos_register_log` DROP COLUMN `cash_sales_amount`;
ALTER TABLE `phppos_register_log` DROP COLUMN `total_cash_additions`;
ALTER TABLE `phppos_register_log` DROP COLUMN `total_cash_subtractions`;