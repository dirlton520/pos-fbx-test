-- fix_collation --
ALTER TABLE phppos_customers_series_log CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE phppos_customers_series CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE phppos_register_log_payments CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE phppos_employees_time_off CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE phppos_people_files CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;