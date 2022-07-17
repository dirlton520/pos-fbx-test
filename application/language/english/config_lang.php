<?php
$lang['config_info']='Store Configuration Information';

$lang['config_address']='Company Address';
$lang['config_phone']='Company Phone';
$lang['config_prefix']='Sale ID Prefix';

$lang['config_fax']='Fax';
$lang['config_default_tax_rate']='Default Tax Rate %';


$lang['config_company_required']='Company name is a required field';

$lang['config_phone_required']='Company phone is a required field';
$lang['config_sale_prefix_required']='Sale ID prefix is a required field';
$lang['config_default_tax_rate_required']='The default tax rate is a required field';
$lang['config_default_tax_rate_number']='The default tax rate must be a number';
$lang['config_company_website_url']='Company website is not a valid URL (http://...)';

$lang['config_saved_unsuccessfully']='Failed to save configuration. Configuration changes are not allowed in demo mode or taxes weren\'t saved correctly';
$lang['config_return_policy_required']='Return policy is a required field';
$lang['config_print_after_sale']='Print receipt after sale';
$lang['config_automatically_email_receipt']='Automatically Email receipt';
$lang['config_barcode_price_include_tax']='Include tax on barcodes';
$lang['disable_confirmation_sale']='Disable confirmation for complete sale';


$lang['config_currency_symbol'] = 'Currency Symbol';
$lang['config_backup_database'] = 'Backup Database';
$lang['config_restore_database'] = 'Restore Database';

$lang['config_number_of_items_per_page'] = 'Number Of Items Per Page';
$lang['config_date_format'] = 'Date Format';
$lang['config_time_format'] = 'Time Format';



$lang['config_database_optimize_successfully'] = 'Optimized Database Successfully';
$lang['config_payment_types'] = 'Payment Types';
$lang['select_sql_file'] = 'select .sql file';

$lang['restore_heading'] = 'This allows you to restore your database';

$lang['type_file'] = 'select .sql file from your computer';

$lang['restore'] = 'restore';

$lang['required_sql_file'] = 'No sql file is selected';

$lang['restore_db_success'] = 'DataBase is restored successfully';

$lang['db_first_alert'] = 'Are you sure of restoring the database?';
$lang['db_second_alert'] = 'Present data will be lost , continue?';
$lang['password_error'] = 'Password incorrect';
$lang['password_required'] = 'Password field cannot be blank';
$lang['restore_database_title'] = 'Restore Database';



$lang['config_environment'] = 'Environment';


$lang['config_sandbox'] = 'Sandbox';
$lang['config_production'] = 'Production';

$lang['config_default_payment_type'] = 'Default Payment Type';
$lang['config_speed_up_note'] = 'Only recommend if you have more than 10,000 items or customers';
$lang['config_hide_signature'] = 'Hide Signature';
$lang['config_round_cash_on_sales'] = 'Round to nearest .05 on receipt';
$lang['config_enable_pdf_receipts'] = 'Enable PDF receipts';
$lang['config_customers_store_accounts'] = 'Customers Store Accounts';
$lang['config_change_sale_date_when_suspending'] = 'Change sale date when suspending sale';
$lang['config_change_sale_date_when_completing_suspended_sale'] = 'Change sale date when completing suspended sale';
$lang['config_price_tiers'] = 'Price tiers';
$lang['config_add_tier'] = 'Add tier';
$lang['config_show_receipt_after_suspending_sale'] = 'Show receipt after suspending sale';
$lang['config_backup_overview'] = 'Backup Overview';
$lang['config_backup_overview_desc'] = 'Backing up your data is very important, but can be troublesome with large amount of data. If you have lots of images, items, and sales this can increase the size of your database.';
$lang['config_backup_options'] = 'We offer many options for backup to help you decide how to proceed';
$lang['config_backup_simple_option'] = 'Clicking "Backup database". This will attempt to download your whole database to a file. If you get a blank screen or can\'t download the file, try one of the other options.';
$lang['config_backup_phpmyadmin_1'] = 'PHPMyAdmin is a popular tool for managing your databases. If you are using the download version with installer, it can be accessed by going to';
$lang['config_backup_phpmyadmin_2'] = 'Your username is root and password is what you used during initial installation of PHP POS. Once logged in select your database from the panel on the left. Then select export and then submit the form.';
$lang['config_backup_control_panel'] = 'If you have installed on your own server that has a control panel such as cpanel, look for the backup module which will often let you download backups of your database.';
$lang['config_backup_mysqldump'] = 'If you have access to the shell and mysqldump on your server, you can try to execute it by clicking the below link. Otherwise  you will need to try other options.';
$lang['config_mysqldump_failed'] = 'mysqldump backup has failed. This could be due to a server restriction or the command might not be available. Please try another backup method';



$lang['config_looking_for_location_settings'] = 'Looking for other configuration options? Go to';
$lang['config_module'] = 'Module';
$lang['config_automatically_calculate_average_cost_price_from_receivings'] = 'Calculate Average Cost Price from Receivings';
$lang['config_averaging_method'] = 'Averaging Method';
$lang['config_historical_average'] = 'Historical Average';
$lang['config_moving_average'] = 'Moving Average';

$lang['config_hide_dashboard_statistics'] = 'Hide Dashboard Statistics';
$lang['config_hide_store_account_payments_in_reports'] = 'Hide Store Account Payments In Reports';
$lang['config_id_to_show_on_sale_interface'] = 'Item ID to Show on Sales Interface';
$lang['config_auto_focus_on_item_after_sale_and_receiving'] = 'Auto Focus On Item Field When using Sales/Receivings Interfaces';
$lang['config_automatically_show_comments_on_receipt'] = 'Automatically Show Comments on Receipt';
$lang['config_hide_customer_recent_sales'] = 'Hide Recent Sales for Customer';
$lang['config_spreadsheet_format'] = 'Spreadsheet Format';
$lang['config_csv'] = 'CSV';
$lang['config_xlsx'] = 'XLSX';
$lang['config_disable_giftcard_detection'] = 'Disable Giftcard Detection';
$lang['config_disable_subtraction_of_giftcard_amount_from_sales'] = 'Disable giftcard subtraction when using giftcard during sale';
$lang['config_always_show_item_grid'] = 'Always Show Item Grid';
$lang['config_legacy_detailed_report_export'] = 'Legacy Detailed Report Excel Export';
$lang['config_print_after_receiving'] = 'Print receipt after receiving';
$lang['config_company_info'] = 'Company Information';


