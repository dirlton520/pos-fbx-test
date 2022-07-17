-- additional_custom_fields --


ALTER TABLE `phppos_suppliers` 
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

ALTER TABLE `phppos_employees` 
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

ALTER TABLE `phppos_customers` 
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