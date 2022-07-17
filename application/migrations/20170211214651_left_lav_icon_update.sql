-- left_lav_icon_update --


UPDATE phppos_modules SET icon = CONCAT('icon ti-',icon);

UPDATE phppos_modules SET icon = "glyphicon glyphicon-tags" WHERE name_lang_key = "module_price_rules";