<?php
require_once ("Secure_area.php");
require_once (APPPATH."models/cart/PHPPOSCartRecv.php");
require_once (APPPATH."traits/taxOverrideTrait.php");

class Receivings extends Secure_area
{	
	use taxOverrideTrait;
	
	public $cart;
	public $view_data = array();
	
	function __construct()
	{	
		parent::__construct('receivings');
		
		$this->lang->load('receivings');
		$this->lang->load('module');
		$this->load->helper('items');
		$this->load->model('Receiving');
		$this->load->model('Supplier');
		$this->load->model('Category');
		$this->load->model('Tag');
		$this->load->model('Item');
		$this->load->model('Item_location');
		$this->load->model('Item_kit_location');
		$this->load->model('Item_kit_location_taxes');
		$this->load->model('Item_kit');
		$this->load->model('Item_kit_taxes');
		$this->load->model('Item_kit_items');
		$this->load->model('Item_location_taxes');
		$this->load->model('Item_taxes');
		$this->load->model('Item_taxes_finder');
		$this->load->model('Item_kit_taxes_finder');
		$this->load->model('Appfile');
		$this->load->model('Item_variation_location');
		$this->load->helper('text');
		$this->cart = PHPPOSCartRecv::get_instance('receiving');
		cache_item_and_item_kit_cart_info($this->cart->get_items());
	}
	
	function index()
	{		
		$this->_reload(array(), false);
	}

