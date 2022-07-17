-- device_override_emv --

ALTER TABLE phppos_locations ADD secure_device_override_emv varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';
ALTER TABLE phppos_locations ADD secure_device_override_non_emv varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';