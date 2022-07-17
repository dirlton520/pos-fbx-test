CREATE TABLE `phppos_all_bitrix_items` ( `id` int(11) NOT NULL AUTO_INCREMENT, `item_id` int(10) NOT NULL, `parent_id` int(10) NOT NULL, `type` int(10) NOT NULL, `title` text COLLATE utf8_unicode_ci NOT NULL, `price` text COLLATE utf8_unicode_ci NULL, `currency_code` text COLLATE utf8_unicode_ci NULL, `preview_image` text COLLATE utf8_unicode_ci NULL, `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; 