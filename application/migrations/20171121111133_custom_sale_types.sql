-- custom_sale_types --
SET SESSION sql_mode="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE `phppos_sale_types` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name`  varchar(255) COLLATE utf8_unicode_ci NULL,
  `sort` int(10) NOT NULL,
  `system_sale_type` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO phppos_sale_types (`id`,`name`,`system_sale_type`,`sort`) VALUES(0,'common_sale',1,0);
INSERT INTO phppos_sale_types (`id`,`name`,`system_sale_type`,`sort`) VALUES(1,'common_layaway',1,0);
INSERT INTO phppos_sale_types (`id`,`name`,`system_sale_type`,`sort`) VALUES(2,'common_estimate',1,0);
ALTER TABLE `phppos_sales` CHANGE `suspended` `suspended` INT(10) NOT NULL DEFAULT '0';
ALTER TABLE `phppos_sales` ADD CONSTRAINT `phppos_sales_ibfk_10` FOREIGN KEY (`suspended`) REFERENCES `phppos_sale_types` (`id`);
