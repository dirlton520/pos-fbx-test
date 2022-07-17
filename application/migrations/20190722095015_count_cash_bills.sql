-- count_cash_bills --
CREATE TABLE `phppos_register_log_denoms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `register_log_id` int(11) NOT NULL,
  `register_currency_denominations_id` int(11) NOT NULL,
  `count` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `phppos_register_log_denoms_ibfk_1` FOREIGN KEY (`register_log_id`) REFERENCES `phppos_register_log` (`register_log_id`),
  CONSTRAINT `phppos_register_log_denoms_ibfk_2` FOREIGN KEY (`register_currency_denominations_id`) REFERENCES `phppos_register_currency_denominations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `phppos_register_currency_denominations` ADD COLUMN deleted int(1) DEFAULT '0';