<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
// ---------------------------------------------------------------------
class Languagecheck extends MY_Controller {

	/*
	 * use this language as comparison reference.
	 * this should be the one that is complete.
	 */
	private $reference = 'english';

	private $lang_path = 'language';

	// -----------------------------------------------------------------

	/*
	 * controller constructor
	 */
	function __construct()
	{
		if (!file_exists('tools/translate/translate_functions.php'))
		{
			die('Cannot run');
		}
		
		set_time_limit(0);
		ini_set('max_input_time','-1');
		parent::__construct();
	}

	// -----------------------------------------------------------------

	function index($disable_white_list = 0, $fix_missing_keys = 0, $fix_matching_english_keys = 0)
	{
		// load the required helpers
		$this->load->helper('directory');

		// for simplicity, we don't use views
		$this->output('h1', 'PHP Point Of Sale - Language file checking and validation');

		// determine the language file path
		if ( ! is_dir($this->lang_path) )
		{
			$this->lang_path = APPPATH . $this->lang_path;

			if ( ! is_dir($this->lang_path) )
			{
				$this->output('h2', 'Defined language path "'.$this->lang_path.'" not found!', TRUE);
				exit;
			}
		}

		// fetch the languages directory map
		$languages = str_replace(DIRECTORY_SEPARATOR,'',directory_map( $this->lang_path, TRUE ));

		// is our reference language present?
		if ( ! in_array($this->reference, $languages ) )
		{
			$this->output('h2', 'Reference language "'.$this->reference.'" not found!', TRUE);
			exit;
		}

		// load the list of language files for the reference language
		$references = str_replace(DIRECTORY_SEPARATOR, '' ,directory_map( $this->lang_path . '/' . $this->reference, TRUE ));

		// now process the list
		foreach( $references as $reference )
		{
			// skip non-language files in the language directory
			if ( strpos($reference, '_lang'.'.php') === FALSE )
			{
				continue;
			}

			// process it
			$this->output('h2', 'Processing '.$this->reference . ' &raquo; ' .$reference);

			// load the language file
			include $this->lang_path . '/' . $this->reference . '/' . $reference;

			// did the file contain any language strings?
			if ( empty($lang) )
			{
				// language file was empty or not properly defined
				$this->output('h3', 'Language file doesn\'t contain any language strings. Skipping file!', TRUE);
				continue;
			}

			// store the loaded language strings
			$lang_ref = $lang;
			unset($lang);

			// now loop through the available languages
			foreach ( $languages as $language )
			{
				// language file to check
				$file = $this->lang_path . '/' . $language . '/' . $reference;

				// skip the reference language
				if ( $language == $this->reference )
				{
					if ($this->last_line($file) !='?>')
					{
						$this->output('h3', 'Language file ('.$language.') does not end in ?>', TRUE);
					}
					continue;
				}


				// check if the language file exists for this language
				if ( ! file_exists( $file ) )
				{
					// file not found
					$this->output('h3', 'Language file doesn\'t exist for the language '.$language.'!', TRUE);
				}
				else
				{
					if ($this->last_line($file) !='?>')
					{
						$this->output('h3', 'Language file does not end in ?> '.$language.'!', TRUE);
					}
					
					// load the file to compare
					include $file;

					// did the file contain any language strings?
					if ( empty($lang) )
					{
						// language file was empty or not properly defined
						$this->output('h3', 'Language file for the language '.$language.' doesn\'t contain any language strings!', TRUE);
					}
					else
					{
						// start comparing
						$this->output('h3', 'Comparing with the '.$language.' version:');

						// assume all goes well
						$failures = 0;

						// start comparing language keys
						foreach( $lang_ref as $key => $value )
						{
							if ( ! isset($lang[$key]) or $lang[$key] == 'NOT_TRANSLATED' or $lang[$key] == $lang_ref[$key])
							{
								if(!isset($lang[$key]))
								{
									// report the missing key
									$this->output('', 'Missing language string "'.$key.'"'.' ('.$lang_ref[$key].')', TRUE);
									
									if ($fix_missing_keys)
									{
										$this->_translate_with_key_and_english_value($key, $lang_ref[$key], $file);
									}
								}	
								elseif($lang[$key] == 'NOT_TRANSLATED')
								{
									$this->output('', 'NOT TRANSLATED language string "'.$key.'"', TRUE);
								}
								elseif($lang[$key] == $lang_ref[$key])
								{
									if (!$disable_white_list)
									{
										$white_list = array(
											'common_email', 'common_inv','common_total', 'common_no', 'config_fax', 'giftcards_id', 'common_description', 'giftcards_amazon',
											'giftcards_giftcard', 'giftcards_one_or_multiple', 'items_description', 'items_image', 'items_amazon', 'items_item_number', 
											'locations_fax', 'locations_email', 'locations_update', 'login_version', 'module_receivings', 'receivings_transfer', 
											'common_date', 'reports_description', 'reports_id', 'reports_serial_number', 'reports_receivings', 'reports_month_04', 'reports_month_09',
											'reports_giftcards', 'sales_description_abbrv', 'common_stock', 'common_type', 'sales_signature', 'sales_item_number','reports_store_account','sales_serial', 'common_powered_by',
											'config_sandbox', 'customers_pay','giftcards_upc_database', 'giftcards_tax_1','giftcards_tax_2','locations_location_id','common_error'
											,'customers_tier_type','items_defaults', 'common_step_1','config_module','common_merchant_id','config_moving_average','items_csv_import','common_tax_3','common_tax_4', 'common_tax_5', 'sales_global_sale_discount',
											'sales_total', 'receivings_total','config_spreadsheet_format', 'config_csv','config_xlsx', 'common_layaway', 'reports_total','reports_taxes',
											'common_website', 'common_det', 'common_update', 'common_barcode_labels', 'common_dashboard', 'config_website', 'config_backup_database', 'config_mailchimp_api_key', 'config_hosted_checkout_merchant_password', 
											'item_kits_successful_updating', 'items_upc_database', 'common_product_id', 'common_clone', 'login_go', 'login_login','reports_type','reports_month_11',
											'reports_month_12', 'reports_sales_generator_selectCondition_1', 'reports_sales_generator_selectCondition_5', 'reports_credit', 'common_sub_total','sales_sub_total', 'config_application_settings_info', 'config_medium', 'item_id'
											,'common_percentage', 'locations_registers','reports_commission', 'sales_half_dollars', 'sales_dimes','sales_pennies', 'sales_nickels','sales_quarters',
 											'common_week','common_tags','common_clear_selection','common_fields','common_credit_card_processing','config_enable_timeclock','config_emv_merchant_id','config_com_port',
 											'common_employees_message','employees_timeclock','giftcards_log','item_kits_id','items_edit_status','items_open',
											'module_messages','locations_color','reports_tiers','reports_credits','reports_gift_card_sales_reports','reports_open',
											'common_status','reports_data','reports_tiers_summary_report','reports_tags_summary_report','sales_merchant_copy','common_show_grid','lookup_receipt',
											'expenses_id','expenses_type','expense_type','expenses_description','expenses_date','expense_description','expense_date','common_open','common_mailing_labels',
											'common_name','common_tag','common_template','config_advanced','config_loyalty_option','config_store_account_statement_message','config_production','config_simple','config_mailing_labels_type',
											'config_point_value','config_test_mode','customers_taxable','customers_import','messages_inbox','receivings_email_po','receivings_purchase_order',
											'reports_time_range','reports_audit_report','reports_date','reports_min','reports_name','reports_month_08','reports_store_account_statements','reports_total_receivings',
											'expenses_basic_information','items_view_count','items_date','items_import','items_are_you_sure_cancel','items_unit_price_value','items_promo_price_value','items_scan_and_set',
											'locations_credit_card_processor','locations_emv_terminal_id','locations_braintree_public_key','locations_braintree_private_key','locations_mailchimp_api_key','locations_stripe_private','locations_name','locations_com_port',
											'reports_subtotal','sales_unredeem','sales_type','sales_entry_method','sales_undeleted_voided','common_item','common_item_number_expanded','item_kits_item','reports_balance_to_pay','common_ebt','common_ebt_cash','common_download_spreadsheet_template',
											'common_database_field_mappings_must_be_unique','common_database_field','common_desktop','common_test','common_minutes','title_cron_documentation','common_mobile','woo_cron_job_entry','common_margin','common_cost_plus_percent','config_email_crlf','config_enable_markup_calculator',
											'config_last_sync_date','config_smtp_crypto','config_smtp_host','config_smtp_timeout','config_barcodes_info','config_store_accounts_info','config_payment_types_info','config_keyword_barcodes',
											'config_keyword_items','config_keyword_store_accounts','config_woo_version','config_ecommerce_progress','config_sync','config_send_test_email','config_keyword_taxes','config_keyword_profit','config_woo_api_url','config_woo_api_key','home_ecommerce_platform_sync',
											'items_in_spreadsheet','items_image_alt_text','items_duplicate_item_number','locations_ip_tran_device_id','locations_secure_device_override_emv','locations_net_e_pay_server','locations_terminal_id','migrate_title','migrate_quickbooks_aaatex_integration','migrate_device_override_emv',
											'migrate_sessions_table_change','migrate_new_import','price_rules_select_tags','simple_discount','price_rules_id','price_rules_new','price_rules_status','spend_x_get_discount','price_rules_select_item_kits','price_rules_percent_discount_per_unit','receivings_pays_receivings',
											'sales_ebt_balance_amount','reports_variance','reports_outstanding_recv','sales_ebt_cash_balance','sales_ebt_balance','config_wide_printer_receipt_format',
											'common_wic','common_item_kit','common_item_kit_id','common_reset','common_save','common_begins','common_zips','common_coupons','common_EXCEPTION','common_text',
											'common_checkbox','common_label_printer','common_standard_printer','common_visible_columns','common_default','common_custom_field_config','common_field_3_type','common_dropdown','common_field_4_name',
 											'common_column_filters','config_zone','config_item_lookup_order','config_rate_name','config_item_lookup_order','config_ecom_sync_logs','deliveries_status','deliveries_error','migrate_Zips',
											'sales_declined','migrate_item_lookup_order','migrate_ecommerce_performance_improvements','migrate_price_tiers_cost_plus_fixed_amount','sales_id','deliveries_search','reports_total_due',
											'locations_cc_email','locations_bcc_email','locations_new','locations_card_reader_actions','reports_returns','common_barcodes','common_details','common_undelete','common_item_kit_info','common_suspended_recievings',
											'common_ecommerce','common_online','common_zip','common_excel_export','common_sales_tax_2','common_access_denied','common_variation','common_item_kit_numbers','common_upload',
											'common_promo_price','make_cache','link_all','ecom_undelete_item','link_item','common_taxable','common_images','sales_ebt_auth_code','receivings_new_location','items_is_service',
											'items_no_description_items','items_variation','items_edit_item','items_variations','items_edit_variations','item_kits_new','item_kits_next_item_kit','migrate_ebt_non_integrated_flag','migrate_cascade_delete_tags',
											'migrate_16_0_version','migrate_item_attributes','migrate_promo_price_no_date_needed','migrate_fulltext_variation','migrate_woocommerce_sync_variations','text_rest_invalid_api_key','text_rest_invalid_credentials','text_rest_ip_denied','text_rest_ip_unauthorized','text_rest_unauthorized',
											'text_rest_ajax_only','text_rest_api_key_unauthorized','text_rest_api_key_permissions','text_rest_api_key_time_limit','text_rest_ip_address_time_limit','text_rest_unknown_method','text_rest_unsupported','migrate_test','migrate_age_verify','migrate_16_1_version','migrate_track_delivery_person','config_keyword_api'
												,'config_api_key_ending_in','config_api_keys','config_api_key','common_id','giftcards_sales_tax_2'
										);
									}
									else
									{
										$white_list = array();
									}
										
									if (!in_array($key, $white_list))
									{
										if ($fix_matching_english_keys)
										{
											$this->_translate_with_key_and_english_value($key, $lang_ref[$key], $file);
										}
										
										$this->output('', 'MATCHES ENGLISH "'.$key.'"'.' ('.$lang[$key].')', TRUE);
									}
								}
								// increment the failure counter
								$failures++;
							}
						}

						if ( ! $failures )
						{
							$this->output('', 'The two language files have matching strings.');
						}
					}

					// make sure the lang array is deleted before the next check
					if ( isset($lang) )
					{
						unset($lang);
					}
				}
			}

		}

		$this->output('h2', 'Language file checking and validation completed');
	}

