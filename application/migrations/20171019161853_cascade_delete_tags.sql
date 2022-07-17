-- cascade_delete_tags --


SET unique_checks=0; 
SET foreign_key_checks=0;
 
ALTER TABLE  `phppos_items_tags` DROP FOREIGN KEY `phppos_items_tags_ibfk_2`;
ALTER TABLE  `phppos_items_tags` ADD CONSTRAINT `phppos_items_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `phppos_tags` (`id`) ON DELETE CASCADE;
	
ALTER TABLE  `phppos_item_kits_tags` DROP FOREIGN KEY `phppos_item_kits_tags_ibfk_2`;
ALTER TABLE  `phppos_item_kits_tags` ADD CONSTRAINT `phppos_item_kits_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `phppos_tags` (`id`) ON DELETE CASCADE;
	
ALTER TABLE  `phppos_price_rules_tags` DROP FOREIGN KEY `phppos_price_rules_tags_ibfk_2`;
ALTER TABLE  `phppos_price_rules_tags` ADD CONSTRAINT `phppos_price_rules_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `phppos_tags` (`id`) ON DELETE CASCADE;
DELETE FROM `phppos_tags` where `deleted` = '1';

SET unique_checks=1;
SET foreign_key_checks=1;