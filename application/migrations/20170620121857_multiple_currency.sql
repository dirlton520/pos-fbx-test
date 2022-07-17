-- multiple_currency --


CREATE TABLE `phppos_currency_exchange_rates` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `currency_code_to` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `currency_symbol` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `exchange_rate` decimal(23,10) NOT NULL,
  `currency_symbol_location` VARCHAR(255) NOT NULL DEFAULT '',
  `number_of_decimals` VARCHAR(255) NOT NULL DEFAULT '',
  `thousands_separator` VARCHAR(255) NOT NULL DEFAULT '',
  `decimal_point` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE phppos_sales 
ADD COLUMN exchange_rate decimal(23,10) NOT NULL DEFAULT 1, 
ADD COLUMN exchange_name VARCHAR(255) NOT NULL DEFAULT '',
ADD COLUMN exchange_currency_symbol VARCHAR(255) NOT NULL DEFAULT '',
ADD COLUMN exchange_currency_symbol_location VARCHAR(255) NOT NULL DEFAULT '',
ADD COLUMN exchange_number_of_decimals VARCHAR(255) NOT NULL DEFAULT '',
ADD COLUMN exchange_thousands_separator VARCHAR(255) NOT NULL DEFAULT '',
ADD COLUMN exchange_decimal_point VARCHAR(255) NOT NULL DEFAULT '';