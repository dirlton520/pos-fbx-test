-- fix_utf8_general_ci_to_utf8_unicode_ci_for_phppos_workorder_statuses --

ALTER TABLE phppos_workorder_statuses convert to character set utf8 collate utf8_unicode_ci;