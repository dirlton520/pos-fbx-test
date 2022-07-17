-- locations_new_fields --

ALTER TABLE `phppos_locations` ADD `company` TEXT NULL AFTER `name`;
ALTER TABLE `phppos_locations` ADD `website` TEXT NULL AFTER `company`;
ALTER TABLE `phppos_locations` ADD `company_logo` int(10) DEFAULT NULL AFTER `website`;

ALTER TABLE phppos_locations 
    ADD CONSTRAINT `phppos_locations_ibfk_1` FOREIGN KEY (`company_logo`) REFERENCES `phppos_app_files` (`file_id`);