$lang['config_suspended_sales_layaways_info'] = 'Suspended Sales/Layaways';
$lang['config_application_settings_info'] = 'Application Settings';
$lang['config_hide_barcode_on_sales_and_recv_receipt'] = 'Hide barcode on receipts';
$lang['config_round_tier_prices_to_2_decimals'] = 'Round tier Prices to 2 decimals';
$lang['config_group_all_taxes_on_receipt'] = 'Group all taxes on receipt';
$lang['config_receipt_text_size'] = 'Receipt text size';
$lang['config_small'] = 'Small';
$lang['config_medium'] = 'Medium';
$lang['config_large'] = 'Large';
$lang['config_extra_large'] = 'Extra large';
$lang['config_select_sales_person_during_sale'] = 'Select sales person during sale';
$lang['config_default_sales_person'] = 'Default sales person';
$lang['config_require_customer_for_sale'] = 'Require customer for sale';

$lang['config_hide_store_account_payments_from_report_totals'] = 'Hide store account payments from report totals';
$lang['config_disable_sale_notifications'] = 'Disable sale notifications';
$lang['config_id_to_show_on_barcode'] = 'ID to show on barcode';
$lang['config_currency_denoms'] = 'Currency Denominations';
$lang['config_currency_value'] = 'Currency Value';
$lang['config_add_currency_denom'] = 'Add currency denomination';
$lang['config_enable_timeclock'] = 'Enable Time Clock';
$lang['config_change_sale_date_for_new_sale'] = 'Change Sale Date For New Sale';
$lang['config_dont_average_use_current_recv_price'] = 'Don\'t average, use current received price';
$lang['config_number_of_recent_sales'] = 'Number of recent sales by customer to show';
$lang['config_hide_suspended_recv_in_reports'] = 'Hide suspended Receivings in reports';
$lang['config_calculate_profit_for_giftcard_when'] = 'Calculate Gift Card Profit When';
$lang['config_selling_giftcard'] = 'Selling Gift Card';
$lang['config_redeeming_giftcard'] = 'Redeeming Gift Card';
$lang['config_remove_customer_contact_info_from_receipt'] = 'Remove customer contact info from receipt';
$lang['config_speed_up_search_queries'] = 'Speed up search queries?';




$lang['config_redirect_to_sale_or_recv_screen_after_printing_receipt'] = 'Redirect to sale or receiving screen after printing receipt';
$lang['config_enable_sounds'] = 'Enable sounds for status messages';
$lang['config_charge_tax_on_recv'] = 'Charge tax on receivings';
$lang['config_report_sort_order'] = 'Report Sort Order';
$lang['config_asc'] = 'Oldest first';
$lang['config_desc'] = 'Newest first';
$lang['config_do_not_group_same_items'] = 'Do NOT group items that are the same';
$lang['config_show_item_id_on_receipt'] = 'Show item id on receipt';
$lang['config_show_language_switcher'] = 'Show Language Switcher';
$lang['config_do_not_allow_out_of_stock_items_to_be_sold'] = 'Do not allow out of stock items to be sold';
$lang['config_number_of_items_in_grid'] = 'Number of items per page in grid';
$lang['config_edit_item_price_if_zero_after_adding'] = 'Edit item price if 0 after adding to sale';
$lang['config_override_receipt_title'] = 'Override receipt title';
$lang['config_automatically_print_duplicate_receipt_for_cc_transactions'] = 'Automatically print duplicate receipt for credit card transactions';






$lang['config_default_type_for_grid'] = 'Default type for Grid';
$lang['config_billing_is_managed_through_paypal'] = 'Billing is managed through  <a target="_blank" href="http://paypal.com">Paypal</a>. You can cancel your subscription by clicking <a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_subscr-find&alias=BNTRX72M8UZ2E">here</a>. You can <a href="http://phppointofsale.com/update_billing.php" target="_blank">update billing here</a>.';
$lang['config_cannot_change_language'] = 'Language cannot be saved at application level. However the default admin employee can change the language using the selector in the header of the program';
$lang['disable_quick_complete_sale'] = 'Disable sale quick complete';
$lang['config_fast_user_switching'] = 'Enable fast user switching (password not required)';
$lang['config_require_employee_login_before_each_sale'] = 'Require employee login before each sale';
$lang['config_reset_location_when_switching_employee'] = 'Reset location when switching employee';
$lang['config_number_of_decimals'] = 'Number of decimals';
$lang['config_let_system_decide'] = 'Let system decide (Recommended)';
$lang['config_thousands_separator'] = 'Thousands Separator';
$lang['config_enhanced_search_method'] = 'Enhanced Search Method';
$lang['config_hide_store_account_balance_on_receipt'] = 'Hide store account balance on receipt';
$lang['config_decimal_point'] = 'Decimal Point';
$lang['config_hide_out_of_stock_grid'] = 'Hide out of stock items in grid';
$lang['config_highlight_low_inventory_items_in_items_module'] = 'Highlight low inventory items in items module';
$lang['config_sort'] = 'Sort';
$lang['config_enable_customer_loyalty_system'] = 'Enable Customer Loyalty system';
$lang['config_spend_to_point_ratio'] = 'Spend amount to point ratio';
$lang['config_point_value'] = 'Point Value';
$lang['config_hide_points_on_receipt'] = 'Hide Points On Receipt';
$lang['config_show_clock_on_header'] = 'Show Clock in Header';
$lang['config_show_clock_on_header_help_text'] = 'This is visible only on wide screens';
$lang['config_loyalty_explained_spend_amount'] = 'Enter the amount to spend';
$lang['config_loyalty_explained_points_to_earn'] = 'Enter points to be earned';
$lang['config_simple'] = 'Simple';
$lang['config_advanced'] = 'Advanced';
$lang['config_loyalty_option'] = 'Loyalty Program Option';
$lang['config_number_of_sales_for_discount'] = 'Number of sales for discount';
$lang['config_discount_percent_earned'] = 'Discount percent earned when reaching sales';
$lang['hide_sales_to_discount_on_receipt'] = 'Hide sales to discount on receipt';
$lang['config_hide_price_on_barcodes'] = 'Hide price on barcodes';
$lang['config_always_use_average_cost_method'] = 'Always Use Global Average Cost Price For A Sale Item\'s Cost Price. (DO NOT check unless you know what it means)';

