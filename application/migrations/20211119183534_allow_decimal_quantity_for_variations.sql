-- allow_decimal_quantity_for_variations --
ALTER TABLE `phppos_location_item_variations` CHANGE `quantity` `quantity` DECIMAL(23,10) NULL DEFAULT NULL;