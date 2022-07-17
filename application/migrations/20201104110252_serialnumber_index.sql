-- serialnumber_index --

ALTER TABLE `phppos_sales_items` ADD INDEX (`serialnumber`);
ALTER TABLE `phppos_receivings_items` ADD INDEX (`serialnumber`);