$lang['config_test_mode_help'] = 'Sales NOT saved';
$lang['config_require_customer_for_suspended_sale'] = 'Require customer for suspended sale';
$lang['config_default_new_items_to_service'] = 'Default New Items as service items';






$lang['config_prompt_for_ccv_swipe'] = 'Prompt for CCV when swiping credit card';
$lang['config_disable_store_account_when_over_credit_limit'] = 'Disable store account when over credit limit';
$lang['config_mailing_labels_type'] = 'Mailing Labels Format';
$lang['config_phppos_session_expiration'] = 'Session expiration';
$lang['config_hours'] = 'Hours';
$lang['config_never'] = 'Never';
$lang['config_on_browser_close'] = 'On Browser Close';
$lang['config_do_not_allow_below_cost'] = 'Do NOT allow items to be sold below cost price';
$lang['config_store_account_statement_message'] = 'Store Account Statement Message';
$lang['config_enable_markup_calculator'] = 'Enable Mark Up Calculator';
$lang['config_enable_quick_edit'] = 'Enable quick edit on manage pages';
$lang['config_show_orig_price_if_marked_down_on_receipt'] = 'Show original price on receipt if marked down';
$lang['config_cancel_account'] = 'Cancel Account';
$lang['config_update_billing'] = 'You can update and cancel your billing information by clicking the buttons below:';
$lang['config_include_child_categories_when_searching_or_reporting'] = 'Include child categories when searching or reporting';
$lang['config_confirm_error_messages_modal'] = 'Confirm error messages using modal dialogs';
$lang['config_remove_commission_from_profit_in_reports'] = 'Remove commission from profit in reports';
$lang['config_remove_points_from_profit'] = 'Remove points redemption from profit';
$lang['config_capture_sig_for_all_payments'] = 'Capture signature for all sales';
$lang['config_suppliers_store_accounts'] = 'Suppliers Store Accounts';
$lang['config_currency_symbol_location'] = 'Currency Symbol Location';
$lang['config_before_number'] = 'Before Number';
$lang['config_after_number'] = 'After Number';
$lang['config_hide_desc_on_receipt'] = 'Hide Description on Receipt';
$lang['config_default_percent_off'] = 'Default Percent Off';
$lang['config_default_cost_plus_percent'] = 'Default Cost Plus Percent';
$lang['config_default_tier_percent_type_for_excel_import'] = 'Default Tier Percent Type for excel import';
$lang['config_override_tier_name'] = 'Override Tier Name on Receipt';
$lang['config_loyalty_points_without_tax'] = 'Loyalty points earned not including tax';
$lang['config_lock_prices_suspended_sales'] = 'Lock prices when unsuspending sale even if they belong to a tier';
$lang['config_remove_customer_name_from_receipt'] = 'Remove Customer Name From Receipt';
$lang['config_scale_1'] = 'UPC-12 4 price digits';
$lang['config_scale_2'] = 'UPC-12 5 Price Digits';
$lang['config_scale_3'] = 'EAN-13 5 price digits';
$lang['config_scale_4'] = 'EAN-13 6 price digits';
$lang['config_scale_format'] = 'Scale Barcode Format';
;
$lang['config_enable_scale'] = 'Enable Scale';

$lang['config_woocommerce_settings_info'] = 'Woocommerce Settings';
$lang['config_store_location'] = 'Store Location';

$lang['config_woo_api_secret'] = 'Woocommerce API Secret';
$lang['config_woo_api_url'] = 'Woocommerce API Url';
$lang['config_woo_api_key'] = 'Woocommerce API Key';











$lang['config_ecommerce_settings_info'] = 'Ecommerce Platform';
$lang['config_ecommerce_platform'] = 'Select Platform';
$lang['config_magento_settings_info'] = 'Magento Settings';
$lang['config_scale_divide_by'] = 'Scale Price Divide By';
$lang['config_logout_on_clock_out'] = 'Log out automatically when clocking out';
$lang['config_user_configured_layaway_name'] = 'Override Layaway Name';
$lang['config_use_tax_value_at_all_locations'] = 'Use Tax Values at ALL locations';
$lang['config_enable_ebt_payments'] = 'Enable EBT payments';
$lang['config_item_id_auto_increment'] = 'Item ID Auto Increment Starting Value';
$lang['config_change_auto_increment_item_id_unsuccessful'] = 'There was an error changing auto_increment for item_id';
$lang['config_item_kit_id_auto_increment'] = 'Item Kit ID Auto Increment Starting Value';
$lang['config_sale_id_auto_increment'] = 'Sale ID Auto Increment Starting Value';
$lang['config_receiving_id_auto_increment'] = 'Receiving ID Auto Increment Starting Value';
$lang['config_change_auto_increment_item_kit_id'] = 'There was an error changing auto_increment for  Iitem_kit_id';
$lang['config_change_auto_increment_sale_id'] = 'There was an error changing auto_increment for sale_id';
$lang['config_change_auto_increment_receiving_id'] = 'There was an error changing auto_increment for receiving_id';
$lang['config_auto_increment_note'] = 'You can only increase Auto Increment values. Updating them will not affect IDs for items, item kits, sales or receivings that already exist.';

$lang['config_online_price_tier'] = 'Online Price Tier';
$lang['config_email_settings_info'] = 'Email Settings';

$lang['config_last_sync_date'] = 'Last Sync Date';
$lang['config_sync'] = 'Sync';
$lang['config_smtp_crypto'] = 'SMTP Encryption';
$lang['config_email_protocol'] = 'Mail Sending Protocol';
$lang['config_smtp_host'] = 'SMTP Server Address';
$lang['config_smtp_user'] = 'Email Address';
$lang['config_smtp_pass'] = 'Email Password';
$lang['config_smtp_port'] = 'SMTP Port';
$lang['config_email_charset'] = 'Character set';
$lang['config_email_newline'] = 'Newline character';
$lang['config_email_crlf'] = 'CRLF';
$lang['config_smtp_timeout'] = 'SMTP Timeout';
$lang['config_send_test_email'] = 'Send Test Email';
$lang['config_please_enter_email_to_send_test_to'] = 'Please enter email address to send test email to';
$lang['config_email_succesfully_sent'] = 'Email has been sent successfully';
$lang['config_taxes_info'] = 'Taxes';
$lang['config_currency_info'] = 'Currency';

