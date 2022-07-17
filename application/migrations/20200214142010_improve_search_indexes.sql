-- improve_search_indexes --
ALTER TABLE `phppos_items` ADD COLUMN tags VARCHAR(255) DEFAULT '';
ALTER TABLE `phppos_items` ADD INDEX `tags` (`tags`);

SET SQL_SAFE_UPDATES = 0;
SET SESSION sql_mode='';
UPDATE `phppos_items` i
SET
    i.tags = (SELECT
            GROUP_CONCAT(DISTINCT t.name)
        FROM
            phppos_items_tags it
                LEFT JOIN
            phppos_tags t ON it.tag_id = t.id
        WHERE
            i.item_id = it.item_id
        GROUP BY i.item_id);