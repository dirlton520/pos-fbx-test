-- promo_price_no_date_needed --


UPDATE phppos_items SET start_date = '1969-01-01', end_date = '1969-01-01' 
WHERE promo_price IS NOT NULL AND start_date IS NULL AND end_date IS NULL;