$lang['config_receipt_info'] = 'Receipt';

$lang['config_barcodes_info'] = 'Barcodes';
$lang['config_customer_loyalty_info'] = 'Customer Loyalty';
$lang['config_price_tiers_info'] = 'Price Tiers';
$lang['config_auto_increment_ids_info'] = 'ID Numbers';
$lang['config_items_info'] = 'Items';
$lang['config_employee_info'] = 'Employee';
$lang['config_store_accounts_info'] = 'Store Accounts';
$lang['config_sales_info'] = 'Sales';
$lang['config_payment_types_info'] = 'Payment Types';
$lang['config_profit_info'] = 'Profit Calculation';
$lang['reports_view_dashboard_stats'] = 'View Dashboard Statistics';
$lang['config_keyword_email'] = 'email settings';
$lang['config_keyword_company'] = 'company';
$lang['config_keyword_taxes'] = 'taxes';
$lang['config_keyword_currency'] = 'currency';
$lang['config_keyword_payment'] = 'payment';
$lang['config_keyword_sales'] = 'sales';
$lang['config_keyword_suspended_layaways'] = 'suspended layaways';
$lang['config_keyword_receipt'] = 'receipt';
$lang['config_keyword_profit'] = 'profit';
$lang['config_keyword_barcodes'] = 'barcodes';
$lang['config_keyword_customer_loyalty'] = 'customer loyalty';
$lang['config_keyword_price_tiers'] = 'price tiers';
$lang['config_keyword_auto_increment'] = 'starting auto increment id numbers database';
$lang['config_keyword_items'] = 'items';
$lang['config_keyword_employees'] = 'employees';
$lang['config_keyword_store_accounts'] = 'store accounts';
$lang['config_keyword_application_settings'] = 'application settings';
$lang['config_keyword_ecommerce'] = 'ecommerce platform';
$lang['config_keyword_woocommerce'] = 'woocommerce settings ecommerce';
$lang['config_billing_info'] = 'Billing Information';
$lang['config_keyword_billing'] = 'billing cancel update';
$lang['config_woo_version'] = 'WooCommerce Version';

$lang['sync_phppos_item_changes'] = 'Sync item changes';
$lang['config_sync_phppos_item_changes'] = 'Sync item changes';
$lang['config_import_ecommerce_items_into_phppos'] = 'Import items into phppos';
$lang['config_sync_inventory_changes'] = 'Sync inventory changes';
$lang['config_export_phppos_tags_to_ecommerce'] = 'Export tags to ecommerce';
$lang['config_export_phppos_categories_to_ecommerce'] = 'Export categories to ecommerce';
$lang['config_export_phppos_items_to_ecommerce'] = 'Export items to ecommerce';
$lang['config_ecommerce_cron_sync_operations'] = 'Ecommerce Sync Operations';
$lang['config_ecommerce_progress'] = 'Sync Progress';
$lang['confirmation_woocommerce_cron_cancel'] = 'Are you sure you want to cancel the sync?';
$lang['config_force_https'] = 'Require https for program';

$lang['config_keyword_price_rules'] = 'Price Rules';
$lang['config_disable_price_rules_dialog'] = 'Disable Price Rules dialog';
$lang['config_price_rules_info'] = 'Price Rules';

$lang['config_prompt_to_use_points'] = 'Prompt to use points when available';



$lang['config_always_print_duplicate_receipt_all'] = 'Always print duplicate receipt for all transactions';


$lang['config_orders_and_deliveries_info'] = 'Orders And Deliveries';
$lang['config_delivery_methods'] = 'Delivery Methods';
$lang['config_shipping_providers'] = 'Shipping Providers';
$lang['config_expand'] = 'Expand';
$lang['config_add_delivery_rate'] = 'Add Delivery Rate';
$lang['config_add_shipping_provider'] = 'Add Shipping Provider';
$lang['config_delivery_rates'] = 'Delivery Rates';
$lang['config_delivery_fee'] = 'Delivery Fee';
$lang['config_keyword_orders_deliveries'] = 'orders delivery deliveries';
$lang['config_delivery_fee_tax'] = 'Delivery Fee Tax';
$lang['config_add_rate'] = 'Add Rate';
$lang['config_delivery_time'] = 'Delivery Time In Days';
$lang['config_delivery_rate'] = 'Delivery Rate';
$lang['config_rate_name'] = 'Rate Name';
$lang['config_rate_fee'] = 'Rate Fee';
$lang['config_rate_tax'] = 'Rate Tax';
$lang['config_tax_classes'] = 'Tax Groups';
$lang['config_add_tax_class'] = 'Add Tax Group';

$lang['config_wide_printer_receipt_format'] = 'Wide Printer Receipt Format';

$lang['config_default_cost_plus_fixed_amount'] = 'Default Cost Plus Fixed Amount';
$lang['config_default_tier_fixed_type_for_excel_import'] = 'Default Tier Fixed Amount for Excel Import';
$lang['config_default_reorder_level_when_creating_items'] = 'Default Reorder Level When Creating Items';
$lang['config_remove_customer_company_from_receipt'] = 'Remove customer company name from receipt';

$lang['config_import_ecommerce_categories_into_phppos'] = 'Import categories into phppos';
$lang['config_import_ecommerce_tags_into_phppos'] = 'Imports tags into phppos';

$lang['config_shipping_zones'] = 'Shipping Zones';
$lang['config_add_shipping_zone'] = 'Add Shipping Zone';
$lang['config_no_results'] = 'No Results';
$lang['config_zip_search_term'] = 'Type in a zipcode';
$lang['config_searching'] = 'Searching...';
$lang['config_tax_class'] = 'Tax Group';
$lang['config_zone'] = 'Zone';

$lang['config_zip_codes'] = 'Zip Codes';
$lang['config_add_zip_code'] = 'Add Zip Code';
$lang['config_ecom_sync_logs'] = 'E-Commerce Syncing Logs';
$lang['config_currency_code'] = 'Currency Code';

