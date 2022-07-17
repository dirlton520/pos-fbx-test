-- location_banning_tags --
CREATE TABLE `phppos_location_ban_tags` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `tag_id` INT(11) NOT NULL,
    `location_id` INT(11) NOT NULL,
    PRIMARY KEY (`id`),
    INDEX (`location_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_unicode_ci;

ALTER TABLE `phppos_location_ban_tags` ADD UNIQUE `unique_location` (`tag_id`, `location_id`) USING BTREE;

ALTER TABLE
    `phppos_location_ban_tags`
ADD
    FOREIGN KEY (`tag_id`) REFERENCES `phppos_tags`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE
    `phppos_location_ban_tags`
ADD
    FOREIGN KEY (`location_id`) REFERENCES `phppos_locations`(`location_id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

