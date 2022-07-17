-- reorganize_ecommerce --


SET unique_checks=0; SET foreign_key_checks=0;

delete a
from phppos_ecommerce_categories a
left join(
select max(ecommerce_category_id) max_ecom_cat, category_id
from phppos_ecommerce_categories
group by category_id) b
on a.ecommerce_category_id = max_ecom_cat and
a.category_id = b.category_id
where b.max_ecom_cat IS NULL;

ALTER TABLE `phppos_ecommerce_categories` 
DROP FOREIGN KEY `phppos_ecommerce_categories_ibfk_1`,
DROP INDEX `phppos_ecommerce_categories_ibfk_1`;

ALTER TABLE `phppos_ecommerce_categories` DROP PRIMARY KEY,
ADD PRIMARY KEY (`category_id`),
ADD CONSTRAINT `phppos_ecommerce_categories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `phppos_categories` (`id`);

delete a
from phppos_ecommerce_tags a
left join(
select max(ecommerce_tag_id) max_ecom_tag, tag_id
from phppos_ecommerce_tags
group by tag_id) b
on a.ecommerce_tag_id = max_ecom_tag and
a.tag_id = b.tag_id
where b.max_ecom_tag IS NULL;

ALTER TABLE `phppos_ecommerce_tags` 
DROP FOREIGN KEY `phppos_ecommerce_tags_ibfk_1`,
DROP INDEX `phppos_ecommerce_tags_ibfk_1`;

ALTER TABLE `phppos_ecommerce_tags` DROP PRIMARY KEY,
ADD PRIMARY KEY (`tag_id`),
ADD CONSTRAINT `phppos_ecommerce_tags_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES `phppos_tags` (`id`);

ALTER TABLE `phppos_tags` 
ADD `ecommerce_tag_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci AFTER `id`,
ADD `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `ecommerce_tag_id`;

UPDATE `phppos_tags` SET ecommerce_tag_id = (SELECT ecommerce_tag_id FROM phppos_ecommerce_tags WHERE tag_id = phppos_tags.id);

ALTER TABLE `phppos_categories` 
ADD `ecommerce_category_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci AFTER `id`,
ADD `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `ecommerce_category_id`;

UPDATE `phppos_categories` SET ecommerce_category_id = (SELECT ecommerce_category_id FROM phppos_ecommerce_categories WHERE category_id = phppos_categories.id);

ALTER TABLE `phppos_items`
	ADD `ecommerce_product_quantity` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci AFTER `ecommerce_product_id`,
	DROP FOREIGN KEY phppos_items_ibfk_5;
	
UPDATE `phppos_items` SET ecommerce_product_quantity = (SELECT product_quantity FROM phppos_ecommerce_products WHERE product_id = phppos_items.ecommerce_product_id);

ALTER TABLE `phppos_item_variations` 
	ADD `ecommerce_variation_quantity` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci AFTER `ecommerce_variation_id`,
	DROP FOREIGN KEY phppos_item_variations_ibfk_2;
	
UPDATE `phppos_item_variations` SET ecommerce_variation_quantity = (SELECT product_quantity FROM phppos_ecommerce_product_variations WHERE variation_id = phppos_item_variations.ecommerce_variation_id);

DROP TABLE `phppos_ecommerce_tags`;
DROP TABLE `phppos_ecommerce_categories`;
DROP TABLE `phppos_ecommerce_products`;
DROP TABLE `phppos_ecommerce_product_variations`;

SET unique_checks=1; SET foreign_key_checks=1;