$lang['config_add_currency_exchange_rate'] = 'Add Currency Exchange Rate';
$lang['config_currency_exchange_rates'] = 'Exchange Rates';
$lang['config_exchange_rate'] = 'Exchange Rate';
$lang['config_item_lookup_order'] = 'Item Lookup Order';
$lang['config_item_id'] = 'Item Id';
$lang['config_reset_ecommerce'] = 'Reset E-Commerce';
$lang['config_confirm_reset_ecom'] = 'Are you sure you want to reset e-commerce? This will only reset php point of sale so items are no longer linked';
$lang['config_reset_ecom_successfully'] = 'You have reset E-Commerce successfully';
$lang['config_number_of_decimals_for_quantity_on_receipt'] = 'Number of Decimals for Quantity On Receipt';
$lang['config_enable_wic'] = 'Enable WIC';
$lang['config_store_opening_time'] = 'Store Opening Time';
$lang['config_store_closing_time'] = 'Store Closing Time';
$lang['config_limit_manual_price_adj'] = 'Limit Manual Price Adjustments And Discounts';
$lang['config_always_minimize_menu'] = 'Always Minimize Left Side Bar Menu';

$lang['config_emailed_receipt_subject'] = 'Email Receipt Subject';


$lang['config_do_not_tax_service_items_for_deliveries'] = 'Do NOT tax service items for deliveries';


$lang['config_do_not_show_closing'] = 'Do not show expected closing amount when closing register';

$lang['config_paypal_me'] = 'PayPal.me Username';


$lang['config_show_barcode_company_name'] = 'Show company name on barcode';
$lang['config_import_ecommerce_attributes_into_phppos'] = 'Import attributes into phppos';
$lang['config_export_phppos_attributes_to_ecommerce'] = 'Export Attributes to ecommerce';

$lang['config_sku_sync_field'] = 'SKU field to sync with';



$lang['config_overwrite_existing_items_on_excel_import'] = 'Overwrite existing items on excel import';

$lang['config_do_not_force_http'] = 'Do not force HTTP when needed for EMV Credit Card Processing';
$lang['config_add_suspended_sale_type'] = 'Add Suspended Sale Type';
$lang['config_additional_suspend_types'] = 'Additional Suspended Sale Types';
$lang['config_remove_employee_from_receipt'] = 'Remove Employee Name From Receipt';
$lang['config_import_ecommerce_orders_into_phppos'] = 'Import orders into phppos';
$lang['import_ecommerce_orders_into_phppos'] = 'Import Orders into php pos';
$lang['config_hide_name_on_barcodes'] = 'Hide Name on Barcodes';


$lang['config_api_settings_info'] = 'API Settings';
$lang['config_keyword_api'] = 'API';
$lang['config_api_keys'] = 'API Keys';
$lang['config_api_key_ending_in'] = 'API Key Ending In';
$lang['config_permissions'] = 'Permissions';
$lang['config_last_access'] = 'Last Access';
$lang['config_add_key'] = 'Add API Key';
$lang['config_api_key'] = 'API Key';
$lang['config_read'] = 'Read';
$lang['config_read_write'] = 'Read/Write';
$lang['config_submit_api_key'] = 'Are you sure you want to add this key? Please make sure you have copied key to safe location as it won\'t be shown again.';
$lang['config_write'] = 'Write';
$lang['config_api_key_confirm_delete'] = 'Are you sure you want to delete this api key?';
$lang['config_key_copied_to_clipboard'] = 'Key Copied To Clipboard';

$lang['config_new_items_are_ecommerce_by_default'] = 'New Items are E-Commerce By Default';


$lang['config_new_items_are_ecommerce_by_default'] = 'New Items are E-Commerce By Default';

$lang['config_hide_description_on_sales_and_recv'] = 'Hide description on sales and receivings interfaces';





$lang['config_hide_item_descriptions_in_reports'] = 'hide item description in reports';





$lang['config_do_not_allow_item_with_variations_to_be_sold_without_selecting_variation'] = 'Do NOT allow variation items to be sold without selecting variation';



$lang['config_verify_age_for_products'] = 'Verify age for products';
$lang['config_default_age_to_verify'] = 'Default age to verify';




$lang['config_remind_customer_facing_display'] = 'Remind employee to open customer facing display';

$lang['config_import_tax_classes_into_phppos'] = 'Import Tax Classes into phppos';
$lang['config_export_tax_classes_into_phppos'] = 'Export tax classes to ecommerce';
$lang['config_import_shipping_classes_into_phppos'] = 'Import Shipping Classes into phppos';
$lang['config_disable_confirm_recv'] = 'Disable Confirmation for Complete Receiving';
$lang['config_minimum_points_to_redeem'] = 'Minimum number of points to redeem';
$lang['config_default_days_to_expire_when_creating_items'] = 'Default days to expire when creating items';


$lang['config_quickbooks_settings'] = 'Quickbooks Settings';
$lang['config_qb_sync_operations'] = 'Quickbooks Sync Operations';
$lang['config_import_quickbooks_items_into_phppos'] = 'Import items into phppos';
$lang['config_export_phppos_items_to_quickbooks'] = 'Export items to quickbooks';
$lang['config_import_customers_into_phppos'] = 'Import customers into phppos';
$lang['config_import_suppliers_into_phppos'] = 'Import suppliers into phppos';
$lang['config_import_employees_into_phppos'] = 'Import employees into phppos';
$lang['config_export_employees_to_quickbooks'] = 'Export employees to quickbooks';
$lang['config_export_sales_to_quickbooks'] = 'Export sales to quickbooks';
$lang['config_export_receivings_to_quickbooks'] = 'Export receivings to quickbooks';
$lang['config_export_customers_to_quickbooks'] = 'Export customers to quickbooks';
$lang['config_export_suppliers_to_quickbooks'] = 'Export suppliers to quickbooks';
$lang['config_connect_to_qb_online'] = 'Connect to quickbooks online';
$lang['config_refresh_tokens'] = 'Refresh Tokens';
$lang['config_reconnect_quickbooks'] = 'Reconnect to quickbooks online';
$lang['config_reset_quickbooks'] = 'Reset Quickbooks';
$lang['config_qb_sync_logs'] = 'Quickbooks sync logs';
$lang['config_quickbooks_progress'] = 'Quickbooks sync progress';
$lang['config_last_qb_sync_date'] = 'Last Sync Date';
$lang['config_confirmation_qb_cron_cancel'] = 'Are you sure you want to cancel the quickbooks sync?';
$lang['config_confirmation_qb_cron'] = 'Are you sure you want to sync quickbooks?';
$lang['config_confirm_reset_qb'] = 'Are you sure you want to reset quickbooks? This will unlink you from quickbooks.';
$lang['$platform=$this->Appconfig->get("ecommerce_platform");'] = 'if($platform=="woocommerce")';
$lang['config_reset_qb_successfully'] = 'You have reset quickbooks successfully';
$lang['config_export_phppos_categories_to_quickbooks'] = 'Export categories from phppos to quickbooks';
$lang['config_create_payment_methods'] = 'Create payment methods in QB';


