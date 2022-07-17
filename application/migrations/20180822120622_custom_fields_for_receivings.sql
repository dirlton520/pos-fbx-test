-- custom_fields_for_receivings --


ALTER TABLE `phppos_receivings` 
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