-- added_internal_notes_to_supplier_table --
ALTER TABLE `phppos_suppliers` ADD `internal_notes` TEXT NULL DEFAULT NULL AFTER `custom_field_10_value`; 