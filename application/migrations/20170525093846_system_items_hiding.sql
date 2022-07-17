-- system_items_hiding --

ALTER table phppos_items ADD `system_item` int(1) NOT NULL DEFAULT '0', ADD INDEX `deleted_system_item` (`deleted`,`system_item`);