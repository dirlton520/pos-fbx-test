-- square --

ALTER TABLE phppos_locations ADD square_currency_code varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'USD';
ALTER TABLE phppos_locations ADD square_location_id varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE phppos_locations ADD square_currency_multiplier varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '100';