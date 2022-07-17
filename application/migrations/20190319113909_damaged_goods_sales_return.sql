-- damaged_goods_sales_return --
ALTER TABLE phppos_sales_items ADD COLUMN damaged_qty DECIMAL (23,10) NOT NULL DEFAULT 0;