	// -----------------------------------------------------------------

	private function output($type = '', $line = '', $highlight = FALSE)
	{
		switch ($type)
		{
			case 'h1':
				$html = "<h1>{line}</h1>\n<hr />\n";
				break;

			case 'h2':
				$html = "<h2>{line}</h2>\n";
				break;

			case 'h3':
				$html = "<h3>&nbsp;&nbsp;&nbsp;{line}</h3>\n";
				break;

			default:
				$html = "&nbsp;&nbsp;&nbsp;&nbsp;&raquo;&nbsp;{line}<br />";
				break;
		}

		if ( $highlight )
		{
			$line = '<span style="color:red;font-weight:bold;">' . $line . '</span>';
		}

		echo str_replace('{line}', $line, $html);
	}
	// -----------------------------------------------------------------
	
	function last_line($filepath) 
	{
		$lines  = file($filepath);
		return array_pop($lines);

	}
	
	function _translate_with_key_and_english_value($key, $value, $file)
	{		
		$file = basename($file);
		//English for comparison
		include $this->lang_path . '/' . 'english' . '/' . $file;
		$lang_english_ref = $lang;
		unset($lang);
		
		require_once('tools/translate/translate_functions.php');
		require('tools/settings.php');
		
		foreach($languages as $folder=>$code)
		{
		   $path = 'application/language/'.$folder.'/'.$file;		
			$current_file_contents = file_get_contents($path); 
		
			//If we have already translated, update it
			if (preg_match("/['\"]{1}${key}['\"]{1}/",$current_file_contents))
			{
				$current_value_matches = array();
				preg_match("/\[['\"]{1}${key}['\"]{1}[^=]+=[ ]*['\"]{1}([^'\"]+)['\"]{1};/", $current_file_contents,$current_value_matches);
				$current_value = array_pop($current_value_matches);
				
				//Only update if current value is the same as english and we are NOT english
				if ($current_value == $lang_english_ref[$key] && $code != 'en')
				{
	  		   	$transaltedValue = str_replace("'","\'", translateTo($value, $code));
					$find_existing_translation = "/(\[['\"]{1})(${key}['\"]{1}[^=]+=[ ]*['\"]{1})([^'\"]+)(['\"]{1};)/";
					$new_file_contents = preg_replace($find_existing_translation, '${1}${2}'.$transaltedValue.'${4}', $current_file_contents);
					file_put_contents($path, $new_file_contents);
				}
			}
			else //We haven't translated: Add
			{
  		  $transaltedValue = str_replace("'","\'", translateTo($value, $code));
				$pair = "\$lang['$key'] = '$transaltedValue';";
				file_put_contents($path, str_replace('?>', "$pair\n?>", $current_file_contents));
			}
		}
	}	
}

/* End of file languagecheck.php */
/* Location: ./application/controllers/languagecheck.php */
