-- app_config_rename_fields --

UPDATE `phppos_app_config` SET `key` = 'reset_location_when_switching_employee' WHERE `key` = 'keep_same_location_after_switching_employee';
UPDATE `phppos_app_config` SET `value` = '0' WHERE `value` = '1' and `key` = 'reset_location_when_switching_employee'; 

UPDATE `phppos_app_config` SET `key` = 'enable_quick_edit' WHERE `key` = 'disable_quick_edit';
UPDATE `phppos_app_config` SET `value` = '0' WHERE `value` = '1' and `key` = 'enable_quick_edit';

UPDATE `phppos_app_config` SET `key` = 'enable_margin_calculator' WHERE `key` = 'disable_margin_calculator';
UPDATE `phppos_app_config` SET `value` = '0' WHERE `value` = '1' and `key` = 'enable_margin_calculator';
