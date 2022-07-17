-- non_taxable_on_sales_table --

ALTER TABLE `phppos_sales` ADD COLUMN `non_taxable` DECIMAL(23,10) NOT NULL DEFAULT '0';

UPDATE phppos_sales as s SET non_taxable = COALESCE((SELECT COALESCE(SUM(subtotal),0) FROM phppos_sales_items as si WHERE tax = 0 and s.sale_id =si.sale_id) + (SELECT COALESCE(SUM(subtotal),0) FROM phppos_sales_item_kits as sik WHERE tax = 0 and s.sale_id =sik.sale_id),0);