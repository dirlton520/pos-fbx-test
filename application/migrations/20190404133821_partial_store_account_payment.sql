-- partial_store_account_payment --
ALTER TABLE `phppos_store_accounts_paid_sales` ADD COLUMN partial_payment_amount DECIMAL(23,10) NOT NULL DEFAULT '0';
ALTER TABLE `phppos_supplier_store_accounts_paid_receivings` ADD COLUMN partial_payment_amount DECIMAL(23,10) NOT NULL DEFAULT '0';