-- recalculate_receiving_item_totals --

CREATE TABLE `phppos_receivings_items_migrate` (
  `receiving_id` int(10) NOT NULL DEFAULT '0',
  `item_id` int(10) NOT NULL DEFAULT '0',
  `line` int(11) NOT NULL DEFAULT '0',
  `subtotal` decimal(23,10) DEFAULT NULL,
  `total` decimal(23,10) DEFAULT NULL,
  `tax` decimal(23,10) DEFAULT NULL,
  `profit` decimal(23,10) DEFAULT NULL,
  KEY `index` (`receiving_id`,`item_id`,`line`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO phppos_receivings_items_migrate SELECT phppos_receivings_items.receiving_id,phppos_receivings_items.item_id, phppos_receivings_items.line, ROUND(item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100, 2) as subtotal, (ROUND(item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100,2))+(item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) +(((item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) + (item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)) *(SUM(CASE WHEN cumulative = 1 THEN percent ELSE 0 END))/100) as total, (item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) +(((item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) + (item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)) *(SUM(CASE WHEN cumulative = 1 THEN percent ELSE 0 END))/100) as tax, ROUND((item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100),2) - (item_cost_price*quantity_purchased) as profit FROM phppos_receivings_items INNER JOIN phppos_items ON phppos_receivings_items.item_id=phppos_items.item_id LEFT OUTER JOIN phppos_receivings_items_taxes ON phppos_receivings_items.receiving_id=phppos_receivings_items_taxes.receiving_id and phppos_receivings_items.item_id=phppos_receivings_items_taxes.item_id and phppos_receivings_items.line=phppos_receivings_items_taxes.line GROUP BY receiving_id, item_id, line;

UPDATE phppos_receivings_items,phppos_receivings_items_migrate 
SET phppos_receivings_items.subtotal = phppos_receivings_items_migrate.subtotal,
phppos_receivings_items.tax = phppos_receivings_items_migrate.tax,
phppos_receivings_items.total = phppos_receivings_items_migrate.total,
phppos_receivings_items.profit = phppos_receivings_items_migrate.profit
WHERE phppos_receivings_items.receiving_id = phppos_receivings_items_migrate.receiving_id and phppos_receivings_items.line = phppos_receivings_items_migrate.line and  phppos_receivings_items.item_id = phppos_receivings_items_migrate.item_id;

DROP TABLE phppos_receivings_items_migrate;