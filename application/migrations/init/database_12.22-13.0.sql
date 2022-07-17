
ALTER TABLE `phppos_sales` ADD  `show_comment_on_receipt` INT( 1 ) NOT NULL DEFAULT  '0' AFTER  `comment`;
ALTER TABLE `phppos_sales` ADD  `cc_ref_no` VARCHAR( 255 ) NOT NULL AFTER  `payment_type`;