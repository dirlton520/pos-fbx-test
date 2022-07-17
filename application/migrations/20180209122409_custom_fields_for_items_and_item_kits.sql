-- custom_fields_for_items_and_item_kits --


ALTER TABLE `phppos_items` 
ADD `custom_field_1_value` VARCHAR(255) NULL DEFAULT NULL, 
ADD INDEX (`custom_field_1_value`),

ADD `custom_field_2_value` VARCHAR(255) NULL DEFAULT NULL, 
ADD INDEX (`custom_field_2_value`),

ADD `custom_field_3_value` VARCHAR(255) NULL DEFAULT NULL, 
ADD INDEX (`custom_field_3_value`),

ADD `custom_field_4_value` VARCHAR(255) NULL DEFAULT NULL, 
ADD INDEX (`custom_field_4_value`),

ADD `custom_field_5_value` VARCHAR(255) NULL DEFAULT NULL, 
ADD INDEX (`custom_field_5_value`),
	
ADD `custom_field_6_value` VARCHAR(255) NULL DEFAULT NULL, 
ADD INDEX (`custom_field_6_value`),

ADD `custom_field_7_value` VARCHAR(255) NULL DEFAULT NULL, 
ADD INDEX (`custom_field_7_value`),

ADD `custom_field_8_value` VARCHAR(255) NULL DEFAULT NULL, 
ADD INDEX (`custom_field_8_value`),

ADD `custom_field_9_value` VARCHAR(255) NULL DEFAULT NULL, 
ADD INDEX (`custom_field_9_value`),

ADD `custom_field_10_value` VARCHAR(255) NULL DEFAULT NULL, 
ADD INDEX (`custom_field_10_value`);

ALTER TABLE `phppos_item_kits` 
ADD `custom_field_1_value` VARCHAR(255) NULL DEFAULT NULL, 
ADD INDEX (`custom_field_1_value`),

ADD `custom_field_2_value` VARCHAR(255) NULL DEFAULT NULL, 
ADD INDEX (`custom_field_2_value`),

ADD `custom_field_3_value` VARCHAR(255) NULL DEFAULT NULL, 
ADD INDEX (`custom_field_3_value`),

ADD `custom_field_4_value` VARCHAR(255) NULL DEFAULT NULL, 
ADD INDEX (`custom_field_4_value`),

ADD `custom_field_5_value` VARCHAR(255) NULL DEFAULT NULL, 
ADD INDEX (`custom_field_5_value`),
	
ADD `custom_field_6_value` VARCHAR(255) NULL DEFAULT NULL, 
ADD INDEX (`custom_field_6_value`),

ADD `custom_field_7_value` VARCHAR(255) NULL DEFAULT NULL, 
ADD INDEX (`custom_field_7_value`),

ADD `custom_field_8_value` VARCHAR(255) NULL DEFAULT NULL, 
ADD INDEX (`custom_field_8_value`),

ADD `custom_field_9_value` VARCHAR(255) NULL DEFAULT NULL, 
ADD INDEX (`custom_field_9_value`),

ADD `custom_field_10_value` VARCHAR(255) NULL DEFAULT NULL, 
ADD INDEX (`custom_field_10_value`);

/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX custom_field_1_value_search (`custom_field_1_value`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX custom_field_2_value_search (`custom_field_2_value`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX custom_field_3_value_search (`custom_field_3_value`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX custom_field_4_value_search (`custom_field_4_value`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX custom_field_5_value_search (`custom_field_5_value`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX custom_field_6_value_search (`custom_field_6_value`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX custom_field_7_value_search (`custom_field_7_value`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX custom_field_8_value_search (`custom_field_8_value`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX custom_field_9_value_search (`custom_field_9_value`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX custom_field_10_value_search (`custom_field_10_value`)*/;