-- update_foregin_key_for_expenses_categories --
ALTER TABLE `phppos_expenses` DROP FOREIGN KEY `phppos_expenses_ibfk_3`;
ALTER TABLE `phppos_expenses` ADD CONSTRAINT `phppos_expenses_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `phppos_expenses_categories`(`id`); 