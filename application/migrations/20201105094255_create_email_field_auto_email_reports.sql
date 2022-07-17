-- create_email_field_auto_email_reports --

ALTER TABLE phppos_locations ADD auto_reports_email varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';