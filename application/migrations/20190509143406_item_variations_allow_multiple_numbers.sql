-- item_variations_allow_multiple_numbers --

ALTER TABLE phppos_additional_item_numbers ADD COLUMN item_variation_id INT(11) NULL DEFAULT NULL,
ADD CONSTRAINT `phppos_additional_item_numbers_ibfk_2` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`);
