-- allow_price_overrides_regardless_of_permissions_main_image_only_integers --

ALTER TABLE `phppos_items` ADD COLUMN `allow_price_override_regardless_of_permissions` INT (1) DEFAULT '0',
ADD COLUMN `main_image_id` INT(10) NULL DEFAULT NULL,
ADD CONSTRAINT `phppos_items_ibfk_7` FOREIGN KEY (`main_image_id`) REFERENCES `phppos_item_images` (`image_id`),
ADD COLUMN `only_integer` INT(1) NOT NULL DEFAULT '0';

UPDATE phppos_items SET main_image_id = (SELECT image_id FROM phppos_item_images WHERE item_id = phppos_items.item_id ORDER BY id LIMIT 1);

ALTER TABLE `phppos_item_kits` ADD COLUMN `allow_price_override_regardless_of_permissions` INT (1) DEFAULT '0',
ADD COLUMN `only_integer` INT(1) NOT NULL DEFAULT '0';