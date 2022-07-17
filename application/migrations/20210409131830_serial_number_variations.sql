-- serial_number_variations --

ALTER TABLE phppos_items_serial_numbers ADD COLUMN variation_id INT(11) NULL DEFAULT NULL,
ADD CONSTRAINT `phppos_items_serial_numbers_ibfk_2` FOREIGN KEY (`variation_id`) REFERENCES `phppos_item_variations` (`id`);