	function reload()
	{
		$this->_reload();
	}
	function item_search()
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		if(!$this->config->item('speed_up_search_queries'))
		{
			$suggestions = $this->Item->get_item_search_suggestions($this->input->get('term'),0,'cost_price',$this->config->item('items_per_search_suggestions') ? (int)$this->config->item('items_per_search_suggestions') : 20);
			$suggestions = array_merge($suggestions, $this->Item_kit->get_item_kit_search_suggestions_sales_recv($this->input->get('term'),0,'cost_price', 100));
		}
		else
		{
			$suggestions = $this->Item->get_item_search_suggestions_without_variations($this->input->get('term'),0,$this->config->item('items_per_search_suggestions') ? (int)$this->config->item('items_per_search_suggestions') : 20,'cost_price');
			$suggestions = array_merge($suggestions, $this->Item_kit->get_item_kit_search_suggestions_sales_recv($this->input->get('term'),0,'cost_price', 100));
			
			for($k=0;$k<count($suggestions);$k++)
			{
				if(isset($suggestions[$k]['avatar']))
				{
					$suggestions[$k]['image'] = $suggestions[$k]['avatar'];
				}
				
				if(isset($suggestions[$k]['subtitle']))
				{
					$suggestions[$k]['category'] = $suggestions[$k]['subtitle'];
				}
			}
		}
		echo json_encode(H($suggestions));
	}

	function supplier_search()
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		$suggestions = $this->Supplier->get_supplier_search_suggestions($this->input->get('term'),0,100);
		
		if ($this->config->item('enable_supplier_quick_add'))
		{
			$suggestions[] = array('subtitle' => '','avatar' => base_url()."assets/img/user.png",'value' => 'QUICK_ADD|'.$this->input->get('term'), 'label' => lang('suppliers_add_new_supplier').' '.$this->input->get('term'));
		}
		
		echo json_encode(H($suggestions));
	}

	function select_supplier()
	{			
		
		
		if ($this->config->item('enable_supplier_quick_add') && strpos($this->input->post('supplier'),'QUICK_ADD|') !== FALSE)
		{
			$_POST['supplier'] = str_replace('QUICK_ADD|','',$_POST['supplier']);
			$_POST['supplier'] = str_replace('|FORCE_PERSON_ID|','',$_POST['supplier']);
			$this->load->helper('text');
			$person_data = array('first_name' => '','last_name' => '');
			$supplier_data = array('company_name' => $this->input->post('supplier'));
			$this->supplier->save_supplier($person_data, $supplier_data);
			$_POST['supplier'] =  $person_data['person_id'];
		}		
		
		$data = array();
		$supplier_id = $this->input->post("supplier");
		
		if (strpos($supplier_id,'|FORCE_PERSON_ID|') !== FALSE)
		{
			$supplier_id = str_replace('|FORCE_PERSON_ID|','',$supplier_id);
		}
		elseif ($this->Supplier->account_number_exists($supplier_id))
		{
			$supplier_id = $this->Supplier->supplier_id_from_account_number($supplier_id);
		}
		
		if ($this->Supplier->exists($supplier_id))
		{
			$this->cart->supplier_id = $supplier_id;
		}
		else
		{
			$data['error']=lang('receivings_unable_to_add_supplier');
		}
		$this->cart->delete_all_paid_store_account_payment_ids();
		$this->cart->save();
		$this->_reload($data);
	}

	function location_search()
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		$suggestions = $this->Location->get_locations_search_suggestions($this->input->get('term'),0,100);
		echo json_encode(H($suggestions));
	}
	
	function select_location_from()
	{
		$data = array();
		$location_id = $this->input->post("location_from");
		
		if ($this->Location->exists($location_id))
		{
			$this->cart->transfer_from_location_id = $location_id;
			$this->cart->save();
		}
		else
		{
			$data['error']=lang('receivings_unable_to_add_location');
		}
		$this->_reload($data);
		
	}

	function select_location()
	{
		$data = array();
		$location_id = $this->input->post("location");
		
		if ($this->Location->exists($location_id))
		{
			$this->cart->transfer_location_id = $location_id;
			$this->cart->save();
		}
		else
		{
			$data['error']=lang('receivings_unable_to_add_location');
		}
		$this->_reload($data);
	}
	
	function set_change_cart_date() 
	{
 	  $this->cart->change_cart_date = $this->input->post('change_cart_date');
		$this->cart->save();
	}
	
	function set_email_receipt()
	{
 	  $this->cart->email_receipt = $this->input->post('email_receipt');
		$this->cart->save();
	}
	
	
	function set_change_date_enable() 
	{
 	  $this->cart->change_date_enable = $this->input->post('change_date_enable');
	  if (!$this->cart->change_cart_date)
	  {
	 	  $this->cart->change_cart_date = date(get_date_format().' '.get_time_format());
	  }
		$this->cart->save();
		
	}
	

	function delete_location()
	{
		$this->cart->transfer_location_id = NULL;
		$this->cart->save();
		$this->_reload();
	}

	function delete_location_from()
	{
		$this->cart->transfer_from_location_id = NULL;
		$this->cart->save();
		$this->_reload();
	}

	function change_mode()
	{
		$data = array();
		$previous_mode = $this->cart->get_mode();
		
		$mode = $this->input->post("mode");		
		
		if ($previous_mode == 'store_account_payment' && ($mode == 'receive' || $mode == 'return' || $mode == 'purchase_order'))
		{
			$this->cart->empty_items();
		}
		
		if($previous_mode == 'transfer' && $mode!='transfer')
		{
			$this->cart->transfer_location_id = NULL;
		}
		
		$this->cart->set_mode($mode);
		
		if ($mode == 'store_account_payment')
		{
			$store_account_payment_item_id = $this->Item->create_or_update_store_account_item();
			$this->cart->empty_items();
			$this->cart->add_item(new PHPPOSCartItemRecv(
				array(
					'cost_price' => 0,
					'unit_price' => 0,
					'scan' => $store_account_payment_item_id.'|FORCE_ITEM_ID|',
					'cart' => $this->cart,
				)));
		}
		
		if ($previous_mode == 'receive' && $mode =='return')
		{
			if ($this->cart->can_convert_cart_from_sale_to_return())
			{
				$data  = array('prompt_convert_sale_to_return' => TRUE);
			}
			else
			{
				$data  = array('prompt_convert_sale_to_return' => FALSE);					
			}
		}
		elseif($previous_mode =='return' && $mode =='receive')
		{
			if ($this->cart->can_convert_cart_from_return_to_sale())
			{
				$data  = array('prompt_convert_return_to_sale' => TRUE);
			}
			else
			{
				$data  = array('prompt_convert_return_to_sale' => FALSE);					
			}				
		}
		
		if ($mode == 'transfer')
		{
			$this->cart->do_convert_cart_from_sale_to_return();
		}
		$this->cart->save();
		
		$this->_reload($data);
	}

	function set_selected_payment()
	{
		$this->cart->selected_payment = $this->input->post('payment');
		$this->cart->save();
	}
	
	function set_comment() 
	{
		$this->cart->comment = $this->input->post('comment');
		$this->cart->save();
	}

	function set_internal_notes()
	{
		$person_data = array();
		$supplier_data = array('internal_notes' => $this->input->post('internal_notes'));
		$this->load->model('Supplier');
		$this->Supplier->save_supplier($person_data,$supplier_data,$this->cart->supplier_id);
	}

	function add()
	{
		$this->cart->sort_clean();
		$this->cart->process_barcode_scan($this->input->post("item"));
		$this->cart->save();
		$this->_reload();
	}

	function add_variations_qty()
	{
		if (!$data = json_decode($this->input->post("items"), true)) return true;
		foreach ($data as $k => $v) {
			$this->cart->process_barcode_scan($v);
		}
		$this->cart->save();
		$this->_reload();
	}

	function get_item_attr()
	{
		$id = explode('#', $this->input->post("item"));
		if ($id == $this->input->post("item")) return true;
		$this->load->model('Item_variations');
		$variations = $this->Item_variations->get_variations($id['0']);
		$l = [];
		foreach ($variations as $k => $v) {
			if ($v['name'] != null) {
				$l[$k] = $v['name'];
				continue;
			}
			$lable = [];
			foreach ($v['attributes'] as $k_a => $la){
				$lable[] = $la['label'];
			}

			$l[$k] = implode(',', $lable);
		}
		echo json_encode($l);
	}

	function delete_tax($name)
	{
		$this->check_action_permission('delete_taxes');
		$name = rawurldecode($name);
		$this->cart->add_excluded_tax($name);
		$this->cart->save();
		$this->_reload();
	}
	
	function edit_line_total($line)
	{
		$item = $this->cart->get_item($line);
		$total =$this->input->post('value');
		$item->unit_price = -1*((100*$total)/($item->quantity*($item->discount-100)));
		$this->cart->save();
		$this->_reload();
	}
	
	
	public function edit_item_variation($line)
	{
		if ($item = $this->cart->get_item($line))
		{
			$variation_id = $this->input->post('value');
			$item->variation_id = $variation_id;
			$this->load->model('Item_variations');
			$item->variation_name = $this->Item_variations ->get_variation_name($variation_id);
			
			$cur_item_variation_info = $this->Item_variations->get_info($variation_id);
			if ($cur_item_variation_info->cost_price)
			{
				 $item->unit_price = $cur_item_variation_info->cost_price;
			}
			
			if ($cur_item_variation_info->unit_price)
			{
				$item->selling_price = $cur_item_variation_info->unit_price;
			}
			
		}
		$this->cart->save();
		
		$this->_reload();
	}

	function edit_item($line)
	{
		$data= array();

		$this->form_validation->set_rules('selling_price', 'lang:common_price', 'numeric');
		$this->form_validation->set_rules('unit_price', 'lang:common_price', 'numeric');
		$this->form_validation->set_rules('quantity', 'lang:common_quantity', 'numeric');
		$this->form_validation->set_rules('quantity_received', 'lang:receivings_qty_received', 'numeric');
		$this->form_validation->set_rules('discount', 'lang:common_discount_percent', 'numeric');

		if ($this->form_validation->run() != FALSE)
		{
			if($this->input->post("name"))
			{
				$variable = $this->input->post("name");
				$$variable = $this->input->post("value");
				
				$this->cart->sort_clean();

				/**
				* Get Serial or Serial Range Number
				* Trim Serial Number, Preg & Make Array
				* 
				* @param Serial Number & Serial Range
				* 
				* @return Serial Number
				*
				**/

				$return_serial_range 	= array();
				$return_array 			= false;
				
				// Get Serial Number 
				if ($this->input->post('name') == 'serialnumber') {
					
					// Remove White Space & Make Array
					$array_preg 			= preg_replace('/\s+/', '-', trim($$variable));
					$return_serial_range 	= explode('-', $array_preg);
					$return_array 			= true;

				}

				
				// Get Serial Range and Create Rest Count 
				if ($this->input->post('name') == 'generate_serial_range') {

					// Remove White Space & Make Array
					$array_preg 			= preg_replace('/\s+/', '-', trim($$variable));
					$array_explode 			= explode('-', $array_preg);

					// Separate Alpha & Numberic Characters
					$get_alpha_characters 	= preg_replace("/[^A-Z]+/", "", $array_explode);
					$get_numeric_characters = preg_replace("/[^0-9]/", "", $array_explode);

					// Validate Array if Exit
					if (isset($get_numeric_characters[1])) {
	
						for ($i=$get_numeric_characters[0]; $i <= $get_numeric_characters[1]; $i++) { 
							$return_serial_range[] = $get_alpha_characters[0].$i;
							$return_array = true;
						}
					} else {
						$return_serial_range = $get_alpha_characters[0].$get_numeric_characters[0];	
					}
					
				}

				if ($item = $this->cart->get_item($line))
				{
			
					if ($return_array == true) {
						$item->serialnumber = $return_serial_range ? implode(', ',$return_serial_range) : $item->serialnumber;
					} else {
						$item->serialnumber = $return_serial_range ? $return_serial_range : $item->serialnumber;
					}
					
					
					try
					{
						//always force negative
						if ($this->cart->get_mode() == 'transfer' && $variable == 'quantity')
						{							
							$quantity = abs($quantity) * -1;
						}
						
						if ($variable != 'expire_date')
						{							
							$item->$variable = $$variable;
							
							if($variable == 'quantity_unit_id')
							{
								$qui = $this->Item->get_quantity_unit_info($$variable);
				
								$cur_item_info = $this->Item->get_info($item->item_id);
								$cur_item_location_info = $this->Item_location->get_info($item->item_id);
	
								$cost_price_to_use = ($cur_item_location_info && $cur_item_location_info->cost_price) ? $cur_item_location_info->cost_price : $cur_item_info->cost_price;
								$unit_price_to_use = ($cur_item_location_info && $cur_item_location_info->unit_price) ? $cur_item_location_info->unit_price : $cur_item_info->unit_price;
				
								if ($qui !== NULL)
								{
									$item->quantity_unit_quantity = $qui->unit_quantity;
									if ($qui->cost_price !== NULL)
									{
										$item->unit_price = $qui->cost_price;
									}
									else
									{
										$item->unit_price = $cost_price_to_use*$item->quantity_unit_quantity;
									}
									
									
									if ($qui->unit_price !== NULL)
									{
										$item->cost_price = $qui->unit_price;
									}
									else
									{
										$item->cost_price = $unit_price_to_use*$item->quantity_unit_quantity;
									}
									
								}
								else //Didn't select quantity unit; reset to be empty
								{
									$item->quantity_unit_quantity = NULL;
									$item->$variable = NULL;
									
									$item->unit_price = $cost_price_to_use;
									$item->cost_price = $unit_price_to_use;
								}
							}
						}
						else
						{
							$expire_date = $$variable;
							$item->expire_date = date(get_date_format(),strtotime($expire_date));
						}
					}
					catch(Exception $e)
					{
						$this->_reload($data);
						return;
					}
					
					$this->load->helper('items');
					$item->cost_price_preview = calculate_average_cost_price_preview($item->item_id,$item->variation_id, $item->unit_price, $item->quantity,$item->discount);
				}
			}
		}
		else
		{
			$data['error']=lang('receivings_error_editing_item');
		}
		$this->cart->save();
		$this->_reload($data);
	}

	function delete_item($line)
	{
		$this->cart->delete_item($line);
		
		$this->cart->save();
		$this->_reload();
	}

	function delete_supplier()
	{		
		$this->cart->supplier_id = NULL;
		$this->cart->delete_all_paid_store_account_payment_ids();
		$this->cart->save();
		$this->_reload();
	}

	function complete()
	{		
		
		if ($this->cart->transfer_from_location_id && !$this->Employee->has_module_action_permission('receivings', 'complete_transfer', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			$this->_reload(array('error' => lang('receivings_you_do_not_have_permission_to_complete_transfers')), false);
			return;				
		}
		
		if ($this->cart->get_mode() == 'transfer' && $this->config->item('do_not_allow_items_to_go_out_of_stock_when_transfering') && $this->cart->transfer_from_location_id && $this->cart->will_be_out_of_stock())
		{
			$this->_reload(array('error' => lang('receivings_out_of_stock')), false);
			return;			
		}
		
		if ($this->config->item('do_not_allow_item_with_variations_to_be_sold_without_selecting_variation') && !$this->cart->do_all_variation_items_have_variation_selected())
		{
			$this->_reload(array('error' => lang('common_you_did_not_select_variations_for_applicable_variation_items')), false);
			return;
		}
		
		if ($this->config->item('sort_receipt_column'))
		{
			$this->cart->sort_items($this->config->item('sort_receipt_column'));
		}
		
		if ($this->cart->get_mode() == 'receive')
		{
			$current_location = $this->Employee->get_logged_in_employee_current_location_id();
			for ($k = 1; $k <= NUMBER_OF_PEOPLE_CUSTOM_FIELDS; $k++) { 
				$custom_field = $this->Receiving->get_custom_field($k);
				if ($custom_field !== FALSE) {
					if($this->Receiving->get_custom_field($k,'required') && in_array($current_location,$this->Receiving->get_custom_field($k,'locations')) && !$this->cart->{"custom_field_${k}_value"}){
						$this->_reload(array('error' => $custom_field.' '.lang('is_required')), false);
						return;
					}
				}
			}
		}
		
		$data = $this->_get_shared_data();

		$exchange_rate = $this->cart->get_exchange_rate() ? $this->cart->get_exchange_rate() : 1;

		
		if (empty($data['cart_items']))
		{
			redirect('receivings');
		}
			
		$data['see_cost_price'] = $this->Employee->has_module_action_permission('items', 'see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id);
		
		$store_account_in_all_languages = get_all_language_values_for_key('common_store_account','common');
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		$emp_info=$this->Employee->get_info($employee_id);
		$location_id=$this->cart->transfer_location_id;
		
		$data['balance'] = 0;
		//Add up balances for all languages
		foreach($store_account_in_all_languages as $store_account_lang)
		{
				$data['balance']+= $this->cart->get_payment_amount($store_account_lang);
		}

		if ($this->input->post('amount_tendered'))
		{
			$data['amount_tendered'] = $this->input->post('amount_tendered');
			$decimals = $this->config->item('number_of_decimals') !== NULL && $this->config->item('number_of_decimals') != '' ? (int)$this->config->item('number_of_decimals') : 2;
			
			$data['amount_change'] = to_currency($data['amount_tendered'] - round($data['total'], $decimals));
		}
		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name;		
		if ($this->config->item('charge_tax_on_recv'))
		{
			//If we don't have any taxes, run a check for items so we don't show the price including tax on receipt
			if (empty($data['taxes']))
			{
				foreach(array_keys($data['cart_items']) as $key)
				{
					if (isset($data['cart_items'][$key]->item_id))
					{
						$item_info = $this->Item->get_info($data['cart_items'][$key]->item_id);
						if($item_info->tax_included)
						{
							$this->load->helper('items');
							$price_to_use = get_price_for_item_excluding_taxes($data['cart_items'][$key]->item_id, $data['cart_items'][$key]->cost_price);
							$data['cart_items'][$key]->cost_price = $price_to_use;
						}					
					}
				}
			}
		}

			
		//SAVE receiving to database		
		$this->cart->suspended = 0;
		$receiving_id_raw = $this->Receiving->save($this->cart);
		$data['receiving_id'] = 'RECV '.$receiving_id_raw;
		$data['receiving_id_raw']=$receiving_id_raw;
		
		//Set exchange details in so receipt has correct info on it (Receiving->save clears it out but we need for receipt)
		if ($data['exchange_name'])
		{
			$this->cart->set_exchange_details($this->Receiving->get_exchange_details($receiving_id_raw));
			for($k=0;$k<count($data['payments']);$k++)
			{
				$data['payments'][$k]->payment_amount = $data['payments'][$k]->payment_amount*$exchange_rate;
			}
			
		}


		$supplier_info=$this->Supplier->get_info($this->cart->supplier_id);
		
		if($this->config->item('suppliers_store_accounts'))
		{
			$data['supplier_balance_for_sale'] = $supplier_info->balance;
		}		
		
		if ($data['receiving_id'] == 'RECV -1')
		{
			$data['error_message'] = '';
			$data['error_message'] .= '<span class="text-danger">'.lang('receivings_transaction_failed').'</span>';
			$data['error_message'] .= '<br /><br />'.anchor('receivings','&laquo; '.lang('receivings_register'));			
			$data['error_message'] .= '<br /><br />'.anchor('receivings/complete',lang('common_try_again'). ' &raquo;');
		}
		else
		{
			
			$this->session->unset_userdata('scroll_to');
			
			if ($this->cart->email_receipt && !empty($supplier_info->email))
			{
				//pdf generate and attached for eamil
				if($this->config->item('enable_pdf_receipts')){
					$receipt_data = $this->load->view("receivings/receipt_html", $data, true);
					
					if($this->config->item('receipt_download_filename_prefix')){
						$filename = $this->config->item('receipt_download_filename_prefix').'_receipt_'.$data['receiving_id'].'.pdf';
					}else{
						$filename = 'receipt_'.$data['receiving_id'].'.pdf';
					}
			    	$this->load->library("m_pdf");
					$pdf_content = $this->m_pdf->generate_pdf($receipt_data);
				}
					
				
				$this->load->library('email');
				$config['mailtype'] = 'html';				
				$this->email->initialize($config);
				$this->email->from($this->Location->get_info_for_key('email') ? $this->Location->get_info_for_key('email') : 'no-reply@mg.phppointofsale.com', $this->config->item('company'));
				$this->email->to($supplier_info->email); 
				
				if($this->Location->get_info_for_key('cc_email'))
				{
					$this->email->cc($this->Location->get_info_for_key('cc_email'));
				}
				
				if($this->Location->get_info_for_key('bcc_email'))
				{
					$this->email->bcc($this->Location->get_info_for_key('bcc_email'));
				}
				
				$this->email->subject(lang('receivings_receipt'));
				
				if($this->config->item('enable_pdf_receipts')){
					if(isset($pdf_content) && $pdf_content){
						$this->email->attach($pdf_content, 'attachment', $filename, 'application/pdf');
						$this->email->message(nl2br($this->config->item('pdf_receipt_message')));
					}
				}else{
					$this->email->message($this->load->view("receivings/receipt_email",$data, true));	
				}
				$this->email->send();
		
			}
		}
		
		if ($this->Location->get_info_for_key('email_receivings_email'))
		{
			
			//pdf generate and attached for eamil
			if($this->config->item('enable_pdf_receipts')){
				$receipt_data = $this->load->view("receivings/receipt_html", $data, true);
				
				if($this->config->item('receipt_download_filename_prefix')){
					$filename = $this->config->item('receipt_download_filename_prefix').'_receipt_'.$data['receiving_id'].'.pdf';
				}else{
					$filename = 'receipt_'.$data['receiving_id'].'.pdf';
				}
			    $this->load->library("m_pdf");
				$pdf_content = $this->m_pdf->generate_pdf($receipt_data);
			}
			
			$this->load->library('email');
			$config['mailtype'] = 'html';				
			$this->email->initialize($config);
			$this->email->from($this->Location->get_info_for_key('email') ? $this->Location->get_info_for_key('email') : 'no-reply@mg.phppointofsale.com', $this->config->item('company'));
			$this->email->to($this->Location->get_info_for_key('email_receivings_email')); 
			
			if($this->Location->get_info_for_key('cc_email'))
			{
				$this->email->cc($this->Location->get_info_for_key('cc_email'));
			}
			
			if($this->Location->get_info_for_key('bcc_email'))
			{
				$this->email->bcc($this->Location->get_info_for_key('bcc_email'));
			}
			
			$this->email->subject(lang('receivings_receipt'));
			if($this->config->item('enable_pdf_receipts')){
				if(isset($pdf_content) && $pdf_content){
					$this->email->attach($pdf_content, 'attachment', $filename, 'application/pdf');
					$this->email->message(nl2br($this->config->item('pdf_receipt_message')));
				}
			}else{
				$this->email->message($this->load->view("receivings/receipt_email",$data, true));	
			}
			$this->email->send();
		}
		
		$current_location_id = $this->cart->transfer_from_location_id;
		$current_location = $this->Location->get_info($current_location_id);
		$data['transfer_from_location'] = $current_location->name;
		
		if ($location_id > 0)
		{
			$transfer_to_location = $this->Location->get_info($location_id);
			$data['transfer_to_location'] = $transfer_to_location->name;
		}

		$this->load->view("receivings/receipt",$data);
		if ($data['receiving_id'] != 'RECV -1')
		{
			$this->cart->destroy();
		}
		
		$this->cart->save();
	}
	
	function email_receipt($receiving_id)
	{
		
		$cart_recv = PHPPOSCartRecv::get_instance_from_recv_id($receiving_id);
		$data = $this->_get_shared_data();
		$data = array_merge($data,$cart_recv->to_array());
		
		$receiving_info = $this->Receiving->get_info($receiving_id)->row_array();	
		$data['see_cost_price'] = $this->Employee->has_module_action_permission('items', 'see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id);
			
		$data['transaction_time']= date(get_date_format().' '.get_time_format(), strtotime($receiving_info['receiving_time']));
		$emp_info=$this->Employee->get_info($receiving_info['employee_id']);
		$data['override_location_id'] = $receiving_info['location_id'];
		$data['suspended'] = $receiving_info['suspended'];
		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name;
		$supplier_id = $cart_recv->supplier_id;
		if($supplier_id)
		{
			$supplier_info=$this->Supplier->get_info($supplier_id);	
			if($this->config->item('suppliers_store_accounts'))
			{
				$data['supplier_balance_for_sale'] = $supplier_info->balance;
			}		
		}
		$data['receiving_id']='RECV '.$receiving_id;
		$data['receiving_id_raw']=$receiving_id;
		
		$current_location = $this->Location->get_info($receiving_info['location_id']);
		$data['transfer_from_location'] = $current_location->name;
		
		if ($receiving_info['transfer_to_location_id'] > 0)
		{
			$transfer_to_location = $this->Location->get_info($receiving_info['transfer_to_location_id']);
			$data['transfer_to_location'] = $transfer_to_location->name;
		}
		
		
		if (!empty($supplier_info->email))
		{
			//pdf generate and attached for eamil
			if($this->config->item('enable_pdf_receipts')){
				$receipt_data = $this->load->view("receivings/receipt_html", $data, true);
				
			if($this->config->item('receipt_download_filename_prefix')){
				$filename = $this->config->item('receipt_download_filename_prefix').'_receipt_'.$data['receiving_id'].'.pdf';
			}else{
				$filename = 'receipt_'.$data['receiving_id'].'.pdf';
			}

			$this->load->library("m_pdf");
				$pdf_content = $this->m_pdf->generate_pdf($receipt_data);
			}
			
			$this->load->library('email');
			$config['mailtype'] = 'html';				
			$this->email->initialize($config);
			$this->email->from($this->Location->get_info_for_key('email') ? $this->Location->get_info_for_key('email') : 'no-reply@mg.phppointofsale.com', $this->config->item('company'));
			$this->email->to($supplier_info->email); 
			
			if($this->Location->get_info_for_key('cc_email'))
			{
				$this->email->cc($this->Location->get_info_for_key('cc_email'));
			}
			
			if($this->Location->get_info_for_key('bcc_email'))
			{
				$this->email->bcc($this->Location->get_info_for_key('bcc_email'));
			}
			
			$this->email->subject($receiving_info['is_po'] ? lang('receivings_purchase_order') : lang('receivings_receipt'));
			
			if($this->config->item('enable_pdf_receipts')){
				if(isset($pdf_content) && $pdf_content){
					$this->email->attach($pdf_content, 'attachment', $filename, 'application/pdf');
					$this->email->message(nl2br($this->config->item('pdf_receipt_message')));
				}
			}else{
				$this->email->message($this->load->view("receivings/receipt_email",$data, true));	
			}		
			
			$this->email->send();
		}
	}
	
	function download_receipt($receiving_id)
	{
		
		$cart_recv = PHPPOSCartRecv::get_instance_from_recv_id($receiving_id);
		$data = $this->_get_shared_data();
		$data = array_merge($data,$cart_recv->to_array());
		
		$receiving_info = $this->Receiving->get_info($receiving_id)->row_array();		
		$data['transaction_time']= date(get_date_format().' '.get_time_format(), strtotime($receiving_info['receiving_time']));
		$emp_info=$this->Employee->get_info($receiving_info['employee_id']);
		$data['override_location_id'] = $receiving_info['location_id'];
		$data['see_cost_price'] = $this->Employee->has_module_action_permission('items', 'see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id);
		
		$data['suspended'] = $receiving_info['suspended'];
		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name;
		$supplier_id = $cart_recv->supplier_id;
		if($supplier_id)
		{
			$supplier_info=$this->Supplier->get_info($supplier_id);	
			if($this->config->item('suppliers_store_accounts'))
			{
				$data['supplier_balance_for_sale'] = $supplier_info->balance;
			}		
		}
		$data['receiving_id']='RECV '.$receiving_id;
		$data['receiving_id_raw']=$receiving_id;
		
		$current_location = $this->Location->get_info($receiving_info['location_id']);
		$data['transfer_from_location'] = $current_location->name;
		
		if ($receiving_info['transfer_to_location_id'] > 0)
		{
			$transfer_to_location = $this->Location->get_info($receiving_info['transfer_to_location_id']);
			$data['transfer_to_location'] = $transfer_to_location->name;
		}
		
		//pdf generate and attached for eamil
		$receipt_data = $this->load->view("receivings/receipt_html", $data, true);
		
		if($this->config->item('receipt_download_filename_prefix')){
			$filename = $this->config->item('receipt_download_filename_prefix').'_receipt_'.$data['receiving_id'].'.pdf';
		}else{
			$filename = 'receipt_'.$data['receiving_id'].'.pdf';
		}
		$this->load->library("m_pdf");
		$pdf_content = $this->m_pdf->generate_pdf($receipt_data,TRUE, $filename);
	}
	
	function suspend($suspend_state = 1)
	{
		if ($this->cart->transfer_from_location_id && !$this->Employee->has_module_action_permission('receivings', 'send_transfer', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			$this->_reload(array('error' => lang('receivings_you_do_not_have_permission_to_complete_transfers')));
			return;				
		}
		
		if ($this->config->item('do_not_allow_item_with_variations_to_be_sold_without_selecting_variation') && !$this->cart->do_all_variation_items_have_variation_selected())
		{
			$this->_reload(array('error' => lang('common_you_did_not_select_variations_for_applicable_variation_items')));
			return;
		}
		
		if ($this->config->item('sort_receipt_column'))
		{
			$this->cart->sort_items($this->config->item('sort_receipt_column'));
		}
		
		
		$data = $this->_get_shared_data();
		
		$data['transaction_time']= date(get_date_format().' '.get_time_format());
		$data['see_cost_price'] = $this->Employee->has_module_action_permission('items', 'see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id);
		
		$is_po = $this->cart->is_po;
		$supplier_id=$this->cart->supplier_id;
		$location_id=$this->cart->transfer_location_id;
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		$emp_info=$this->Employee->get_info($employee_id);
		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name;
		$store_account_in_all_languages = get_all_language_values_for_key('common_store_account','common');
		
		$data['balance'] = 0;
		//Add up balances for all languages
		foreach($store_account_in_all_languages as $store_account_lang)
		{
				$data['balance']+= $this->cart->get_payment_amount($store_account_lang);
		}

		//SAVE receiving to database
		$this->cart->suspended = $suspend_state;
		$receiving_id_raw =$this->Receiving->save($this->cart);
		$data['receiving_id']='RECV '.$receiving_id_raw;
		$data['receiving_id_raw']=$receiving_id_raw;
		
		if ($data['receiving_id'] == 'RECV -1')
		{
			$this->_reload(array('error' => lang('receivings_transaction_failed')));
			return;
		}
		
		if ($this->config->item('show_receipt_after_suspending_sale') || $is_po)
		{
			$supplier_id = $this->cart->supplier_id;
			$supplier_info=$this->Supplier->get_info($supplier_id);	
			
			//Email receipt if is PO
			if ($is_po && $this->cart->email_receipt && !empty($supplier_info->email))
			{
				
				//pdf generate and attached for eamil
				if($this->config->item('enable_pdf_receipts')){
					$receipt_data = $this->load->view("receivings/receipt_html", $data, true);
					
					if($this->config->item('receipt_download_filename_prefix')){
						$filename = $this->config->item('receipt_download_filename_prefix').'_receipt_'.$data['receiving_id'].'.pdf';
					}else{
						$filename = 'receipt_'.$data['receiving_id'].'.pdf';
					}
					$this->load->library("m_pdf");
					$pdf_content = $this->m_pdf->generate_pdf($receipt_data);
				}
				
				
				$this->load->library('email');
				$config['mailtype'] = 'html';				
				$this->email->initialize($config);
				$this->email->from($this->Location->get_info_for_key('email') ? $this->Location->get_info_for_key('email') : 'no-reply@mg.phppointofsale.com', $this->config->item('company'));
				$this->email->to($supplier_info->email); 
				
				if($this->Location->get_info_for_key('cc_email'))
				{
					$this->email->cc($this->Location->get_info_for_key('cc_email'));
				}
				
				if($this->Location->get_info_for_key('bcc_email'))
				{
					$this->email->bcc($this->Location->get_info_for_key('bcc_email'));
				}
				
				$this->email->subject(lang('receivings_purchase_order'));
				
				if($this->config->item('enable_pdf_receipts')){
					if(isset($pdf_content) && $pdf_content){
						$this->email->attach($pdf_content, 'attachment', $filename, 'application/pdf');
						$this->email->message(nl2br($this->config->item('pdf_receipt_message')));
					}
				}else{
					$this->email->message($this->load->view("receivings/receipt_email",$data, true));	
				}
				$this->email->send();
			}
			$this->cart->destroy();
			$this->cart->save();
			redirect('receivings/receipt/'.$receiving_id_raw);
		}
		else
		{
			$this->cart->destroy();
			$this->_reload(array('success' => lang('receivings_successfully_suspended_receiving')));
		}
		
		$this->cart->save();
	}
	
	function suspended($suspended_status = 1,$location_column = 'receivings.location_id')
	{
		
		if ($location_column == 'receivings.location_id' || $location_column == 'receivings.transfer_to_location_id')
		{			
			
			if ($location_column == 'receivings.transfer_to_location_id')
			{
				$suspended_status = array(1,2);
			}
			
			$data = array();
			$data['controller_name'] = strtolower(get_class());
			$table_data = $this->Receiving->get_all_suspended($suspended_status,$location_column);
			$data['manage_table'] = get_suspended_receivings_manage_table($table_data, $this);
		
			$data['default_columns'] = $this->Receiving->get_suspended_receivings_default_columns();
			$data['selected_columns'] = $this->Employee->get_suspended_receivings_columns_to_display();
		
			$data['all_columns'] = array_merge($data['selected_columns'], $this->Receiving->get_suspended_receivings_displayable_columns());
			$data['suspended_receivings'] = $this->Receiving->get_all_suspended($suspended_status,$location_column);
			$data['suspended_status'] = $suspended_status;
			$data['transfer_type'] = $location_column;
			$this->load->view('receivings/suspended', $data);
		}
	}
	
	function save_column_prefs()
	{
		$this->load->model('Employee_appconfig');
		
		if ($this->input->post('columns'))
		{
			$this->Employee_appconfig->save('suspended_receivings_column_prefs',serialize($this->input->post('columns')));
		}
		else
		{
			$this->Employee_appconfig->delete('suspended_receivings_column_prefs');			
		}
	}
	
	function reload_table(){
		$data['controller_name'] = strtolower(get_class());
		$table_data = $this->Receiving->get_all_suspended();
		echo get_suspended_receivings_manage_table($table_data, $this);
	}
	
	function do_excel_import()
	{
		$this->load->helper('demo');

		$file_info = pathinfo($_FILES['file_path']['name']);
		if($file_info['extension'] != 'xlsx' && $file_info['extension'] != 'csv')
		{
			echo json_encode(array('success'=>false,'message'=>lang('common_upload_file_not_supported_format')));
			return;
		}
		
		set_time_limit(0);
		ini_set('max_input_time','-1');
		$this->db->trans_start();
		
		$msg = 'do_excel_import';
		$failCodes = array();
		
		if ($_FILES['file_path']['error']!=UPLOAD_ERR_OK)
		{
			$msg = lang('common_excel_import_failed');
			echo json_encode( array('success'=>false,'message'=>$msg) );
			$this->db->trans_complete();
			return;
		}
		else
		{
			if (($handle = fopen($_FILES['file_path']['tmp_name'], "r")) !== FALSE)
			{
				$this->load->helper('spreadsheet');
				$file_info = pathinfo($_FILES['file_path']['name']);
				
				$sheet = file_to_spreadsheet($_FILES['file_path']['tmp_name'],$file_info['extension']);
				$num_rows = $sheet->getNumberOfRows();
				
				//Loop through rows, skip header row
				for($k = 2;$k<=$num_rows; $k++)
				{
					
					$item_id = $sheet->getCellByColumnAndRow(0, $k);
					if (!$item_id)
					{
						continue;
					}
					
					
					$price = $sheet->getCellByColumnAndRow(1, $k);
					if (!$price)
					{
						$price = null;;
					}
				
					$quantity = $sheet->getCellByColumnAndRow(2, $k);
					if (!$quantity)
					{
						continue;
					}

					$discount = $sheet->getCellByColumnAndRow(3, $k);
					if (!$discount)
					{
						$discount = 0;
					}
					
					$selling_price = $sheet->getCellByColumnAndRow(4, $k);
					
					$serial_number = $sheet->getCellByColumnAndRow(5, $k);
					if (!$serial_number)
					{
						$serial_number = null;
					}
					
					
					if($this->cart->is_valid_item_kit($item_id))
					{
						$item_kit_to_add = new PHPPOSCartItemKitRecv(array('scan' => $item_id,'quantity' => $quantity,'cart' => $this->cart));
						
						if(!$this->cart->add_item_kit($item_kit_to_add))
						{
							$this->cart->empty_items();
							echo json_encode( array('success'=>false,'message'=>lang('batch_receivings_error')));
							return;
						}
					}
					else
					{
						$item_to_add = new PHPPOSCartItemRecv(array('scan' => $item_id,'quantity' => $quantity,'cart' => $this->cart,'serialnumber' => $serial_number));
						
						if ($price)
						{	
							$item_to_add->unit_price = $price;
						}
						
						if ($discount)
						{
							$item_to_add->discount = $discount;
						}
						
						if ($selling_price)
						{
							$item_to_add->selling_price = $selling_price;							
						}
						
						if($item_to_add->item_id && !$this->cart->add_item($item_to_add))
						{	
							$this->cart->empty_items();
							echo json_encode( array('success'=>false,'message'=>lang('batch_receivings_error')));
							return;
						}	
						
					}				
				}
			}
			else 
			{
				echo json_encode( array('success'=>false,'message'=>lang('common_upload_file_not_supported_format')));
				return;
			}
		}
		$this->db->trans_complete();
		
		if ($this->cart->get_mode() == 'transfer')
		{
			$this->cart->do_convert_cart_from_sale_to_return();
		}
		
		$this->cart->save();
		echo json_encode(array('success'=>true,'message'=>lang('receivings_import_successfull')));
	}
	
	function _excel_get_header_row()
	{
		return array(lang('common_item_id').'/'.lang('common_item_number').'/'.lang('common_product_id'),lang('cost_price'),lang('quantity'),lang('discount_percent'),lang('common_unit_price'),lang('common_serial_number'));
	}
	
	function batch_receiving()
	{
		$this->load->view('receivings/batch');
	}
	
	function excel()
	{	
		$this->load->helper('report');
		$header_row = $this->_excel_get_header_row();
		$this->load->helper('spreadsheet');
		array_to_spreadsheet(array($header_row),'batch_receiving_export.'.($this->config->item('spreadsheet_format') == 'XLSX' ? 'xlsx' : 'csv'));
	}
	
	function switch_location_and_unsuspend($location_id,$recv_id)
	{
		$this->Employee->set_employee_current_location_id($location_id);
		
		//Clear out logged in register when we switch locations
		$this->Employee->set_employee_current_register_id(null);
		
		$this->unsuspend($recv_id);
	}
	
	function unsuspend($recv_id = 0)
	{
		$receiving_id = $this->input->post('suspended_receiving_id') ? $this->input->post('suspended_receiving_id') : $recv_id;
		$this->cart->destroy();
		$this->cart = PHPPOSCartRecv::get_instance_from_recv_id($receiving_id,'receiving');
		$this->cart->is_editing_previous = TRUE;
		$this->cart->is_po = FALSE;
		$this->cart->save();
		$this->_reload(array(), false);
	}
	
	function delete_suspended_receiving()
	{
		$this->check_action_permission('delete_receiving');
		
		$suspended_recv_id = $this->input->post('suspended_receiving_id');
		if ($suspended_recv_id)
		{
			$this->cart->receiving_id = NULL;
			if(is_array($suspended_recv_id)){
				foreach($suspended_recv_id as $recv_id){
						$this->Receiving->delete($recv_id);
				}
			}else{
				$this->Receiving->delete($suspended_recv_id, false);
			}
		}
		$this->cart->save();
    	redirect('receivings/suspended');
	}
	
	function clone_receiving($receiving_id)
	{
		if ($this->config->item('disable_recv_cloning'))
		{
			$this->_reload(array(), false);
			return;
		}
		
		$this->cart->destroy();
		$this->cart = PHPPOSCartRecv::get_instance_from_recv_id($receiving_id,'receiving', TRUE);
		$this->cart->receiving_id = NULL;
		$this->cart->payments = array();
		$this->cart->suspended = 0;
		$this->cart->save();
		$this->_reload(array(), false);
	}
	
	
	function change_recv($receiving_id)
	{
		$this->check_action_permission('edit_receiving');
		$this->cart->destroy();
		$this->cart = PHPPOSCartRecv::get_instance_from_recv_id($receiving_id,'receiving');
		$this->cart->is_editing_previous = TRUE;
		$this->cart->save();
		$this->_reload(array(), false);
	}

	function receipt($receiving_id)
	{
		$receipt_cart = PHPPOSCartRecv::get_instance_from_recv_id($receiving_id);
		
		if ($receipt_cart->suspended && !$this->Employee->has_module_action_permission('receivings', 'view_suspended_receipt', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			redirect('no_access/'.$this->module_id);
		}
		
		
		if ($this->config->item('sort_receipt_column'))
		{
			$receipt_cart->sort_items($this->config->item('sort_receipt_column'));
		}
		
		$data = $this->_get_shared_data();
		$data = array_merge($data,$receipt_cart->to_array());
		
		$receiving_info = $this->Receiving->get_info($receiving_id)->row_array();
		$data['see_cost_price'] = $this->Employee->has_module_action_permission('items', 'see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id);
				
		$data['show_payment_times'] = TRUE;
		$data['transaction_time']= date(get_date_format().' '.get_time_format(), strtotime($receiving_info['receiving_time']));
		$emp_info=$this->Employee->get_info($receiving_info['employee_id']);
		$data['override_location_id'] = $receiving_info['location_id'];
		$data['suspended'] = $receiving_info['suspended'];
		$data['deleted'] = $receiving_info['deleted'];
		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name;
		$data['receiving_id']='RECV '.$receiving_id;
		$data['receiving_id_raw']=$receiving_id;
		
		$data['signature_file_id'] = $receiving_info['signature_image_id'];

		$current_location = $this->Location->get_info($receiving_info['location_id']);
		$data['transfer_from_location'] = $current_location->name;
		
		if ($receiving_info['transfer_to_location_id'] > 0)
		{
			$transfer_to_location = $this->Location->get_info($receiving_info['transfer_to_location_id']);
			$data['transfer_to_location'] = $transfer_to_location->name;
		}
		
		$supplier_info=$this->Supplier->get_info($receipt_cart->supplier_id);
		if($this->config->item('suppliers_store_accounts'))
		{
			$data['supplier_balance_for_sale'] = $supplier_info->balance;
		}
		
		$this->load->view("receivings/receipt",$data);
		$receipt_cart->destroy();
		
	}
	
	function edit($receiving_id)
	{
		$data = array();

		$data['suppliers'] = array('' => lang('receivings_no_supplier'));
		foreach ($this->Supplier->get_all()->result() as $supplier)
		{
			$data['suppliers'][$supplier->person_id] = $supplier->company_name.' ('.$supplier->first_name . ' '. $supplier->last_name.')';
		}

		$data['employees'] = array();
		foreach ($this->Employee->get_all()->result() as $employee)
		{
			$data['employees'][$employee->person_id] = $employee->first_name . ' '. $employee->last_name;
		}
		
		$data['receiving_info'] = $this->Receiving->get_info($receiving_id)->row_array();
		
		if($data['receiving_info']['supplier_id'])
		{
			$supplier = $this->Supplier->get_info($data['receiving_info']['supplier_id']);
			$data['selected_supplier_name'] = $supplier->company_name.' ('.$supplier->first_name . ' '. $supplier->last_name.')';
			$data['selected_supplier_email'] = $supplier->email;
		}
		
		$data['store_account_payment'] = $data['receiving_info']['store_account_payment'];		
		$data['store_account_charge'] = $this->Receiving->get_store_account_payment_total($receiving_id) != 0 ? true : false;
		
		$this->load->view('receivings/edit', $data);
	}
	
	function delete($receiving_id)
	{
		$this->check_action_permission('delete_receiving');
		
		$receiving_info = $this->Receiving->get_info($receiving_id)->row_array();
		
		$data = array();
		
		if (!$this->Receiving->is_receiving_deleted($receiving_id) && $this->Receiving->delete($receiving_id, false))
		{
			$data['success'] = true;
		}
		else
		{
			$data['success'] = false;
		}
		
		$this->load->view('receivings/delete', $data);
		
	}
	
	function undelete($receiving_id)
	{
		$data = array();
		
		if (!$this->Receiving->is_receiving_undeleted($receiving_id) && $this->Receiving->undelete($receiving_id))
		{
			$data['success'] = true;
		}
		else
		{
			$data['success'] = false;
		}
		
		$this->load->view('receivings/undelete', $data);
		
	}
	
	function save($receiving_id)
	{
		$receiving_data = array(
			'receiving_time' => date('Y-m-d H:i:s', strtotime($this->input->post('date'))),
			'supplier_id' => $this->input->post('supplier_id') ? $this->input->post('supplier_id') : null,
			'employee_id' => $this->input->post('employee_id'),
			'comment' => $this->input->post('comment')
		);
		
		if ($this->Receiving->update($receiving_data, $receiving_id))
		{
			echo json_encode(array('success'=>true,'message'=>lang('receivings_successfully_updated')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('receivings_unsuccessfully_updated')));
		}
	}
	
	//Alain Multiple Payments
	function delete_payment($payment_id)
	{
		$this->cart->delete_payment($payment_id);
		$this->cart->save();
		$this->_reload();
	}
	
	function paginate($offset = 0)
	{
		$this->cart->offset = $offset;
		$this->cart->save();
		$this->_reload(array());
	}

	function _reload($data=array(), $is_ajax = true)
	{		
		//This is used for upgrade installs that never had this set (sales in progress)
		if ($this->cart->limit === NULL)
		{
			$this->cart->limit = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;	
			$this->cart->save();			
		}
		
		if ($this->cart->offset === NULL)
		{
			$this->cart->offset = 0;	
			$this->cart->save();			
		}
		
		$the_cart_items = $this->cart->get_items();
		
		if ($this->cart->offset >= count($the_cart_items))
		{
			$this->cart->offset = 0;	
			$this->cart->save();			
		}
		
		$data = array_merge($this->_get_shared_data(),$data);
		
		$config['base_url'] = site_url('receivings/paginate');
		$config['per_page'] = $this->cart->limit; 
		$config['uri_segment'] = -1; //Set this to non possible url so it doesn't use URL
		//Undocumented feature to get page
		
		$config['cur_page'] = $this->cart->offset; 
		
		$config['total_rows'] = count($the_cart_items);
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		
		$person_info = $this->Employee->get_logged_in_employee_info();
				
		if ($this->Location->count_all() > 1)
		{
			$data['modes']['transfer']= lang('receivings_transfer');
		}
		
		$can_receive_store_account_payment = $this->Employee->has_module_action_permission('receivings', 'receive_store_account_payment', $this->Employee->get_logged_in_employee_info()->person_id);		
		
		if($this->config->item('suppliers_store_accounts') && $can_receive_store_account_payment) 
		{
			$data['modes']['store_account_payment'] = lang('common_store_account_payment');
		}
		
		$data['items_module_allowed'] = $this->Employee->has_module_permission('items', $person_info->person_id);
		$data['current_location'] = $this->Employee->get_logged_in_employee_current_location_id();
		if (!$this->session->userdata('foreign_language_to_cur_language_recv'))
		{
			$this->load->helper('directory');
			$language_folder = directory_map(APPPATH.'language',1);

			$languages = array();

			foreach($language_folder as $language_folder)
			{
				$languages[] = substr($language_folder,0,strlen($language_folder)-1);
			}

			$cur_lang = array();
			foreach($this->Receiving->get_payment_options_with_language_keys() as $cur_lang_value => $lang_key)
			{
				$cur_lang[$lang_key] = $cur_lang_value;
			}


			foreach($languages as $language)
			{
				$this->lang->load('common', $language);

				foreach($this->Receiving->get_payment_options_with_language_keys() as $cur_lang_value => $lang_key)
				{
					if (strpos($lang_key,'common') !== FALSE)
					{
						$foreign_language_to_cur_language[lang($lang_key)] = $cur_lang[$lang_key];
					}
					else
					{
						$foreign_language_to_cur_language[$cur_lang_value] = $cur_lang_value;
					}
				}
			}
			
			$this->session->set_userdata('foreign_language_to_cur_language_recv', $foreign_language_to_cur_language);
			//Switch back
			$this->lang->switch_to($this->config->item('language'));
		}
		else
		{
			$foreign_language_to_cur_language = $this->session->userdata('foreign_language_to_cur_language_recv');
		}

		$default_payment_type_translated = false;
		if (isset($foreign_language_to_cur_language[$this->config->item('default_payment_type_recv')]))
		{
			$default_payment_type_translated = $foreign_language_to_cur_language[$this->config->item('default_payment_type_recv')];
		}
		else
		{
			$default_payment_type_translated = $this->config->item('default_payment_type_recv');
		}
		
		$data['default_payment_type'] = $default_payment_type_translated ? $default_payment_type_translated : lang('common_cash');
		
		$supplier_id=$this->cart->supplier_id;
		
		$data['supplier_internal_notes'] = '';

		if ($supplier_id)
		{
			$supplier_info=$this->Supplier->get_info($supplier_id, TRUE);
			$data['supplier_internal_notes'] = $supplier_info->internal_notes;
		}

		$data['supplier_required_check'] = !$this->config->item('require_supplier_for_recv') || ($this->config->item('require_supplier_for_recv') && isset($this->cart->supplier_id) && $this->cart->supplier_id);

		if ($data['mode'] == 'store_account_payment' && $this->cart->supplier_id)
		{
			$receiving_ids = $this->Receiving->get_unpaid_store_account_recv_ids($this->cart->supplier_id);
			
			$paid_receivings = $this->cart->get_paid_store_account_ids();			
			$data['unpaid_store_account_receivings'] = 	$this->Receiving->get_unpaid_store_account_recvs($receiving_ids);
			
			for($k=0;$k<count($data['unpaid_store_account_receivings']);$k++)
			{
				if (isset($paid_receivings[$data['unpaid_store_account_receivings'][$k]['receiving_id']]))
				{
					$data['unpaid_store_account_receivings'][$k]['paid'] = TRUE;
				}
			}
		}
		
		$data['fullscreen'] = $this->session->userdata('fullscreen');
		
		$data['see_cost_price'] = $this->Employee->has_module_action_permission('items', 'see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id);
		if ($is_ajax)
		{
			$this->load->view("receivings/receiving",$data);
		}
		else
		{
			if ($this->config->item('quick_variation_grid'))
			{
				$this->load->view("receivings/receiving_initial_quick",$data);			
			}
			else
			{
				$this->load->view("receivings/receiving_initial",$data);
			}
		}
	}

	
	function pay_store_account_receiving($receiving_id, $amount)
	{
		$this->cart->add_paid_store_account_payment_id($receiving_id,$amount);
		$cart = $this->cart->get_items();
		foreach($cart as $item)
		{
			if ($item->name == lang('common_store_account_payment'))
			{
				$item->unit_price += $amount; 
				break;
			}
		}
		$comment = lang('receivings_pays_receivings'). ' - '.implode(', ',array_keys($this->cart->get_paid_store_account_ids()));
			
		$this->cart->comment = $comment;
		$this->cart->save();
		$this->_reload();
	}
	
	function toggle_pay_all_store_account()
	{
		$recv_ids = $this->Receiving->get_unpaid_store_account_recv_ids($this->cart->supplier_id);
		
		$unpaid_recvs = $this->Receiving->get_unpaid_store_account_recvs($recv_ids);
		
		if(count($this->cart->get_paid_store_account_ids()) ==  0)
		{
			$amount_to_pay = 0;
		
			foreach($unpaid_recvs as $unpaid_recv)
			{
					$this->cart->add_paid_store_account_payment_id($unpaid_recv['receiving_id'],$unpaid_recv['payment_amount']);
					$amount_to_pay +=$unpaid_recv['payment_amount'];
			}
		
			$cart = $this->cart->get_items();
			foreach($cart as $item)
			{
				if ($item->name == lang('common_store_account_payment'))
				{
					$item->unit_price = $amount_to_pay; 
					break;
				}
			}
			
			$comment = lang('receivings_pays_receivings'). ' - '.implode(', ',array_keys($this->cart->get_paid_store_account_ids()));
		}
		else
		{
			$comment  = '';
			$this->cart->delete_all_paid_store_account_payment_ids();
			
			$cart = $this->cart->get_items();
			foreach($cart as $item)
			{
				if ($item->name == lang('common_store_account_payment'))
				{
					$item->unit_price = 0;
					break;
				}
			}
		}
		
		$this->cart->comment = $comment;
		$this->cart->save();
		$this->_reload();
	}
	
	function delete_store_account_receiving($receiving_id, $amount)
	{
		if (isset($this->cart->paid_store_account_amounts[$receiving_id]))
		{
			$amount = $this->cart->paid_store_account_amounts[$receiving_id];
		}
		
		$this->cart->delete_paid_store_account_id($receiving_id);
		$cart = $this->cart->get_items();
		foreach($cart as $item)
		{
			if ($item->name == lang('common_store_account_payment'))
			{
				$item->unit_price -= $amount; 
				break;
			}
		}
		$comment = lang('receivings_pays_receivings'). ' - '.implode(', ',array_keys($this->cart->get_paid_store_account_ids()));
			
		$this->cart->comment = $comment;
		$this->cart->save();
    $this->_reload();
	}
	
  function cancel_receiving()
  {
  	$this->cart->destroy();
		$this->cart->save();
  	$this->_reload();
  }

	function categories($parent_id = NULL, $offset = 0)
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		
		//If a false value, make sure it is NULL
		if (!$parent_id)
		{
				$parent_id = NULL;
		}
		$categories = $this->Category->get_all($parent_id, FALSE, $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14, $offset);
		
		$categories_count = $this->Category->count_all($parent_id);		
		$config['base_url'] = site_url('receivings/categories/'.($parent_id ? $parent_id : 0));
		$config['uri_segment'] = 4;
		$config['total_rows'] = $categories_count;
		$config['per_page'] = $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14; 
		$this->load->library('pagination');$this->pagination->initialize($config);
		
		$categories_response = array();
		$this->load->model('Appfile');
		foreach($categories as $id=>$value)
		{
				$categories_response[] = array('id' => $id, 'name' => $value['name'], 'color' => $value['color'], 'image_id' => $value['image_id'], 'image_timestamp' => $this->Appfile->get_file_timestamp($value['image_id']));
		}
		
		$data = array();
		$data['categories'] = H($categories_response);
		$data['pagination'] = $this->pagination->create_links();
		
		echo json_encode($data);	
	}

	function suppliers($offset = 0)
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		
		$suppliers = $this->Supplier->get_all(0, $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14, $offset);
		
		$suppliers_count = $this->Supplier->count_all();		
		$config['base_url'] = site_url('receivings/suppliers');
		$config['uri_segment'] = 3;
		$config['total_rows'] = $suppliers_count;
		$config['per_page'] = $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14; 
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		
		$suppliers_response = array();
		$this->load->model('Appfile');
		foreach($suppliers->result_array() as $id=>$value)
		{
				$suppliers_response[] = array('id' => $value['pid'], 'name' => $value['company_name'], 'image_id' => $value['image_id'], 'image_timestamp' => $this->Appfile->get_file_timestamp($value['image_id']));
		}

		$data = array();
		$data['suppliers'] = H($suppliers_response);
		$data['pagination'] = $this->pagination->create_links();
		
		echo json_encode($data);	
	}
	
	function tags($offset = 0)
	{
		//allow parallel searchs to improve performance.
		session_write_close();
	
		$tags = $this->Tag->get_all($this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14, $offset,'name','asc',FALSE);
	
		$tags_count = $this->Tag->count_all(FALSE);		
		$config['base_url'] = site_url('receivings/tags');
		$config['uri_segment'] = 3;
		$config['total_rows'] = $tags_count;
		$config['per_page'] = $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14; 
		$this->load->library('pagination');$this->pagination->initialize($config);
	
		$tags_response = array();
	
		foreach($tags as $id=>$value)
		{
				$tags_response[] = array('id' => $id, 'name' => $value['name']);
		}
	

		$data = array();
		$data['tags'] = H($tags_response);
		$data['pagination'] = $this->pagination->create_links();
	
		echo json_encode($data);	
	}

	function tag_items($tag_id, $offset = 0)
	{
		$this->load->model('Item_variations');
		
		//allow parallel searchs to improve performance.
		session_write_close();
	
		$config['base_url'] = site_url('receivings/tag_items/'.($tag_id ? $tag_id : 0));
		$config['uri_segment'] = 4;
		$config['per_page'] = $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14; 
	
			
		//Items
		$items = array();
	
		$items_result = $this->Item->get_all_by_tag($tag_id,$this->config->item('hide_out_of_stock_grid') ? TRUE : FALSE, $offset, $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14)->result();
	
	
		foreach($items_result as $item)
		{
			$img_src = "";
			if ($item->image_id != 'no_image' && trim($item->image_id) != '') {
				$img_src = cacheable_app_file_url($item->image_id);
			}
			
			
			if (strpos($item->item_id, 'KIT') === 0)
			{
				$price_to_use = FALSE;
			}
			else
			{
				$cur_item_info = $this->Item->get_info($item->item_id);
				$cur_item_location_info = $this->Item_location->get_info($item->item_id);
	
				$price_to_use = ($cur_item_location_info && $cur_item_location_info->cost_price) ? $cur_item_location_info->cost_price : $cur_item_info->cost_price;
			}
			
			
			$has_cost_price_permission = $this->Employee->has_module_action_permission('items', 'see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id);
			$items[] = array(
				'id' => $item->item_id,
				'has_variations' => count($this->Item_variations->get_variations($item->item_id)) > 0 ? TRUE: FALSE,
				'name' => character_limiter($item->name, 58),				
				'image_src' => 	$img_src,
				'type' => 'item',		
				'price' => $has_cost_price_permission && $price_to_use !== FALSE ? to_currency($price_to_use) : FALSE,		

			);	
		}

		$items_count = $this->Item->count_all_by_tag($tag_id);		
	
		$data = array();
		$data['items'] = H($items);
		$config['total_rows'] = $items_count;
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
	
		echo json_encode($data);
	}
	
	function favorite_items($offset = 0)
	{
		$this->load->model('Item_variations');
		
		//allow parallel searchs to improve performance.
		session_write_close();
		
		$config['base_url'] = site_url('receivings/favorite_items/');
		$config['uri_segment'] = 3;
		$config['per_page'] = $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14; 
		
				
		//Items
		$items = array();
		
		$items_result = $this->Item->get_all_favorite_items($this->config->item('hide_out_of_stock_grid') ? TRUE : FALSE, $offset, $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid')+4 : 18)->result();
		
		
		foreach($items_result as $item)
		{
			$img_src = "";
			if ($item->image_id != 'no_image' && trim($item->image_id) != '') {
				$img_src = cacheable_app_file_url($item->image_id);
			}

			if (strpos($item->item_id, 'KIT') === 0)
			{
				$price_to_use = FALSE;
			}
			else
			{
				$cur_item_info = $this->Item->get_info($item->item_id);
				$cur_item_location_info = $this->Item_location->get_info($item->item_id);
	
				$price_to_use = ($cur_item_location_info && $cur_item_location_info->cost_price) ? $cur_item_location_info->cost_price : $cur_item_info->cost_price;
			}

			$has_cost_price_permission = $this->Employee->has_module_action_permission('items', 'see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id);

			$items[] = array(
				'id' => $item->item_id,
				'has_variations' => count($this->Item_variations->get_variations($item->item_id)) > 0 ? TRUE: FALSE,
				'name' => character_limiter($item->name, 58),				
				'image_src' => 	$img_src,
				'type' => 'item',		
				'price' => $has_cost_price_permission && $price_to_use !== FALSE ? to_currency($price_to_use) : FALSE,		

			);		
		}
	
		$items_count = $this->Item->count_all_favorite_items();
		
		$data = array();
		$data['items'] = H($items);
		$config['total_rows'] = $items_count;
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		
		echo json_encode($data);
	}

	function supplier_items($supplier_id, $offset = 0)
	{
		$this->load->model('Item_variations');
		
		//allow parallel searchs to improve performance.
		session_write_close();
		
		$config['base_url'] = site_url('receivings/supplier_items/'.($supplier_id ? $supplier_id : 0));
		$config['uri_segment'] = 4;
		$config['per_page'] = $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14; 
		
				
		//Items
		$items = array();
		
		$items_result = $this->Item->get_all_item_by_supplier($supplier_id, $this->config->item('hide_out_of_stock_grid') ? TRUE : FALSE, $offset, $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14)->result();
		
		
		foreach($items_result as $item)
		{
			$img_src = "";
			if ($item->image_id != 'no_image' && trim($item->image_id) != '') {
				$img_src = cacheable_app_file_url($item->image_id);
			}

			$cur_item_info = $this->Item->get_info($item->item_id);
			$cur_item_location_info = $this->Item_location->get_info($item->item_id);

			$price_to_use = ($cur_item_location_info && $cur_item_location_info->cost_price) ? $cur_item_location_info->cost_price : $cur_item_info->cost_price;
	
			$has_cost_price_permission = $this->Employee->has_module_action_permission('items', 'see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id);
	
			$items[] = array(
				'id' => $item->item_id,
				'name' => character_limiter($item->name, 58),				
				'image_src' => 	$img_src,
				'type' => 'item',		
				'has_variations' => count($this->Item_variations->get_variations($item->item_id)) > 0 ? TRUE : FALSE,
				'price' => $price_to_use != '0.00' ? to_currency($price_to_use) : FALSE,
				'regular_price' => $has_cost_price_permission ? to_currency($price_to_use) : '',	
				'different_price' => $price_to_use != $item->unit_price,	
			);	
		}
	
		$items_count = count($items_result) + 1; //$this->Item->count_all_by_tag($supplier_id);
		
		$data = array();
		$data['items'] = H($items);
		$config['total_rows'] = $items_count;
		$this->load->library('pagination');$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		
		echo json_encode($data);
	}

	function categories_and_items($category_id = NULL, $offset = 0)
	{
		$this->load->model('Item_variations');
		
		//allow parallel searchs to improve performance.
		session_write_close();
	
		//If a false value, make sure it is NULL
		if (!$category_id)
		{
			$category_id = NULL;
		}
	
		//Categories
		$categories = $this->Category->get_all($category_id);
		$categories_count = count($categories);		
		$config['base_url'] = site_url('receivings/categories_and_items/'.($category_id ? $category_id : 0));
		$config['uri_segment'] = 4;
		$config['per_page'] = $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14; 
	
		$categories_and_items_response = array();
	
		foreach($categories as $id=>$value)
		{
			$categories_and_items_response[] = array('id' => $id, 'name' => $value['name'],'color' => $value['color'], 'image_id' => $value['image_id'], 'image_timestamp' => $this->Appfile->get_file_timestamp($value['image_id']), 'type' => 'category');
		}
	
		//Items
		$items = array();
	
		$items_offset = ($offset - $categories_count > 0 ? $offset - $categories_count : 0);		
		$items_result = $this->Item->get_all_by_category($category_id, FALSE, $items_offset, $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14)->result();
	
		foreach($items_result as $item)
		{
			$img_src = "";
			if ($item->image_id != 'no_image' && trim($item->image_id) != '') {
				$img_src = cacheable_app_file_url($item->image_id);
			}
		
			$size = $item->size ? ' - '.$item->size : '';
			
			if (strpos($item->item_id, 'KIT') === 0)
			{
				$price_to_use = FALSE;
			}
			else
			{
				$cur_item_info = $this->Item->get_info($item->item_id);
				$cur_item_location_info = $this->Item_location->get_info($item->item_id);
	
				$price_to_use = ($cur_item_location_info && $cur_item_location_info->cost_price) ? $cur_item_location_info->cost_price : $cur_item_info->cost_price;
			}

			$has_cost_price_permission = $this->Employee->has_module_action_permission('items', 'see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id);

			$categories_and_items_response[] = array(
				'id' => $item->item_id,
				'name' => character_limiter($item->name, 58).$size,				
				'image_src' => 	$img_src,
				'type' => 'item',		
				'has_variations' => count($this->Item_variations->get_variations($item->item_id)) > 0 ? TRUE : FALSE,
				'price' => $has_cost_price_permission && $price_to_use !== FALSE ? to_currency($price_to_use) : FALSE,		
			);	
		}

		$items_count = $this->Item->count_all_by_category($category_id);		
		$categories_and_items_response = array_slice($categories_and_items_response, $offset > $categories_count ? $categories_count : $offset, $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14);
	
		$data = array();
		$data['categories_and_items'] = H($categories_and_items_response);
		$config['total_rows'] = $categories_count + $items_count;
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		
		echo json_encode($data);
	}
	
	function item_variations($item_id)
	{
		$variations = array();
		$this->load->model('Item_variations');

		$variation_result = $this->Item_variations->get_variations($item_id);
	
		foreach($variation_result as $variation_id => $variation)
		{
			
			$img_src = "";
			if ($variation['image']['image_id']) 
			{
				$img_src = cacheable_app_file_url($variation['image']['image_id']);
			}
					
			$cur_item_info = $this->Item->get_info($item_id);
			$cur_item_location_info = $this->Item_location->get_info($item_id);
			
			if ($variation['cost_price'])
			{
				$price_to_use = $variation['cost_price'];
			}
			else
			{
				$price_to_use = ($cur_item_location_info && $cur_item_location_info->cost_price) ? $cur_item_location_info->cost_price : $cur_item_info->cost_price;
			}
			
			$cur_item_variation_location_info = $this->Item_variation_location->get_info($variation_id);
			
			$has_cost_price_permission = $this->Employee->has_module_action_permission('items', 'see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id);
			
			$variations[] = array(
				'id' => $item_id.'#'.$variation_id,
				'name' => $variation['name'] ? $variation['name'] : implode(', ', array_column($variation['attributes'],'label')),				
				'image_src' => 	$img_src,
				'type' => 'variation',		
				'has_variations' => FALSE,
				'price' => $has_cost_price_permission && $price_to_use !== FALSE ? to_currency($price_to_use) : FALSE,		
			);	
		}

		
		echo json_encode(H($variations));
	}
	
	function po()
	{
		$data = array();
		$suppliers = array();
		foreach($this->Supplier->get_all()->result_array() as $row)
		{
			$suppliers[$row['person_id']] = $row['company_name'] .' ('.$row['first_name'] .' '. $row['last_name'].')';
		}
		
		$this->load->model('Category');
		$categories = array();
		$categories[''] =lang('common_all');
		
		$categories_phppos= $this->Category->sort_categories_and_sub_categories($this->Category->get_all_categories_and_sub_categories());
		
		foreach($categories_phppos as $key=>$value)
		{
			$name = $this->config->item('show_full_category_path') ? str_repeat('&nbsp;&nbsp;', $value['depth']).$this->Category->get_full_path($key) : str_repeat('&nbsp;&nbsp;', $value['depth']).$value['name'];
			$categories[$key] = $name;
		}
		
		$data['categories'] = $categories;
		$data['suppliers'] = $suppliers;
		$data['selected_supplier'] = $this->cart->supplier_id;
		
		$data['criterias'] = array(
			'below_reorder_level' => lang('common_below_reorder_level'),
			'below_reorder_level_and_out_of_stock' => lang('common_below_reorder_level_and_out_of_stock'),
			'sales_past_week' => lang('receivings_sales_in_past_week'),
			'sales_past_month' => lang('receivings_sales_in_past_month'),
			'all_items_for_supplier' => lang('receivings_all_items_for_supplier'),
		);
		$this->load->view("receivings/po",$data);
	}
	
	function create_po()
	{
		$supplier_id = $this->input->post('supplier_id');
		$criteria = $this->input->post('criteria');
		$category = $this->input->post('category');
		$this->load->model('Item_variation_location');
		$this->load->model('Item_variations');
		
		$item_ids = array();
		switch($criteria)
		{
			case 'below_reorder_level':
			case 'below_reorder_level_and_out_of_stock':
			
			$this->load->model('reports/Inventory_low');
			$model = $this->Inventory_low;
			$model->setParams(array('supplier'=>$supplier_id,'category_id' => $category, 'export_excel' => 1, 'offset'=>0, 'inventory' => $criteria == 'below_reorder_level' ? 'all' : 'out_of_stock' ,'reorder_only' => true, 'location_id' => $this->Employee->get_logged_in_employee_current_location_id()));
			
			$exclude_item_ids_with_variations = array();
			
			$low_inventory = $model->getData();
			
			foreach($low_inventory['details'] as $row)
			{
				$item_ids[] = $row['item_id'].'#'.$row['variation_id'];
				$exclude_item_ids_with_variations[] = $row['item_id'];
			}
			
			$exclude_item_ids_with_variations = array_unique($exclude_item_ids_with_variations);
			
			foreach($low_inventory['summary'] as $row)
			{
				if(!in_array($row['item_id'],$exclude_item_ids_with_variations))
				{
					$item_ids[] = $row['item_id'];
				}
			}
			
			break;

			case 'sales_past_week': 
			case 'sales_past_month':
			
			$start_date = false;
			$end_date = false;
			
			if ($criteria == 'sales_past_week')
			{
				$start_date = date("Y-m-d",strtotime('-7 days'));
				$end_date = date("Y-m-d  23:59:59");
				
			}
			elseif('sales_past_month')
			{
				$start_date = date("Y-m-d",strtotime('-31 days'));
				$end_date = date("Y-m-d 23:59:59");
			}
			$this->load->model('Sale');
			$item_ids = $this->Sale->get_item_ids_sold_for_date_range($start_date, $end_date, $supplier_id);
			break;
			
			case 'all_items_for_supplier':
				foreach($this->Item->get_all_by_supplier($supplier_id) as $row)
				{
					$variations = $this->Item_variations->get_all($row['item_id']);
					if (count($variations) > 0)
					{
						foreach($variations as $variation)
						{
							$item_ids[] = $row['item_id'].'#'.$variation['id'];
						}
					}
					else
					{
						$item_ids[] = $row['item_id'];
					}
				}
			break;
			
		}
		
		if ($this->input->post('clear_current_items_in_cart'))
		{
			$this->cart->empty_items();			
		}
		foreach($item_ids as $item_id)
		{
			
			if ($category)
			{
				$real_item_id = strpos($item_id,'#')!==FALSE ? strtok($item_id,'#') : $item_id;
				$item_info = $this->Item->get_info($real_item_id);
				$category_ids= array();
				if ($this->config->item('include_child_categories_when_searching_or_reporting'))
				{	
					$category_ids = $this->Category->get_category_id_and_children_category_ids_for_category_id($category);
				}
				else
				{
					$category_ids = array($item_info->category_id);
				}
				
				if (!in_array($category,$category_ids))
				{
					continue;
				}				
			}
			
			$quantity_to_add= 1;
			
			if ($criteria == 'below_reorder_level' || $criteria == 'below_reorder_level_and_out_of_stock')
			{
				if(strpos($item_id,'#')!==FALSE)
				{
					$variation_id = substr($item_id,strpos($item_id,'#')+1);
        	$cur_item_location_info = $this->Item_variation_location->get_info($variation_id);
        	$cur_item_info = $this->Item_variations->get_info($variation_id);
					if ($cur_item_info->reorder_level === NULL)
					{
	        	$cur_item_info = $this->Item->get_info(strtok($item_id,'#'));
					}
				}
				else
				{
        	$cur_item_location_info = $this->Item_location->get_info($item_id);
        	$cur_item_info = $this->Item->get_info($item_id);
				}	
					
					$replenish_level = ($cur_item_location_info && $cur_item_location_info->replenish_level) ? $cur_item_location_info->replenish_level : $cur_item_info->replenish_level;
					$reorder_level = ($cur_item_location_info && $cur_item_location_info->reorder_level) ? $cur_item_location_info->reorder_level : $cur_item_info->reorder_level;
					$quantity_to_add = ($replenish_level ? $replenish_level : $reorder_level) - $cur_item_location_info->quantity;
			}
			
			if ($quantity_to_add > 0 || !($criteria == 'below_reorder_level' || $criteria == 'below_reorder_level_and_out_of_stock'))
			{
				$this->cart->add_item(new PHPPOSCartItemRecv(
					array(
						'scan' => $item_id.'|FORCE_ITEM_ID|',
						'quantity' => max(1,$quantity_to_add),
						'cart' => $this->cart,
					)));
			}		
		}
		
		$this->cart->supplier_id = $supplier_id;
		$this->cart->is_po = TRUE;
		$this->cart->set_mode('purchase_order');
		$this->cart->save();
		redirect('receivings');
	}
	
	function payment_check($amount)
	{
		return ($amount != '0' || $this->cart->get_total() == 0) && is_numeric($amount);
	}
	
	
	function add_payment()
	{
		//Percent of amount due
		if(strpos($this->input->post('amount_tendered'),'%') !== FALSE)
		{
			$percentage = (float)$this->input->post('amount_tendered');
			$_POST['amount_tendered'] = $this->cart->get_amount_due()*($percentage/100);
		}
		
		$data=array();
		$this->form_validation->set_rules('amount_tendered', 'lang:common_amount_tendered', 'required|callback_payment_check');
	
		if ($this->form_validation->run() == FALSE)
		{
			if($this->input->post('amount_tendered') == '0' && $this->cart->get_total() != 0)
			{
				if ($this->cart->get_amount_due() != 0)
				{
					$data['error']=lang('common_cannot_add_zero_payment');		
				}		
			}
			else
			{
				$data['error']=lang('common_must_enter_numeric');				
			}
			
 			$this->_reload($data);
 			return;
		}
		
		if (($this->input->post('payment_type') == lang('common_store_account') && !$this->cart->supplier_id) ||
			($this->cart->get_mode() == 'store_account_payment' && !$this->cart->supplier_id)
			) 
		{
				$data['error']=lang('receivings_supplier_required_store_account');
				$this->_reload($data);
				return;
		}
		
		$store_account_payment_amount = $this->cart->get_total();
		if ($this->cart->get_mode() == 'store_account_payment'  && $store_account_payment_amount == 0) 
		{
          $data['error']=lang('common_store_account_payment_item_must_not_be_0');
          $this->_reload($data);
          return;
		}
	
		$this->cart->add_payment(new PHPPOSCartPaymentRecv(array('payment_type' => $this->input->post('payment_type'), 'payment_amount' => $this->input->post('amount_tendered'))));
		$this->cart->save();
		$this->_reload($data);
	}
	
	private function _get_shared_data()
	{
		$data = $this->cart->to_array();
		$data['receipt_title']=lang('receivings_receipt');
		$old_date = $this->cart->receiving_id  ? $this->Receiving->get_info($this->cart->receiving_id)->row_array() : false;
		
		if($old_date)
		{
			$old_date = $old_date['receiving_time'];
		}
		else
		{
			$old_date = date(get_date_format().' '.get_time_format());
		}
		$data['transaction_time']= $this->cart->change_date_enable ?  date(get_date_format().' '.get_time_format(), strtotime($this->cart->change_cart_date)) : date(get_date_format().' '.get_time_format(), strtotime($old_date));
		$data['modes'] = array('receive'=>lang('common_receiving'),'return'=>lang('receivings_return'),'purchase_order'=>lang('receivings_purchase_order'));
		$data['payment_options'] = $this->Receiving->get_payment_options($this->cart);
		
		foreach($this->view_data as $key=>$value)
		{
			$data[$key] = $value;
		}
		
		return $data;
	}
	
	function convert_sale_to_return()
	{
		//do logic for making a sale a return
		$this->cart->do_convert_cart_from_sale_to_return();
		$this->cart->save();
		$this->_reload();
	}
	
	function convert_return_to_sale()
	{
		//do logic for making a sale a return
		$this->cart->do_convert_cart_from_return_to_sale();
		$this->cart->save();
		$this->_reload();		
	}
	
	function receipt_validate()
	{
		if ($this->cart->is_valid_receipt($this->input->post('receiving_id')))
		{
			$receiving_id = substr(strtolower($this->input->post('receiving_id')), strpos(strtolower($this->input->post('receiving_id')),'RECV'.' ') + strlen(strtolower('RECV').' '));
		}
		else
		{
			$receiving_id = $this->input->post('receiving_id');
		}
		
		$sale_info = $this->Receiving->get_info($receiving_id)->row_array();
		if(!$sale_info)
		{
			echo json_encode(array('success'=>false,'message'=>lang('receivings_receiving_id_not_found')));
			die();
		}
		else
		{
			echo json_encode(array('success'=>true,'receiving_id' => $receiving_id));
			die();
		}
	}
	
	function custom_fields()
	{
		$this->lang->load('config');
		$fields_prefs = $this->config->item('receiving_custom_field_prefs') ? unserialize($this->config->item('receiving_custom_field_prefs')) : array();
		$data = array_merge(array('controller_name' => strtolower(get_class())),$fields_prefs);
		$locations_list = $this->Location->get_all()->result();
		$data['locations'] = $locations_list;
		$this->load->view('custom_fields',$data);
	}
	
	function save_shipping_cost()
	{
		$this->cart->shipping_cost = $this->input->post('shipping_cost');
		$this->cart->save();
	}
	
	function save_custom_fields()
	{
		$this->load->model('Appconfig');
		$this->Appconfig->save('receiving_custom_field_prefs',serialize($this->input->post()));
	}
	
	function save_custom_field()
	{
		$k = str_replace(array('custom_field_','_value'),array('',''),$this->input->post('name'));
		if ($this->Receiving->get_custom_field($k) !== FALSE)
		{		
			if($this->Receiving->get_custom_field($k,'type') == 'date')
			{
				$this->cart->{$this->input->post('name')} = (string)strtotime($this->input->post('value'));
			}
			elseif($this->Receiving->get_custom_field($k,'type') == 'image')
			{
		    $this->load->library('image_lib');
				
		    $config['image_library'] = 'gd2';
		    $config['source_image']	= $_FILES["value"]['tmp_name'];
		    $config['create_thumb'] = FALSE;
		    $config['maintain_ratio'] = TRUE;
		    $config['width']	 = 1200;
		    $config['height']	= 900;
				$this->image_lib->initialize($config);
		    $this->image_lib->resize();
	   	 	$this->load->model('Appfile');
		    $image_file_id = $this->Appfile->save($_FILES['value']['name'], file_get_contents($_FILES["value"]['tmp_name']));
				$this->cart->{$this->input->post('name')} = $image_file_id;
			}
			elseif($this->Receiving->get_custom_field($k,'type') == 'file')
			{
	   	 	$this->load->model('Appfile');
		    $image_file_id = $this->Appfile->save($_FILES['value']['name'], file_get_contents($_FILES["value"]['tmp_name']));
				$this->cart->{$this->input->post('name')} = $image_file_id;
			}
			else
			{
				$this->cart->{$this->input->post('name')} = $this->input->post('value');
			}
		}
		
		$this->cart->save();
	}
	function get_attribute_values() {
		/*
		** Destroy Session if already Exits 
		** Fetch Variations for Edit 
		** Use function in receiving/register.php
		*/
		$this->session->unset_userdata('rec_editable_popup');
		/* 
		** Requst Cart Line Number 
		** Fetch Result against Line Number
		*/
		$attr_id 				= 	(int) $_REQUEST["attr_id"];
		$variation 				= 	$this->cart->get_item($attr_id);
		$variation 				= 	$variation->variation_choices_model;
		/* 
		** Create Variations Array, Post Line Number in Session
		*/
		$editable_popup 		= 	$this->session->set_userdata('rec_editable_popup',$attr_id); 
		$attributes_available   = 	array();
	    $attributes_final_array = 	array();
	    foreach ($variation as $variation_id => $single_variation) {
	        $variation_temp = array();
	        $variation_temp = explode(", ", trim($single_variation));
	        foreach ($variation_temp as $single_temp) {
	            $attributes_available[$variation_id][] = explode(": ", trim($single_temp))[1];
	        }
	    }
	    /*
		** Variations Loop for Child
		*/
	    foreach ($attributes_available as $key => $attibute) {
	        $total_index = count($attibute);

		        switch($total_index):
		        	case 1:
		        		$attributes_final_array[$attibute[0]][$key] = NULL;
		        		break;
		        	case 2:
		        		$attributes_final_array[$attibute[0]][$attibute[1]][$key] = NULL;
		        		break;
		        	case 3:
		        		@$attributes_final_array[$attibute[0]][$attibute[1]][$attibute[2]][$key] = NULL;
		        		break;
		        	case 4:
		        		@$attributes_final_array[$attibute[0]][$attibute[1]][$attibute[2]][$attibute[3]][$key] = NULL;
		        		break;
		        	case 5:
		        		@$attributes_final_array[$attibute[0]][$attibute[1]][$attibute[2]][$attibute[3]][$attibute[4]][$key] = NULL;			
		        		break;
		        	case 6:
		        		@$attributes_final_array[$attibute[0]][$attibute[1]][$attibute[2]][$attibute[3]][$attibute[4]][$attibute[5]][$key] = NULL;		
		        		break;
		        	case 7:
		        		@$attributes_final_array[$attibute[0]][$attibute[1]][$attibute[2]][$attibute[3]][$attibute[4]][$attibute[5]][$attibute[6]][$key] = NULL;		
		        		break;	
		        endswitch;
	    }

	    /*
		** Show Variations for Edit in Model
		*/
		foreach ($attributes_final_array as $key => $variation) {
			echo "<a href='javascript:fetch_attr_values(".htmlspecialchars(json_encode(trim($key)), ENT_QUOTES).");' class='btn btn-primary popup_button' style='margin:5px;' id='attri_".htmlspecialchars(trim($key), ENT_QUOTES)."'>".trim($key)."</a>";
		}
		
		$this->session->set_userdata('show_model',$attributes_final_array);
		return ;
	}
	
	function get_attributes_values() {
		$attr_id 		= 	$_REQUEST["attr_id"];
		$check_attr 	= 	explode(",",$attr_id);
		$count 			=  	count($check_attr);
		$get_data 		= 	$this->session->userdata('rec_popup');
			switch($count):
				case 1:
	        		$get_data 	= 	$get_data[$check_attr[0]];
	        		$link  		= 	$check_attr[0];
	        		break;
	        	case 2:
	        		$get_data 	= 	$get_data[$check_attr[0]][$check_attr[1]];
	        		$link  		= 	$check_attr[0].','.$check_attr[1];
	        		break;
	        	case 3:
	        		$get_data 	= 	$get_data[$check_attr[0]][$check_attr[1]][$check_attr[2]];
	        		$link  		= 	$check_attr[0].','.$check_attr[1].','.$check_attr[2];
	        		break;
	        	case 4:
	        		$get_data 	= 	$get_data[$check_attr[0]][$check_attr[1]][$check_attr[2]][$check_attr[3]];
	        		$link  		= 	$check_attr[0].','.$check_attr[1].','.$check_attr[2].','.$check_attr[3];
	        		break;
	        	case 5:
	        		@$get_data 	= 	$get_data[$check_attr[0]][$check_attr[1]][$check_attr[2]][$check_attr[3]][$check_attr[4]];
	        		@$link  	= 	$check_attr[0].','.$check_attr[1].','.$check_attr[2].','.$check_attr[3].','.$check_attr[4];
	        		break;
	        	case 6:
	        		@$get_data 	= 	$get_data[$check_attr[0]][$check_attr[1]][$check_attr[2]][$check_attr[3]][$check_attr[4]][$check_attr[5]];
	        		@$link  	= 	$check_attr[0].','.$check_attr[1].','.$check_attr[3].','.$check_attr[4].','.$check_attr[5];
	        		break;
	        	case 7:
	        		@$get_data 	= 	$get_data[$check_attr[0]][$check_attr[1]][$check_attr[2]][$check_attr[3]][$check_attr[4]][$check_attr[5]][$check_attr[6]];
	        		@$link  	= 	$check_attr[0].','.$check_attr[1].','.$check_attr[3].','.$check_attr[4].','.$check_attr[5].','.$check_attr[6];
	        		break;	
	        	case 8:
	        		@$get_data 	= 	$get_data[$check_attr[0]][$check_attr[1]][$check_attr[2]][$check_attr[3]][$check_attr[4]][$check_attr[5]][$check_attr[6]][$check_attr[7]];
	        		@$link  	= 	$check_attr[0].','.$check_attr[1].','.$check_attr[3].','.$check_attr[4].','.$check_attr[5].','.$check_attr[6].','.$check_attr[7];
	        		break;	
	        endswitch;

			foreach ($get_data as $index => $attribute) {
				/* 
				** SAVE Variation 
				*/
				if (is_numeric($index) and empty($attribute)) 
				{ 	
					$line_no   = $this->cart->get_items();
					
					if ($this->session->userdata('rec_editable_popup') !== NULL)
					{
						$line_no 	= $this->session->userdata('rec_editable_popup');
						$this->session->unset_userdata('rec_editable_popup');
					}
					else
					{						
						$line_no 	= count($line_no) -1;
					}
					$_POST["name"] 	= "variation";
					$_POST["value"] = $index;

					self::edit_item_variation($line_no);

					echo '<script>
						
						$("#choose_var").modal("hide");
						setTimeout(function()
						{
 							jQuery("#register_container").load("'.site_url('receivings/reload').'");
						},200);
						</script>';
					die;
				} else { 
					echo "<a href='javascript:fetch_attr_value(".htmlspecialchars(json_encode(trim($link.','.$index)), ENT_QUOTES).");' class='btn btn-success popup_button' style='margin:5px;' id='attri_".htmlspecialchars(trim($index), ENT_QUOTES)."'>".trim($index)."</a>";
				}
				
			}
	
		//$this->_reload();
	}
	
	function edit_subtotal()
	{
		$new_subtotal = $this->input->post('value');
		$this->cart->edit_subtotal($new_subtotal);
		$this->cart->save();
		$this->_reload(array());
	}
	
	function delete_custom_field_value($k)
	{
		$this->cart->{"custom_field_${k}_value"} = NULL;
		$this->cart->save();
	}
	
	function download($file_id)
	{
		//Don't allow images to cause hangups with session
		session_write_close();
		$this->load->model('Appfile');
		$file = $this->Appfile->get($file_id);
		$this->load->helper('file');
		$this->load->helper('download');
		force_download($file->file_name,$file->file_data);
	}
	
	function set_details_collapsed()
	{
		if($this->input->post('value'))
		{
			$this->cart->details_collapsed = TRUE;
		}
		else
		{
			$this->cart->details_collapsed = FALSE;				
		}
		
		$this->cart->save();
	}

	function sig_save($sale_register_id_display = false)
	{
		$this->load->model('Appfile');
		$receiving_id = $this->input->post('receiving_id');
		$receiving_info = $this->Receiving->get_info($receiving_id)->row_array();
		
		//If we have a signature delete it
		if ($receiving_info['signature_image_id'])
		{
			$this->Sale->update(array('signature_image_id' => NULL), $receiving_id);
			$this->Appfile->delete($receiving_info['signature_image_id']);
		}
		
		$image = base64_decode($this->input->post('image'));
    	$image_file_id = $this->Appfile->save('signature_'.$receiving_id.'.png', $image);
		$this->Receiving->update(array('signature_image_id' => $image_file_id), $receiving_id);

		echo json_encode(array('file_id' => $image_file_id, 'file_timestamp' => $this->Appfile->get_file_timestamp($image_file_id)));
	}

	function sort()
	{
		$sort_column_1 = $this->input->post("sort_column");
		$sort_column = "";
		if($sort_column_1 == 'price'){
			$sort_column = 'unit_price';
		}else if($sort_column_1 == 'quantity'){
			$sort_column = 'quantity';
		}else if($sort_column_1 == 'name'){
			$sort_column = 'name';
		}else if($sort_column_1 == 'discount'){
			$sort_column = 'discount';
		}else if($sort_column_1 == 'total'){
			$sort_column = 'total';
		}

		if($sort_column != ""){
			$sort_type = $this->input->post("sort_type");
			$this->cart->sort($sort_column, $sort_type);
			$this->cart->save();
		}else if($sort_column_1 == 'drag_drop'){
			$drag_index = $this->input->post("drag_index");
			$drop_index = $this->input->post("drop_index");
			if($drag_index > -1 && $drop_index > -1 & $drag_index != $drop_index){
				$this->cart->drag_drop($drag_index, $drop_index);
				$this->cart->save();
			}
		}
		$this->_reload();
	}

	function update_receivings_item_order(){
		$list = $this->input->post("item_lines");

		foreach($list as $item_line){
			$item_id = $item_line['item_id'];
			$receiving_id = $item_line['receiving_id'];
			$item_class = $item_line['item_class'];
			$receipt_line_sort_order = $item_line['receipt_line_sort_order'];
			$this->Receiving->receiving_item_line_update($receiving_id, $item_id, $item_class, $receipt_line_sort_order);
		}

		$result = array(
			'state' => 1
		);
		echo json_encode($result);
		exit;	
	}

	function exchange_to()
	{

		$data = array();
		$rate = $this->input->post('rate');
		$this->cart->set_exchange_details($rate);
		$this->cart->save();
  		$this->_reload($data);
	}
}
?>