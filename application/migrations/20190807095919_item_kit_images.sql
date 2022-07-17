-- item_kit_images --

CREATE TABLE `phppos_item_kit_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `alt_text` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `item_kit_id` int(11) DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `phppos_item_kit_images_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`),
  CONSTRAINT `phppos_item_kit_images_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `phppos_app_files` (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `phppos_item_kits` ADD COLUMN `main_image_id` INT(10) NULL DEFAULT NULL, ADD CONSTRAINT `phppos_item_kits_ibfk_4` FOREIGN KEY (`main_image_id`) REFERENCES `phppos_item_kit_images` (`image_id`);