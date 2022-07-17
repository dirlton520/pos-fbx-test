
ALTER TABLE phppos_sessions CHANGE ip_address ip_address varchar(45) default '0' NOT NULL;
ALTER TABLE `phppos_items` ADD `promo_price` DECIMAL( 15, 2 ) NOT NULL DEFAULT '0' AFTER `unit_price` ,
ADD `start_date` DATE NOT NULL DEFAULT '1969-01-01' AFTER `promo_price` ,
ADD `end_date` DATE NOT NULL DEFAULT '1969-01-01' AFTER `start_date` ;