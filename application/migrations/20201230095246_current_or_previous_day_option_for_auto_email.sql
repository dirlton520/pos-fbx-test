-- current_or_previous_day_option_for_auto_email --

ALTER TABLE phppos_locations ADD auto_reports_day varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'previous_day';