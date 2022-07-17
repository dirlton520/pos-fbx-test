-- additional_item_numbers_change --

ALTER TABLE `phppos_additional_item_numbers`  DROP FOREIGN KEY `phppos_additional_item_numbers_ibfk_1`;
ALTER TABLE `phppos_additional_item_numbers` CHANGE `item_id` `item_id` INT(11) NOT NULL;
ALTER TABLE `phppos_additional_item_numbers`  ADD CONSTRAINT `phppos_additional_item_numbers_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`);