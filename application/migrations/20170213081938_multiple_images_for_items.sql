-- multiple_images_for_items --

CREATE TABLE `phppos_item_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `alt_text` VARCHAR(255) NOT NULL DEFAULT '',
  `item_id` int (11) NULL,
  `ecommerce_image_id` VARCHAR(255) NULL DEFAULT NULL,
  `image_id` int (11) NULL,
  CONSTRAINT `phppos_item_images_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_item_images_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `phppos_app_files` (`file_id`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------
--  Migrate item images.  ---
-- --------------------------

INSERT INTO phppos_item_images (item_id,image_id) 
SELECT item_id,image_id FROM phppos_items WHERE image_id is NOT NULL;

ALTER TABLE `phppos_items`  DROP FOREIGN KEY `phppos_items_ibfk_2`;

ALTER TABLE phppos_items DROP COLUMN image_id;