-- track_delivery_person --


ALTER TABLE  `phppos_sales_deliveries` ADD COLUMN `delivery_employee_person_id` int(10) NULL DEFAULT NULL AFTER `shipping_address_person_id`,ADD CONSTRAINT `phppos_sales_deliveries_ibfk_6` FOREIGN KEY (`delivery_employee_person_id`) REFERENCES `phppos_employees` (`person_id`);
