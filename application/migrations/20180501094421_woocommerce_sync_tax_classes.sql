-- woocommerce_sync_tax_classes --

ALTER TABLE `phppos_tax_classes` ADD COLUMN ecommerce_tax_class_id VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `phppos_tax_classes_taxes` ADD COLUMN ecommerce_tax_class_tax_rate_id VARCHAR(255) NULL DEFAULT NULL;