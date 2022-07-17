-- email_sales_and_email_receivings --
ALTER TABLE `phppos_locations` ADD COLUMN email_sales_email VARCHAR(255) NULL DEFAULT NULL, ADD COLUMN email_receivings_email VARCHAR(255) NULL DEFAULT NULL;