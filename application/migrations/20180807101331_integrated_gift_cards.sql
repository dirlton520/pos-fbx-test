-- integrated_gift_cards --

ALTER TABLE phppos_locations ADD COLUMN `integrated_gift_cards` INT(1) NOT NULL DEFAULT '0';
ALTER TABLE phppos_giftcards ADD COLUMN `integrated_gift_card` INT(1) NOT NULL DEFAULT '0';
ALTER TABLE phppos_giftcards ADD COLUMN `integrated_auth_code` VARCHAR (255) NULL DEFAULT NULL;