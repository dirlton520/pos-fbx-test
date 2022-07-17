 -- rename_legacy_search_method --

UPDATE `phppos_app_config` SET `key` = 'enhanced_search_method' WHERE `key` = 'legacy_search_method';
UPDATE `phppos_app_config` SET value = (CASE `value` WHEN 1 THEN 0 ELSE 1 END) WHERE `key` = 'enhanced_search_method';