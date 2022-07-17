-- disable_loyalty --

ALTER TABLE `phppos_item_kits` ADD `disable_loyalty` int(1) NOT NULL DEFAULT '0' AFTER `change_cost_price`;