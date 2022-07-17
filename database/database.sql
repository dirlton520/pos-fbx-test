SET SESSION sql_mode="";SET FOREIGN_KEY_CHECKS=0;SET SESSION innodb_strict_mode=OFF;
-- MySQL dump 10.13  Distrib 5.7.34, for osx11.0 (x86_64)
--
-- Host: localhost    Database: pos
-- ------------------------------------------------------
-- Server version	5.7.34

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `phppos_access`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `all_access` tinyint(1) NOT NULL DEFAULT '0',
  `controller` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `controller` (`controller`),
  KEY `phppos_access_key_fk` (`key`),
  CONSTRAINT `phppos_access_key_fk` FOREIGN KEY (`key`) REFERENCES `phppos_keys` (`key`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_access`
--

LOCK TABLES `phppos_access` WRITE;
/*!40000 ALTER TABLE `phppos_access` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_additional_item_numbers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_additional_item_numbers` (
  `item_id` int(11) NOT NULL,
  `item_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `item_variation_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`item_id`,`item_number`),
  UNIQUE KEY `item_number` (`item_number`),
  KEY `phppos_additional_item_numbers_ibfk_2` (`item_variation_id`),
  CONSTRAINT `phppos_additional_item_numbers_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_additional_item_numbers_ibfk_2` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_additional_item_numbers`
--

LOCK TABLES `phppos_additional_item_numbers` WRITE;
/*!40000 ALTER TABLE `phppos_additional_item_numbers` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_additional_item_numbers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_app_config`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_app_config` (
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_app_config`
--

LOCK TABLES `phppos_app_config` WRITE;
/*!40000 ALTER TABLE `phppos_app_config` DISABLE KEYS */;
INSERT INTO `phppos_app_config` VALUES ('additional_payment_types',''),('always_minimize_menu','1'),('always_print_duplicate_receipt_all','0'),('always_show_item_grid','0'),('always_use_average_cost_method','0'),('announcement_special',''),('auto_focus_on_item_after_sale_and_receiving','0'),('automatically_email_receipt','0'),('automatically_print_duplicate_receipt_for_cc_transactions','0'),('automatically_show_comments_on_receipt','0'),('averaging_method','moving_average'),('barcode_price_include_tax','0'),('calculate_average_cost_price_from_receivings','0'),('calculate_profit_for_giftcard_when',''),('capture_sig_for_all_payments','0'),('change_sale_date_for_new_sale','0'),('change_sale_date_when_completing_suspended_sale','0'),('change_sale_date_when_suspending','0'),('charge_tax_on_recv','0'),('commission_default_rate','0'),('commission_percent_type','selling_price'),('company','PHP Point Of Sale, LLC'),('confirm_error_adding_item','0'),('crlf','\r\n'),('currency_code',''),('currency_symbol','$'),('currency_symbol_location','before'),('customers_store_accounts','0'),('date_format','middle_endian'),('decimal_point','.'),('default_new_items_to_service','0'),('default_payment_type','Cash'),('default_reorder_level_when_creating_items',''),('default_sales_person','logged_in_employee'),('default_tax_1_name','Sales Tax'),('default_tax_1_rate',''),('default_tax_2_cumulative','0'),('default_tax_2_name','Sales Tax 2'),('default_tax_2_rate',''),('default_tax_3_name',''),('default_tax_3_rate',''),('default_tax_4_name',''),('default_tax_4_rate',''),('default_tax_5_name',''),('default_tax_5_rate',''),('default_tax_rate','8'),('default_tier_fixed_type_for_excel_import','fixed_amount'),('default_tier_percent_type_for_excel_import','percent_off'),('default_type_for_grid','categories'),('deleted_payment_types',''),('disable_confirmation_sale','0'),('disable_giftcard_detection','0'),('disable_price_rules_dialog','0'),('disable_quick_complete_sale','0'),('disable_sale_notifications','0'),('disable_store_account_when_over_credit_limit','0'),('disable_test_mode','0'),('discount_percent_earned','0'),('do_not_allow_below_cost','0'),('do_not_allow_out_of_stock_items_to_be_sold','0'),('do_not_force_http','0'),('do_not_group_same_items','0'),('do_not_show_closing','0'),('do_not_tax_service_items_for_deliveries','0'),('ecom_store_location','1'),('ecommerce_cron_sync_operations','a:13:{i:0;s:22:\"sync_inventory_changes\";i:1;s:33:\"import_ecommerce_tags_into_phppos\";i:2;s:39:\"import_ecommerce_categories_into_phppos\";i:3;s:39:\"import_ecommerce_attributes_into_phppos\";i:4;s:30:\"import_tax_classes_into_phppos\";i:5;s:35:\"import_shipping_classes_into_phppos\";i:6;s:34:\"import_ecommerce_items_into_phppos\";i:7;s:35:\"import_ecommerce_orders_into_phppos\";i:8;s:31:\"export_phppos_tags_to_ecommerce\";i:9;s:37:\"export_phppos_categories_to_ecommerce\";i:10;s:37:\"export_phppos_attributes_to_ecommerce\";i:11;s:30:\"export_tax_classes_into_phppos\";i:12;s:32:\"export_phppos_items_to_ecommerce\";}'),('ecommerce_platform',''),('ecommerce_suspended_sale_type_id','3'),('edit_item_price_if_zero_after_adding','0'),('email_charset',''),('email_provider','Use System Default'),('emailed_receipt_subject',''),('enable_customer_loyalty_system','0'),('enable_ebt_payments','0'),('enable_markup_calculator','0'),('enable_quick_edit','0'),('enable_scale','0'),('enable_sounds','0'),('enable_wic','0'),('enhanced_search_method','0'),('fast_user_switching','0'),('force_https','0'),('group_all_taxes_on_receipt','0'),('hide_barcode_on_sales_and_recv_receipt','0'),('hide_customer_recent_sales','0'),('hide_desc_on_receipt','0'),('hide_layaways_sales_in_reports','0'),('hide_name_on_barcodes','0'),('hide_out_of_stock_grid','0'),('hide_points_on_receipt','0'),('hide_price_on_barcodes','0'),('hide_sales_to_discount_on_receipt','0'),('hide_signature','0'),('hide_size_field','1'),('hide_store_account_balance_on_receipt','0'),('hide_store_account_payments_from_report_totals','0'),('hide_store_account_payments_in_reports','0'),('hide_suspended_recv_in_reports','0'),('hide_test_mode_home','0'),('highlight_low_inventory_items_in_items_module','0'),('id_to_show_on_barcode','id'),('id_to_show_on_sale_interface','number'),('include_child_categories_when_searching_or_reporting','0'),('indicate_taxable_on_receipt','0'),('item_id_auto_increment','1'),('item_kit_id_auto_increment','1'),('item_lookup_order','a:6:{i:0;s:7:\"item_id\";i:1;s:11:\"item_number\";i:2;s:10:\"product_id\";i:3;s:23:\"additional_item_numbers\";i:4;s:14:\"serial_numbers\";i:5;s:26:\"item_variation_item_number\";}'),('items_per_search_suggestions','20'),('language','english'),('legacy_detailed_report_export','0'),('limit_manual_price_adj','0'),('lock_prices_suspended_sales','0'),('logout_on_clock_out','0'),('loyalty_option','simple'),('loyalty_points_without_tax','0'),('mailing_labels_type','pdf'),('new_items_are_ecommerce_by_default','1'),('newline','\r\n'),('number_of_decimals',''),('number_of_decimals_for_quantity_on_receipt',''),('number_of_items_in_grid','14'),('number_of_items_per_page','20'),('number_of_recent_sales','10'),('number_of_sales_for_discount',''),('online_price_tier','0'),('override_receipt_title',''),('override_tier_name',''),('overwrite_existing_items_on_excel_import','0'),('past_inventory_date','2021-12-21'),('paypal_me',''),('phppos_session_expiration','0'),('point_value',''),('prices_include_tax','0'),('print_after_receiving','0'),('print_after_sale','0'),('prompt_for_ccv_swipe','0'),('prompt_to_use_points','0'),('protocol',''),('qb_sync_operations','a:1:{i:0;s:33:\"export_journalentry_to_quickbooks\";}'),('receipt_text_size','small'),('receiving_id_auto_increment','1'),('redirect_to_sale_or_recv_screen_after_printing_receipt','0'),('remove_commission_from_profit_in_reports','0'),('remove_customer_company_from_receipt','0'),('remove_customer_contact_info_from_receipt','0'),('remove_customer_name_from_receipt','0'),('remove_employee_from_receipt','0'),('remove_points_from_profit','0'),('report_sort_order','asc'),('require_customer_for_sale','0'),('require_customer_for_suspended_sale','0'),('require_employee_login_before_each_sale','0'),('reset_location_when_switching_employee','0'),('return_policy','Change return policy'),('round_cash_on_sales','0'),('round_tier_prices_to_2_decimals','0'),('sale_id_auto_increment','1'),('sale_prefix','POS'),('scale_divide_by','100'),('scale_format','scale_1'),('select_sales_person_during_sale','0'),('show_barcode_company_name','1'),('show_clock_on_header','0'),('show_item_id_on_receipt','0'),('show_language_switcher','0'),('show_orig_price_if_marked_down_on_receipt','0'),('show_receipt_after_suspending_sale','0'),('shown_setup_wizard','0'),('sku_sync_field','item_number'),('smtp_crypto',''),('smtp_host',''),('smtp_pass',''),('smtp_port',''),('smtp_timeout',''),('smtp_user',''),('speed_up_search_queries','0'),('spend_to_point_ratio',''),('spreadsheet_format','XLSX'),('store_account_statement_message',''),('store_closing_time',''),('store_opening_time',''),('suppliers_store_accounts','0'),('supports_full_text','1'),('tax_class_id','1'),('test_mode','0'),('thousands_separator',','),('time_format','12_hour'),('timeclock','0'),('track_cash','0'),('user_configured_layaway_name',''),('version','18.4'),('virtual_keyboard',''),('website',''),('wide_printer_receipt_format','0'),('woo_api_key',''),('woo_api_secret',''),('woo_api_url',''),('woo_version','3.0.0');
/*!40000 ALTER TABLE `phppos_app_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_app_files`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_app_files` (
  `file_id` int(10) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file_data` longblob NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`file_id`),
  KEY `expires` (`expires`),
  KEY `file_name` (`file_name`),
  KEY `timestamp` (`timestamp`),
  KEY `filename_timestamp` (`file_name`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_app_files`
--

LOCK TABLES `phppos_app_files` WRITE;
/*!40000 ALTER TABLE `phppos_app_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_app_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_appointment_types`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_appointment_types` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_appointment_types`
--

LOCK TABLES `phppos_appointment_types` WRITE;
/*!40000 ALTER TABLE `phppos_appointment_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_appointment_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_appointments`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_id` int(10) NOT NULL,
  `person_id` int(10) DEFAULT NULL,
  `employee_id` int(10) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `appointments_type_id` int(10) NOT NULL,
  `notes` text COLLATE utf8_unicode_ci NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phppos_appointments_ibfk_1` (`appointments_type_id`),
  KEY `phppos_appointments_ibfk_2` (`person_id`),
  KEY `phppos_appointments_ibfk_3` (`location_id`),
  KEY `phppos_appointments_ibfk_4` (`employee_id`),
  CONSTRAINT `phppos_appointments_ibfk_1` FOREIGN KEY (`appointments_type_id`) REFERENCES `phppos_appointment_types` (`id`),
  CONSTRAINT `phppos_appointments_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `phppos_people` (`person_id`),
  CONSTRAINT `phppos_appointments_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_appointments_ibfk_4` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_appointments`
--

LOCK TABLES `phppos_appointments` WRITE;
/*!40000 ALTER TABLE `phppos_appointments` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_attribute_values`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_attribute_values` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `ecommerce_attribute_term_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attribute_id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_attribute_id` (`name`,`attribute_id`),
  KEY `phppos_attribute_values_ibfk_1` (`attribute_id`),
  CONSTRAINT `phppos_attribute_values_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `phppos_attributes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_attribute_values`
--

LOCK TABLES `phppos_attribute_values` WRITE;
/*!40000 ALTER TABLE `phppos_attribute_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_attribute_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_attributes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_attributes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL,
  `ecommerce_attribute_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`item_id`,`name`),
  CONSTRAINT `phppos_attributes_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_attributes`
--

LOCK TABLES `phppos_attributes` WRITE;
/*!40000 ALTER TABLE `phppos_attributes` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_attributes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_categories`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ecommerce_category_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `hide_from_grid` int(1) NOT NULL DEFAULT '0',
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `image_id` int(10) DEFAULT NULL,
  `color` text COLLATE utf8_unicode_ci,
  `system_category` int(1) DEFAULT '0',
  `exclude_from_e_commerce` int(1) NOT NULL DEFAULT '0',
  `category_info_popup` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `deleted` (`deleted`),
  KEY `phppos_categories_ibfk_1` (`parent_id`),
  KEY `phppos_categories_ibfk_2` (`image_id`),
  KEY `name` (`name`),
  CONSTRAINT `phppos_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `phppos_categories` (`id`),
  CONSTRAINT `phppos_categories_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `phppos_app_files` (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_categories`
--

LOCK TABLES `phppos_categories` WRITE;
/*!40000 ALTER TABLE `phppos_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_currency_exchange_rates`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_currency_exchange_rates` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `currency_code_to` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `currency_symbol` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `exchange_rate` decimal(23,10) NOT NULL,
  `currency_symbol_location` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `number_of_decimals` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `thousands_separator` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `decimal_point` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_currency_exchange_rates`
--

LOCK TABLES `phppos_currency_exchange_rates` WRITE;
/*!40000 ALTER TABLE `phppos_currency_exchange_rates` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_currency_exchange_rates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_customers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_customers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `person_id` int(10) NOT NULL,
  `account_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `override_default_tax` int(1) NOT NULL DEFAULT '0',
  `company_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `balance` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `credit_limit` decimal(23,10) DEFAULT NULL,
  `points` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `disable_loyalty` int(1) NOT NULL DEFAULT '0',
  `current_spend_for_points` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `current_sales_for_discount` int(10) NOT NULL DEFAULT '0',
  `taxable` int(1) NOT NULL DEFAULT '1',
  `tax_certificate` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cc_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cc_expire` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cc_ref_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cc_preview` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `card_issuer` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `tier_id` int(10) DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `tax_class_id` int(10) DEFAULT NULL,
  `custom_field_1_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_2_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_3_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_4_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_5_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_6_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_7_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_8_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_9_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_10_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `internal_notes` text COLLATE utf8_unicode_ci NOT NULL,
  `customer_info_popup` text COLLATE utf8_unicode_ci,
  `auto_email_receipt` int(1) NOT NULL DEFAULT '0',
  `always_sms_receipt` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_number` (`account_number`),
  KEY `person_id` (`person_id`),
  KEY `deleted` (`deleted`),
  KEY `cc_token` (`cc_token`),
  KEY `phppos_customers_ibfk_2` (`tier_id`),
  KEY `company_name` (`company_name`),
  KEY `phppos_customers_ibfk_3` (`tax_class_id`),
  KEY `custom_field_1_value` (`custom_field_1_value`),
  KEY `custom_field_2_value` (`custom_field_2_value`),
  KEY `custom_field_3_value` (`custom_field_3_value`),
  KEY `custom_field_4_value` (`custom_field_4_value`),
  KEY `custom_field_5_value` (`custom_field_5_value`),
  KEY `custom_field_6_value` (`custom_field_6_value`),
  KEY `custom_field_7_value` (`custom_field_7_value`),
  KEY `custom_field_8_value` (`custom_field_8_value`),
  KEY `custom_field_9_value` (`custom_field_9_value`),
  KEY `custom_field_10_value` (`custom_field_10_value`),
  KEY `phppos_customers_ibfk_4` (`location_id`),
  CONSTRAINT `phppos_customers_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `phppos_people` (`person_id`),
  CONSTRAINT `phppos_customers_ibfk_2` FOREIGN KEY (`tier_id`) REFERENCES `phppos_price_tiers` (`id`),
  CONSTRAINT `phppos_customers_ibfk_3` FOREIGN KEY (`tax_class_id`) REFERENCES `phppos_tax_classes` (`id`),
  CONSTRAINT `phppos_customers_ibfk_4` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_customers`
--

LOCK TABLES `phppos_customers` WRITE;
/*!40000 ALTER TABLE `phppos_customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_customers_series`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_customers_series` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) NOT NULL,
  `item_id` int(1) NOT NULL DEFAULT '0',
  `expire_date` date DEFAULT NULL,
  `quantity_remaining` decimal(23,10) DEFAULT '0.0000000000',
  `customer_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_customers_series_ibfk_1` (`item_id`),
  KEY `phppos_customers_series_ibfk_2` (`customer_id`),
  KEY `phppos_customers_series_ibfk_3` (`sale_id`),
  CONSTRAINT `phppos_customers_series_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_customers_series_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `phppos_people` (`person_id`),
  CONSTRAINT `phppos_customers_series_ibfk_3` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_customers_series`
--

LOCK TABLES `phppos_customers_series` WRITE;
/*!40000 ALTER TABLE `phppos_customers_series` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_customers_series` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_customers_series_log`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_customers_series_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `series_id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `quantity_used` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_customers_series_log_ibfk_1` (`series_id`),
  CONSTRAINT `phppos_customers_series_log_ibfk_1` FOREIGN KEY (`series_id`) REFERENCES `phppos_customers_series` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_customers_series_log`
--

LOCK TABLES `phppos_customers_series_log` WRITE;
/*!40000 ALTER TABLE `phppos_customers_series_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_customers_series_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_customers_taxes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_customers_taxes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(15,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tax` (`customer_id`,`name`,`percent`),
  CONSTRAINT `phppos_customers_taxes_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `phppos_customers` (`person_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_customers_taxes`
--

LOCK TABLES `phppos_customers_taxes` WRITE;
/*!40000 ALTER TABLE `phppos_customers_taxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_customers_taxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_damaged_items_log`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_damaged_items_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `damaged_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `damaged_qty` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `item_id` int(10) NOT NULL,
  `item_variation_id` int(10) DEFAULT NULL,
  `sale_id` int(10) DEFAULT NULL,
  `location_id` int(10) NOT NULL,
  `damaged_reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `damaged_reason_comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_damaged_items_log_ibfk_1` (`item_id`),
  KEY `phppos_damaged_items_log_ibfk_2` (`item_variation_id`),
  KEY `phppos_damaged_items_log_ibfk_3` (`sale_id`),
  KEY `phppos_damaged_items_log_ibfk_4` (`location_id`),
  CONSTRAINT `phppos_damaged_items_log_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_damaged_items_log_ibfk_2` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`),
  CONSTRAINT `phppos_damaged_items_log_ibfk_3` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
  CONSTRAINT `phppos_damaged_items_log_ibfk_4` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_damaged_items_log`
--

LOCK TABLES `phppos_damaged_items_log` WRITE;
/*!40000 ALTER TABLE `phppos_damaged_items_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_damaged_items_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_delivery_categories`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_delivery_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `color` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `delivery_category_name` (`name`),
  KEY `deleted` (`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_delivery_categories`
--

LOCK TABLES `phppos_delivery_categories` WRITE;
/*!40000 ALTER TABLE `phppos_delivery_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_delivery_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_delivery_email_templates`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_delivery_email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status_id` int(11) NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_delivery_email_templates`
--

LOCK TABLES `phppos_delivery_email_templates` WRITE;
/*!40000 ALTER TABLE `phppos_delivery_email_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_delivery_email_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_delivery_files`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_delivery_files` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `file_id` int(11) NOT NULL,
  `delivery_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`),
  KEY `delivery_id` (`delivery_id`),
  CONSTRAINT `phppos_delivery_files_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `phppos_app_files` (`file_id`),
  CONSTRAINT `phppos_delivery_files_ibfk_2` FOREIGN KEY (`delivery_id`) REFERENCES `phppos_sales_deliveries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_delivery_files`
--

LOCK TABLES `phppos_delivery_files` WRITE;
/*!40000 ALTER TABLE `phppos_delivery_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_delivery_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_delivery_item_kits`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_delivery_item_kits` (
  `delivery_item_kits_id` int(11) NOT NULL AUTO_INCREMENT,
  `delivery_id` int(11) DEFAULT NULL,
  `item_kit_id` int(11) DEFAULT NULL,
  `quantity` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`delivery_item_kits_id`),
  KEY `delivery_id` (`delivery_id`),
  KEY `item_kit_id` (`item_kit_id`),
  CONSTRAINT `phppos_delivery_item_kits_ibfk_1` FOREIGN KEY (`delivery_id`) REFERENCES `phppos_sales_deliveries` (`id`),
  CONSTRAINT `phppos_delivery_item_kits_ibfk_2` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_delivery_item_kits`
--

LOCK TABLES `phppos_delivery_item_kits` WRITE;
/*!40000 ALTER TABLE `phppos_delivery_item_kits` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_delivery_item_kits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_delivery_items`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_delivery_items` (
  `delivery_items_id` int(11) NOT NULL AUTO_INCREMENT,
  `delivery_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `item_variation_id` int(11) DEFAULT NULL,
  `quantity` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`delivery_items_id`),
  KEY `delivery_id` (`delivery_id`),
  KEY `item_id` (`item_id`),
  KEY `item_variation_id` (`item_variation_id`),
  CONSTRAINT `phppos_delivery_items_ibfk_1` FOREIGN KEY (`delivery_id`) REFERENCES `phppos_sales_deliveries` (`id`),
  CONSTRAINT `phppos_delivery_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_delivery_items_ibfk_3` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_delivery_items`
--

LOCK TABLES `phppos_delivery_items` WRITE;
/*!40000 ALTER TABLE `phppos_delivery_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_delivery_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_delivery_statuses`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_delivery_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `color` text COLLATE utf8_unicode_ci,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `notify_by_email` int(1) DEFAULT '0',
  `notify_by_sms` int(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_delivery_statuses`
--

LOCK TABLES `phppos_delivery_statuses` WRITE;
/*!40000 ALTER TABLE `phppos_delivery_statuses` DISABLE KEYS */;
INSERT INTO `phppos_delivery_statuses` VALUES (1,'Not Scheduled','Not Scheduled','#FF0179','2021-08-23 11:28:11',1,1),(2,'Scheduled','Scheduled','#02B085','2021-08-23 11:28:11',1,1),(3,'Shipped','Shipped','#0072C6','2021-08-23 11:28:11',1,1),(4,'Delivered','Delivered','#5F0082','2021-08-23 11:28:11',1,1);
/*!40000 ALTER TABLE `phppos_delivery_statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_ecommerce_locations`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_ecommerce_locations` (
  `location_id` int(10) NOT NULL,
  PRIMARY KEY (`location_id`),
  CONSTRAINT `phppos_ecommerce_locations_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_ecommerce_locations`
--

LOCK TABLES `phppos_ecommerce_locations` WRITE;
/*!40000 ALTER TABLE `phppos_ecommerce_locations` DISABLE KEYS */;
INSERT INTO `phppos_ecommerce_locations` VALUES (1);
/*!40000 ALTER TABLE `phppos_ecommerce_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_employee_registers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_employee_registers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `register_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_employee_registers_ibfk_1` (`employee_id`),
  KEY `phppos_employee_registers_ibfk_2` (`register_id`),
  CONSTRAINT `phppos_employee_registers_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_employee_registers_ibfk_2` FOREIGN KEY (`register_id`) REFERENCES `phppos_registers` (`register_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_employee_registers`
--

LOCK TABLES `phppos_employee_registers` WRITE;
/*!40000 ALTER TABLE `phppos_employee_registers` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_employee_registers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_employees`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_employees` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `force_password_change` int(1) NOT NULL DEFAULT '0',
  `always_require_password` int(1) NOT NULL DEFAULT '0',
  `person_id` int(10) NOT NULL,
  `language` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `commission_percent` decimal(23,10) DEFAULT '0.0000000000',
  `commission_percent_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `hourly_pay_rate` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `not_required_to_clock_in` int(1) NOT NULL DEFAULT '0',
  `inactive` int(1) NOT NULL DEFAULT '0',
  `reason_inactive` text COLLATE utf8_unicode_ci,
  `hire_date` date DEFAULT NULL,
  `employee_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `termination_date` date DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `custom_field_1_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_2_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_3_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_4_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_5_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_6_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_7_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_8_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_9_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_10_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `max_discount_percent` decimal(15,3) DEFAULT NULL,
  `login_start_time` time DEFAULT NULL,
  `login_end_time` time DEFAULT NULL,
  `dark_mode` int(1) NOT NULL DEFAULT '0',
  `template_id` int(11) DEFAULT NULL,
  `override_price_adjustments` int(1) DEFAULT '0',
  `allowed_ip_address` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `employee_number` (`employee_number`),
  KEY `person_id` (`person_id`),
  KEY `deleted` (`deleted`),
  KEY `custom_field_1_value` (`custom_field_1_value`),
  KEY `custom_field_2_value` (`custom_field_2_value`),
  KEY `custom_field_3_value` (`custom_field_3_value`),
  KEY `custom_field_4_value` (`custom_field_4_value`),
  KEY `custom_field_5_value` (`custom_field_5_value`),
  KEY `custom_field_6_value` (`custom_field_6_value`),
  KEY `custom_field_7_value` (`custom_field_7_value`),
  KEY `custom_field_8_value` (`custom_field_8_value`),
  KEY `custom_field_9_value` (`custom_field_9_value`),
  KEY `custom_field_10_value` (`custom_field_10_value`),
  KEY `template_id` (`template_id`),
  CONSTRAINT `phppos_employees_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `phppos_people` (`person_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_employees`
--

LOCK TABLES `phppos_employees` WRITE;
/*!40000 ALTER TABLE `phppos_employees` DISABLE KEYS */;
INSERT INTO `phppos_employees` VALUES (1,'admin','439a6de57d475c1a0ba9bcb1c39f0af6',0,0,1,NULL,0.0000000000,'',0.0000000000,0,0,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,NULL);
/*!40000 ALTER TABLE `phppos_employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_employees_app_config`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_employees_app_config` (
  `employee_id` int(11) NOT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`employee_id`,`key`),
  CONSTRAINT `phppos_employees_app_config_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_employees_app_config`
--

LOCK TABLES `phppos_employees_app_config` WRITE;
/*!40000 ALTER TABLE `phppos_employees_app_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_employees_app_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_employees_locations`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_employees_locations` (
  `employee_id` int(10) NOT NULL,
  `location_id` int(10) NOT NULL,
  PRIMARY KEY (`employee_id`,`location_id`),
  KEY `phppos_employees_locations_ibfk_2` (`location_id`),
  CONSTRAINT `phppos_employees_locations_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_employees_locations_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_employees_locations`
--

LOCK TABLES `phppos_employees_locations` WRITE;
/*!40000 ALTER TABLE `phppos_employees_locations` DISABLE KEYS */;
INSERT INTO `phppos_employees_locations` VALUES (1,1);
/*!40000 ALTER TABLE `phppos_employees_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_employees_reset_password`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_employees_reset_password` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `employee_id` int(11) NOT NULL,
  `expire` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `phppos_employees_reset_password_ibfk_1` (`employee_id`),
  CONSTRAINT `phppos_employees_reset_password_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_employees_reset_password`
--

LOCK TABLES `phppos_employees_reset_password` WRITE;
/*!40000 ALTER TABLE `phppos_employees_reset_password` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_employees_reset_password` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_employees_time_clock`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_employees_time_clock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `clock_in` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `clock_out` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `clock_in_comment` text COLLATE utf8_unicode_ci NOT NULL,
  `clock_out_comment` text COLLATE utf8_unicode_ci NOT NULL,
  `hourly_pay_rate` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `ip_address_clock_in` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ip_address_clock_out` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_employees_time_clock_ibfk_1` (`employee_id`),
  KEY `phppos_employees_time_clock_ibfk_2` (`location_id`),
  CONSTRAINT `phppos_employees_time_clock_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_employees_time_clock_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_employees_time_clock`
--

LOCK TABLES `phppos_employees_time_clock` WRITE;
/*!40000 ALTER TABLE `phppos_employees_time_clock` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_employees_time_clock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_employees_time_off`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_employees_time_off` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `approved` int(1) NOT NULL DEFAULT '0',
  `start_day` date DEFAULT NULL,
  `end_day` date DEFAULT NULL,
  `hours_requested` decimal(23,10) DEFAULT '0.0000000000',
  `is_paid` int(1) NOT NULL DEFAULT '0',
  `reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `employee_requested_person_id` int(10) DEFAULT NULL,
  `employee_requested_location_id` int(10) DEFAULT NULL,
  `employee_approved_person_id` int(10) DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phppos_employees_time_off_ibfk_1` (`employee_requested_person_id`),
  KEY `phppos_employees_time_off_ibfk_2` (`employee_approved_person_id`),
  KEY `phppos_employees_time_off_ibfk_3` (`employee_requested_location_id`),
  CONSTRAINT `phppos_employees_time_off_ibfk_1` FOREIGN KEY (`employee_requested_person_id`) REFERENCES `phppos_people` (`person_id`),
  CONSTRAINT `phppos_employees_time_off_ibfk_2` FOREIGN KEY (`employee_approved_person_id`) REFERENCES `phppos_people` (`person_id`),
  CONSTRAINT `phppos_employees_time_off_ibfk_3` FOREIGN KEY (`employee_requested_location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_employees_time_off`
--

LOCK TABLES `phppos_employees_time_off` WRITE;
/*!40000 ALTER TABLE `phppos_employees_time_off` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_employees_time_off` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_expenses`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_expenses` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `location_id` int(10) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `expense_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `expense_description` text COLLATE utf8_unicode_ci,
  `expense_reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `expense_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expense_amount` decimal(23,10) NOT NULL,
  `expense_tax` decimal(23,10) NOT NULL,
  `expense_note` text COLLATE utf8_unicode_ci NOT NULL,
  `employee_id` int(10) NOT NULL,
  `approved_employee_id` int(10) DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `expense_payment_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `expense_image_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `location_id` (`location_id`),
  KEY `employee_id` (`employee_id`),
  KEY `approved_employee_id` (`approved_employee_id`),
  KEY `category_id` (`category_id`),
  KEY `deleted` (`deleted`),
  KEY `expense_type` (`expense_type`),
  KEY `expense_date` (`expense_date`),
  KEY `expense_amount` (`expense_amount`),
  KEY `expense_description` (`expense_description`(255)),
  KEY `expense_reason` (`expense_reason`),
  KEY `expense_note` (`expense_note`(255)),
  KEY `expense_image_id` (`expense_image_id`),
  CONSTRAINT `phppos_expenses_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_expenses_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_expenses_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `phppos_expenses_categories` (`id`),
  CONSTRAINT `phppos_expenses_ibfk_4` FOREIGN KEY (`approved_employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_expenses_ibfk_5` FOREIGN KEY (`expense_image_id`) REFERENCES `phppos_app_files` (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_expenses`
--

LOCK TABLES `phppos_expenses` WRITE;
/*!40000 ALTER TABLE `phppos_expenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_expenses_categories`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_expenses_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted` (`deleted`),
  KEY `phppos_expenses_categories_ibfk_1` (`parent_id`),
  KEY `name` (`name`),
  CONSTRAINT `phppos_expenses_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `phppos_expenses_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_expenses_categories`
--

LOCK TABLES `phppos_expenses_categories` WRITE;
/*!40000 ALTER TABLE `phppos_expenses_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_expenses_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_expenses_files`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_expenses_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_id` int(11) NOT NULL,
  `expense_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`),
  KEY `expense_id` (`expense_id`),
  CONSTRAINT `phppos_expenses_files_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `phppos_app_files` (`file_id`),
  CONSTRAINT `phppos_expenses_files_ibfk_2` FOREIGN KEY (`expense_id`) REFERENCES `phppos_expenses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_expenses_files`
--

LOCK TABLES `phppos_expenses_files` WRITE;
/*!40000 ALTER TABLE `phppos_expenses_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_expenses_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_giftcards`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_giftcards` (
  `giftcard_id` int(11) NOT NULL AUTO_INCREMENT,
  `giftcard_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `value` decimal(23,10) NOT NULL,
  `customer_id` int(10) DEFAULT NULL,
  `inactive` int(1) NOT NULL DEFAULT '0',
  `deleted` int(1) NOT NULL DEFAULT '0',
  `integrated_gift_card` int(1) NOT NULL DEFAULT '0',
  `integrated_auth_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`giftcard_id`),
  UNIQUE KEY `giftcard_number` (`giftcard_number`),
  KEY `deleted` (`deleted`),
  KEY `phppos_giftcards_ibfk_1` (`customer_id`),
  KEY `description` (`description`(255)),
  CONSTRAINT `phppos_giftcards_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `phppos_customers` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_giftcards`
--

LOCK TABLES `phppos_giftcards` WRITE;
/*!40000 ALTER TABLE `phppos_giftcards` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_giftcards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_giftcards_log`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_giftcards_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `giftcard_id` int(11) NOT NULL,
  `transaction_amount` decimal(23,10) NOT NULL,
  `log_message` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_giftcards_log_ibfk_1` (`giftcard_id`),
  CONSTRAINT `phppos_giftcards_log_ibfk_1` FOREIGN KEY (`giftcard_id`) REFERENCES `phppos_giftcards` (`giftcard_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_giftcards_log`
--

LOCK TABLES `phppos_giftcards_log` WRITE;
/*!40000 ALTER TABLE `phppos_giftcards_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_giftcards_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_grid_hidden_categories`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_grid_hidden_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_grid` (`category_id`,`location_id`),
  KEY `phppos_grid_hidden_categories_ibfk_2` (`location_id`),
  CONSTRAINT `phppos_grid_hidden_categories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `phppos_categories` (`id`),
  CONSTRAINT `phppos_grid_hidden_categories_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_grid_hidden_categories`
--

LOCK TABLES `phppos_grid_hidden_categories` WRITE;
/*!40000 ALTER TABLE `phppos_grid_hidden_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_grid_hidden_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_grid_hidden_item_kits`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_grid_hidden_item_kits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_kit_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_grid` (`item_kit_id`,`location_id`),
  KEY `phppos_grid_hidden_item_kits_ibfk_2` (`location_id`),
  CONSTRAINT `phppos_grid_hidden_item_kits_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`),
  CONSTRAINT `phppos_grid_hidden_item_kits_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_grid_hidden_item_kits`
--

LOCK TABLES `phppos_grid_hidden_item_kits` WRITE;
/*!40000 ALTER TABLE `phppos_grid_hidden_item_kits` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_grid_hidden_item_kits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_grid_hidden_items`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_grid_hidden_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_grid` (`item_id`,`location_id`),
  KEY `phppos_grid_hidden_items_ibfk_2` (`location_id`),
  CONSTRAINT `phppos_grid_hidden_items_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_grid_hidden_items_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_grid_hidden_items`
--

LOCK TABLES `phppos_grid_hidden_items` WRITE;
/*!40000 ALTER TABLE `phppos_grid_hidden_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_grid_hidden_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_grid_hidden_tags`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_grid_hidden_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_grid` (`tag_id`,`location_id`),
  KEY `phppos_grid_hidden_tags_ibfk_2` (`location_id`),
  CONSTRAINT `phppos_grid_hidden_tags_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES `phppos_tags` (`id`),
  CONSTRAINT `phppos_grid_hidden_tags_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_grid_hidden_tags`
--

LOCK TABLES `phppos_grid_hidden_tags` WRITE;
/*!40000 ALTER TABLE `phppos_grid_hidden_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_grid_hidden_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_inventory`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_inventory` (
  `trans_id` int(11) NOT NULL AUTO_INCREMENT,
  `trans_items` int(11) NOT NULL DEFAULT '0',
  `item_variation_id` int(10) DEFAULT NULL,
  `trans_user` int(11) NOT NULL DEFAULT '0',
  `trans_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `trans_comment` text COLLATE utf8_unicode_ci NOT NULL,
  `trans_inventory` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `location_id` int(11) NOT NULL,
  `trans_current_quantity` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`trans_id`),
  KEY `phppos_inventory_ibfk_1` (`trans_items`),
  KEY `phppos_inventory_ibfk_2` (`trans_user`),
  KEY `location_id` (`location_id`),
  KEY `trans_date` (`trans_date`,`trans_inventory`,`location_id`),
  KEY `phppos_inventory_ibfk_4` (`item_variation_id`),
  KEY `phppos_inventory_custom` (`trans_items`,`location_id`,`trans_date`,`item_variation_id`,`trans_id`),
  CONSTRAINT `phppos_inventory_ibfk_1` FOREIGN KEY (`trans_items`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_inventory_ibfk_2` FOREIGN KEY (`trans_user`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_inventory_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_inventory_ibfk_4` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_inventory`
--

LOCK TABLES `phppos_inventory` WRITE;
/*!40000 ALTER TABLE `phppos_inventory` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_inventory_counts`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_inventory_counts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `count_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `employee_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_inventory_counts_ibfk_1` (`employee_id`),
  KEY `phppos_inventory_counts_ibfk_2` (`location_id`),
  CONSTRAINT `phppos_inventory_counts_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_inventory_counts_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_inventory_counts`
--

LOCK TABLES `phppos_inventory_counts` WRITE;
/*!40000 ALTER TABLE `phppos_inventory_counts` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_inventory_counts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_inventory_counts_items`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_inventory_counts_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inventory_counts_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_variation_id` int(10) DEFAULT NULL,
  `count` decimal(23,10) DEFAULT '0.0000000000',
  `actual_quantity` decimal(23,10) DEFAULT '0.0000000000',
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_inventory_counts_items_ibfk_1` (`inventory_counts_id`),
  KEY `phppos_inventory_counts_items_ibfk_2` (`item_id`),
  KEY `inventory_counts_items_ibfk_3` (`item_variation_id`),
  CONSTRAINT `inventory_counts_items_ibfk_3` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`),
  CONSTRAINT `phppos_inventory_counts_items_ibfk_1` FOREIGN KEY (`inventory_counts_id`) REFERENCES `phppos_inventory_counts` (`id`),
  CONSTRAINT `phppos_inventory_counts_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_inventory_counts_items`
--

LOCK TABLES `phppos_inventory_counts_items` WRITE;
/*!40000 ALTER TABLE `phppos_inventory_counts_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_inventory_counts_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_item_attribute_values`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_item_attribute_values` (
  `item_id` int(10) NOT NULL,
  `attribute_value_id` int(10) NOT NULL,
  PRIMARY KEY (`attribute_value_id`,`item_id`),
  KEY `phppos_item_attribute_values_ibfk_1` (`item_id`),
  CONSTRAINT `phppos_item_attribute_values_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_item_attribute_values_ibfk_2` FOREIGN KEY (`attribute_value_id`) REFERENCES `phppos_attribute_values` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_item_attribute_values`
--

LOCK TABLES `phppos_item_attribute_values` WRITE;
/*!40000 ALTER TABLE `phppos_item_attribute_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_item_attribute_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_item_attributes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_item_attributes` (
  `attribute_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  PRIMARY KEY (`attribute_id`,`item_id`),
  KEY `phppos_item_attributes_ibfk_1` (`item_id`),
  CONSTRAINT `phppos_item_attributes_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_item_attributes_ibfk_2` FOREIGN KEY (`attribute_id`) REFERENCES `phppos_attributes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_item_attributes`
--

LOCK TABLES `phppos_item_attributes` WRITE;
/*!40000 ALTER TABLE `phppos_item_attributes` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_item_attributes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_item_images`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_item_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `alt_text` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `item_id` int(11) DEFAULT NULL,
  `item_variation_id` int(10) DEFAULT NULL,
  `ecommerce_image_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_item_images_ibfk_1` (`item_id`),
  KEY `phppos_item_images_ibfk_2` (`image_id`),
  KEY `phppos_item_images_ibfk_3` (`item_variation_id`),
  CONSTRAINT `phppos_item_images_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_item_images_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `phppos_app_files` (`file_id`),
  CONSTRAINT `phppos_item_images_ibfk_3` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_item_images`
--

LOCK TABLES `phppos_item_images` WRITE;
/*!40000 ALTER TABLE `phppos_item_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_item_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_item_kit_images`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_item_kit_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `alt_text` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `item_kit_id` int(11) DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_item_kit_images_ibfk_1` (`item_kit_id`),
  KEY `phppos_item_kit_images_ibfk_2` (`image_id`),
  CONSTRAINT `phppos_item_kit_images_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`),
  CONSTRAINT `phppos_item_kit_images_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `phppos_app_files` (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_item_kit_images`
--

LOCK TABLES `phppos_item_kit_images` WRITE;
/*!40000 ALTER TABLE `phppos_item_kit_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_item_kit_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_item_kit_item_kits`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_item_kit_item_kits` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `item_kit_id` int(11) NOT NULL,
  `item_kit_item_kit` int(11) NOT NULL,
  `quantity` decimal(23,10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_item_kit_item_kits_ibfk_1` (`item_kit_id`),
  KEY `phppos_item_kit_item_kits_ibfk_2` (`item_kit_item_kit`),
  CONSTRAINT `phppos_item_kit_item_kits_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_item_kit_item_kits_ibfk_2` FOREIGN KEY (`item_kit_item_kit`) REFERENCES `phppos_item_kits` (`item_kit_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_item_kit_item_kits`
--

LOCK TABLES `phppos_item_kit_item_kits` WRITE;
/*!40000 ALTER TABLE `phppos_item_kit_item_kits` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_item_kit_item_kits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_item_kit_items`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_item_kit_items` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `item_kit_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_variation_id` int(10) DEFAULT NULL,
  `quantity` decimal(23,10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_item_kit_items_ibfk_1` (`item_kit_id`),
  KEY `phppos_item_kit_items_ibfk_2` (`item_id`),
  KEY `phppos_item_kit_items_ibfk_3` (`item_variation_id`),
  CONSTRAINT `phppos_item_kit_items_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_item_kit_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_item_kit_items_ibfk_3` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_item_kit_items`
--

LOCK TABLES `phppos_item_kit_items` WRITE;
/*!40000 ALTER TABLE `phppos_item_kit_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_item_kit_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_item_kits`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_item_kits` (
  `item_kit_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_kit_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `product_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `manufacturer_id` int(11) DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tax_included` int(1) NOT NULL DEFAULT '0',
  `unit_price` decimal(23,10) DEFAULT NULL,
  `cost_price` decimal(23,10) DEFAULT NULL,
  `override_default_tax` int(1) NOT NULL DEFAULT '0',
  `is_ebt_item` int(1) NOT NULL DEFAULT '0',
  `commission_percent` decimal(23,10) DEFAULT '0.0000000000',
  `commission_percent_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `commission_fixed` decimal(23,10) DEFAULT '0.0000000000',
  `change_cost_price` int(1) NOT NULL DEFAULT '0',
  `disable_loyalty` int(1) NOT NULL DEFAULT '0',
  `deleted` int(1) NOT NULL DEFAULT '0',
  `tax_class_id` int(10) DEFAULT NULL,
  `max_discount_percent` decimal(15,3) DEFAULT NULL,
  `max_edit_price` decimal(23,10) DEFAULT NULL,
  `min_edit_price` decimal(23,10) DEFAULT NULL,
  `custom_field_1_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_2_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_3_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_4_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_5_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_6_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_7_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_8_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_9_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_10_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `required_age` int(10) DEFAULT NULL,
  `verify_age` int(1) NOT NULL DEFAULT '0',
  `allow_price_override_regardless_of_permissions` int(1) DEFAULT '0',
  `only_integer` int(1) NOT NULL DEFAULT '0',
  `is_barcoded` int(1) NOT NULL DEFAULT '1',
  `default_quantity` decimal(23,10) DEFAULT NULL,
  `disable_from_price_rules` int(1) DEFAULT '0',
  `main_image_id` int(10) DEFAULT NULL,
  `dynamic_pricing` int(1) NOT NULL DEFAULT '0',
  `info_popup` text COLLATE utf8_unicode_ci,
  `item_kit_inactive` int(1) DEFAULT '0',
  `barcode_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `is_favorite` int(1) DEFAULT '0',
  `loyalty_multiplier` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`item_kit_id`),
  UNIQUE KEY `item_kit_number` (`item_kit_number`),
  UNIQUE KEY `product_id` (`product_id`),
  KEY `deleted` (`deleted`),
  KEY `phppos_item_kits_ibfk_1` (`category_id`),
  KEY `phppos_item_kits_ibfk_2` (`manufacturer_id`),
  KEY `name` (`name`),
  KEY `description` (`description`),
  KEY `cost_price` (`cost_price`),
  KEY `unit_price` (`unit_price`),
  KEY `phppos_item_kits_ibfk_3` (`tax_class_id`),
  KEY `custom_field_1_value` (`custom_field_1_value`),
  KEY `custom_field_2_value` (`custom_field_2_value`),
  KEY `custom_field_3_value` (`custom_field_3_value`),
  KEY `custom_field_4_value` (`custom_field_4_value`),
  KEY `custom_field_5_value` (`custom_field_5_value`),
  KEY `custom_field_6_value` (`custom_field_6_value`),
  KEY `custom_field_7_value` (`custom_field_7_value`),
  KEY `custom_field_8_value` (`custom_field_8_value`),
  KEY `custom_field_9_value` (`custom_field_9_value`),
  KEY `custom_field_10_value` (`custom_field_10_value`),
  KEY `verify_age` (`verify_age`),
  KEY `phppos_item_kits_ibfk_4` (`main_image_id`),
  KEY `item_kit_inactive_index` (`item_kit_inactive`),
  KEY `is_favorite_index` (`is_favorite`),
  CONSTRAINT `phppos_item_kits_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `phppos_categories` (`id`),
  CONSTRAINT `phppos_item_kits_ibfk_2` FOREIGN KEY (`manufacturer_id`) REFERENCES `phppos_manufacturers` (`id`),
  CONSTRAINT `phppos_item_kits_ibfk_3` FOREIGN KEY (`tax_class_id`) REFERENCES `phppos_tax_classes` (`id`),
  CONSTRAINT `phppos_item_kits_ibfk_4` FOREIGN KEY (`main_image_id`) REFERENCES `phppos_item_kit_images` (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_item_kits`
--

LOCK TABLES `phppos_item_kits` WRITE;
/*!40000 ALTER TABLE `phppos_item_kits` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_item_kits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_item_kits_modifiers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_item_kits_modifiers` (
  `item_kit_id` int(10) NOT NULL,
  `modifier_id` int(10) NOT NULL,
  PRIMARY KEY (`item_kit_id`,`modifier_id`),
  KEY `phppos_item_kits_modifiers_ibfk_1` (`modifier_id`),
  CONSTRAINT `phppos_item_kits_modifiers_ibfk_1` FOREIGN KEY (`modifier_id`) REFERENCES `phppos_modifiers` (`id`),
  CONSTRAINT `phppos_item_kits_modifiers_ibfk_2` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_item_kits_modifiers`
--

LOCK TABLES `phppos_item_kits_modifiers` WRITE;
/*!40000 ALTER TABLE `phppos_item_kits_modifiers` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_item_kits_modifiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_item_kits_pricing_history`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_item_kits_pricing_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `on_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `employee_id` int(11) NOT NULL,
  `item_kit_id` int(11) NOT NULL,
  `location_id` int(11) DEFAULT NULL,
  `unit_price` decimal(23,10) DEFAULT NULL,
  `cost_price` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_item_kits_pricing_history_ibfk_1` (`item_kit_id`),
  KEY `phppos_item_kits_pricing_history_ibfk_2` (`location_id`),
  KEY `phppos_item_kits_pricing_history_ibfk_3` (`employee_id`),
  CONSTRAINT `phppos_item_kits_pricing_history_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`),
  CONSTRAINT `phppos_item_kits_pricing_history_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_item_kits_pricing_history_ibfk_3` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_item_kits_pricing_history`
--

LOCK TABLES `phppos_item_kits_pricing_history` WRITE;
/*!40000 ALTER TABLE `phppos_item_kits_pricing_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_item_kits_pricing_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_item_kits_secondary_categories`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_item_kits_secondary_categories` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `item_kit_id` int(10) NOT NULL,
  `category_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_kit_category` (`item_kit_id`,`category_id`),
  KEY `phppos_item_kits_secondary_categories_ibfk_1` (`item_kit_id`),
  KEY `phppos_item_kits_secondary_categories_ibfk_2` (`category_id`),
  CONSTRAINT `phppos_item_kits_secondary_categories_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`),
  CONSTRAINT `phppos_item_kits_secondary_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `phppos_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_item_kits_secondary_categories`
--

LOCK TABLES `phppos_item_kits_secondary_categories` WRITE;
/*!40000 ALTER TABLE `phppos_item_kits_secondary_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_item_kits_secondary_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_item_kits_tags`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_item_kits_tags` (
  `item_kit_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`item_kit_id`,`tag_id`),
  KEY `phppos_item_kits_tags_ibfk_2` (`tag_id`),
  CONSTRAINT `phppos_item_kits_tags_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`),
  CONSTRAINT `phppos_item_kits_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `phppos_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_item_kits_tags`
--

LOCK TABLES `phppos_item_kits_tags` WRITE;
/*!40000 ALTER TABLE `phppos_item_kits_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_item_kits_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_item_kits_taxes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_item_kits_taxes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `item_kit_id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(15,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tax` (`item_kit_id`,`name`,`percent`),
  CONSTRAINT `phppos_item_kits_taxes_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_item_kits_taxes`
--

LOCK TABLES `phppos_item_kits_taxes` WRITE;
/*!40000 ALTER TABLE `phppos_item_kits_taxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_item_kits_taxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_item_kits_tier_prices`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_item_kits_tier_prices` (
  `tier_id` int(10) NOT NULL,
  `item_kit_id` int(10) NOT NULL,
  `unit_price` decimal(23,10) DEFAULT '0.0000000000',
  `percent_off` decimal(15,3) DEFAULT NULL,
  `cost_plus_percent` decimal(15,3) DEFAULT NULL,
  `cost_plus_fixed_amount` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`tier_id`,`item_kit_id`),
  KEY `phppos_item_kits_tier_prices_ibfk_2` (`item_kit_id`),
  CONSTRAINT `phppos_item_kits_tier_prices_ibfk_1` FOREIGN KEY (`tier_id`) REFERENCES `phppos_price_tiers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_item_kits_tier_prices_ibfk_2` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_item_kits_tier_prices`
--

LOCK TABLES `phppos_item_kits_tier_prices` WRITE;
/*!40000 ALTER TABLE `phppos_item_kits_tier_prices` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_item_kits_tier_prices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_item_variation_attribute_values`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_item_variation_attribute_values` (
  `attribute_value_id` int(10) NOT NULL,
  `item_variation_id` int(10) NOT NULL,
  PRIMARY KEY (`attribute_value_id`,`item_variation_id`),
  KEY `phppos_item_variation_attribute_values_ibfk_2` (`item_variation_id`),
  CONSTRAINT `phppos_item_variation_attribute_values_ibfk_1` FOREIGN KEY (`attribute_value_id`) REFERENCES `phppos_attribute_values` (`id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_item_variation_attribute_values_ibfk_2` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_item_variation_attribute_values`
--

LOCK TABLES `phppos_item_variation_attribute_values` WRITE;
/*!40000 ALTER TABLE `phppos_item_variation_attribute_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_item_variation_attribute_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_item_variations`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_item_variations` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `ecommerce_variation_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ecommerce_variation_quantity` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `item_id` int(10) NOT NULL,
  `reorder_level` decimal(23,10) DEFAULT NULL,
  `replenish_level` decimal(23,10) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `item_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unit_price` decimal(23,10) DEFAULT NULL,
  `cost_price` decimal(23,10) DEFAULT NULL,
  `promo_price` decimal(23,10) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ecommerce_last_modified` timestamp NULL DEFAULT NULL,
  `is_ecommerce` int(1) NOT NULL DEFAULT '1',
  `ecommerce_inventory_item_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_number` (`item_number`),
  KEY `phppos_item_variations_ibfk_1` (`item_id`),
  KEY `phppos_item_variations_ibfk_2` (`ecommerce_variation_id`),
  KEY `ecommerce_inventory_item_id` (`ecommerce_inventory_item_id`),
  CONSTRAINT `phppos_item_variations_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_item_variations`
--

LOCK TABLES `phppos_item_variations` WRITE;
/*!40000 ALTER TABLE `phppos_item_variations` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_item_variations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_items`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_items` (
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `manufacturer_id` int(11) DEFAULT NULL,
  `item_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `product_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ecommerce_product_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ecommerce_product_quantity` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `size` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tax_included` int(1) NOT NULL DEFAULT '0',
  `cost_price` decimal(23,10) NOT NULL,
  `unit_price` decimal(23,10) NOT NULL,
  `promo_price` decimal(23,10) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `reorder_level` decimal(23,10) DEFAULT NULL,
  `expire_days` int(10) DEFAULT NULL,
  `item_id` int(10) NOT NULL AUTO_INCREMENT,
  `allow_alt_description` tinyint(1) NOT NULL,
  `is_serialized` tinyint(1) NOT NULL,
  `override_default_tax` int(1) NOT NULL DEFAULT '0',
  `is_ecommerce` int(1) DEFAULT '1',
  `is_service` int(1) NOT NULL DEFAULT '0',
  `is_ebt_item` int(1) NOT NULL DEFAULT '0',
  `commission_percent` decimal(23,10) DEFAULT '0.0000000000',
  `commission_percent_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `commission_fixed` decimal(23,10) DEFAULT '0.0000000000',
  `change_cost_price` int(1) NOT NULL DEFAULT '0',
  `disable_loyalty` int(1) NOT NULL DEFAULT '0',
  `deleted` int(1) NOT NULL DEFAULT '0',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ecommerce_last_modified` timestamp NULL DEFAULT NULL,
  `tax_class_id` int(10) DEFAULT NULL,
  `replenish_level` decimal(23,10) DEFAULT NULL,
  `system_item` int(1) NOT NULL DEFAULT '0',
  `max_discount_percent` decimal(15,3) DEFAULT NULL,
  `max_edit_price` decimal(23,10) DEFAULT NULL,
  `min_edit_price` decimal(23,10) DEFAULT NULL,
  `custom_field_1_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_2_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_3_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_4_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_5_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_6_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_7_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_8_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_9_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_10_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `required_age` int(10) DEFAULT NULL,
  `verify_age` int(1) NOT NULL DEFAULT '0',
  `weight` decimal(23,10) DEFAULT NULL,
  `length` decimal(23,10) DEFAULT NULL,
  `width` decimal(23,10) DEFAULT NULL,
  `height` decimal(23,10) DEFAULT NULL,
  `ecommerce_shipping_class_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `long_description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `allow_price_override_regardless_of_permissions` int(1) DEFAULT '0',
  `main_image_id` int(10) DEFAULT NULL,
  `only_integer` int(1) NOT NULL DEFAULT '0',
  `is_series_package` int(1) NOT NULL DEFAULT '0',
  `series_quantity` int(10) DEFAULT NULL,
  `series_days_to_use_within` int(10) DEFAULT NULL,
  `is_barcoded` int(1) NOT NULL DEFAULT '1',
  `default_quantity` decimal(23,10) DEFAULT NULL,
  `disable_from_price_rules` int(1) DEFAULT '0',
  `last_edited` timestamp NULL DEFAULT NULL,
  `info_popup` text COLLATE utf8_unicode_ci,
  `item_inactive` int(1) DEFAULT '0',
  `barcode_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tags` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `is_favorite` int(1) DEFAULT '0',
  `loyalty_multiplier` decimal(23,10) DEFAULT NULL,
  `ecommerce_inventory_item_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `weight_unit` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  UNIQUE KEY `item_number` (`item_number`),
  UNIQUE KEY `product_id` (`product_id`),
  KEY `phppos_items_ibfk_1` (`supplier_id`),
  KEY `deleted` (`deleted`),
  KEY `phppos_items_ibfk_3` (`category_id`),
  KEY `phppos_items_ibfk_4` (`manufacturer_id`),
  KEY `phppos_items_ibfk_5` (`ecommerce_product_id`),
  KEY `description` (`description`(255)),
  KEY `size` (`size`),
  KEY `reorder_level` (`reorder_level`),
  KEY `cost_price` (`cost_price`),
  KEY `unit_price` (`unit_price`),
  KEY `promo_price` (`promo_price`),
  KEY `last_modified` (`last_modified`),
  KEY `name` (`name`),
  KEY `phppos_items_ibfk_6` (`tax_class_id`),
  KEY `deleted_system_item` (`deleted`,`system_item`),
  KEY `custom_field_1_value` (`custom_field_1_value`),
  KEY `custom_field_2_value` (`custom_field_2_value`),
  KEY `custom_field_3_value` (`custom_field_3_value`),
  KEY `custom_field_4_value` (`custom_field_4_value`),
  KEY `custom_field_5_value` (`custom_field_5_value`),
  KEY `custom_field_6_value` (`custom_field_6_value`),
  KEY `custom_field_7_value` (`custom_field_7_value`),
  KEY `custom_field_8_value` (`custom_field_8_value`),
  KEY `custom_field_9_value` (`custom_field_9_value`),
  KEY `custom_field_10_value` (`custom_field_10_value`),
  KEY `verify_age` (`verify_age`),
  KEY `phppos_items_ibfk_7` (`main_image_id`),
  KEY `item_inactive_index` (`item_inactive`),
  KEY `tags` (`tags`),
  KEY `is_favorite_index` (`is_favorite`),
  KEY `ecommerce_product_id` (`ecommerce_product_id`),
  KEY `ecommerce_inventory_item_id` (`ecommerce_inventory_item_id`),
  CONSTRAINT `phppos_items_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `phppos_suppliers` (`person_id`),
  CONSTRAINT `phppos_items_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `phppos_categories` (`id`),
  CONSTRAINT `phppos_items_ibfk_4` FOREIGN KEY (`manufacturer_id`) REFERENCES `phppos_manufacturers` (`id`),
  CONSTRAINT `phppos_items_ibfk_6` FOREIGN KEY (`tax_class_id`) REFERENCES `phppos_tax_classes` (`id`),
  CONSTRAINT `phppos_items_ibfk_7` FOREIGN KEY (`main_image_id`) REFERENCES `phppos_item_images` (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_items`
--

LOCK TABLES `phppos_items` WRITE;
/*!40000 ALTER TABLE `phppos_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_items_modifiers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_items_modifiers` (
  `item_id` int(10) NOT NULL,
  `modifier_id` int(10) NOT NULL,
  PRIMARY KEY (`item_id`,`modifier_id`),
  KEY `phppos_items_modifiers_ibfk_1` (`modifier_id`),
  CONSTRAINT `phppos_items_modifiers_ibfk_1` FOREIGN KEY (`modifier_id`) REFERENCES `phppos_modifiers` (`id`),
  CONSTRAINT `phppos_items_modifiers_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_items_modifiers`
--

LOCK TABLES `phppos_items_modifiers` WRITE;
/*!40000 ALTER TABLE `phppos_items_modifiers` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_items_modifiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_items_pricing_history`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_items_pricing_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `on_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `employee_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_variation_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `unit_price` decimal(23,10) DEFAULT NULL,
  `cost_price` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_items_pricing_history_ibfk_1` (`item_id`),
  KEY `phppos_items_pricing_history_ibfk_2` (`item_variation_id`),
  KEY `phppos_items_pricing_history_ibfk_3` (`location_id`),
  KEY `phppos_items_pricing_history_ibfk_4` (`employee_id`),
  CONSTRAINT `phppos_items_pricing_history_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_items_pricing_history_ibfk_2` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`),
  CONSTRAINT `phppos_items_pricing_history_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_items_pricing_history_ibfk_4` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_items_pricing_history`
--

LOCK TABLES `phppos_items_pricing_history` WRITE;
/*!40000 ALTER TABLE `phppos_items_pricing_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_items_pricing_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_items_quantity_units`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_items_quantity_units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `unit_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `unit_quantity` decimal(23,10) NOT NULL,
  `unit_price` decimal(23,10) DEFAULT NULL,
  `cost_price` decimal(23,10) DEFAULT NULL,
  `quantity_unit_item_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quantity_unit_item_number` (`quantity_unit_item_number`),
  KEY `phppos_items_quantity_units_ibfk_1` (`item_id`),
  CONSTRAINT `phppos_items_quantity_units_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_items_quantity_units`
--

LOCK TABLES `phppos_items_quantity_units` WRITE;
/*!40000 ALTER TABLE `phppos_items_quantity_units` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_items_quantity_units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_items_secondary_categories`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_items_secondary_categories` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `item_id` int(10) NOT NULL,
  `category_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_category` (`item_id`,`category_id`),
  KEY `phppos_items_secondary_categories_ibfk_1` (`item_id`),
  KEY `phppos_items_secondary_categories_ibfk_2` (`category_id`),
  CONSTRAINT `phppos_items_secondary_categories_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_items_secondary_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `phppos_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_items_secondary_categories`
--

LOCK TABLES `phppos_items_secondary_categories` WRITE;
/*!40000 ALTER TABLE `phppos_items_secondary_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_items_secondary_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_items_serial_numbers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_items_serial_numbers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `item_id` int(10) NOT NULL,
  `serial_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unit_price` decimal(23,10) DEFAULT NULL,
  `cost_price` decimal(23,10) DEFAULT NULL,
  `variation_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `serial_number` (`serial_number`),
  KEY `phppos_items_serial_numbers_ibfk_1` (`item_id`),
  KEY `phppos_items_serial_numbers_ibfk_2` (`variation_id`),
  CONSTRAINT `phppos_items_serial_numbers_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_items_serial_numbers_ibfk_2` FOREIGN KEY (`variation_id`) REFERENCES `phppos_item_variations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_items_serial_numbers`
--

LOCK TABLES `phppos_items_serial_numbers` WRITE;
/*!40000 ALTER TABLE `phppos_items_serial_numbers` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_items_serial_numbers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_items_tags`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_items_tags` (
  `item_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`item_id`,`tag_id`),
  KEY `phppos_items_tags_ibfk_2` (`tag_id`),
  CONSTRAINT `phppos_items_tags_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_items_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `phppos_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_items_tags`
--

LOCK TABLES `phppos_items_tags` WRITE;
/*!40000 ALTER TABLE `phppos_items_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_items_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_items_taxes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_items_taxes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `item_id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(15,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tax` (`item_id`,`name`,`percent`),
  CONSTRAINT `phppos_items_taxes_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_items_taxes`
--

LOCK TABLES `phppos_items_taxes` WRITE;
/*!40000 ALTER TABLE `phppos_items_taxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_items_taxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_items_tier_prices`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_items_tier_prices` (
  `tier_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  `unit_price` decimal(23,10) DEFAULT '0.0000000000',
  `percent_off` decimal(15,3) DEFAULT NULL,
  `cost_plus_percent` decimal(15,3) DEFAULT NULL,
  `cost_plus_fixed_amount` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`tier_id`,`item_id`),
  KEY `phppos_items_tier_prices_ibfk_2` (`item_id`),
  CONSTRAINT `phppos_items_tier_prices_ibfk_1` FOREIGN KEY (`tier_id`) REFERENCES `phppos_price_tiers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_items_tier_prices_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_items_tier_prices`
--

LOCK TABLES `phppos_items_tier_prices` WRITE;
/*!40000 ALTER TABLE `phppos_items_tier_prices` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_items_tier_prices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_keys`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `key` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `key_ending` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `level` int(2) NOT NULL,
  `ignore_limits` tinyint(1) NOT NULL DEFAULT '0',
  `is_private_key` tinyint(1) NOT NULL DEFAULT '0',
  `ip_addresses` text COLLATE utf8_unicode_ci,
  `date_created` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `phppos_keys_user_id_fk` (`user_id`),
  CONSTRAINT `phppos_keys_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `phppos_employees` (`person_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_keys`
--

LOCK TABLES `phppos_keys` WRITE;
/*!40000 ALTER TABLE `phppos_keys` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_limits`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_key` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `uri` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(10) NOT NULL,
  `hour_started` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uri` (`uri`),
  KEY `phppos_limits_api_key_fk` (`api_key`),
  CONSTRAINT `phppos_limits_api_key_fk` FOREIGN KEY (`api_key`) REFERENCES `phppos_keys` (`key`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_limits`
--

LOCK TABLES `phppos_limits` WRITE;
/*!40000 ALTER TABLE `phppos_limits` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_location_ban_item_kits`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_location_ban_item_kits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_kit_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_location` (`item_kit_id`,`location_id`) USING BTREE,
  KEY `location_id` (`location_id`),
  CONSTRAINT `phppos_location_ban_item_kits_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`),
  CONSTRAINT `phppos_location_ban_item_kits_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_location_ban_item_kits`
--

LOCK TABLES `phppos_location_ban_item_kits` WRITE;
/*!40000 ALTER TABLE `phppos_location_ban_item_kits` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_location_ban_item_kits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_location_ban_items`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_location_ban_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_location` (`item_id`,`location_id`) USING BTREE,
  KEY `location_id` (`location_id`),
  CONSTRAINT `phppos_location_ban_items_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_location_ban_items_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_location_ban_items`
--

LOCK TABLES `phppos_location_ban_items` WRITE;
/*!40000 ALTER TABLE `phppos_location_ban_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_location_ban_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_location_ban_tags`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_location_ban_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_location` (`tag_id`,`location_id`) USING BTREE,
  KEY `location_id` (`location_id`),
  CONSTRAINT `phppos_location_ban_tags_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES `phppos_tags` (`id`),
  CONSTRAINT `phppos_location_ban_tags_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_location_ban_tags`
--

LOCK TABLES `phppos_location_ban_tags` WRITE;
/*!40000 ALTER TABLE `phppos_location_ban_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_location_ban_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_location_item_kits`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_location_item_kits` (
  `location_id` int(11) NOT NULL,
  `item_kit_id` int(11) NOT NULL,
  `unit_price` decimal(23,10) DEFAULT NULL,
  `cost_price` decimal(23,10) DEFAULT NULL,
  `override_default_tax` int(1) NOT NULL DEFAULT '0',
  `tax_class_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`location_id`,`item_kit_id`),
  KEY `phppos_location_item_kits_ibfk_2` (`item_kit_id`),
  KEY `phppos_location_item_kits_ibfk_3` (`tax_class_id`),
  CONSTRAINT `phppos_location_item_kits_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_location_item_kits_ibfk_2` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`),
  CONSTRAINT `phppos_location_item_kits_ibfk_3` FOREIGN KEY (`tax_class_id`) REFERENCES `phppos_tax_classes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_location_item_kits`
--

LOCK TABLES `phppos_location_item_kits` WRITE;
/*!40000 ALTER TABLE `phppos_location_item_kits` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_location_item_kits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_location_item_kits_taxes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_location_item_kits_taxes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) NOT NULL,
  `item_kit_id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(16,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tax` (`location_id`,`item_kit_id`,`name`,`percent`),
  KEY `phppos_location_item_kits_taxes_ibfk_2` (`item_kit_id`),
  CONSTRAINT `phppos_location_item_kits_taxes_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_location_item_kits_taxes_ibfk_2` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_location_item_kits_taxes`
--

LOCK TABLES `phppos_location_item_kits_taxes` WRITE;
/*!40000 ALTER TABLE `phppos_location_item_kits_taxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_location_item_kits_taxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_location_item_kits_tier_prices`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_location_item_kits_tier_prices` (
  `tier_id` int(10) NOT NULL,
  `item_kit_id` int(10) NOT NULL,
  `location_id` int(10) NOT NULL,
  `unit_price` decimal(23,10) DEFAULT '0.0000000000',
  `percent_off` decimal(15,3) DEFAULT NULL,
  `cost_plus_percent` decimal(15,3) DEFAULT NULL,
  `cost_plus_fixed_amount` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`tier_id`,`item_kit_id`,`location_id`),
  KEY `phppos_location_item_kits_tier_prices_ibfk_2` (`location_id`),
  KEY `phppos_location_item_kits_tier_prices_ibfk_3` (`item_kit_id`),
  CONSTRAINT `phppos_location_item_kits_tier_prices_ibfk_1` FOREIGN KEY (`tier_id`) REFERENCES `phppos_price_tiers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_location_item_kits_tier_prices_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_location_item_kits_tier_prices_ibfk_3` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_location_item_kits_tier_prices`
--

LOCK TABLES `phppos_location_item_kits_tier_prices` WRITE;
/*!40000 ALTER TABLE `phppos_location_item_kits_tier_prices` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_location_item_kits_tier_prices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_location_item_variations`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_location_item_variations` (
  `item_variation_id` int(10) NOT NULL,
  `location_id` int(10) NOT NULL,
  `quantity` decimal(23,10) DEFAULT NULL,
  `reorder_level` decimal(23,10) DEFAULT NULL,
  `replenish_level` decimal(23,10) DEFAULT NULL,
  `unit_price` decimal(23,10) DEFAULT NULL,
  `cost_price` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`item_variation_id`,`location_id`),
  KEY `phppos_item_attribute_location_values_ibfk_2` (`location_id`),
  CONSTRAINT `phppos_item_attribute_location_values_ibfk_1` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`),
  CONSTRAINT `phppos_item_attribute_location_values_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_location_item_variations`
--

LOCK TABLES `phppos_location_item_variations` WRITE;
/*!40000 ALTER TABLE `phppos_location_item_variations` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_location_item_variations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_location_items`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_location_items` (
  `location_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `location` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cost_price` decimal(23,10) DEFAULT NULL,
  `unit_price` decimal(23,10) DEFAULT NULL,
  `promo_price` decimal(23,10) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `quantity` decimal(23,10) DEFAULT '0.0000000000',
  `reorder_level` decimal(23,10) DEFAULT NULL,
  `override_default_tax` int(1) NOT NULL DEFAULT '0',
  `tax_class_id` int(10) DEFAULT NULL,
  `replenish_level` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`location_id`,`item_id`),
  KEY `phppos_location_items_ibfk_2` (`item_id`),
  KEY `quantity` (`quantity`),
  KEY `phppos_location_items_ibfk_3` (`tax_class_id`),
  CONSTRAINT `phppos_location_items_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_location_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_location_items_ibfk_3` FOREIGN KEY (`tax_class_id`) REFERENCES `phppos_tax_classes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_location_items`
--

LOCK TABLES `phppos_location_items` WRITE;
/*!40000 ALTER TABLE `phppos_location_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_location_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_location_items_taxes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_location_items_taxes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) NOT NULL,
  `item_id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(16,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tax` (`location_id`,`item_id`,`name`,`percent`),
  KEY `phppos_location_items_taxes_ibfk_2` (`item_id`),
  CONSTRAINT `phppos_location_items_taxes_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_location_items_taxes_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_location_items_taxes`
--

LOCK TABLES `phppos_location_items_taxes` WRITE;
/*!40000 ALTER TABLE `phppos_location_items_taxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_location_items_taxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_location_items_tier_prices`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_location_items_tier_prices` (
  `tier_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  `location_id` int(10) NOT NULL,
  `unit_price` decimal(23,10) DEFAULT '0.0000000000',
  `percent_off` decimal(15,3) DEFAULT NULL,
  `cost_plus_percent` decimal(15,3) DEFAULT NULL,
  `cost_plus_fixed_amount` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`tier_id`,`item_id`,`location_id`),
  KEY `phppos_location_items_tier_prices_ibfk_2` (`location_id`),
  KEY `phppos_location_items_tier_prices_ibfk_3` (`item_id`),
  CONSTRAINT `phppos_location_items_tier_prices_ibfk_1` FOREIGN KEY (`tier_id`) REFERENCES `phppos_price_tiers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `phppos_location_items_tier_prices_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_location_items_tier_prices_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_location_items_tier_prices`
--

LOCK TABLES `phppos_location_items_tier_prices` WRITE;
/*!40000 ALTER TABLE `phppos_location_items_tier_prices` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_location_items_tier_prices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_locations`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_locations` (
  `location_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8_unicode_ci,
  `company` text COLLATE utf8_unicode_ci,
  `website` text COLLATE utf8_unicode_ci,
  `company_logo` int(10) DEFAULT NULL,
  `address` text COLLATE utf8_unicode_ci,
  `phone` text COLLATE utf8_unicode_ci,
  `fax` text COLLATE utf8_unicode_ci,
  `email` text COLLATE utf8_unicode_ci,
  `cc_email` text COLLATE utf8_unicode_ci,
  `bcc_email` text COLLATE utf8_unicode_ci,
  `color` text COLLATE utf8_unicode_ci,
  `return_policy` text COLLATE utf8_unicode_ci,
  `receive_stock_alert` text COLLATE utf8_unicode_ci,
  `stock_alert_email` text COLLATE utf8_unicode_ci,
  `timezone` text COLLATE utf8_unicode_ci,
  `mailchimp_api_key` text COLLATE utf8_unicode_ci,
  `enable_credit_card_processing` text COLLATE utf8_unicode_ci,
  `credit_card_processor` text COLLATE utf8_unicode_ci,
  `hosted_checkout_merchant_id` text COLLATE utf8_unicode_ci,
  `hosted_checkout_merchant_password` text COLLATE utf8_unicode_ci,
  `emv_merchant_id` text COLLATE utf8_unicode_ci,
  `net_e_pay_server` text COLLATE utf8_unicode_ci,
  `listener_port` text COLLATE utf8_unicode_ci,
  `com_port` text COLLATE utf8_unicode_ci,
  `stripe_public` text COLLATE utf8_unicode_ci,
  `stripe_private` text COLLATE utf8_unicode_ci,
  `stripe_currency_code` text COLLATE utf8_unicode_ci,
  `braintree_merchant_id` text COLLATE utf8_unicode_ci,
  `braintree_public_key` text COLLATE utf8_unicode_ci,
  `braintree_private_key` text COLLATE utf8_unicode_ci,
  `default_tax_1_rate` text COLLATE utf8_unicode_ci,
  `default_tax_1_name` text COLLATE utf8_unicode_ci,
  `default_tax_2_rate` text COLLATE utf8_unicode_ci,
  `default_tax_2_name` text COLLATE utf8_unicode_ci,
  `default_tax_2_cumulative` text COLLATE utf8_unicode_ci,
  `default_tax_3_rate` text COLLATE utf8_unicode_ci,
  `default_tax_3_name` text COLLATE utf8_unicode_ci,
  `default_tax_4_rate` text COLLATE utf8_unicode_ci,
  `default_tax_4_name` text COLLATE utf8_unicode_ci,
  `default_tax_5_rate` text COLLATE utf8_unicode_ci,
  `default_tax_5_name` text COLLATE utf8_unicode_ci,
  `deleted` int(1) DEFAULT '0',
  `secure_device_override_emv` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `secure_device_override_non_emv` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tax_class_id` int(10) DEFAULT NULL,
  `ebt_integrated` int(1) NOT NULL DEFAULT '0',
  `integrated_gift_cards` int(1) NOT NULL DEFAULT '0',
  `square_currency_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'USD',
  `square_location_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `square_currency_multiplier` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '100',
  `email_sales_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email_receivings_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stock_alerts_just_order_level` int(1) DEFAULT '0',
  `platformly_api_key` text COLLATE utf8_unicode_ci,
  `platformly_project_id` text COLLATE utf8_unicode_ci,
  `tax_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `disable_markup_markdown` text COLLATE utf8_unicode_ci,
  `card_connect_mid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `card_connect_rest_username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `card_connect_rest_password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `default_mailchimp_lists` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `twilio_sid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `twilio_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `twilio_sms_from` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `auto_reports_email` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `auto_reports_email_time` time DEFAULT NULL,
  `auto_reports_day` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'previous_day',
  `disable_confirmation_option_for_emv_credit_card` int(1) NOT NULL DEFAULT '0',
  `blockchyp_api_key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `blockchyp_bearer_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `blockchyp_signing_key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `blockchyp_test_mode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`location_id`),
  KEY `deleted` (`deleted`),
  KEY `phppos_locations_ibfk_1` (`company_logo`),
  KEY `name` (`name`(255)),
  KEY `address` (`address`(255)),
  KEY `phone` (`phone`(255)),
  KEY `email` (`email`(255)),
  KEY `phppos_locations_ibfk_2` (`tax_class_id`),
  CONSTRAINT `phppos_locations_ibfk_1` FOREIGN KEY (`company_logo`) REFERENCES `phppos_app_files` (`file_id`),
  CONSTRAINT `phppos_locations_ibfk_2` FOREIGN KEY (`tax_class_id`) REFERENCES `phppos_tax_classes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_locations`
--

LOCK TABLES `phppos_locations` WRITE;
/*!40000 ALTER TABLE `phppos_locations` DISABLE KEYS */;
INSERT INTO `phppos_locations` VALUES (1,'Default',NULL,NULL,NULL,'123 Nowhere street','555-555-5555','','no-reply@phppointofsale.com',NULL,NULL,NULL,NULL,'0','','America/New_York','','0',NULL,'','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','','','','0','','','','','','',0,'','',NULL,1,0,'USD','','100',NULL,NULL,0,NULL,NULL,'',NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,'',NULL,'previous_day',0,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `phppos_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_logs`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_key` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `uri` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `method` enum('get','post','options','put','patch','delete') COLLATE utf8_unicode_ci NOT NULL,
  `params` text COLLATE utf8_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(11) NOT NULL,
  `rtime` float DEFAULT NULL,
  `authorized` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `response_code` smallint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_logs`
--

LOCK TABLES `phppos_logs` WRITE;
/*!40000 ALTER TABLE `phppos_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_manufacturers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_manufacturers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted` (`deleted`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_manufacturers`
--

LOCK TABLES `phppos_manufacturers` WRITE;
/*!40000 ALTER TABLE `phppos_manufacturers` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_manufacturers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_message_receiver`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_message_receiver` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message_read` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phppos_message_receiver_ibfk_2` (`receiver_id`),
  KEY `phppos_message_receiver_key_1` (`message_id`,`receiver_id`),
  CONSTRAINT `phppos_message_receiver_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `phppos_messages` (`id`),
  CONSTRAINT `phppos_message_receiver_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `phppos_employees` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_message_receiver`
--

LOCK TABLES `phppos_message_receiver` WRITE;
/*!40000 ALTER TABLE `phppos_message_receiver` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_message_receiver` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_messages`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `sender_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phppos_messages_ibfk_1` (`sender_id`),
  KEY `phppos_messages_key_1` (`deleted`,`created_at`,`id`),
  CONSTRAINT `phppos_messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `phppos_employees` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_messages`
--

LOCK TABLES `phppos_messages` WRITE;
/*!40000 ALTER TABLE `phppos_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_migrations`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
DROP TABLE IF EXISTS `phppos_migrations`;
CREATE TABLE `phppos_migrations` (
  `version` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_migrations`
--

LOCK TABLES `phppos_migrations` WRITE;
/*!40000 ALTER TABLE `phppos_migrations` DISABLE KEYS */;
INSERT INTO `phppos_migrations` VALUES (20211218025608);
/*!40000 ALTER TABLE `phppos_migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_modifier_items`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_modifier_items` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `sort_order` int(10) NOT NULL DEFAULT '0',
  `modifier_id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cost_price` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `unit_price` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phppos_modifier_items_ibfk_1` (`modifier_id`),
  KEY `sort_index` (`deleted`,`modifier_id`,`sort_order`),
  CONSTRAINT `phppos_modifier_items_ibfk_1` FOREIGN KEY (`modifier_id`) REFERENCES `phppos_modifiers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_modifier_items`
--

LOCK TABLES `phppos_modifier_items` WRITE;
/*!40000 ALTER TABLE `phppos_modifier_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_modifier_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_modifiers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_modifiers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `sort_order` int(10) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `sort_index` (`deleted`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_modifiers`
--

LOCK TABLES `phppos_modifiers` WRITE;
/*!40000 ALTER TABLE `phppos_modifiers` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_modifiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_modules`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_modules` (
  `name_lang_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `desc_lang_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sort` int(10) NOT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `module_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`module_id`),
  UNIQUE KEY `desc_lang_key` (`desc_lang_key`),
  UNIQUE KEY `name_lang_key` (`name_lang_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_modules`
--

LOCK TABLES `phppos_modules` WRITE;
/*!40000 ALTER TABLE `phppos_modules` DISABLE KEYS */;
INSERT INTO `phppos_modules` VALUES ('module_appointments','module_appointments_desc',75,'ti-calendar','appointments'),('module_config','module_config_desc',100,'icon ti-settings','config'),('module_customers','module_customers_desc',10,'icon ti-user','customers'),('module_deliveries','module_deliveries_desc',71,'ion-android-car','deliveries'),('module_employees','module_employees_desc',80,'icon ti-id-badge','employees'),('module_expenses','module_expenses_desc',75,'icon ti-money','expenses'),('module_giftcards','module_giftcards_desc',90,'icon ti-credit-card','giftcards'),('module_item_kits','module_item_kits_desc',30,'icon ti-harddrives','item_kits'),('module_items','module_items_desc',20,'icon ti-harddrive','items'),('module_locations','module_locations_desc',110,'icon ti-home','locations'),('module_messages','module_messages_desc',120,'icon ti-email','messages'),('module_price_rules','module_item_price_rules_desc',35,'ion-ios-pricetags-outline','price_rules'),('module_receivings','module_receivings_desc',60,'icon ti-cloud-down','receivings'),('module_reports','module_reports_desc',50,'icon ti-bar-chart','reports'),('module_sales','module_sales_desc',70,'icon ti-shopping-cart','sales'),('module_suppliers','module_suppliers_desc',40,'icon ti-download','suppliers'),('module_work_orders','module_work_orders_desc',71,'ion-hammer','work_orders');
/*!40000 ALTER TABLE `phppos_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_modules_actions`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_modules_actions` (
  `action_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `module_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `action_name_key` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`action_id`,`module_id`),
  KEY `phppos_modules_actions_ibfk_1` (`module_id`),
  CONSTRAINT `phppos_modules_actions_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `phppos_modules` (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_modules_actions`
--

LOCK TABLES `phppos_modules_actions` WRITE;
/*!40000 ALTER TABLE `phppos_modules_actions` DISABLE KEYS */;
INSERT INTO `phppos_modules_actions` VALUES ('add','appointments','appointments_add',240),('add_update','customers','module_action_add_update',1),('add_update','deliveries','deliveries_add_update',240),('add_update','employees','module_action_add_update',130),('add_update','expenses','module_expenses_add_update',315),('add_update','giftcards','module_action_add_update',200),('add_update','item_kits','module_action_add_update',70),('add_update','items','module_action_add_update',40),('add_update','locations','module_action_add_update',240),('add_update','price_rules','module_action_add_update',400),('add_update','suppliers','module_action_add_update',100),('allow_customer_search_suggestions_for_sales','sales','sales_allow_customer_search_suggestions_for_sales',302),('allow_item_search_suggestions_for_receivings','receivings','receivings_allow_item_search_suggestions_for_receivings',301),('allow_item_search_suggestions_for_sales','sales','sales_allow_item_search_suggestions_for_sales',300),('allow_supplier_search_suggestions_for_suppliers','receivings','receivings_allow_supplier_search_suggestions_for_suppliers',303),('assign_all_locations','employees','module_action_assign_all_locations',151),('can_edit_inventory_comment','items','items_can_edit_inventory_comment',500),('can_lookup_last_receipt','sales','sales_can_lookup_last_receipt',503),('can_lookup_receipt','sales','sales_can_lookup_receipt',503),('change_sale_date','sales','sales_change_sale_date',184),('complete_sale','sales','sales_complete_sale',184),('complete_transfer','receivings','receivings_complete_transfer',184),('count_inventory','items','items_count_inventory',65),('delete','appointments','appointments_delete',250),('delete','customers','module_action_delete',20),('delete','deliveries','deliveries_delete',250),('delete','employees','module_action_delete',140),('delete','expenses','module_expenses_delete',330),('delete','giftcards','module_action_delete',210),('delete','item_kits','module_action_delete',80),('delete','items','module_action_delete',50),('delete','locations','module_action_delete',250),('delete','price_rules','module_action_delete',405),('delete','suppliers','module_action_delete',110),('delete','work_orders','work_orders_delete',241),('delete_receiving','receivings','module_action_delete_receiving',306),('delete_register_log','reports','common_delete_register_log',232),('delete_sale','sales','module_action_delete_sale',230),('delete_suspended_receiving','receivings','module_action_delete_suspended_receiving',181),('delete_suspended_sale','sales','module_action_delete_suspended_sale',181),('delete_taxes','receivings','module_action_delete_taxes',300),('delete_taxes','sales','module_action_delete_taxes',182),('edit','appointments','appointments_edit',245),('edit','deliveries','deliveries_edit',245),('edit','work_orders','work_orders_edit',240),('edit_customer_points','customers','module_edit_customer_points',35),('edit_giftcard_value','giftcards','module_edit_giftcard_value',205),('edit_prices','item_kits','common_edit_prices',502),('edit_prices','items','common_edit_prices',501),('edit_profile','employees','common_edit_profile',155),('edit_quantity','items','items_edit_quantity',62),('edit_receiving','receivings','module_action_edit_receiving',303),('edit_register_log','reports','common_edit_register_log',231),('edit_sale','sales','module_edit_sale',190),('edit_sale_cost_price','sales','module_edit_sale_cost_price',175),('edit_sale_price','sales','module_edit_sale_price',170),('edit_store_account_balance','customers','customers_edit_store_account_balance',31),('edit_store_account_balance','suppliers','suppliers_edit_store_account_balance',130),('edit_suspended_sale','sales','sales_edit_suspended_sale',192),('edit_taxes','receivings','module_edit_taxes',304),('edit_taxes','sales','module_edit_taxes',191),('edit_tier','customers','customers_edit_tier',45),('excel_export','customers','common_excel_export',40),('excel_export','employees','common_excel_export',160),('excel_export','giftcards','common_excel_export',225),('excel_export','item_kits','common_excel_export',95),('excel_export','items','common_excel_export',80),('excel_export','suppliers','common_excel_export',135),('give_discount','sales','module_give_discount',180),('manage_categories','deliveries','items_manage_categories',256),('manage_categories','expenses','items_manage_categories',316),('manage_categories','items','items_manage_categories',70),('manage_manufacturers','items','items_manage_manufacturers',76),('manage_statuses','deliveries','deliveries_manage_statuses',251),('manage_statuses','work_orders','work_orders_manage_statuses',243),('manage_tags','items','items_manage_tags',75),('process_returns','sales','config_process_returns',184),('receive_store_account_payment','receivings','common_receive_store_account_payment',260),('receive_store_account_payment','sales','common_receive_store_account_payment',255),('search','appointments','appointments_search',255),('search','customers','module_action_search_customers',30),('search','deliveries','deliveries_search',255),('search','employees','module_action_search_employees',150),('search','expenses','module_expenses_search',310),('search','giftcards','module_action_search_giftcards',220),('search','item_kits','module_action_search_item_kits',90),('search','items','module_action_search_items',60),('search','locations','module_action_search_locations',260),('search','price_rules','module_action_search_price_rules',415),('search','sales','module_action_search_sales',235),('search','suppliers','module_action_search_suppliers',120),('search','work_orders','work_orders_search',242),('see_all_item_kits','item_kits','common_see_all_item_kits',505),('see_all_items','items','common_see_all_items',504),('see_cost_price','item_kits','module_see_cost_price',91),('see_cost_price','items','module_see_cost_price',61),('see_count_when_count_inventory','items','items_see_count_when_count_inventory',66),('send_message','messages','employees_send_message',350),('send_transfer','receivings','receivings_send_transfer',185),('show_cost_price','reports','reports_show_cost_price',290),('show_profit','reports','reports_show_profit',280),('suspend_sale','sales','sales_suspend_sale',183),('view_all_employee_commissions','reports','reports_view_all_employee_commissions',107),('view_appointments','reports','reports_appointments',95),('view_categories','reports','reports_categories',100),('view_closeout','reports','reports_closeout',105),('view_commissions','reports','reports_commission',106),('view_customers','reports','reports_customers',120),('view_dashboard_stats','reports','reports_view_dashboard_stats',300),('view_deleted_sales','reports','reports_deleted_sales',130),('view_deliveries','reports','reports_deliveries',135),('view_discounts','reports','reports_discounts',140),('view_edit_transaction_history','sales','common_view_edit_transaction_history',400),('view_employees','reports','reports_employees',150),('view_expenses','reports','module_expenses_report',155),('view_giftcards','reports','reports_giftcards',160),('view_inventory_at_all_locations','items','common_view_inventory_at_all_locations',268),('view_inventory_at_all_locations','reports','reports_view_inventory_at_all_locations',300),('view_inventory_print_list','items','common_view_inventory_print_list',267),('view_inventory_reports','reports','reports_inventory_reports',170),('view_item_kits','reports','module_item_kits',180),('view_items','reports','reports_items',190),('view_manufacturers','reports','reports_manufacturers',195),('view_payments','reports','reports_payments',200),('view_price_rules','reports','reports_price_rules',205),('view_profit_and_loss','reports','reports_profit_and_loss',210),('view_receivings','reports','reports_receivings',220),('view_register_log','reports','reports_register_log_title',230),('view_registers','reports','reports_registers',235),('view_sales','reports','reports_sales',240),('view_sales_generator','reports','reports_sales_generator',110),('view_store_account','reports','reports_store_account',250),('view_store_account_suppliers','reports','reports_store_account_suppliers',255),('view_suppliers','reports','reports_suppliers',260),('view_suspended_receipt','receivings','receivings_view_suspended_receipt',503),('view_suspended_receipt','sales','sales_view_suspended_receipt',503),('view_suspended_sales','reports','reports_suspended_sales',261),('view_tags','reports','common_tags',264),('view_taxes','reports','reports_taxes',270),('view_tiers','reports','reports_tiers',275),('view_timeclock','reports','employees_timeclock',280);
/*!40000 ALTER TABLE `phppos_modules_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_people`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_people` (
  `first_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `full_name` text COLLATE utf8_unicode_ci NOT NULL,
  `phone_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address_1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address_2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `zip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `comments` text COLLATE utf8_unicode_ci NOT NULL,
  `image_id` int(10) DEFAULT NULL,
  `person_id` int(10) NOT NULL AUTO_INCREMENT,
  `create_date` timestamp NULL DEFAULT NULL,
  `last_modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`person_id`),
  KEY `phppos_people_ibfk_1` (`image_id`),
  KEY `first_name` (`first_name`),
  KEY `last_name` (`last_name`),
  KEY `email` (`email`),
  KEY `phone_number` (`phone_number`),
  KEY `full_name` (`full_name`(255)),
  CONSTRAINT `phppos_people_ibfk_1` FOREIGN KEY (`image_id`) REFERENCES `phppos_app_files` (`file_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_people`
--

LOCK TABLES `phppos_people` WRITE;
/*!40000 ALTER TABLE `phppos_people` DISABLE KEYS */;
INSERT INTO `phppos_people` VALUES ('John','Doe','John Doe','555-555-5555','no-reply@phppointofsale.com','Address 1','','','','','','',NULL,1,NULL,NULL);
/*!40000 ALTER TABLE `phppos_people` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_people_files`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_people_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_id` int(10) NOT NULL,
  `person_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_people_files_ibfk_1` (`file_id`),
  CONSTRAINT `phppos_people_files_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `phppos_app_files` (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_people_files`
--

LOCK TABLES `phppos_people_files` WRITE;
/*!40000 ALTER TABLE `phppos_people_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_people_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_permissions`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_permissions` (
  `module_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `person_id` int(10) NOT NULL,
  PRIMARY KEY (`module_id`,`person_id`),
  KEY `person_id` (`person_id`),
  CONSTRAINT `phppos_permissions_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_permissions_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `phppos_modules` (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_permissions`
--

LOCK TABLES `phppos_permissions` WRITE;
/*!40000 ALTER TABLE `phppos_permissions` DISABLE KEYS */;
INSERT INTO `phppos_permissions` VALUES ('appointments',1),('config',1),('customers',1),('deliveries',1),('employees',1),('expenses',1),('giftcards',1),('item_kits',1),('items',1),('locations',1),('messages',1),('price_rules',1),('receivings',1),('reports',1),('sales',1),('suppliers',1),('work_orders',1);
/*!40000 ALTER TABLE `phppos_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_permissions_actions`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_permissions_actions` (
  `module_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `person_id` int(11) NOT NULL,
  `action_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`module_id`,`person_id`,`action_id`),
  KEY `phppos_permissions_actions_ibfk_2` (`person_id`),
  KEY `phppos_permissions_actions_ibfk_3` (`action_id`),
  CONSTRAINT `phppos_permissions_actions_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `phppos_modules` (`module_id`),
  CONSTRAINT `phppos_permissions_actions_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_permissions_actions_ibfk_3` FOREIGN KEY (`action_id`) REFERENCES `phppos_modules_actions` (`action_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_permissions_actions`
--

LOCK TABLES `phppos_permissions_actions` WRITE;
/*!40000 ALTER TABLE `phppos_permissions_actions` DISABLE KEYS */;
INSERT INTO `phppos_permissions_actions` VALUES ('appointments',1,'add'),('appointments',1,'delete'),('appointments',1,'edit'),('appointments',1,'search'),('customers',1,'add_update'),('customers',1,'delete'),('customers',1,'edit_customer_points'),('customers',1,'edit_store_account_balance'),('customers',1,'edit_tier'),('customers',1,'excel_export'),('customers',1,'search'),('deliveries',1,'add_update'),('deliveries',1,'delete'),('deliveries',1,'edit'),('deliveries',1,'manage_categories'),('deliveries',1,'manage_statuses'),('deliveries',1,'search'),('employees',1,'add_update'),('employees',1,'assign_all_locations'),('employees',1,'delete'),('employees',1,'edit_profile'),('employees',1,'excel_export'),('employees',1,'search'),('expenses',1,'add_update'),('expenses',1,'delete'),('expenses',1,'manage_categories'),('expenses',1,'search'),('giftcards',1,'add_update'),('giftcards',1,'delete'),('giftcards',1,'edit_giftcard_value'),('giftcards',1,'excel_export'),('giftcards',1,'search'),('item_kits',1,'add_update'),('item_kits',1,'delete'),('item_kits',1,'edit_prices'),('item_kits',1,'excel_export'),('item_kits',1,'search'),('item_kits',1,'see_all_item_kits'),('item_kits',1,'see_cost_price'),('items',1,'add_update'),('items',1,'can_edit_inventory_comment'),('items',1,'count_inventory'),('items',1,'delete'),('items',1,'edit_prices'),('items',1,'edit_quantity'),('items',1,'excel_export'),('items',1,'manage_categories'),('items',1,'manage_manufacturers'),('items',1,'manage_tags'),('items',1,'search'),('items',1,'see_all_items'),('items',1,'see_cost_price'),('items',1,'see_count_when_count_inventory'),('items',1,'view_inventory_at_all_locations'),('items',1,'view_inventory_print_list'),('locations',1,'add_update'),('locations',1,'delete'),('locations',1,'search'),('messages',1,'send_message'),('price_rules',1,'add_update'),('price_rules',1,'delete'),('price_rules',1,'search'),('receivings',1,'allow_item_search_suggestions_for_receivings'),('receivings',1,'allow_supplier_search_suggestions_for_suppliers'),('receivings',1,'complete_transfer'),('receivings',1,'delete_receiving'),('receivings',1,'delete_suspended_receiving'),('receivings',1,'delete_taxes'),('receivings',1,'edit_receiving'),('receivings',1,'edit_taxes'),('receivings',1,'receive_store_account_payment'),('receivings',1,'send_transfer'),('receivings',1,'view_suspended_receipt'),('reports',1,'delete_register_log'),('reports',1,'edit_register_log'),('reports',1,'show_cost_price'),('reports',1,'show_profit'),('reports',1,'view_all_employee_commissions'),('reports',1,'view_appointments'),('reports',1,'view_categories'),('reports',1,'view_closeout'),('reports',1,'view_commissions'),('reports',1,'view_customers'),('reports',1,'view_dashboard_stats'),('reports',1,'view_deleted_sales'),('reports',1,'view_deliveries'),('reports',1,'view_discounts'),('reports',1,'view_employees'),('reports',1,'view_expenses'),('reports',1,'view_giftcards'),('reports',1,'view_inventory_at_all_locations'),('reports',1,'view_inventory_reports'),('reports',1,'view_item_kits'),('reports',1,'view_items'),('reports',1,'view_manufacturers'),('reports',1,'view_payments'),('reports',1,'view_price_rules'),('reports',1,'view_profit_and_loss'),('reports',1,'view_receivings'),('reports',1,'view_register_log'),('reports',1,'view_registers'),('reports',1,'view_sales'),('reports',1,'view_sales_generator'),('reports',1,'view_store_account'),('reports',1,'view_store_account_suppliers'),('reports',1,'view_suppliers'),('reports',1,'view_suspended_sales'),('reports',1,'view_tags'),('reports',1,'view_taxes'),('reports',1,'view_tiers'),('reports',1,'view_timeclock'),('sales',1,'allow_customer_search_suggestions_for_sales'),('sales',1,'allow_item_search_suggestions_for_sales'),('sales',1,'can_lookup_last_receipt'),('sales',1,'can_lookup_receipt'),('sales',1,'change_sale_date'),('sales',1,'complete_sale'),('sales',1,'delete_sale'),('sales',1,'delete_suspended_sale'),('sales',1,'delete_taxes'),('sales',1,'edit_sale'),('sales',1,'edit_sale_cost_price'),('sales',1,'edit_sale_price'),('sales',1,'edit_suspended_sale'),('sales',1,'edit_taxes'),('sales',1,'give_discount'),('sales',1,'process_returns'),('sales',1,'receive_store_account_payment'),('sales',1,'search'),('sales',1,'suspend_sale'),('sales',1,'view_edit_transaction_history'),('sales',1,'view_suspended_receipt'),('suppliers',1,'add_update'),('suppliers',1,'delete'),('suppliers',1,'edit_store_account_balance'),('suppliers',1,'excel_export'),('suppliers',1,'search'),('work_orders',1,'delete'),('work_orders',1,'edit'),('work_orders',1,'manage_statuses'),('work_orders',1,'search');
/*!40000 ALTER TABLE `phppos_permissions_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_permissions_actions_locations`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_permissions_actions_locations` (
  `module_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `person_id` int(11) NOT NULL,
  `action_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `location_id` int(10) NOT NULL,
  PRIMARY KEY (`module_id`,`person_id`,`action_id`,`location_id`),
  KEY `phppos_permissions_actions_locations_ibfk_2` (`person_id`),
  KEY `phppos_permissions_actions_locations_ibfk_3` (`action_id`),
  KEY `phppos_permissions_actions_locations_ibfk_4` (`location_id`),
  CONSTRAINT `phppos_permissions_actions_locations_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `phppos_modules` (`module_id`),
  CONSTRAINT `phppos_permissions_actions_locations_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_permissions_actions_locations_ibfk_3` FOREIGN KEY (`action_id`) REFERENCES `phppos_modules_actions` (`action_id`),
  CONSTRAINT `phppos_permissions_actions_locations_ibfk_4` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_permissions_actions_locations`
--

LOCK TABLES `phppos_permissions_actions_locations` WRITE;
/*!40000 ALTER TABLE `phppos_permissions_actions_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_permissions_actions_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_permissions_locations`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_permissions_locations` (
  `module_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `person_id` int(10) NOT NULL,
  `location_id` int(10) NOT NULL,
  PRIMARY KEY (`module_id`,`person_id`,`location_id`),
  KEY `phppos_permissions_locations_ibfk_1` (`person_id`),
  KEY `phppos_permissions_locations_ibfk_3` (`location_id`),
  CONSTRAINT `phppos_permissions_locations_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_permissions_locations_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `phppos_modules` (`module_id`),
  CONSTRAINT `phppos_permissions_locations_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_permissions_locations`
--

LOCK TABLES `phppos_permissions_locations` WRITE;
/*!40000 ALTER TABLE `phppos_permissions_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_permissions_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_permissions_template`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_permissions_template` (
  `template_id` int(11) NOT NULL,
  `module_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`template_id`,`module_id`),
  KEY `phppos_permissions_template_ibfk_1` (`module_id`),
  CONSTRAINT `phppos_permissions_template_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `phppos_modules` (`module_id`),
  CONSTRAINT `phppos_permissions_template_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `phppos_permissions_templates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_permissions_template`
--

LOCK TABLES `phppos_permissions_template` WRITE;
/*!40000 ALTER TABLE `phppos_permissions_template` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_permissions_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_permissions_template_actions`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_permissions_template_actions` (
  `template_id` int(11) NOT NULL,
  `module_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `action_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`template_id`,`module_id`,`action_id`),
  KEY `phppos_permissions_template_actions_ibfk_2` (`action_id`),
  KEY `phppos_permissions_template_actions_ibfk_3` (`template_id`),
  KEY `phppos_permissions_template_actions_ibfk_1` (`module_id`),
  CONSTRAINT `phppos_permissions_template_actions_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `phppos_modules` (`module_id`),
  CONSTRAINT `phppos_permissions_template_actions_ibfk_2` FOREIGN KEY (`action_id`) REFERENCES `phppos_modules_actions` (`action_id`),
  CONSTRAINT `phppos_permissions_template_actions_ibfk_3` FOREIGN KEY (`template_id`) REFERENCES `phppos_permissions_templates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_permissions_template_actions`
--

LOCK TABLES `phppos_permissions_template_actions` WRITE;
/*!40000 ALTER TABLE `phppos_permissions_template_actions` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_permissions_template_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_permissions_template_actions_locations`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_permissions_template_actions_locations` (
  `template_id` int(11) NOT NULL,
  `module_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `action_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `location_id` int(10) NOT NULL,
  PRIMARY KEY (`template_id`,`module_id`,`action_id`,`location_id`),
  KEY `phppos_permissions_template_actions_locations_ibfk_2` (`action_id`),
  KEY `phppos_permissions_template_actions_locations_ibfk_3` (`location_id`),
  KEY `phppos_permissions_template_actions_locations_ibfk_4` (`template_id`),
  KEY `phppos_permissions_template_actions_locations_ibfk_1` (`module_id`),
  CONSTRAINT `phppos_permissions_template_actions_locations_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `phppos_modules` (`module_id`),
  CONSTRAINT `phppos_permissions_template_actions_locations_ibfk_2` FOREIGN KEY (`action_id`) REFERENCES `phppos_modules_actions` (`action_id`),
  CONSTRAINT `phppos_permissions_template_actions_locations_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_permissions_template_actions_locations_ibfk_4` FOREIGN KEY (`template_id`) REFERENCES `phppos_permissions_templates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_permissions_template_actions_locations`
--

LOCK TABLES `phppos_permissions_template_actions_locations` WRITE;
/*!40000 ALTER TABLE `phppos_permissions_template_actions_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_permissions_template_actions_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_permissions_template_locations`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_permissions_template_locations` (
  `template_id` int(11) NOT NULL,
  `module_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `location_id` int(10) NOT NULL,
  PRIMARY KEY (`template_id`,`module_id`,`location_id`),
  KEY `phppos_permissions_template_locations_ibfk_2` (`location_id`),
  KEY `phppos_permissions_template_locations_ibfk_3` (`template_id`),
  KEY `phppos_permissions_template_locations_ibfk_1` (`module_id`),
  CONSTRAINT `phppos_permissions_template_locations_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `phppos_modules` (`module_id`),
  CONSTRAINT `phppos_permissions_template_locations_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_permissions_template_locations_ibfk_3` FOREIGN KEY (`template_id`) REFERENCES `phppos_permissions_templates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_permissions_template_locations`
--

LOCK TABLES `phppos_permissions_template_locations` WRITE;
/*!40000 ALTER TABLE `phppos_permissions_template_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_permissions_template_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_permissions_templates`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_permissions_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `deleted` int(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `deleted` (`deleted`),
  KEY `name` (`name`),
  KEY `name_deleted` (`name`,`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_permissions_templates`
--

LOCK TABLES `phppos_permissions_templates` WRITE;
/*!40000 ALTER TABLE `phppos_permissions_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_permissions_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_price_rules`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_price_rules` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `added_on` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `active` int(1) NOT NULL DEFAULT '1',
  `deleted` int(1) NOT NULL DEFAULT '0',
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `items_to_buy` decimal(23,10) DEFAULT NULL,
  `items_to_get` decimal(23,10) DEFAULT NULL,
  `percent_off` decimal(23,10) DEFAULT NULL,
  `fixed_off` decimal(23,10) DEFAULT NULL,
  `spend_amount` decimal(23,10) DEFAULT NULL,
  `num_times_to_apply` int(10) NOT NULL,
  `coupon_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `show_on_receipt` int(1) NOT NULL DEFAULT '0',
  `coupon_spend_amount` decimal(23,10) DEFAULT NULL,
  `mix_and_match` int(1) NOT NULL DEFAULT '0',
  `disable_loyalty_for_rule` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `start_date` (`start_date`),
  KEY `end_date` (`end_date`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_price_rules`
--

LOCK TABLES `phppos_price_rules` WRITE;
/*!40000 ALTER TABLE `phppos_price_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_price_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_price_rules_categories`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_price_rules_categories` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) NOT NULL,
  `category_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_price_rules_categories_ibfk_1` (`rule_id`),
  KEY `phppos_price_rules_categories_ibfk_2` (`category_id`),
  CONSTRAINT `phppos_price_rules_categories_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`),
  CONSTRAINT `phppos_price_rules_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `phppos_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_price_rules_categories`
--

LOCK TABLES `phppos_price_rules_categories` WRITE;
/*!40000 ALTER TABLE `phppos_price_rules_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_price_rules_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_price_rules_item_kits`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_price_rules_item_kits` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) NOT NULL,
  `item_kit_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_price_rules_item_kits_ibfk_1` (`rule_id`),
  KEY `phppos_price_rules_item_kits_ibfk_2` (`item_kit_id`),
  CONSTRAINT `phppos_price_rules_item_kits_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`),
  CONSTRAINT `phppos_price_rules_item_kits_ibfk_2` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_price_rules_item_kits`
--

LOCK TABLES `phppos_price_rules_item_kits` WRITE;
/*!40000 ALTER TABLE `phppos_price_rules_item_kits` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_price_rules_item_kits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_price_rules_items`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_price_rules_items` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_price_rules_items_ibfk_1` (`rule_id`),
  KEY `phppos_price_rules_items_ibfk_2` (`item_id`),
  CONSTRAINT `phppos_price_rules_items_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`),
  CONSTRAINT `phppos_price_rules_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_price_rules_items`
--

LOCK TABLES `phppos_price_rules_items` WRITE;
/*!40000 ALTER TABLE `phppos_price_rules_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_price_rules_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_price_rules_locations`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_price_rules_locations` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) NOT NULL,
  `location_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_price_rules_locations_ibfk_1` (`rule_id`),
  KEY `phppos_price_rules_locations_ibfk_2` (`location_id`),
  CONSTRAINT `phppos_price_rules_locations_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`),
  CONSTRAINT `phppos_price_rules_locations_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_price_rules_locations`
--

LOCK TABLES `phppos_price_rules_locations` WRITE;
/*!40000 ALTER TABLE `phppos_price_rules_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_price_rules_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_price_rules_manufacturers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_price_rules_manufacturers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) NOT NULL,
  `manufacturer_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_price_rules_manufacturers_ibfk_1` (`rule_id`),
  KEY `phppos_price_rules_manufacturers_ibfk_2` (`manufacturer_id`),
  CONSTRAINT `phppos_price_rules_manufacturers_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`),
  CONSTRAINT `phppos_price_rules_manufacturers_ibfk_2` FOREIGN KEY (`manufacturer_id`) REFERENCES `phppos_manufacturers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_price_rules_manufacturers`
--

LOCK TABLES `phppos_price_rules_manufacturers` WRITE;
/*!40000 ALTER TABLE `phppos_price_rules_manufacturers` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_price_rules_manufacturers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_price_rules_price_breaks`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_price_rules_price_breaks` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) NOT NULL,
  `item_qty_to_buy` decimal(23,10) DEFAULT NULL,
  `discount_per_unit_fixed` decimal(23,10) DEFAULT NULL,
  `discount_per_unit_percent` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_price_rules_custom_ibfk_1` (`rule_id`),
  CONSTRAINT `phppos_price_rules_price_breaks_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_price_rules_price_breaks`
--

LOCK TABLES `phppos_price_rules_price_breaks` WRITE;
/*!40000 ALTER TABLE `phppos_price_rules_price_breaks` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_price_rules_price_breaks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_price_rules_tags`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_price_rules_tags` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) NOT NULL,
  `tag_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_price_rules_tags_ibfk_1` (`rule_id`),
  KEY `phppos_price_rules_tags_ibfk_2` (`tag_id`),
  CONSTRAINT `phppos_price_rules_tags_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`),
  CONSTRAINT `phppos_price_rules_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `phppos_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_price_rules_tags`
--

LOCK TABLES `phppos_price_rules_tags` WRITE;
/*!40000 ALTER TABLE `phppos_price_rules_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_price_rules_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_price_rules_tiers_exclude`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_price_rules_tiers_exclude` (
  `price_rule_id` int(10) NOT NULL,
  `tier_id` int(10) NOT NULL,
  PRIMARY KEY (`price_rule_id`,`tier_id`),
  KEY `phppos_price_rules_tiers_ibfk_2` (`tier_id`),
  CONSTRAINT `phppos_price_rules_tiers_ibfk_1` FOREIGN KEY (`price_rule_id`) REFERENCES `phppos_price_rules` (`id`),
  CONSTRAINT `phppos_price_rules_tiers_ibfk_2` FOREIGN KEY (`tier_id`) REFERENCES `phppos_price_tiers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_price_rules_tiers_exclude`
--

LOCK TABLES `phppos_price_rules_tiers_exclude` WRITE;
/*!40000 ALTER TABLE `phppos_price_rules_tiers_exclude` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_price_rules_tiers_exclude` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_price_tiers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_price_tiers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `order` int(10) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `default_percent_off` decimal(15,3) DEFAULT NULL,
  `default_cost_plus_percent` decimal(15,3) DEFAULT NULL,
  `default_cost_plus_fixed_amount` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_price_tiers`
--

LOCK TABLES `phppos_price_tiers` WRITE;
/*!40000 ALTER TABLE `phppos_price_tiers` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_price_tiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_processing_return_logs`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_processing_return_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `return_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `employee_id` int(10) NOT NULL,
  `sale_id` int(10) DEFAULT NULL,
  `orig_voided_processor_transaction_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `voided_processor_transaction_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(23,10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_processing_return_logs_ibfk_1` (`employee_id`),
  KEY `phppos_processing_return_logs_ibfk_2` (`sale_id`),
  CONSTRAINT `phppos_processing_return_logs_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_processing_return_logs_ibfk_2` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_processing_return_logs`
--

LOCK TABLES `phppos_processing_return_logs` WRITE;
/*!40000 ALTER TABLE `phppos_processing_return_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_processing_return_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_receivings`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_receivings` (
  `receiving_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `supplier_id` int(10) DEFAULT NULL,
  `employee_id` int(10) NOT NULL DEFAULT '0',
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `receiving_id` int(10) NOT NULL AUTO_INCREMENT,
  `payment_type` text COLLATE utf8_unicode_ci,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `deleted_by` int(10) DEFAULT NULL,
  `suspended` int(1) NOT NULL DEFAULT '0',
  `location_id` int(11) NOT NULL,
  `transfer_to_location_id` int(11) DEFAULT NULL,
  `deleted_taxes` text COLLATE utf8_unicode_ci,
  `is_po` int(1) NOT NULL DEFAULT '0',
  `store_account_payment` int(1) NOT NULL DEFAULT '0',
  `total_quantity_purchased` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `total_quantity_received` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `subtotal` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `tax` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `total` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `profit` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `exchange_rate` decimal(23,10) NOT NULL DEFAULT '1.0000000000',
  `exchange_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `exchange_currency_symbol` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `exchange_currency_symbol_location` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `exchange_number_of_decimals` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `exchange_thousands_separator` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `exchange_decimal_point` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `custom_field_1_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_2_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_3_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_4_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_5_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_6_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_7_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_8_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_9_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_10_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_modified` timestamp NULL DEFAULT NULL,
  `override_taxes` text COLLATE utf8_unicode_ci,
  `shipping_cost` decimal(23,10) DEFAULT NULL,
  `signature_image_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`receiving_id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `employee_id` (`employee_id`),
  KEY `deleted` (`deleted`),
  KEY `location_id` (`location_id`),
  KEY `transfer_to_location_id` (`transfer_to_location_id`),
  KEY `recv_search` (`location_id`,`deleted`,`receiving_time`,`suspended`,`store_account_payment`,`total_quantity_purchased`),
  KEY `custom_field_1_value` (`custom_field_1_value`),
  KEY `custom_field_2_value` (`custom_field_2_value`),
  KEY `custom_field_3_value` (`custom_field_3_value`),
  KEY `custom_field_4_value` (`custom_field_4_value`),
  KEY `custom_field_5_value` (`custom_field_5_value`),
  KEY `custom_field_6_value` (`custom_field_6_value`),
  KEY `custom_field_7_value` (`custom_field_7_value`),
  KEY `custom_field_8_value` (`custom_field_8_value`),
  KEY `custom_field_9_value` (`custom_field_9_value`),
  KEY `custom_field_10_value` (`custom_field_10_value`),
  KEY `signature_image_id` (`signature_image_id`),
  CONSTRAINT `phppos_receivings_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_receivings_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `phppos_suppliers` (`person_id`),
  CONSTRAINT `phppos_receivings_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_receivings_ibfk_4` FOREIGN KEY (`transfer_to_location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_receivings_ibfk_5` FOREIGN KEY (`signature_image_id`) REFERENCES `phppos_app_files` (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_receivings`
--

LOCK TABLES `phppos_receivings` WRITE;
/*!40000 ALTER TABLE `phppos_receivings` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_receivings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_receivings_items`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_receivings_items` (
  `receiving_id` int(10) NOT NULL DEFAULT '0',
  `item_id` int(10) NOT NULL DEFAULT '0',
  `item_variation_id` int(10) DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `serialnumber` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `line` int(11) NOT NULL DEFAULT '0',
  `quantity_purchased` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `quantity_received` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `item_cost_price` decimal(23,10) NOT NULL,
  `item_unit_price` decimal(23,10) NOT NULL,
  `discount_percent` decimal(15,3) NOT NULL DEFAULT '0.000',
  `expire_date` date DEFAULT NULL,
  `subtotal` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `tax` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `total` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `profit` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `override_taxes` text COLLATE utf8_unicode_ci,
  `unit_quantity` decimal(23,10) DEFAULT NULL,
  `items_quantity_units_id` int(11) DEFAULT NULL,
  `receipt_line_sort_order` int(11) DEFAULT NULL,
  PRIMARY KEY (`receiving_id`,`item_id`,`line`),
  KEY `item_id` (`item_id`),
  KEY `phppos_receivings_items_ibfk_3` (`item_variation_id`),
  KEY `phppos_receivings_items_ibfk_4` (`items_quantity_units_id`),
  KEY `serialnumber` (`serialnumber`),
  CONSTRAINT `phppos_receivings_items_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_receivings_items_ibfk_2` FOREIGN KEY (`receiving_id`) REFERENCES `phppos_receivings` (`receiving_id`),
  CONSTRAINT `phppos_receivings_items_ibfk_3` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`),
  CONSTRAINT `phppos_receivings_items_ibfk_4` FOREIGN KEY (`items_quantity_units_id`) REFERENCES `phppos_items_quantity_units` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_receivings_items`
--

LOCK TABLES `phppos_receivings_items` WRITE;
/*!40000 ALTER TABLE `phppos_receivings_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_receivings_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_receivings_items_taxes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_receivings_items_taxes` (
  `receiving_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  `line` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(15,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`receiving_id`,`item_id`,`line`,`name`,`percent`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `phppos_receivings_items_taxes_ibfk_1` FOREIGN KEY (`receiving_id`) REFERENCES `phppos_receivings` (`receiving_id`),
  CONSTRAINT `phppos_receivings_items_taxes_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_receivings_items_taxes`
--

LOCK TABLES `phppos_receivings_items_taxes` WRITE;
/*!40000 ALTER TABLE `phppos_receivings_items_taxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_receivings_items_taxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_receivings_payments`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_receivings_payments` (
  `payment_id` int(10) NOT NULL AUTO_INCREMENT,
  `receiving_id` int(10) NOT NULL,
  `payment_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payment_amount` decimal(23,10) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `receiving_id` (`receiving_id`),
  KEY `payment_date` (`payment_date`),
  CONSTRAINT `phppos_receivings_payments_ibfk_1` FOREIGN KEY (`receiving_id`) REFERENCES `phppos_receivings` (`receiving_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_receivings_payments`
--

LOCK TABLES `phppos_receivings_payments` WRITE;
/*!40000 ALTER TABLE `phppos_receivings_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_receivings_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_register_currency_denominations`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_register_currency_denominations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` decimal(23,10) NOT NULL,
  `deleted` int(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_register_currency_denominations`
--

LOCK TABLES `phppos_register_currency_denominations` WRITE;
/*!40000 ALTER TABLE `phppos_register_currency_denominations` DISABLE KEYS */;
INSERT INTO `phppos_register_currency_denominations` VALUES (1,'100\'s',100.0000000000,0),(2,'50\'s',50.0000000000,0),(3,'20\'s',20.0000000000,0),(4,'10\'s',10.0000000000,0),(5,'5\'s',5.0000000000,0),(6,'2\'s',2.0000000000,0),(7,'1\'s',1.0000000000,0),(8,'Half Dollars',0.5000000000,0),(9,'Quarters',0.2500000000,0),(10,'Dimes',0.1000000000,0),(11,'Nickels',0.0500000000,0),(12,'Pennies',0.0100000000,0);
/*!40000 ALTER TABLE `phppos_register_currency_denominations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_register_log`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_register_log` (
  `register_log_id` int(10) NOT NULL AUTO_INCREMENT,
  `employee_id_open` int(10) NOT NULL,
  `employee_id_close` int(11) DEFAULT NULL,
  `register_id` int(11) DEFAULT NULL,
  `shift_start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `shift_end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `notes` text COLLATE utf8_unicode_ci NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`register_log_id`),
  KEY `phppos_register_log_ibfk_1` (`employee_id_open`),
  KEY `phppos_register_log_ibfk_2` (`register_id`),
  KEY `phppos_register_log_ibfk_3` (`employee_id_close`),
  CONSTRAINT `phppos_register_log_ibfk_1` FOREIGN KEY (`employee_id_open`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_register_log_ibfk_2` FOREIGN KEY (`register_id`) REFERENCES `phppos_registers` (`register_id`),
  CONSTRAINT `phppos_register_log_ibfk_3` FOREIGN KEY (`employee_id_close`) REFERENCES `phppos_employees` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_register_log`
--

LOCK TABLES `phppos_register_log` WRITE;
/*!40000 ALTER TABLE `phppos_register_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_register_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_register_log_audit`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_register_log_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `register_log_id` int(10) NOT NULL,
  `employee_id` int(10) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `amount` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `note` text COLLATE utf8_unicode_ci NOT NULL,
  `payment_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `register_log_audit_ibfk_1` (`register_log_id`),
  KEY `register_log_audit_ibfk_2` (`employee_id`),
  CONSTRAINT `register_log_audit_ibfk_1` FOREIGN KEY (`register_log_id`) REFERENCES `phppos_register_log` (`register_log_id`),
  CONSTRAINT `register_log_audit_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_register_log_audit`
--

LOCK TABLES `phppos_register_log_audit` WRITE;
/*!40000 ALTER TABLE `phppos_register_log_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_register_log_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_register_log_denoms`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_register_log_denoms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `register_log_id` int(11) NOT NULL,
  `register_currency_denominations_id` int(11) NOT NULL,
  `count` int(11) NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_register_log_denoms_ibfk_1` (`register_log_id`),
  KEY `phppos_register_log_denoms_ibfk_2` (`register_currency_denominations_id`),
  CONSTRAINT `phppos_register_log_denoms_ibfk_1` FOREIGN KEY (`register_log_id`) REFERENCES `phppos_register_log` (`register_log_id`),
  CONSTRAINT `phppos_register_log_denoms_ibfk_2` FOREIGN KEY (`register_currency_denominations_id`) REFERENCES `phppos_register_currency_denominations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_register_log_denoms`
--

LOCK TABLES `phppos_register_log_denoms` WRITE;
/*!40000 ALTER TABLE `phppos_register_log_denoms` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_register_log_denoms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_register_log_payments`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_register_log_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `register_log_id` int(10) NOT NULL,
  `payment_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `open_amount` decimal(23,10) NOT NULL,
  `close_amount` decimal(23,10) NOT NULL,
  `payment_sales_amount` decimal(23,10) NOT NULL,
  `total_payment_additions` decimal(23,10) NOT NULL,
  `total_payment_subtractions` decimal(23,10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_register_log_payments_ibfk_1` (`register_log_id`),
  CONSTRAINT `phppos_register_log_payments_ibfk_1` FOREIGN KEY (`register_log_id`) REFERENCES `phppos_register_log` (`register_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_register_log_payments`
--

LOCK TABLES `phppos_register_log_payments` WRITE;
/*!40000 ALTER TABLE `phppos_register_log_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_register_log_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_registers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_registers` (
  `register_id` int(11) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `iptran_device_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `emv_terminal_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `card_connect_hsn` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `emv_pinpad_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `emv_pinpad_port` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`register_id`),
  KEY `deleted` (`deleted`),
  KEY `phppos_registers_ibfk_1` (`location_id`),
  CONSTRAINT `phppos_registers_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_registers`
--

LOCK TABLES `phppos_registers` WRITE;
/*!40000 ALTER TABLE `phppos_registers` DISABLE KEYS */;
INSERT INTO `phppos_registers` VALUES (1,1,'Default',NULL,NULL,0,NULL,NULL,NULL);
/*!40000 ALTER TABLE `phppos_registers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_registers_cart`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_registers_cart` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `register_id` int(11) NOT NULL,
  `data` longblob NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `register_id` (`register_id`),
  CONSTRAINT `phppos_registers_cart_ibfk_1` FOREIGN KEY (`register_id`) REFERENCES `phppos_registers` (`register_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_registers_cart`
--

LOCK TABLES `phppos_registers_cart` WRITE;
/*!40000 ALTER TABLE `phppos_registers_cart` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_registers_cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_sale_types`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_sale_types` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sort` int(10) NOT NULL,
  `system_sale_type` int(1) NOT NULL DEFAULT '0',
  `remove_quantity` int(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_sale_types`
--

LOCK TABLES `phppos_sale_types` WRITE;
/*!40000 ALTER TABLE `phppos_sale_types` DISABLE KEYS */;
INSERT INTO `phppos_sale_types` VALUES (0,'common_sale',0,1,0),(1,'common_layaway',0,1,1),(2,'common_estimate',0,1,0),(3,'E-Commerce',99,0,1);
/*!40000 ALTER TABLE `phppos_sale_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_sales`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_sales` (
  `sale_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `customer_id` int(10) DEFAULT NULL,
  `employee_id` int(10) NOT NULL DEFAULT '0',
  `sold_by_employee_id` int(10) DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `discount_reason` text COLLATE utf8_unicode_ci NOT NULL,
  `show_comment_on_receipt` int(1) NOT NULL DEFAULT '0',
  `sale_id` int(10) NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) DEFAULT NULL,
  `rule_discount` decimal(23,10) DEFAULT NULL,
  `payment_type` text COLLATE utf8_unicode_ci,
  `cc_ref_no` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `auth_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `deleted_by` int(10) DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `suspended` int(10) NOT NULL DEFAULT '0',
  `is_ecommerce` int(1) NOT NULL DEFAULT '0',
  `ecommerce_order_id` bigint(20) DEFAULT NULL,
  `ecommerce_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `store_account_payment` int(1) NOT NULL DEFAULT '0',
  `was_layaway` int(1) NOT NULL DEFAULT '0',
  `was_estimate` int(1) NOT NULL DEFAULT '0',
  `location_id` int(11) NOT NULL,
  `register_id` int(11) DEFAULT NULL,
  `tier_id` int(10) DEFAULT NULL,
  `points_used` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `points_gained` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `did_redeem_discount` int(1) NOT NULL DEFAULT '0',
  `signature_image_id` int(10) DEFAULT NULL,
  `deleted_taxes` text COLLATE utf8_unicode_ci,
  `total_quantity_purchased` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `subtotal` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `tax` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `total` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `profit` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `exchange_rate` decimal(23,10) NOT NULL DEFAULT '1.0000000000',
  `exchange_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `exchange_currency_symbol` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `exchange_currency_symbol_location` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `exchange_number_of_decimals` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `exchange_thousands_separator` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `exchange_decimal_point` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `is_purchase_points` int(1) NOT NULL DEFAULT '0',
  `custom_field_1_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_2_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_3_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_4_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_5_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_6_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_7_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_8_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_9_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_10_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_modified` timestamp NULL DEFAULT NULL,
  `override_taxes` text COLLATE utf8_unicode_ci,
  `return_sale_id` int(10) DEFAULT NULL,
  `tip` decimal(23,10) DEFAULT NULL,
  `total_quantity_received` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `non_taxable` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  PRIMARY KEY (`sale_id`),
  KEY `customer_id` (`customer_id`),
  KEY `employee_id` (`employee_id`),
  KEY `deleted` (`deleted`),
  KEY `location_id` (`location_id`),
  KEY `phppos_sales_ibfk_4` (`deleted_by`),
  KEY `phppos_sales_ibfk_5` (`tier_id`),
  KEY `phppos_sales_ibfk_7` (`register_id`),
  KEY `phppos_sales_ibfk_6` (`sold_by_employee_id`),
  KEY `phppos_sales_ibfk_8` (`signature_image_id`),
  KEY `was_layaway` (`was_layaway`),
  KEY `was_estimate` (`was_estimate`),
  KEY `phppos_sales_ibfk_9` (`rule_id`),
  KEY `sales_search` (`location_id`,`deleted`,`sale_time`,`suspended`,`store_account_payment`,`total_quantity_purchased`),
  KEY `phppos_sales_ibfk_10` (`suspended`),
  KEY `custom_field_1_value` (`custom_field_1_value`),
  KEY `custom_field_2_value` (`custom_field_2_value`),
  KEY `custom_field_3_value` (`custom_field_3_value`),
  KEY `custom_field_4_value` (`custom_field_4_value`),
  KEY `custom_field_5_value` (`custom_field_5_value`),
  KEY `custom_field_6_value` (`custom_field_6_value`),
  KEY `custom_field_7_value` (`custom_field_7_value`),
  KEY `custom_field_8_value` (`custom_field_8_value`),
  KEY `custom_field_9_value` (`custom_field_9_value`),
  KEY `custom_field_10_value` (`custom_field_10_value`),
  KEY `phppos_sales_ibfk_11` (`return_sale_id`),
  KEY `ecommerce_order_id` (`ecommerce_order_id`),
  CONSTRAINT `phppos_sales_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_sales_ibfk_10` FOREIGN KEY (`suspended`) REFERENCES `phppos_sale_types` (`id`),
  CONSTRAINT `phppos_sales_ibfk_11` FOREIGN KEY (`return_sale_id`) REFERENCES `phppos_sales` (`sale_id`),
  CONSTRAINT `phppos_sales_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `phppos_customers` (`person_id`),
  CONSTRAINT `phppos_sales_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_sales_ibfk_4` FOREIGN KEY (`deleted_by`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_sales_ibfk_5` FOREIGN KEY (`tier_id`) REFERENCES `phppos_price_tiers` (`id`),
  CONSTRAINT `phppos_sales_ibfk_6` FOREIGN KEY (`sold_by_employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_sales_ibfk_7` FOREIGN KEY (`register_id`) REFERENCES `phppos_registers` (`register_id`),
  CONSTRAINT `phppos_sales_ibfk_8` FOREIGN KEY (`signature_image_id`) REFERENCES `phppos_app_files` (`file_id`),
  CONSTRAINT `phppos_sales_ibfk_9` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_sales`
--

LOCK TABLES `phppos_sales` WRITE;
/*!40000 ALTER TABLE `phppos_sales` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_sales_coupons`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_sales_coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) NOT NULL,
  `rule_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_sales_coupons_ibfk_1` (`sale_id`),
  KEY `phppos_sales_coupons_ibfk_2` (`rule_id`),
  CONSTRAINT `phppos_sales_coupons_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
  CONSTRAINT `phppos_sales_coupons_ibfk_2` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_sales_coupons`
--

LOCK TABLES `phppos_sales_coupons` WRITE;
/*!40000 ALTER TABLE `phppos_sales_coupons` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_sales_coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_sales_deliveries`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_sales_deliveries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(10) DEFAULT NULL,
  `shipping_address_person_id` int(10) NOT NULL,
  `delivery_employee_person_id` int(10) DEFAULT NULL,
  `shipping_method_id` int(10) DEFAULT NULL,
  `shipping_zone_id` int(10) DEFAULT NULL,
  `tax_class_id` int(10) DEFAULT NULL,
  `status` int(30) DEFAULT NULL,
  `estimated_shipping_date` timestamp NULL DEFAULT NULL,
  `actual_shipping_date` timestamp NULL DEFAULT NULL,
  `estimated_delivery_or_pickup_date` timestamp NULL DEFAULT NULL,
  `actual_delivery_or_pickup_date` timestamp NULL DEFAULT NULL,
  `is_pickup` int(1) NOT NULL DEFAULT '0',
  `tracking_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `deleted` int(1) DEFAULT '0',
  `duration` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `delivery_type` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `search_index` (`status`,`shipping_address_person_id`),
  KEY `phppos_sales_deliveries_ibfk_1` (`sale_id`),
  KEY `phppos_sales_deliveries_ibfk_2` (`shipping_address_person_id`),
  KEY `phppos_sales_deliveries_ibfk_3` (`shipping_method_id`),
  KEY `phppos_sales_deliveries_ibfk_4` (`shipping_zone_id`),
  KEY `phppos_sales_deliveries_ibfk_5` (`tax_class_id`),
  KEY `deleted` (`deleted`),
  KEY `phppos_sales_deliveries_ibfk_6` (`delivery_employee_person_id`),
  KEY `location_id` (`location_id`),
  CONSTRAINT `phppos_sales_deliveries_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
  CONSTRAINT `phppos_sales_deliveries_ibfk_2` FOREIGN KEY (`shipping_address_person_id`) REFERENCES `phppos_people` (`person_id`),
  CONSTRAINT `phppos_sales_deliveries_ibfk_3` FOREIGN KEY (`shipping_method_id`) REFERENCES `phppos_shipping_methods` (`id`),
  CONSTRAINT `phppos_sales_deliveries_ibfk_4` FOREIGN KEY (`shipping_zone_id`) REFERENCES `phppos_shipping_zones` (`id`),
  CONSTRAINT `phppos_sales_deliveries_ibfk_5` FOREIGN KEY (`tax_class_id`) REFERENCES `phppos_tax_classes` (`id`),
  CONSTRAINT `phppos_sales_deliveries_ibfk_6` FOREIGN KEY (`delivery_employee_person_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_sales_deliveries_ibfk_7` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`),
  CONSTRAINT `phppos_sales_deliveries_ibfk_8` FOREIGN KEY (`status`) REFERENCES `phppos_delivery_statuses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_sales_deliveries`
--

LOCK TABLES `phppos_sales_deliveries` WRITE;
/*!40000 ALTER TABLE `phppos_sales_deliveries` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_sales_deliveries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_sales_item_kits`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_sales_item_kits` (
  `sale_id` int(10) NOT NULL DEFAULT '0',
  `item_kit_id` int(10) NOT NULL DEFAULT '0',
  `rule_id` int(10) DEFAULT NULL,
  `rule_discount` decimal(23,10) DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `line` int(11) NOT NULL DEFAULT '0',
  `quantity_purchased` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `quantity_received` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `item_kit_cost_price` decimal(23,10) NOT NULL,
  `item_kit_unit_price` decimal(23,10) NOT NULL,
  `regular_item_kit_unit_price_at_time_of_sale` decimal(23,10) DEFAULT NULL,
  `discount_percent` decimal(15,3) NOT NULL DEFAULT '0.000',
  `commission` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `subtotal` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `tax` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `total` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `profit` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `tier_id` int(10) DEFAULT NULL,
  `override_taxes` text COLLATE utf8_unicode_ci,
  `loyalty_multiplier` decimal(23,10) DEFAULT NULL,
  `receipt_line_sort_order` int(11) DEFAULT NULL,
  PRIMARY KEY (`sale_id`,`item_kit_id`,`line`),
  KEY `item_kit_id` (`item_kit_id`),
  KEY `phppos_sales_item_kits_ibfk_3` (`rule_id`),
  CONSTRAINT `phppos_sales_item_kits_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`),
  CONSTRAINT `phppos_sales_item_kits_ibfk_2` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
  CONSTRAINT `phppos_sales_item_kits_ibfk_3` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_sales_item_kits`
--

LOCK TABLES `phppos_sales_item_kits` WRITE;
/*!40000 ALTER TABLE `phppos_sales_item_kits` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_sales_item_kits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_sales_item_kits_modifier_items`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_sales_item_kits_modifier_items` (
  `item_kit_id` int(10) NOT NULL,
  `sale_id` int(10) NOT NULL,
  `line` int(10) NOT NULL,
  `modifier_item_id` int(10) NOT NULL,
  `cost_price` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `unit_price` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  PRIMARY KEY (`item_kit_id`,`sale_id`,`line`,`modifier_item_id`),
  KEY `phppos_sales_item_kits_modifier_items_ibfk_2` (`sale_id`),
  KEY `phppos_sales_item_kits_modifier_items_ibfk_3` (`modifier_item_id`),
  CONSTRAINT `phppos_sales_item_kits_modifier_items_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`),
  CONSTRAINT `phppos_sales_item_kits_modifier_items_ibfk_2` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
  CONSTRAINT `phppos_sales_item_kits_modifier_items_ibfk_3` FOREIGN KEY (`modifier_item_id`) REFERENCES `phppos_modifier_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_sales_item_kits_modifier_items`
--

LOCK TABLES `phppos_sales_item_kits_modifier_items` WRITE;
/*!40000 ALTER TABLE `phppos_sales_item_kits_modifier_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_sales_item_kits_modifier_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_sales_item_kits_taxes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_sales_item_kits_taxes` (
  `sale_id` int(10) NOT NULL,
  `item_kit_id` int(10) NOT NULL,
  `line` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(15,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sale_id`,`item_kit_id`,`line`,`name`,`percent`),
  KEY `item_id` (`item_kit_id`),
  CONSTRAINT `phppos_sales_item_kits_taxes_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales_item_kits` (`sale_id`),
  CONSTRAINT `phppos_sales_item_kits_taxes_ibfk_2` FOREIGN KEY (`item_kit_id`) REFERENCES `phppos_item_kits` (`item_kit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_sales_item_kits_taxes`
--

LOCK TABLES `phppos_sales_item_kits_taxes` WRITE;
/*!40000 ALTER TABLE `phppos_sales_item_kits_taxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_sales_item_kits_taxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_sales_items`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_sales_items` (
  `sale_id` int(10) NOT NULL DEFAULT '0',
  `item_id` int(10) NOT NULL DEFAULT '0',
  `item_variation_id` int(10) DEFAULT NULL,
  `rule_id` int(10) DEFAULT NULL,
  `rule_discount` decimal(23,10) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `serialnumber` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `line` int(11) NOT NULL DEFAULT '0',
  `quantity_purchased` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `quantity_received` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `item_cost_price` decimal(23,10) NOT NULL,
  `item_unit_price` decimal(23,10) NOT NULL,
  `regular_item_unit_price_at_time_of_sale` decimal(23,10) DEFAULT NULL,
  `discount_percent` decimal(15,3) NOT NULL DEFAULT '0.000',
  `commission` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `subtotal` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `tax` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `total` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `profit` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `tier_id` int(10) DEFAULT NULL,
  `series_id` int(11) DEFAULT NULL,
  `damaged_qty` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `override_taxes` text COLLATE utf8_unicode_ci,
  `unit_quantity` decimal(23,10) DEFAULT NULL,
  `items_quantity_units_id` int(11) DEFAULT NULL,
  `loyalty_multiplier` decimal(23,10) DEFAULT NULL,
  `receipt_line_sort_order` int(11) DEFAULT NULL,
  PRIMARY KEY (`sale_id`,`item_id`,`line`),
  KEY `item_id` (`item_id`),
  KEY `phppos_sales_items_ibfk_3` (`rule_id`),
  KEY `phppos_sales_items_ibfk_4` (`item_variation_id`),
  KEY `phppos_sales_items_ibfk_5` (`series_id`),
  KEY `phppos_sales_items_ibfk_6` (`items_quantity_units_id`),
  KEY `serialnumber` (`serialnumber`),
  CONSTRAINT `phppos_sales_items_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_sales_items_ibfk_2` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
  CONSTRAINT `phppos_sales_items_ibfk_3` FOREIGN KEY (`rule_id`) REFERENCES `phppos_price_rules` (`id`),
  CONSTRAINT `phppos_sales_items_ibfk_4` FOREIGN KEY (`item_variation_id`) REFERENCES `phppos_item_variations` (`id`),
  CONSTRAINT `phppos_sales_items_ibfk_5` FOREIGN KEY (`series_id`) REFERENCES `phppos_customers_series` (`id`),
  CONSTRAINT `phppos_sales_items_ibfk_6` FOREIGN KEY (`items_quantity_units_id`) REFERENCES `phppos_items_quantity_units` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_sales_items`
--

LOCK TABLES `phppos_sales_items` WRITE;
/*!40000 ALTER TABLE `phppos_sales_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_sales_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_sales_items_modifier_items`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_sales_items_modifier_items` (
  `item_id` int(10) NOT NULL,
  `sale_id` int(10) NOT NULL,
  `line` int(10) NOT NULL,
  `modifier_item_id` int(10) NOT NULL,
  `cost_price` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `unit_price` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  PRIMARY KEY (`item_id`,`sale_id`,`line`,`modifier_item_id`),
  KEY `phppos_sales_items_modifier_items_ibfk_2` (`sale_id`),
  KEY `phppos_sales_items_modifier_items_ibfk_3` (`modifier_item_id`),
  CONSTRAINT `phppos_sales_items_modifier_items_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_sales_items_modifier_items_ibfk_2` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
  CONSTRAINT `phppos_sales_items_modifier_items_ibfk_3` FOREIGN KEY (`modifier_item_id`) REFERENCES `phppos_modifier_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_sales_items_modifier_items`
--

LOCK TABLES `phppos_sales_items_modifier_items` WRITE;
/*!40000 ALTER TABLE `phppos_sales_items_modifier_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_sales_items_modifier_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_sales_items_notes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_sales_items_notes` (
  `note_id` int(11) NOT NULL AUTO_INCREMENT,
  `note_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sale_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  `line` int(10) NOT NULL DEFAULT '0',
  `item_variation_id` int(10) DEFAULT NULL,
  `note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `detailed_notes` text COLLATE utf8_unicode_ci,
  `internal` tinyint(10) DEFAULT NULL,
  `employee_id` int(10) NOT NULL,
  `images` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`note_id`),
  KEY `phppos_sales_items_notes_ibfk_1` (`sale_id`),
  KEY `phppos_sales_items_notes_ibfk_2` (`item_id`),
  KEY `phppos_sales_items_notes_ibfk_3` (`employee_id`),
  CONSTRAINT `phppos_sales_items_notes_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
  CONSTRAINT `phppos_sales_items_notes_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`),
  CONSTRAINT `phppos_sales_items_notes_ibfk_3` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_sales_items_notes`
--

LOCK TABLES `phppos_sales_items_notes` WRITE;
/*!40000 ALTER TABLE `phppos_sales_items_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_sales_items_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_sales_items_taxes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_sales_items_taxes` (
  `sale_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  `line` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(15,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sale_id`,`item_id`,`line`,`name`,`percent`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `phppos_sales_items_taxes_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales_items` (`sale_id`),
  CONSTRAINT `phppos_sales_items_taxes_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `phppos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_sales_items_taxes`
--

LOCK TABLES `phppos_sales_items_taxes` WRITE;
/*!40000 ALTER TABLE `phppos_sales_items_taxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_sales_items_taxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_sales_payments`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_sales_payments` (
  `payment_id` int(10) NOT NULL AUTO_INCREMENT,
  `sale_id` int(10) NOT NULL,
  `payment_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payment_amount` decimal(23,10) NOT NULL,
  `auth_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `ref_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `cc_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `acq_ref_data` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `process_data` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `entry_method` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `aid` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `tvr` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `iad` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `tsi` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `arc` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `cvm` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `tran_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `application_label` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `truncated_card` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `card_issuer` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `payment_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ebt_auth_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `ebt_voucher_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  PRIMARY KEY (`payment_id`),
  KEY `sale_id` (`sale_id`),
  KEY `payment_date` (`payment_date`),
  CONSTRAINT `phppos_sales_payments_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_sales_payments`
--

LOCK TABLES `phppos_sales_payments` WRITE;
/*!40000 ALTER TABLE `phppos_sales_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_sales_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_sales_work_orders`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_sales_work_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(10) NOT NULL,
  `status` int(11) DEFAULT '1',
  `employee_id` int(11) DEFAULT NULL,
  `estimated_repair_date` timestamp NULL DEFAULT NULL,
  `estimated_parts` decimal(23,10) DEFAULT NULL,
  `estimated_labor` decimal(23,10) DEFAULT NULL,
  `warranty` tinyint(1) DEFAULT NULL,
  `custom_field_1_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_2_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_3_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_4_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_5_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_6_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_7_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_8_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_9_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_10_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  `images` text COLLATE utf8_unicode_ci,
  `deleted` int(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `custom_field_1_value` (`custom_field_1_value`),
  KEY `custom_field_2_value` (`custom_field_2_value`),
  KEY `custom_field_3_value` (`custom_field_3_value`),
  KEY `custom_field_4_value` (`custom_field_4_value`),
  KEY `custom_field_5_value` (`custom_field_5_value`),
  KEY `custom_field_6_value` (`custom_field_6_value`),
  KEY `custom_field_7_value` (`custom_field_7_value`),
  KEY `custom_field_8_value` (`custom_field_8_value`),
  KEY `custom_field_9_value` (`custom_field_9_value`),
  KEY `custom_field_10_value` (`custom_field_10_value`),
  KEY `phppos_sales_work_orders_ibfk_1` (`sale_id`),
  KEY `phppos_sales_work_orders_ibfk_2` (`employee_id`),
  KEY `phppos_sales_work_orders_ibfk_3` (`status`),
  CONSTRAINT `phppos_sales_work_orders_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
  CONSTRAINT `phppos_sales_work_orders_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `phppos_employees` (`person_id`),
  CONSTRAINT `phppos_sales_work_orders_ibfk_3` FOREIGN KEY (`status`) REFERENCES `phppos_workorder_statuses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_sales_work_orders`
--

LOCK TABLES `phppos_sales_work_orders` WRITE;
/*!40000 ALTER TABLE `phppos_sales_work_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_sales_work_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_sessions`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_sessions` (
  `id` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `data` longblob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_sessions`
--

LOCK TABLES `phppos_sessions` WRITE;
/*!40000 ALTER TABLE `phppos_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_shipping_methods`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_shipping_methods` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `shipping_provider_id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `fee` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `fee_tax_class_id` int(10) DEFAULT NULL,
  `time_in_days` int(11) DEFAULT NULL,
  `has_tracking_number` int(1) NOT NULL DEFAULT '0',
  `is_default` int(1) NOT NULL DEFAULT '0',
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phppos_shipping_methods_ibfk_1` (`shipping_provider_id`),
  KEY `phppos_shipping_methods_ibfk_2` (`fee_tax_class_id`),
  CONSTRAINT `phppos_shipping_methods_ibfk_1` FOREIGN KEY (`shipping_provider_id`) REFERENCES `phppos_shipping_providers` (`id`),
  CONSTRAINT `phppos_shipping_methods_ibfk_2` FOREIGN KEY (`fee_tax_class_id`) REFERENCES `phppos_tax_classes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_shipping_methods`
--

LOCK TABLES `phppos_shipping_methods` WRITE;
/*!40000 ALTER TABLE `phppos_shipping_methods` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_shipping_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_shipping_providers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_shipping_providers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `order` int(10) NOT NULL DEFAULT '0',
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_shipping_providers`
--

LOCK TABLES `phppos_shipping_providers` WRITE;
/*!40000 ALTER TABLE `phppos_shipping_providers` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_shipping_providers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_shipping_zones`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_shipping_zones` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `fee` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `tax_class_id` int(10) DEFAULT NULL,
  `order` int(10) NOT NULL DEFAULT '0',
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `phppos_shipping_zones_ibfk_1` (`tax_class_id`),
  CONSTRAINT `phppos_shipping_zones_ibfk_1` FOREIGN KEY (`tax_class_id`) REFERENCES `phppos_tax_classes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_shipping_zones`
--

LOCK TABLES `phppos_shipping_zones` WRITE;
/*!40000 ALTER TABLE `phppos_shipping_zones` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_shipping_zones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_store_accounts`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_store_accounts` (
  `sno` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `transaction_amount` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `balance` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`sno`),
  KEY `phppos_store_accounts_ibfk_1` (`sale_id`),
  KEY `phppos_store_accounts_ibfk_2` (`customer_id`),
  CONSTRAINT `phppos_store_accounts_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
  CONSTRAINT `phppos_store_accounts_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `phppos_customers` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_store_accounts`
--

LOCK TABLES `phppos_store_accounts` WRITE;
/*!40000 ALTER TABLE `phppos_store_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_store_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_store_accounts_paid_sales`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_store_accounts_paid_sales` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `store_account_payment_sale_id` int(10) DEFAULT NULL,
  `sale_id` int(10) DEFAULT NULL,
  `partial_payment_amount` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  PRIMARY KEY (`id`),
  KEY `phppos_store_accounts_sales_ibfk_1` (`sale_id`),
  KEY `phppos_store_accounts_sales_ibfk_2` (`store_account_payment_sale_id`),
  CONSTRAINT `phppos_store_accounts_sales_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `phppos_sales` (`sale_id`),
  CONSTRAINT `phppos_store_accounts_sales_ibfk_2` FOREIGN KEY (`store_account_payment_sale_id`) REFERENCES `phppos_sales` (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_store_accounts_paid_sales`
--

LOCK TABLES `phppos_store_accounts_paid_sales` WRITE;
/*!40000 ALTER TABLE `phppos_store_accounts_paid_sales` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_store_accounts_paid_sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_supplier_store_accounts`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_supplier_store_accounts` (
  `sno` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL,
  `receiving_id` int(11) DEFAULT NULL,
  `transaction_amount` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `balance` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`sno`),
  KEY `phppos_supplier_store_accounts_ibfk_1` (`receiving_id`),
  KEY `phppos_supplier_store_accounts_ibfk_2` (`supplier_id`),
  CONSTRAINT `phppos_supplier_store_accounts_ibfk_1` FOREIGN KEY (`receiving_id`) REFERENCES `phppos_receivings` (`receiving_id`),
  CONSTRAINT `phppos_supplier_store_accounts_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `phppos_suppliers` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_supplier_store_accounts`
--

LOCK TABLES `phppos_supplier_store_accounts` WRITE;
/*!40000 ALTER TABLE `phppos_supplier_store_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_supplier_store_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_supplier_store_accounts_paid_receivings`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_supplier_store_accounts_paid_receivings` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `store_account_payment_receiving_id` int(10) DEFAULT NULL,
  `receiving_id` int(10) DEFAULT NULL,
  `partial_payment_amount` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  PRIMARY KEY (`id`),
  KEY `phppos_supplier_store_accounts_paid_receivings_ibfk_1` (`receiving_id`),
  KEY `phppos_supplier_store_accounts_paid_receivings_ibfk_2` (`store_account_payment_receiving_id`),
  CONSTRAINT `phppos_supplier_store_accounts_paid_receivings_ibfk_1` FOREIGN KEY (`receiving_id`) REFERENCES `phppos_receivings` (`receiving_id`),
  CONSTRAINT `phppos_supplier_store_accounts_paid_receivings_ibfk_2` FOREIGN KEY (`store_account_payment_receiving_id`) REFERENCES `phppos_receivings` (`receiving_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_supplier_store_accounts_paid_receivings`
--

LOCK TABLES `phppos_supplier_store_accounts_paid_receivings` WRITE;
/*!40000 ALTER TABLE `phppos_supplier_store_accounts_paid_receivings` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_supplier_store_accounts_paid_receivings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_suppliers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_suppliers` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `person_id` int(10) NOT NULL,
  `company_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `account_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `override_default_tax` int(1) NOT NULL DEFAULT '0',
  `balance` decimal(23,10) NOT NULL DEFAULT '0.0000000000',
  `deleted` int(1) NOT NULL DEFAULT '0',
  `tax_class_id` int(10) DEFAULT NULL,
  `custom_field_1_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_2_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_3_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_4_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_5_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_6_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_7_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_8_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_9_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_field_10_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `internal_notes` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_number` (`account_number`),
  KEY `person_id` (`person_id`),
  KEY `deleted` (`deleted`),
  KEY `phppos_suppliers_ibfk_2` (`tax_class_id`),
  KEY `custom_field_1_value` (`custom_field_1_value`),
  KEY `custom_field_2_value` (`custom_field_2_value`),
  KEY `custom_field_3_value` (`custom_field_3_value`),
  KEY `custom_field_4_value` (`custom_field_4_value`),
  KEY `custom_field_5_value` (`custom_field_5_value`),
  KEY `custom_field_6_value` (`custom_field_6_value`),
  KEY `custom_field_7_value` (`custom_field_7_value`),
  KEY `custom_field_8_value` (`custom_field_8_value`),
  KEY `custom_field_9_value` (`custom_field_9_value`),
  KEY `custom_field_10_value` (`custom_field_10_value`),
  CONSTRAINT `phppos_suppliers_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `phppos_people` (`person_id`),
  CONSTRAINT `phppos_suppliers_ibfk_2` FOREIGN KEY (`tax_class_id`) REFERENCES `phppos_tax_classes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_suppliers`
--

LOCK TABLES `phppos_suppliers` WRITE;
/*!40000 ALTER TABLE `phppos_suppliers` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_suppliers_taxes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_suppliers_taxes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(15,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tax` (`supplier_id`,`name`,`percent`),
  CONSTRAINT `phppos_suppliers_taxes_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `phppos_suppliers` (`person_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_suppliers_taxes`
--

LOCK TABLES `phppos_suppliers_taxes` WRITE;
/*!40000 ALTER TABLE `phppos_suppliers_taxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_suppliers_taxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_tags`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ecommerce_tag_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag_name` (`name`),
  KEY `deleted` (`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_tags`
--

LOCK TABLES `phppos_tags` WRITE;
/*!40000 ALTER TABLE `phppos_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_tax_classes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_tax_classes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `order` int(10) NOT NULL DEFAULT '0',
  `location_id` int(10) DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ecommerce_tax_class_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `phppos_tax_classes_ibfk_1` (`location_id`),
  CONSTRAINT `phppos_tax_classes_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `phppos_locations` (`location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_tax_classes`
--

LOCK TABLES `phppos_tax_classes` WRITE;
/*!40000 ALTER TABLE `phppos_tax_classes` DISABLE KEYS */;
INSERT INTO `phppos_tax_classes` VALUES (1,1,NULL,0,'Taxes',NULL);
/*!40000 ALTER TABLE `phppos_tax_classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_tax_classes_taxes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_tax_classes_taxes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `order` int(10) NOT NULL DEFAULT '0',
  `tax_class_id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` decimal(15,3) NOT NULL,
  `cumulative` int(1) NOT NULL DEFAULT '0',
  `ecommerce_tax_class_tax_rate_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tax` (`tax_class_id`,`name`,`percent`),
  CONSTRAINT `phppos_tax_classes_taxes_ibfk_1` FOREIGN KEY (`tax_class_id`) REFERENCES `phppos_tax_classes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_tax_classes_taxes`
--

LOCK TABLES `phppos_tax_classes_taxes` WRITE;
/*!40000 ALTER TABLE `phppos_tax_classes_taxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_tax_classes_taxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_work_order_files`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_work_order_files` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `file_id` int(11) NOT NULL,
  `work_order_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`),
  KEY `work_order_id` (`work_order_id`),
  KEY `file_id_2` (`file_id`),
  KEY `work_order_id_2` (`work_order_id`),
  CONSTRAINT `phppos_work_order_files_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `phppos_app_files` (`file_id`),
  CONSTRAINT `phppos_work_order_files_ibfk_2` FOREIGN KEY (`work_order_id`) REFERENCES `phppos_sales_work_orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_work_order_files`
--

LOCK TABLES `phppos_work_order_files` WRITE;
/*!40000 ALTER TABLE `phppos_work_order_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_work_order_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_workorder_statuses`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_workorder_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notify_by_email` tinyint(1) DEFAULT '0',
  `notify_by_sms` tinyint(1) DEFAULT '0',
  `color` text COLLATE utf8_unicode_ci,
  `sort_order` int(11) DEFAULT '0',
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_workorder_statuses`
--

LOCK TABLES `phppos_workorder_statuses` WRITE;
/*!40000 ALTER TABLE `phppos_workorder_statuses` DISABLE KEYS */;
INSERT INTO `phppos_workorder_statuses` VALUES (1,'lang:work_orders_new',NULL,0,0,'#4594cc',10,'2020-07-09 09:32:32'),(2,'lang:work_orders_in_progress',NULL,0,0,'#28a745',20,'2020-07-09 09:32:47'),(3,'lang:work_orders_out_for_repair',NULL,0,0,'#f7ac08',30,'2020-07-09 09:32:54'),(4,'lang:work_orders_waiting_on_customer',NULL,0,0,'#6a0dad',40,'2020-07-09 09:33:01'),(5,'lang:work_orders_repaired',NULL,0,0,'#006400',50,'2020-07-09 09:33:09'),(6,'lang:work_orders_complete',NULL,0,0,'#28a745',60,'2020-07-09 09:33:17'),(7,'lang:work_orders_cancelled',NULL,0,0,'#fb5d5d',70,'2020-07-09 09:33:34');
/*!40000 ALTER TABLE `phppos_workorder_statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phppos_zips`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phppos_zips` (
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_zone_id` int(10) DEFAULT NULL,
  `order` int(10) NOT NULL DEFAULT '0',
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`name`),
  UNIQUE KEY `name` (`name`),
  KEY `phppos_zips_ibfk_1` (`shipping_zone_id`),
  CONSTRAINT `phppos_zips_ibfk_1` FOREIGN KEY (`shipping_zone_id`) REFERENCES `phppos_shipping_zones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phppos_zips`
--

LOCK TABLES `phppos_zips` WRITE;
/*!40000 ALTER TABLE `phppos_zips` DISABLE KEYS */;
/*!40000 ALTER TABLE `phppos_zips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'pos'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-12-21 11:24:33
REPLACE INTO `phppos_app_config` (`key`, `value`) VALUES ('supports_full_text', '0');
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX full_search (`name`, `item_number`, `product_id`, `description`)*/;	
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX name_search (`name`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX item_number_search (`item_number`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX product_id_search (`product_id`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX description_search (`description`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX size_search (`size`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX custom_field_1_value_search (`custom_field_1_value`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX custom_field_2_value_search (`custom_field_2_value`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX custom_field_3_value_search (`custom_field_3_value`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX custom_field_4_value_search (`custom_field_4_value`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX custom_field_5_value_search (`custom_field_5_value`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX custom_field_6_value_search (`custom_field_6_value`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX custom_field_7_value_search (`custom_field_7_value`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX custom_field_8_value_search (`custom_field_8_value`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX custom_field_9_value_search (`custom_field_9_value`)*/;
/*!50604	ALTER TABLE `phppos_items` ADD FULLTEXT INDEX custom_field_10_value_search (`custom_field_10_value`)*/;
/*!50604	ALTER TABLE `phppos_item_variations` ADD FULLTEXT INDEX name_search (`name`)*/;
/*!50604 REPLACE INTO `phppos_app_config` (`key`, `value`) VALUES ('supports_full_text', '1')*/;

SET FOREIGN_KEY_CHECKS=1;
REPLACE INTO phppos_app_config (`key`,`value`) VALUES ('past_inventory_date',date(now()));
