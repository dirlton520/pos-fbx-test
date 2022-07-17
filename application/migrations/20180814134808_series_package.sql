-- series_package --

ALTER TABLE phppos_items ADD COLUMN `is_series_package` INT(1) NOT NULL DEFAULT '0', ADD COLUMN `series_quantity` INT(10) NULL DEFAULT NULL, ADD COLUMN `series_days_to_use_within` INT(10) NULL DEFAULT NULL;

CREATE TABLE `phppos_customers_series` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) NOT NULL,
  `item_id` int(1) NOT NULL DEFAULT '0',
  `expire_date` date DEFAULT NULL,
  `quantity_remaining` DECIMAL(23,10) DEFAULT 0,
  `customer_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `phppos_customers_series_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_customers_series_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `phppos_people` (`person_id`),
  CONSTRAINT `phppos_customers_series_ibfk_3` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `phppos_customers_series_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `series_id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `quantity_used` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `phppos_customers_series_log_ibfk_1` FOREIGN KEY (`series_id`) REFERENCES `phppos_customers_series` (`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE phppos_sales_items ADD COLUMN `series_id` INT(11) NULL DEFAULT NULL, ADD CONSTRAINT `phppos_sales_items_ibfk_5` FOREIGN KEY (`series_id`) REFERENCES `phppos_customers_series` (`id`);