$lang['config_allow_scan_of_customer_into_item_field'] = 'Allow scan of customer into item field';
$lang['config_cash_alert_high'] = 'Alert when cash is above';
$lang['config_cash_alert_low'] = 'Alert when cash is below';


$lang['config_sync_inventory_changes_qb'] = 'Sync inventory changes';

$lang['config_sort_receipt_column'] = 'Sort Receipt Column';





$lang['config_show_tax_per_item_on_receipt'] = 'Show tax per item on receipt';





$lang['config_enable_timeclock_pto'] = 'Enable time clock paid time off';


$lang['config_enable_timeclock_pto'] = 'Enable time clock paid time off';

$lang['config_show_item_id_on_recv_receipt'] = 'Show item id on receiving';





$lang['config_import_all_past_orders_for_woo_commerce'] = 'Import ALL past orders for WooCommerce';


// Custom lang Starts
$lang['config_default_revenue_account_for_item'] = 'Default Revenue Account For Items';
$lang['config_default_asset_account_for_item'] = 'Default Asset Account For Items';
$lang['config_default_expense_account_for_item'] = 'Default Expense Account For Items';
$lang['config_export_expenses_to_quickbooks'] = 'Export expenses to quickbooks';
$lang['config_chart_of_accounts'] = 'Quickbooks Chart of accounts';
$lang['config_keyword_chart_of_account'] = 'Quickbooks Chart of accounts';
$lang['config_default_refund_cash_account_name'] = 'Refund Cash Account';
$lang['config_default_refund_credit_account_name'] = 'Refund Credit Account';
$lang['config_default_refund_debit_card_account_name'] = 'Refund Debit Card Account';
$lang['config_default_refund_credit_card_account_name'] = 'Refund Credit Card Account';
$lang['config_default_refund_check_account_name'] = 'Refund Check Account';
$lang['config_default_refund_deposit_account_name'] = 'Refund Deposit Account';
$lang['config_default_expense_account_name'] = 'Expense Account';
$lang['config_default_expense_bank_credit_account_name'] = 'Expense Bank/Credit Account';
$lang['config_default_commission_credit_account_name'] = 'Commission Credit Account';
$lang['config_default_commission_debit_account_name'] = 'Commission Debit Account';
$lang['config_default_house_account_name'] = 'Store Account Name';
$lang['config_default_discount_item_name'] = 'Discount Item';
$lang['config_default_house_item_name'] = 'House Item Name';
$lang['config_default_store_account_item_name'] = 'Store Account Item';
$lang['config_default_house_account_category_name'] = 'House Account Category';
$lang['config_default_customer_id'] = 'Default Customer Name';
$lang['config_revenue_id']='Failed to save configuration. Default Revenue Account For Items is missing.';
$lang['config_asset_id']='Failed to save configuration. Default Asset Account For Items is missing';
$lang['config_export_confirm_box_text']='Do you want to Export items to quickbooks ?';
$lang['config_discount_accounting_id']='Discount Item Accounting Id is missing for Sale';
$lang['config_sync_for_discount_accounting_id']='Please sync items before creating invoices with discount';
$lang['config_default_tax']='Default Tax';
$lang['config_default_store_account_tax']='Default Store account tax';
$lang['config_check_tax_name']='Tax name provided is not correct. Please check sale id : ';


// Custom lang Ends



$lang['config_enable_margin_calculator'] = 'Enable Margin Calculator';










$lang['config_hide_barcode_on_barcode_labels'] = 'Hide Barcode On Labels';



$lang['config_do_not_delete_saved_card_after_failure'] = 'Do NOT delete  saved card after failure';





$lang['config_capture_internal_notes_during_sale'] = 'Capture Internal Notes During Sale';





$lang['config_hide_prices_on_fill_sheet'] = 'Hide Prices on Fulfillment Sheet';



$lang['$platform=$this->Appconfig->get("ecommerce_platform");'] = 'if($platform=="woocommerce")';


$lang['config_hide_desc_emailed_receipts'] = 'Hide Description on E-Mailed Receipts';


$lang['config_qb_start_sync_date'] = 'Start Sync Date';
$lang['config_default_tax_id'] = 'Default Tax';
$lang['config_markup_markdown'] = 'Markup/Markdown';
$lang['config_show_total_discount_on_receipt'] = 'Show Total Discount On Receipt';
$lang['config_default_credit_limit'] = 'Default Credit Limit';

$lang['config_hide_expire_date_on_barcodes'] = 'Hide Expire Date on Barcodes';

$lang['config_auto_capture_signature'] = 'Auto Capture Signature';


$lang['config_pdf_receipt_message'] = 'PDF receipt message in email body';

$lang['config_hide_merchant_id_from_receipt'] = 'Hide Merchant ID from receipt';


$lang['config_hide_all_prices_on_recv'] = 'Hide ALL prices on receiving';
$lang['config_do_not_delete_serial_number_when_selling'] = 'Do NOT delete serial number when selling';
$lang['config_webhooks'] = 'Web Hooks';
$lang['config_new_customer_web_hook'] = 'New Customer Web Hook URL';
$lang['config_new_sale_web_hook'] = 'New Sale Web Hook URL';
$lang['config_new_receiving_web_hook'] = 'New Receiving Web Hook';

$lang['config_strict_age_format_check'] = 'Age verification strict date format check';

