-- quickbooks_online --


ALTER TABLE `phppos_customers`
	ADD `accounting_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci;

ALTER TABLE `phppos_employees`
	ADD `accounting_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci;

ALTER TABLE `phppos_suppliers`
	ADD `accounting_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci;

ALTER TABLE `phppos_expenses`
	ADD `accounting_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci;

ALTER TABLE `phppos_inventory`
	ADD `inventory_sync_needed` int(1) NOT NULL DEFAULT '0';

ALTER TABLE `phppos_people` 
	ADD `last_modified` timestamp NULL DEFAULT NULL;

ALTER TABLE `phppos_categories`
   ADD `accounting_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci;

ALTER TABLE `phppos_items` 
	ADD `accounting_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci;

ALTER TABLE `phppos_item_kits` 
	ADD `accounting_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci;

ALTER TABLE `phppos_location_items` 
	ADD `accounting_product_quantity` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL;


ALTER TABLE `phppos_sales` 
	ADD `accounting_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
	ADD `last_modified` timestamp NULL DEFAULT NULL;

ALTER TABLE `phppos_receivings` 
	ADD `accounting_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
	ADD `last_modified` timestamp NULL DEFAULT NULL;

INSERT INTO `phppos_app_config` (`key`, `value`) VALUES ('qb_sync_operations', 'a:12:{i:0;s:35:"import_quickbooks_items_into_phppos";i:1;s:28:"import_customers_into_phppos";i:2;s:28:"import_suppliers_into_phppos";i:3;s:28:"import_employees_into_phppos";i:4;s:33:"export_phppos_items_to_quickbooks";i:5;s:25:"sync_inventory_changes_qb";i:6;s:30:"export_customers_to_quickbooks";i:7;s:30:"export_suppliers_to_quickbooks";i:8;s:30:"export_employees_to_quickbooks";i:9;s:26:"export_sales_to_quickbooks";i:10;s:31:"export_receivings_to_quickbooks";i:11;s:29:"export_expenses_to_quickbooks";}');