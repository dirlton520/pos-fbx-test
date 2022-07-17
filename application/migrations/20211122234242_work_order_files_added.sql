-- work_order_files_added --
CREATE TABLE `phppos_work_order_files` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_id` INT(11) NOT NULL,
  `work_order_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`),
  KEY `work_order_id` (`work_order_id`),
  CONSTRAINT `phppos_work_order_files_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `phppos_app_files` (`file_id`),
  CONSTRAINT `phppos_work_order_files_ibfk_2` FOREIGN KEY (`work_order_id`) REFERENCES `phppos_sales_work_orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `phppos_work_order_files` ADD INDEX(`file_id`);
ALTER TABLE `phppos_work_order_files` ADD INDEX(`work_order_id`);
