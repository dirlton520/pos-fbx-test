-- platform_ly --
ALTER TABLE phppos_locations ADD COLUMN `platformly_api_key` text COLLATE utf8_unicode_ci;
ALTER TABLE phppos_locations ADD COLUMN `platformly_project_id` text COLLATE utf8_unicode_ci;