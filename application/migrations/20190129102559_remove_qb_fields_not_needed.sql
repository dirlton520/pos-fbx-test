-- remove_qb_fields_not_needed --


ALTER TABLE phppos_item_kits DROP COLUMN aaatex_qb_item_name;
ALTER TABLE phppos_items DROP COLUMN aaatex_qb_item_name;
ALTER TABLE phppos_sales DROP COLUMN aaatex_qb_imported;


ALTER TABLE `phppos_customers`
	DROP COLUMN `accounting_id`;

ALTER TABLE `phppos_employees`
	DROP COLUMN `accounting_id`;

ALTER TABLE `phppos_suppliers`
	DROP COLUMN `accounting_id`;

ALTER TABLE `phppos_expenses`
	DROP COLUMN `accounting_id`;

ALTER TABLE `phppos_inventory`
	DROP COLUMN `inventory_sync_needed`;

ALTER TABLE `phppos_categories`
   DROP COLUMN `accounting_id`;

ALTER TABLE `phppos_items` 
	DROP COLUMN `accounting_id`;

ALTER TABLE `phppos_item_kits` 
	DROP COLUMN `accounting_id`;

ALTER TABLE `phppos_location_items` 
	DROP COLUMN `accounting_product_quantity`;


ALTER TABLE `phppos_sales` 
	DROP COLUMN `accounting_id`;
	
ALTER TABLE `phppos_receivings` 
	DROP COLUMN `accounting_id`;
	
REPLACE INTO `phppos_app_config` (`key`, `value`) VALUES ('qb_sync_operations', 'a:1:{i:0;s:33:\"export_journalentry_to_quickbooks\";}');