CREATE TABLE `phppos_bitrix_field_mapping` (
  `id` int NOT NULL,
  `catalog_id` int NULL,
  `bitrix_field_code` varchar(255) NULL,
  `name` text NULL,
  `type` varchar(100) NULL,
  `userType` varchar(100) NULL,
  `values` int NULL,
  `propertyType` varchar(100) NULL,
  `pos_field_code` varchar(100) NULL,
  `pos_table_name` varchar(100) NULL,
  `pos_insert_after_item` int NULL,
  `is_pos_taf` varchar(100) NULL,
  `default_value` varchar(100) NULL,
  `pos_update_item` int NULL,
  `bitrix_api` text NULL
);

ALTER TABLE `phppos_bitrix_field_mapping`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `phppos_bitrix_field_mapping`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;