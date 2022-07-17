-- age_verify --


ALTER TABLE `phppos_items` 
ADD `required_age` int(10)  DEFAULT NULL,
ADD `verify_age` int(1) NOT NULL DEFAULT 0, 
ADD INDEX (`verify_age`);


ALTER TABLE `phppos_item_kits`
ADD `required_age` int(10)  DEFAULT NULL,
ADD `verify_age` int(1) NOT NULL DEFAULT 0, 
ADD INDEX (`verify_age`);