$lang['config_flat_discounts_discount_tax'] = 'Flat Discount also discounts tax';
$lang['config_show_item_kit_items_on_receipt'] = 'Show Item Kit Items On Receipt';
$lang['config_amount_of_cash_to_be_left_in_drawer_at_closing'] = 'Amount of Cash to be Left in Drawer at Closing';
$lang['config_hide_tier_on_receipt'] = 'Hide Tier On Receipt';
$lang['config_second_language'] = 'Second Language on Receipts';
$lang['config_disable_gift_cards_sold_from_loyalty'] = 'Disable Gift Cards Sold From Earning Loyalty';
$lang['config_track_shipping_cost_for_receivings'] = 'Track Shipping Cost For Receivings';
$lang['config_enable_points_for_giftcard_payments'] = 'Enable points for gift card payments';




$lang['config_enable_tips'] = 'Enable Tips';

$lang['config_support_regex'] = 'Supports regular expressions. Example: 144.* matches anything starting with 144';

$lang['config_not_all_processors_support_tips'] = 'Not all processors support integrated tip processing';
$lang['config_require_supplier_recv'] = 'Require Supplier for Receiving';
$lang['config_default_payment_type_recv'] = 'Default Payment Type for Receivings';
$lang['config_taxjar_api_key'] = 'TaxJar API Key (US Only)';

$lang['config_quick_variation_grid'] = 'Enable Quick Select for Varitions on item grid';


$lang['config_quick_variation_grid'] = 'Quick select for Variations';


$lang['config_quick_variation_grid'] = 'Enable Quick Select In Item Grid For Variations';



$lang['config_show_full_category_path'] = 'Show Full Category Path When Searching';


$lang['config_do_not_upload_images_to_ecommerce'] = 'Do NOT upload images to E-Commerce';

$lang['config_woo_enable_html_desc'] = 'Enable HTML for descriptions';

$lang['config_use_rtl_barcode_library'] = 'Use RTL barcode library';
$lang['config_default_new_customer_to_current_location'] = 'Default new customer to current location';
$lang['config_week_start_day'] = 'Week Start Day';
$lang['config_scan_and_set_sales'] = 'Choose Quantity After Adding Item in Sales';
$lang['config_scan_and_set_recv'] = 'Choose Quantity After Adding Item in Receivings';
$lang['config_edit_sale_web_hook'] = 'Edit Sale Web Hook URL';
$lang['config_edit_recv_web_hook'] = 'Edit Receiving Web Hook URL';
$lang['config_hide_expire_dashboard'] = 'Hide Expiring Items On Dashboard';
$lang['config_hide_images_in_grid'] = 'Hide Images in Grid';
$lang['config_taxes_summary_on_receipt'] = 'Show Taxable and Non Taxable Summary On Receipt';
$lang['config_collapse_sales_ui_by_default'] = 'Collapse Sales interface by default';
$lang['config_collapse_recv_ui_by_default'] = 'Collapse Receiving interface by default';
$lang['config_enable_customer_quick_add'] = 'Enable Customer Quick Add';
$lang['config_uppercase_receipts'] = 'Uppercase Receipt Text';

$lang['config_edit_customer_web_hook'] = 'Edit Customer Web Hook URL';
$lang['config_show_selling_price_on_recv'] = 'Show Selling Price On Receiving Receipt';

$lang['config_hide_email_on_receipts'] = 'Hide E-Mail On Receipt';



$lang['config_hide_available_giftcards'] = 'Hide available gift cards in sales register';


$lang['config_enable_supplier_quick_add'] = 'Enable Supplier Quick Add';
$lang['config_sync_inventory_from_location'] = 'Sync Inventory From Location';
$lang['config_taxes_summary_details_on_receipt'] = 'Show Tax Details On Receipt';
$lang['config_disable_recv_number_on_barcode'] = 'Disable Receiving Number On Barcode';
$lang['config_tax_jar_location'] = 'Use TaxJar Location API to pull taxes';
$lang['config_disable_loyalty_by_default'] = 'Disable Loyalty By Default';

$lang['config_ecommerce_only_sync_completed_orders'] = 'Only Sync Completed E-Commerce Orders';

$lang['config_damaged_reasons'] = 'Damaged Reasons';

$lang['config_display_item_name_first_for_variation_name'] = 'Display item name first for variations on barcodes';


$lang['config_do_not_allow_sales_with_zero_value'] = 'Do NOT Allow Sales With Zero Value';

$lang['config_dont_recalculate_cost_price_when_unsuspending_estimates'] = 'Do not recalculate cost price when unsuspending estimates';


$lang['config_show_signature_on_receiving_receipt'] = 'Show signature on receiving receipt';

$lang['config_do_not_treat_service_items_as_virtual'] = 'Do NOT treat service items as virtual products in woo commerce';

$lang['config_hide_latest_updates_in_header'] = 'Hide Latest Updates in Header';
$lang['config_prompt_amount_for_cash_sale'] = 'Prompt Amount For Cash Sale';
$lang['config_do_not_allow_items_to_go_out_of_stock_when_transfering'] = 'Do not allow items to be out of stock when transferring';
$lang['config_show_tags_on_fulfillment_sheet'] = 'Show Item Tags On Fulfillment Sheet';
$lang['config_automatically_sms_receipt'] = 'Automatically SMS receipt';
$lang['config_items_per_search_suggestions'] = 'Number of items for search suggestions';

$lang['config_shopify_settings_info'] = 'Shopify Settings';
$lang['config_shopify_shop'] = 'Shopify Store URL';
$lang['config_connect_to_shopify'] = 'Connect To Shopify';
$lang['config_connect_to_shopify_reconnect'] = 'Reconnect To Shopify';
$lang['config_connected_to_shopify'] = 'You are connected to Shopify';
$lang['config_disconnect_to_shopify'] = 'Disconnect From Shopify';

$lang['config_offline_mode'] = 'Enable Offline Mode';
$lang['config_reset_offline_data'] = 'Reset Offline Data';



$lang['config_remove_quantity_suspending'] = 'Remove Quantity When Suspending';
$lang['config_auto_sync_offline_sales'] = 'Auto Sync Offline Sales When Back Online';

