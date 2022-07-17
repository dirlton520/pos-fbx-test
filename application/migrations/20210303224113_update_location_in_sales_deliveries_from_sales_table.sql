-- update_location_in_sales_deliveries_from_sales_table --
UPDATE
    `phppos_sales_deliveries` t1,
    `phppos_sales` t2
SET
    t1.location_id = t2.location_id
WHERE
    t1.sale_id = t2.sale_id;