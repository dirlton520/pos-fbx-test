-- denormalize_sales --

SET unique_checks=0; SET foreign_key_checks=0;
ALTER TABLE `phppos_inventory` ADD INDEX( `trans_date`, `trans_inventory`, `location_id`);

CREATE TABLE `phppos_sales_items_migrate` (
  `sale_id` int(10) NOT NULL DEFAULT '0',
  `item_id` int(10) NOT NULL DEFAULT '0',
  `line` int(11) NOT NULL DEFAULT '0',
  `subtotal` decimal(23,10) DEFAULT NULL,
  `total` decimal(23,10) DEFAULT NULL,
  `tax` decimal(23,10) DEFAULT NULL,
  `profit` decimal(23,10) DEFAULT NULL,
  KEY `index` (`sale_id`,`item_id`,`line`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO phppos_sales_items_migrate SELECT phppos_sales_items.sale_id,phppos_sales_items.item_id, phppos_sales_items.line, ROUND(item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100,CASE WHEN tax_included =1 THEN 10 ELSE 2 END) as subtotal, (ROUND(item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100,CASE WHEN tax_included =1 THEN 10 ELSE 2 END))+(item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) +(((item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) + (item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)) *(SUM(CASE WHEN cumulative = 1 THEN percent ELSE 0 END))/100) as total, (item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) +(((item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) + (item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)) *(SUM(CASE WHEN cumulative = 1 THEN percent ELSE 0 END))/100) as tax, ROUND((item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100),CASE WHEN tax_included =1 THEN 10 ELSE 2 END) - (item_cost_price*quantity_purchased) as profit FROM phppos_sales_items INNER JOIN phppos_items ON phppos_sales_items.item_id=phppos_items.item_id LEFT OUTER JOIN phppos_sales_items_taxes ON phppos_sales_items.sale_id=phppos_sales_items_taxes.sale_id and phppos_sales_items.item_id=phppos_sales_items_taxes.item_id and phppos_sales_items.line=phppos_sales_items_taxes.line GROUP BY sale_id, item_id, line;


CREATE TABLE `phppos_sales_item_kits_migrate` (
  `sale_id` int(10) NOT NULL DEFAULT '0',
  `item_kit_id` int(10) NOT NULL DEFAULT '0',
  `line` int(11) NOT NULL DEFAULT '0',
  `subtotal` decimal(23,10) DEFAULT NULL,
  `total` decimal(23,10) DEFAULT NULL,
  `tax` decimal(23,10) DEFAULT NULL,
  `profit` decimal(23,10) DEFAULT NULL,
  KEY `index` (`sale_id`,`item_kit_id`,`line`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO phppos_sales_item_kits_migrate SELECT phppos_sales_item_kits.sale_id, phppos_sales_item_kits.item_kit_id, phppos_sales_item_kits.line, ROUND(item_kit_unit_price*quantity_purchased-item_kit_unit_price*quantity_purchased*discount_percent/100,CASE WHEN tax_included =1 THEN 10 ELSE 2 END) as subtotal, (ROUND(item_kit_unit_price*quantity_purchased-item_kit_unit_price*quantity_purchased*discount_percent/100,CASE WHEN tax_included =1 THEN 10 ELSE 2 END))+(item_kit_unit_price*quantity_purchased-item_kit_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) +(((item_kit_unit_price*quantity_purchased-item_kit_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) + (item_kit_unit_price*quantity_purchased-item_kit_unit_price*quantity_purchased*discount_percent/100)) *(SUM(CASE WHEN cumulative = 1 THEN percent ELSE 0 END))/100) as total, (item_kit_unit_price*quantity_purchased-item_kit_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) +(((item_kit_unit_price*quantity_purchased-item_kit_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) + (item_kit_unit_price*quantity_purchased-item_kit_unit_price*quantity_purchased*discount_percent/100)) *(SUM(CASE WHEN cumulative = 1 THEN percent ELSE 0 END))/100) as tax, ROUND((item_kit_unit_price*quantity_purchased-item_kit_unit_price*quantity_purchased*discount_percent/100),CASE WHEN tax_included =1 THEN 10 ELSE 2 END) - (item_kit_cost_price*quantity_purchased) as profit FROM phppos_sales_item_kits INNER JOIN phppos_item_kits ON phppos_sales_item_kits.item_kit_id=phppos_item_kits.item_kit_id LEFT OUTER JOIN phppos_sales_item_kits_taxes ON phppos_sales_item_kits.sale_id=phppos_sales_item_kits_taxes.sale_id and phppos_sales_item_kits.item_kit_id=phppos_sales_item_kits_taxes.item_kit_id and phppos_sales_item_kits.line=phppos_sales_item_kits_taxes.line GROUP BY sale_id, item_kit_id, line;

UPDATE phppos_sales_items,phppos_sales_items_migrate 
SET phppos_sales_items.subtotal = phppos_sales_items_migrate.subtotal,
phppos_sales_items.tax = phppos_sales_items_migrate.tax,
phppos_sales_items.total = phppos_sales_items_migrate.total,
phppos_sales_items.profit = phppos_sales_items_migrate.profit
WHERE phppos_sales_items.sale_id = phppos_sales_items_migrate.sale_id and phppos_sales_items.line = phppos_sales_items_migrate.line and  phppos_sales_items.item_id = phppos_sales_items_migrate.item_id;

UPDATE phppos_sales_item_kits,phppos_sales_item_kits_migrate 
SET phppos_sales_item_kits.subtotal = phppos_sales_item_kits_migrate.subtotal,
phppos_sales_item_kits.tax = phppos_sales_item_kits_migrate.tax,
phppos_sales_item_kits.total = phppos_sales_item_kits_migrate.total,
phppos_sales_item_kits.profit = phppos_sales_item_kits_migrate.profit
WHERE phppos_sales_item_kits.sale_id = phppos_sales_item_kits_migrate.sale_id and phppos_sales_item_kits.line = phppos_sales_item_kits_migrate.line and  phppos_sales_item_kits.item_kit_id = phppos_sales_item_kits_migrate.item_kit_id;

DROP TABLE phppos_sales_items_migrate;
DROP TABLE phppos_sales_item_kits_migrate;

CREATE TABLE `phppos_sales_migrate` (
  `sale_id` int(11) NOT NULL DEFAULT '0',
  `quantity_purchased` decimal(23,10) DEFAULT NULL,
  `subtotal` decimal(23,10) DEFAULT NULL,
  `tax` decimal(23,10) DEFAULT NULL,
  `total` decimal(23,10) DEFAULT NULL,
  `profit` decimal(23,10) DEFAULT NULL,
  KEY `index` (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO phppos_sales_migrate SELECT sale_id, SUM(quantity_purchased) as quantity_purchased, SUM(subtotal) as subtotal,SUM(tax) as tax,SUM(total) as total,SUM(profit) as profit 
FROM 
(
	SELECT phppos_sales_items.sale_id, SUM(quantity_purchased) as quantity_purchased, SUM(subtotal) as subtotal,SUM(tax) as tax,SUM(total) as total,SUM(profit) as profit FROM phppos_sales_items GROUP BY sale_id 
	UNION ALL 
	SELECT phppos_sales_item_kits.sale_id, SUM(quantity_purchased) as quantity_purchased, SUM(subtotal) as subtotal,SUM(tax) as tax,SUM(total) as total,SUM(profit) as profit FROM phppos_sales_item_kits GROUP BY sale_id
) as sale_totals group by sale_id;


UPDATE phppos_sales,phppos_sales_migrate 
SET phppos_sales.total_quantity_purchased = phppos_sales_migrate.quantity_purchased,
phppos_sales.subtotal = ROUND(phppos_sales_migrate.subtotal,2),
phppos_sales.tax = ROUND(phppos_sales_migrate.tax,2),
phppos_sales.total = ROUND(phppos_sales_migrate.total,2),
phppos_sales.profit = ROUND(phppos_sales_migrate.profit,2)
WHERE phppos_sales.sale_id = phppos_sales_migrate.sale_id;

DROP TABLE phppos_sales_migrate;