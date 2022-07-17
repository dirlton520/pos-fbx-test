-- mutliple_currencies_for_receivings --
ALTER TABLE `phppos_receivings` 
	ADD COLUMN `exchange_rate` decimal(23,10)   NOT NULL DEFAULT 1.0000000000 after `profit` , 
	ADD COLUMN `exchange_name` varchar(255)  COLLATE utf8_unicode_ci NOT NULL DEFAULT '' after `exchange_rate` , 
	ADD COLUMN `exchange_currency_symbol` varchar(255)  COLLATE utf8_unicode_ci NOT NULL DEFAULT '' after `exchange_name` , 
	ADD COLUMN `exchange_currency_symbol_location` varchar(255)  COLLATE utf8_unicode_ci NOT NULL DEFAULT '' after `exchange_currency_symbol` , 
	ADD COLUMN `exchange_number_of_decimals` varchar(255)  COLLATE utf8_unicode_ci NOT NULL DEFAULT '' after `exchange_currency_symbol_location` , 
	ADD COLUMN `exchange_thousands_separator` varchar(255)  COLLATE utf8_unicode_ci NOT NULL DEFAULT '' after `exchange_number_of_decimals` , 
	ADD COLUMN `exchange_decimal_point` varchar(255)  COLLATE utf8_unicode_ci NOT NULL DEFAULT '' after `exchange_thousands_separator` , 
	CHANGE `custom_field_1_value` `custom_field_1_value` varchar(255)  COLLATE utf8_unicode_ci NULL after `exchange_decimal_point` ; 