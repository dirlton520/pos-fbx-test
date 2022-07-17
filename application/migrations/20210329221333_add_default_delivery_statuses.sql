-- add_default_delivery_sttauses --

INSERT INTO `phppos_delivery_statuses` (`id`, `name`, `description`, `color`, `last_modified`, `notify_by_email`, `notify_by_sms`) 
VALUES (1,'Not Scheduled', 'Not Scheduled', '#FF0179', NOW(), 1, 1),
(2,'Scheduled', 'Scheduled', '#02B085', NOW(), 1, 1),
(3,'Shipped', 'Shipped', '#0072C6', NOW(), 1, 1),
(4,'Delivered', 'Delivered', '#5F0082', NOW(), 1, 1);

-- update_existing_delivery_status_and_change_status_column_type_to_sales_deliveries --
UPDATE `phppos_sales_deliveries` SET `status` = 1 WHERE `status` = 'not_scheduled';
UPDATE `phppos_sales_deliveries` SET `status` = 2 WHERE `status` = 'scheduled';
UPDATE `phppos_sales_deliveries` SET `status` = 3 WHERE `status` = 'shipped';
UPDATE `phppos_sales_deliveries` SET `status` = 4 WHERE `status` = 'delivered';

SET FOREIGN_KEY_CHECKS = 0;
ALTER TABLE `phppos_sales_deliveries` CHANGE `status` `status` INT(30) NULL, ADD FOREIGN KEY (`status`) REFERENCES `phppos_delivery_statuses`(`id`);
SET FOREIGN_KEY_CHECKS = 1;