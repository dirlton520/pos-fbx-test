-- tax_id_locations --

ALTER TABLE phppos_locations ADD tax_id varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
