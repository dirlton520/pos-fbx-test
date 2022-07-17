-- remove_fulltext_from_most_entities --

-- DROP FULL TEXT INDEXES NOT NEEDED (Replace with Btree index where needed)-- (mysql 5.6)

/*!50604	ALTER TABLE `phppos_customers` DROP INDEX full_search*/;
/*!50604	ALTER TABLE `phppos_customers` DROP INDEX account_number_search*/;
/*!50604	ALTER TABLE `phppos_customers` DROP INDEX company_name_search*/;
/*!50604	ALTER TABLE `phppos_customers` DROP INDEX tax_certificate_search*/;


/*!50604 ALTER TABLE `phppos_employees` DROP INDEX username_search*/;


/*!50604	ALTER TABLE `phppos_expenses` DROP INDEX `full_search`*/;	
/*!50604	ALTER TABLE `phppos_expenses` DROP INDEX `expense_type_search`*/;
/*!50604	ALTER TABLE `phppos_expenses` DROP INDEX `expense_description_search`*/;
/*!50604	ALTER TABLE `phppos_expenses` DROP INDEX `expense_reason_search`*/;
/*!50604	ALTER TABLE `phppos_expenses` DROP INDEX `expense_note_search`*/;

/*!50604	ALTER TABLE `phppos_giftcards` DROP INDEX giftcard_number_search*/;

/*!50604	ALTER TABLE `phppos_item_kits` DROP INDEX full_search*/;	
/*!50604	ALTER TABLE `phppos_item_kits` DROP INDEX name_search*/;
/*!50604	ALTER TABLE `phppos_item_kits` DROP INDEX item_kit_number_search*/;
/*!50604	ALTER TABLE `phppos_item_kits` DROP INDEX product_id_search*/;
/*!50604	ALTER TABLE `phppos_item_kits` DROP INDEX description_search*/;
/*!50604	ALTER TABLE `phppos_item_kits` ADD INDEX `name` (`name`)*/;

/*!50604	ALTER TABLE `phppos_locations` DROP INDEX full_search*/;	
/*!50604	ALTER TABLE `phppos_locations` DROP INDEX name_search*/;
/*!50604	ALTER TABLE `phppos_locations` DROP INDEX address_search*/;
/*!50604	ALTER TABLE `phppos_locations` DROP INDEX phone_search*/;
/*!50604	ALTER TABLE `phppos_locations` DROP INDEX email_search*/;


/*!50604	ALTER TABLE `phppos_people` DROP INDEX full_search*/;	
/*!50604	ALTER TABLE `phppos_people` DROP INDEX first_name_search*/;
/*!50604	ALTER TABLE `phppos_people` DROP INDEX last_name_search*/;
/*!50604	ALTER TABLE `phppos_people` DROP INDEX full_name_search*/;
/*!50604	ALTER TABLE `phppos_people` DROP INDEX email_search*/;
/*!50604	ALTER TABLE `phppos_people` DROP INDEX phone_number_search*/;
/*!50604	ALTER TABLE `phppos_people` ADD INDEX `first_name` (`first_name`)*/;
/*!50604	ALTER TABLE `phppos_people` ADD INDEX `last_name` (`last_name`)*/;
/*!50604	ALTER TABLE `phppos_people` ADD INDEX `email` (`email`)*/;

/*!50604	ALTER TABLE `phppos_suppliers` DROP INDEX full_search*/;	
/*!50604	ALTER TABLE `phppos_suppliers` DROP INDEX company_name_search*/;
/*!50604	ALTER TABLE `phppos_suppliers` DROP INDEX account_number_search*/;

/*!50604	ALTER TABLE `phppos_tags` DROP INDEX name_search*/;	
/*!50604	ALTER TABLE `phppos_additional_item_numbers` DROP INDEX item_number_search*/;	
/*!50604	ALTER TABLE `phppos_categories` DROP INDEX name_search*/;	

/*!50604	ALTER TABLE `phppos_items` ADD INDEX `name` (`name`)*/;