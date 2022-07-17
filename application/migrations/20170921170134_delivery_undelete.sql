-- delivery_undelete --

ALTER TABLE phppos_sales_deliveries ADD COLUMN `deleted` INT(1) default '0';
ALTER TABLE `phppos_sales_deliveries` ADD INDEX (  `deleted` );