$lang['config_shopify_billing_terms'] = 'Activate billing - 14 day trial then ${SHOPIFY_PRICE} per month';
$lang['config_shopfiy_billing_failed'] = 'Shopify Billing Failed';
$lang['config_cancel_shopify'] = 'Cancel Shopify Billing';
$lang['config_confirm_cancel_shopify'] = 'Are you sure you want to cancel shopify?';
$lang['config_step_1'] = 'Step 1';
$lang['config_step_2'] = 'Step 2';
$lang['config_step_3'] = 'Step 3';
$lang['config_step_4'] = 'Step 4';
$lang['config_install_shopify_app'] = 'Install Shopify app';
$lang['config_connect_billing'] = 'Connect Billing';
$lang['config_choose_sync_options'] = 'Choose Sync Options';
$lang['config_ecommerce_sync_running'] = 'The E-Commerce sync is now running in the background. You can check the status in Store Config.';
$lang['config_show_total_on_fulfillment'] = 'Show Total On Fulfillment Sheet';
$lang['config_connect_shopify_in_app_store'] = 'You are not connected to Shopify. You can connect to Shopify in App Store';
$lang['config_override_signature_text'] = 'Override Signature Text';

$lang['config_delivery_color_based_on'] = 'Delivery Color Based On';
$lang['config_delivery_color_based_on_status'] = 'Status';
$lang['config_delivery_color_based_on_category'] = 'Category';


$lang['config_update_cost_price_on_transfer'] = 'Update Cost Price On Transfer';



$lang['config_tip_preset_zero'] = 'Tip preset amount of 0%';



$lang['config_layaway_statement_message'] = 'Layaway Statement Message';


$lang['config_show_person_id_on_receipt'] = 'Show Person ID on receipt';




$lang['config_import_ecommerce_orders_suspended'] = 'Import E-Commerce Orders Suspended';

$lang['config_show_images_on_receipt'] = 'Show Images on Receipt';

$lang['config_disabled_fixed_discounts'] = 'Disable Entire Fixed Discounts On Sales Interface';



$lang['config_always_put_last_added_item_on_top_of_cart'] = 'Always put last added item on top of cart';



$lang['config_show_giftcards_even_if_0_balance'] = 'Show Gift cards even if zero balance';

$lang['config_scale_5'] = 'Weight Embedded Barcode';

$lang['config_disable_modules'] = 'Disable Modules';

$lang['config_hide_description_on_suspended_sales'] = 'Hide Item Description On Suspended Sales';
$lang['config_override_symbol_non_taxable'] = 'Override Symbol for Non Taxable';



$lang['config_hide_categories_sales_grid'] = 'Hide Categories on Sales Grid';
$lang['config_hide_tags_sales_grid'] = 'Hide Tags on Sales Grid';
$lang['config_hide_favorites_sales_grid'] = 'Hide Favorites on Sales Grid';
$lang['config_hide_categories_receivings_grid'] = 'Hide Categories on Receivings Grid';
$lang['config_hide_tags_receivings_grid'] = 'Hide Tags on Receivings Grid';
$lang['config_hide_suppliers_receivings_grid'] = 'Hide Suppliers on Receivings Grid';
$lang['config_hide_favorites_receivings_grid'] = 'Hide Favorites on Receivings Grid';
$lang['config_hide_suppliers_sales_grid'] = 'Hide Suppliers on Sales Grid';

$lang['config_offline_mode_sync_period'] = 'Offline Mode Sync Cycle Time (Hour)';



$lang['config_receipt_download_filename_prefix'] = 'Receipt Download Filename Prefix';
$lang['config_remove_employee_lastname_from_receipt'] = 'Remove Employee Last Name From Receipt';
$lang['config_override_symbol_taxes_summary_on_receipt'] = 'OVERRIDE SYMBOL FOR SHOW TAXABLE AND NON TAXABLE SUMMARY ON RECEIPT';

$lang['config_show_images_on_receipt_width_percent'] = 'Max Width Of Item Images On Receipt (Percentage)';

$lang['config_link_to_receipt'] = 'Link To Receipt';
$lang['config_sale_summary_info'] = 'Sale Summary Info';
$lang['config_qr_code_format'] = 'QR Code Format';
$lang['config_override_symbol_taxable_summary'] = 'Override Symbol for Taxable Summary On Receipt';
$lang['config_override_symbol_non_taxable_summary'] = 'Override Symbol for Non Taxable Summary On Receipt';
$lang['config_allow_drag_drop_recv'] = 'Allow Reorder on receiving screen';
$lang['config_allow_drag_drop_sale'] = 'Allow Reorder on sale screen';
$lang['config_disable_signature_capture_on_terminal_for_phppos_credit_card_processing'] = 'Disable Signature Capture for PHP POS Credit Card Processing';


$lang['config_capture_internal_notes_during_receiving'] = 'Capture Internal Notes During Receiving';


$lang['config_default_employee_for_deliveries'] = 'Default Employee for Deliveries';

$lang['config_disable_verification_for_qr_codes'] = 'Disable verification for QR codes (Do not require a phone number or email)';

$lang['config_disable_variation_popup_in_receivings'] = 'Disable Variation Popup in Receiving';

$lang['config_saudi_arabia_digital_receipt'] = 'Saudi Arabia Digital Receipt';

$lang['config_scale_6'] = 'Weight Embedded Barcode EAN 13';

$lang['config_hide_location_name_on_receipt'] = 'Hide Location Name On Receipt';
$lang['config_disable_discount_by_percentage'] = 'Disable Entire Discounts By Percentage on Sales Interface';

$lang['Delivery ID'] = 'Delivery ID';
$lang['config_scale_7'] = 'Weight Embedded Barcodes EAN 13 4 price digits';


$lang['config_use_main_image_as_default_image_in_e_commerce'] = 'Use Main Image As Default Image In E-Commerce';

$lang['config_disable_discounts_percentage_per_line_item'] = 'Disable Discounts Percentage Per Line Item';
$lang['config_send_sms_via_whatsapp'] = 'Send SMS Via Whatsapp';
$lang['config_additional_appointment_note'] = 'Additional Appointment Note';
$lang['config_keywords_help_text'] = 'Here, you can use some keywords.';

$lang['config_number_of_decimals_displayed_on_sales_interface'] = 'Number of Decimals Displayed on Sales Interface';

$lang['shopify_private_key'] = 'Shopify Private Key';
$lang['shopify_public_key'] = 'Shopify Public Key';
$lang['config_reconnect_to_shopify'] = 'Reconnect to Shopify';

$lang['config_customer_allow_partial_match'] = 'Allow Partial Match For Customer Search';
?>