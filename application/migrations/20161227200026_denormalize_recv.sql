-- denormalize_recv --

SET unique_checks=0; SET foreign_key_checks=0;
ALTER TABLE `phppos_receivings` ADD `total_quantity_purchased` decimal(23,10) NOT NULL DEFAULT '0.0000000000', ADD `total_quantity_received` decimal(23,10) NOT NULL DEFAULT '0.0000000000', ADD `subtotal` decimal(23,10) NOT NULL DEFAULT '0.0000000000', ADD `tax` decimal(23,10) NOT NULL DEFAULT '0.0000000000', ADD `total` decimal(23,10) NOT NULL DEFAULT '0.0000000000', ADD `profit` decimal(23,10) NOT NULL DEFAULT '0.0000000000',ADD KEY `recv_search` (`location_id`,`deleted`,`receiving_time`,`suspended`,`store_account_payment`,`total_quantity_purchased`);
ALTER TABLE `phppos_receivings_items` ADD `subtotal` decimal(23,10) NOT NULL DEFAULT '0.0000000000', ADD `tax` decimal(23,10) NOT NULL DEFAULT '0.0000000000', ADD `total` decimal(23,10) NOT NULL DEFAULT '0.0000000000', ADD `profit` decimal(23,10) NOT NULL DEFAULT '0.0000000000';

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

CREATE TABLE `phppos_receivings_migrate` (
  `receiving_id` int(10) NOT NULL DEFAULT '0',
  `quantity_purchased` decimal(23,10) DEFAULT NULL,
  `quantity_received` decimal(23,10) DEFAULT NULL,
  `subtotal` decimal(23,10) DEFAULT NULL,
  `tax` decimal(23,10) DEFAULT NULL,
  `total` decimal(23,10) DEFAULT NULL,
  `profit` decimal(23,10) DEFAULT NULL,
  KEY `index` (`receiving_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO phppos_receivings_items_migrate SELECT phppos_receivings_items.receiving_id,phppos_receivings_items.item_id, phppos_receivings_items.line, ROUND(item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100, 2) as subtotal, (ROUND(item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100,2))+(item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) +(((item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) + (item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)) *(SUM(CASE WHEN cumulative = 1 THEN percent ELSE 0 END))/100) as total, (item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) +(((item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)*(SUM(CASE WHEN cumulative != 1 THEN percent ELSE 0 END)/100) + (item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100)) *(SUM(CASE WHEN cumulative = 1 THEN percent ELSE 0 END))/100) as tax, ROUND((item_unit_price*quantity_purchased-item_unit_price*quantity_purchased*discount_percent/100),2) - (item_cost_price*quantity_purchased) as profit FROM phppos_receivings_items INNER JOIN phppos_items ON phppos_receivings_items.item_id=phppos_items.item_id LEFT OUTER JOIN phppos_receivings_items_taxes ON phppos_receivings_items.receiving_id=phppos_receivings_items_taxes.receiving_id and phppos_receivings_items.item_id=phppos_receivings_items_taxes.item_id and phppos_receivings_items.line=phppos_receivings_items_taxes.line GROUP BY receiving_id, item_id, line;

UPDATE phppos_receivings_items,phppos_receivings_items_migrate 
SET phppos_receivings_items.subtotal = phppos_receivings_items_migrate.subtotal,
phppos_receivings_items.tax = phppos_receivings_items_migrate.tax,
phppos_receivings_items.total = phppos_receivings_items_migrate.total,
phppos_receivings_items.profit = phppos_receivings_items_migrate.profit
WHERE phppos_receivings_items.receiving_id = phppos_receivings_items_migrate.receiving_id and phppos_receivings_items.line = phppos_receivings_items_migrate.line and  phppos_receivings_items.item_id = phppos_receivings_items_migrate.item_id;

DROP TABLE phppos_receivings_items_migrate;

INSERT INTO phppos_receivings_migrate SELECT phppos_receivings_items.receiving_id, SUM(quantity_purchased) as quantity_purchased, SUM(quantity_received) as quantity_received, SUM(subtotal) as subtotal,SUM(tax) as tax,SUM(total) as total,SUM(profit) as profit FROM phppos_receivings_items GROUP BY receiving_id;

UPDATE phppos_receivings,phppos_receivings_migrate 
SET phppos_receivings.total_quantity_purchased = phppos_receivings_migrate.quantity_purchased,
phppos_receivings.total_quantity_received = phppos_receivings_migrate.quantity_received,
phppos_receivings.subtotal = ROUND(phppos_receivings_migrate.subtotal,2),
phppos_receivings.tax = ROUND(phppos_receivings_migrate.tax,2),
phppos_receivings.total = ROUND(phppos_receivings_migrate.total,2),
phppos_receivings.profit = ROUND(phppos_receivings_migrate.profit,2)
WHERE phppos_receivings.receiving_id = phppos_receivings_migrate.receiving_id;

DROP TABLE phppos_receivings_migrate;