-- added_internal_notes_to_supplier_table --
ALTER TABLE `phppos_attributes` ADD `bitrix_property_id` TEXT NULL DEFAULT NULL AFTER `name`; 
ALTER TABLE `phppos_attributes` ADD `bitrix_attribute_code` TEXT NULL DEFAULT NULL AFTER `bitrix_property_id`; 