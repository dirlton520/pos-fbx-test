
REPLACE INTO `phppos_app_config` (`key`, `value`) VALUES ('version', '14.1');
UPDATE phppos_sales_items SET item_cost_price = item_unit_price WHERE item_id IN 
(SELECT item_id FROM phppos_items
WHERE name = 'Store Account Payment' or name = 'Compte magasin Paiement' or name = 'Toko Rekening Pembayaran' or name = 'Conto deposito Pagamento' or name = 'Cuenta tienda de venta');