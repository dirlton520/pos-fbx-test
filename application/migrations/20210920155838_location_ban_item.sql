-- location_ban_item --
CREATE TABLE `phppos_location_ban_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `item_id` INT(11) NOT NULL,
    `location_id` INT(11) NOT NULL,
    PRIMARY KEY (`id`),
    INDEX (`location_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_unicode_ci;

ALTER TABLE `phppos_location_ban_items` ADD UNIQUE `unique_location` (`item_id`, `location_id`) USING BTREE;

ALTER TABLE
    `phppos_location_ban_items`
ADD
    FOREIGN KEY (`item_id`) REFERENCES `phppos_items`(`item_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE
    `phppos_location_ban_items`
ADD
    FOREIGN KEY (`location_id`) REFERENCES `phppos_locations`(`location_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

