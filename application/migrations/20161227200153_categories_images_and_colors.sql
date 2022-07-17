-- categories_images_and_colors --

ALTER TABLE `phppos_categories` ADD `image_id` int(10) DEFAULT NULL AFTER `name`;
ALTER TABLE `phppos_categories` ADD `color` text COLLATE utf8_unicode_ci AFTER `image_id`;

ALTER TABLE `phppos_categories`
	ADD CONSTRAINT `phppos_categories_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `phppos_app_files` (`file_id`);