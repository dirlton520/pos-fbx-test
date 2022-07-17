-- add_expenses_image_field --
ALTER TABLE `phppos_expenses` ADD `expense_image_id` INT(11) NULL AFTER `expense_payment_type`;
ALTER TABLE `phppos_expenses` ADD INDEX(`expense_image_id`);
ALTER TABLE `phppos_expenses` ADD FOREIGN KEY (`expense_image_id`) REFERENCES `phppos_app_files`(`file_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
