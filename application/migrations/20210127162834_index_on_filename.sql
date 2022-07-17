-- index_on_filename --
ALTER TABLE phppos_app_files ADD INDEX(`file_name`);
ALTER TABLE phppos_app_files ADD INDEX(`timestamp`);
ALTER TABLE phppos_app_files ADD INDEX `filename_timestamp` (`file_name`,`timestamp`);