-- rename_margin_to_markup --

UPDATE phppos_app_config SET `key` = 'enable_markup_calculator' WHERE `key` = 'enable_margin_calculator';