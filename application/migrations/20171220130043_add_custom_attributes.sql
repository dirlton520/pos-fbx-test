-- add_custom_attributes --


ALTER TABLE `phppos_attributes`
ADD `item_id` int(11) NULL DEFAULT NULL AFTER `id`,
ADD CONSTRAINT `phppos_attributes_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
DROP INDEX name, 
ADD UNIQUE KEY `name` (`item_id`,`name`);

