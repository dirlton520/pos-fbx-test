-- tips --
ALTER table phppos_sales ADD COLUMN `tip` DECIMAL (23,10) NULL DEFAULT NULL;
UPDATE phppos_sales SET tip = 0;