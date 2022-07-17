-- new_indexes --


-- Make field that combines first + last name for searching purposes

ALTER TABLE `phppos_people` ADD `full_name` text COLLATE utf8_unicode_ci NOT NULL AFTER `last_name`;
UPDATE `phppos_people` SET full_name = CONCAT(first_name,' ',last_name);

-- New Indexes --
ALTER TABLE `phppos_people` ADD INDEX `phone_number` (`phone_number`);
ALTER TABLE `phppos_people` ADD INDEX `full_name` (`full_name`(255));
ALTER TABLE `phppos_customers` ADD INDEX `company_name` (`company_name`);
ALTER TABLE `phppos_categories` ADD INDEX `name` (`name`);

ALTER TABLE `phppos_locations` ADD INDEX `name` (`name`(255));
ALTER TABLE `phppos_locations` ADD INDEX `address` (`address`(255));
ALTER TABLE `phppos_locations` ADD INDEX `phone` (`phone`(255));
ALTER TABLE `phppos_locations` ADD INDEX `email` (`email`(255));


ALTER TABLE `phppos_expenses` ADD INDEX `expense_type` (`expense_type`);
ALTER TABLE `phppos_expenses` ADD INDEX `expense_date` (`expense_date`);
ALTER TABLE `phppos_expenses` ADD INDEX `expense_amount` (`expense_amount`);
ALTER TABLE `phppos_expenses` ADD INDEX `expense_description` (`expense_description`(255));
ALTER TABLE `phppos_expenses` ADD INDEX `expense_reason` (`expense_reason`);
ALTER TABLE `phppos_expenses` ADD INDEX `expense_note` (`expense_note`(255));

ALTER TABLE `phppos_giftcards` ADD INDEX `description` (`description`(255));


ALTER TABLE `phppos_location_items` ADD INDEX `quantity` (`quantity`);

ALTER TABLE `phppos_item_kits` ADD INDEX `description` (`description`);
ALTER TABLE `phppos_item_kits` ADD INDEX `cost_price` (`cost_price`);
ALTER TABLE `phppos_item_kits` ADD INDEX `unit_price` (`unit_price`);
