-- disable_confirmation_option_for_emv_credit_card --
ALTER TABLE phppos_locations ADD disable_confirmation_option_for_emv_credit_card INT(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0';