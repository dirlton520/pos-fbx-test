-- limit_price_adjustments_and_discounts_globally_and_per_employee --

ALTER TABLE `phppos_employees` 
ADD COLUMN `max_discount_percent` decimal(15,3) DEFAULT NULL;
