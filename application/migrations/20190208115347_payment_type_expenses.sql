-- payment_type_expenses --

ALTER table phppos_expenses ADD COLUMN `expense_payment_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL;
