<?php
require_once ("Secure_area.php");
require_once (APPPATH."models/cart/PHPPOSCartSale.php");
require_once (APPPATH."traits/taxOverrideTrait.php");
require_once (APPPATH."traits/creditcardProcessingTrait.php");

class Sales extends Secure_area
{
	use taxOverrideTrait;
	use creditcardProcessingTrait;
	
	public $cart;
	public $view_data = array();
	
	function __construct()
	{
		parent::__construct('sales');
		$this->lang->load('sales');
		$this->lang->load('module');
		$this->load->helper('order');
		$this->load->helper('items');
		$this->load->helper('sale');
		$this->load->model('Sale');
		$this->load->model('Customer');
		$this->load->model('Tier');
		$this->load->model('Category');
		$this->load->model('Giftcard');
		$this->load->model('Tag');
		$this->load->model('Item');
		$this->load->model('Item_location');
		$this->load->model('Item_kit_location');
		$this->load->model('Item_kit_location_taxes');
		$this->load->model('Item_kit');
		$this->load->model('Item_kit_items');
		$this->load->model('Item_kit_taxes');
		$this->load->model('Item_location_taxes');
		$this->load->model('Item_taxes');
		$this->load->model('Item_taxes_finder');
		$this->load->model('Item_kit_taxes_finder');
		$this->load->model('Appfile');
		$this->load->model('Item_serial_number');
		$this->load->model('Price_rule');
		$this->load->model('Shipping_provider');
		$this->load->model('Shipping_method');
		$this->lang->load('deliveries');
		$this->load->model('Item_variation_location');
		$this->load->model('Item_variations');
		$this->load->helper('giftcards');
		$this->load->model('Item_attribute_value');
		$this->load->model('Item_modifier');
		$this->load->model('Delivery_category');
		$this->load->model('Work_order');
		$this->load->helper('text');
		$this->load->model('Supplier');
		
		$this->cart = PHPPOSCartSale::get_instance('sale');
		cache_item_and_item_kit_cart_info($this->cart->get_items());
	}	
	
	
	function index($dont_switch_employee = 0)
	{	
		if (count($this->cart->get_items()) > 0)
		{
			$dont_switch_employee = 1;
		}
		if($this->config->item('automatically_show_comments_on_receipt'))
		{
			$this->cart->show_comment_on_receipt = 1;
			$this->cart->save();
		}

		if ($this->config->item('default_sales_person') != 'not_set' && !$this->cart->sold_by_employee_id)
		{
			$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
			$this->cart->sold_by_employee_id = $employee_id;
			$this->cart->save();
		}

		$location_id=$this->Employee->get_logged_in_employee_current_location_id();
		
		$register_count = $this->Register->count_all($location_id);
		
		if ($register_count > 0)
		{
			if ($register_count == 1)
			{
				$registers = $this->Register->get_all($location_id);
				$register = $registers->row_array();
			
				if (isset($register['register_id']))
				{
					$this->Employee->set_employee_current_register_id($register['register_id']);
				}
			}
		
			if (!$this->Employee->get_logged_in_employee_current_register_id())
			{
				$default_register = $this->Employee->getDefaultRegister($this->Employee->get_logged_in_employee_info()->person_id,$this->Employee->get_logged_in_employee_current_location_id());
				
				if ($default_register && $default_register['location_id'] == $location_id)
				{
					$this->Employee->set_employee_current_register_id($default_register['register_id']);
					redirect(site_url('sales'));
					
				}
				else
				{
					$this->load->view('sales/choose_register');		
					return;
				}
			}
		}
		$track_payment_types = unserialize($this->config->item('track_payment_types'));
		
		if ($this->config->item('track_payment_types') && !empty($track_payment_types)) 
		{
			
			if ($this->input->post('opening_amount') != '' && !$this->Register->is_register_log_open())  
			{
				$now = date('Y-m-d H:i:s');

				$cash_register = new stdClass();
				$cash_register->register_id = $this->Employee->get_logged_in_employee_current_register_id();
				$cash_register->employee_id_open = $this->session->userdata('person_id');
				$cash_register->shift_start = $now;
				$regiser_payment_data  = array();
				foreach($this->input->post('opening_amount') as $payment_type => $opening_amount)
				{
					$regiser_payment_data[] = array('payment_type' => $payment_type,'open_amount' =>$opening_amount);
				}
				
				$register_log_id = $this->Register->insert_register($cash_register,$regiser_payment_data);

				$cash_register = $this->Register->get_current_register_log();				
				$this->Register->insert_denoms($cash_register,$this->input->post('denoms'),'open');

				redirect(site_url('sales'));
			}
			else if ($this->Register->is_register_log_open()) 
			{				
				$cash_in_register = NULL;
				
				if (($this->config->item('cash_alert_low') !== NULL && $this->config->item('cash_alert_low') !== '') || ($this->config->item('cash_alert_high') !== NULL && $this->config->item('cash_alert_high') !== ''))
				{
					if (in_array('common_cash',$track_payment_types))
					{
						$cash_register = $this->Register->get_current_register_log();
						$register_log_id = $cash_register->register_log_id;
							
						$payment_sales = array();
						foreach($track_payment_types as $payment_type)
						{
							$payment_sales[$payment_type] = $this->Sale->get_payment_sales_total_for_shift($cash_register->shift_start, date("Y-m-d H:i:s"),$payment_type);
						}
			
						$closeout = $this->Register->get_closeout_amounts($register_log_id,$payment_sales);
						$cash_in_register = $closeout['common_cash'];
					
					}
				}
				
				$this->_reload(array('dont_switch_employee' => $dont_switch_employee,'cash_in_register' => $cash_in_register), false);
			} 
			else 
			{
				
				$this->load->view('sales/opening_amount', array('previous_closings' => $this->Register->get_closing_amounts($this->Register->get_last_closing_register_log_id($this->Employee->get_logged_in_employee_current_register_id())),'denominations' => $this->Register->get_register_currency_denominations()->result_array()));
			}
		} 
		else 
		{			
			$this->_reload(array('dont_switch_employee' => $dont_switch_employee), false);
		}
		
	}
	
	function choose_register($register_id)
	{
		if ($this->Register->exists($register_id))
		{
			$this->Employee->set_employee_current_register_id($register_id);
		}
		
		redirect(site_url('sales'));
		return;		
	}
	
	function clear_register()
	{
		//Clear out logged in register when we switch locations
		$this->Employee->set_employee_current_register_id(null);
		
		redirect(site_url('sales'));
		return;		
	}
	
	function register_add_subtract($mode,$payment_type,$return = 'sales')
	{
		$data = array();
		$data['mode'] = $mode;
		$data['payment_type'] = $payment_type;
		$data['return'] = $return;
		$cash_register = $this->Register->get_current_register_log();
		$additions = $this->Register->get_total_payment_additions($cash_register->register_log_id);
		$subtractions = $this->Register->get_total_payment_subtractions($cash_register->register_log_id);
		
		if (!$this->Register->is_register_log_open()) 
		{
			redirect(site_url('home'));
			return;
		}
		
		if ($this->input->post('amount') != '') 
		{
			$message = '';
			$amount = to_currency_no_money($this->input->post('amount'));
		
			if ($mode == 'add')
			{
				$payment_data = array('total_payment_additions' => $additions[$payment_type]+$this->input->post('amount'));
				$message = lang('sales_cash_successfully_added_to_drawer');
			}
			else
			{
				$payment_data = array('total_payment_subtractions' => $subtractions[$payment_type]+$this->input->post('amount'));
				$message = lang('sales_cash_successfully_removed_from_drawer');
			}
			$this->Register->update_register_log_payment($cash_register->register_log_id,$payment_type,$payment_data);
						
					
			$employee_id_audit = $this->Employee->get_logged_in_employee_info()->person_id;
			$register_audit_log_data = array(
				'register_log_id'=> $cash_register->register_log_id,
				'employee_id'=> $employee_id_audit,
				'date' => date('Y-m-d H:i:s'),
				'payment_type' => $payment_type,
				'amount' => $mode == 'add' ? $amount : -$amount,
				'note' => $this->input->post('note'),
			);
			
			$this->Register->insert_audit_log($register_audit_log_data);
			
			$this->session->set_flashdata('cash_drawer_add_subtract_message', $message);
			
			if ($return == 'sales')
			{
				redirect('sales');	
			}
			elseif ($return == 'closeregister')
			{
				redirect('sales/closeregister?continue=home');
			}
		} 
		else
		{
			if ($mode == 'add')
			{
				$data['amount'] = to_currency($additions[$payment_type]);
			}
			else
			{
				$data['amount'] = to_currency($subtractions[$payment_type]);
				
			}
			
			$this->load->view('sales/register_add_subtract', $data);
		}
		
	}
	
	function closeregister() 
	{
		if (!$this->Register->is_register_log_open()) 
		{
			redirect(site_url('home'));
			return;
		}
		
		$cash_register = $this->Register->get_current_register_log();
		$register_log_id = $cash_register->register_log_id;
		$payment_types = unserialize($this->config->item('track_payment_types'));
		
		$payment_sales = array();
		foreach($payment_types as $payment_type)
		{
			$payment_sales[$payment_type] = $this->Sale->get_payment_sales_total_for_shift($cash_register->shift_start, date("Y-m-d H:i:s"),$payment_type);
			
			if ($payment_type == 'common_credit')
			{
				$payment_sales[$payment_type] += $this->Sale->get_payment_sales_total_for_shift($cash_register->shift_start, date("Y-m-d H:i:s"),'sales_partial_credit');
			}
			
		}
		
		$continueUrl = $this->input->get('continue');
		if ($this->input->post('closing_amount') != '') {
			$now = date('Y-m-d H:i:s');
			$cash_register->register_id = $this->Employee->get_logged_in_employee_current_register_id();
			$cash_register->employee_id_close = $this->session->userdata('person_id');
			$cash_register->shift_end = $now;
			$register_log_id_to_update = $cash_register->register_log_id;
			$this->Register->insert_denoms($cash_register,$this->input->post('denoms'),'close');
			unset($cash_register->register_log_id);
			$cash_register->notes = $this->input->post('notes');
			$this->Register->update_register_log($cash_register,$cash_register->register_id);
			
			foreach($this->input->post('closing_amount') as $payment_type=>$closing_amount)
			{
				$this->Register->update_register_log_payment($register_log_id,$payment_type,array('close_amount' => $closing_amount,'payment_sales_amount' => $payment_sales[$payment_type]));
			}
			if ($continueUrl == 'logout') 
			{
				redirect(site_url('home/logout'));
			} 
			elseif($continueUrl == 'timeclocks')
			{
				redirect(site_url('timeclocks'));				
			}
			elseif($continueUrl == 'closeoutreceipt')
			{
				redirect(site_url("reports/register_log_details/$register_log_id"));
				
			}
			else
			{
				redirect(site_url('home'));
			}
		} 
		else
		{
			
			if (!empty($payment_types))
			{
				$open_amounts = $this->Register->get_opening_amounts($register_log_id);
				$total_payment_additions = $this->Register->get_total_payment_additions($register_log_id);
				$total_payment_subtractions = $this->Register->get_total_payment_subtractions($register_log_id);				
				$closeout_amounts = $this->Register->get_closeout_amounts($register_log_id,$payment_sales);
			}
			
			$this->load->view('sales/closing_amount', array(
				'continue'=>$continueUrl ? "?continue=$continueUrl" : '',
				'open_amounts' => $open_amounts,
				'notes'=>'',
				'closeout_amounts'=>$closeout_amounts,
				'payment_sales' => $payment_sales,
				'total_payment_additions' => $total_payment_additions,
				'total_payment_subtractions' => $total_payment_subtractions,
				'denominations' => $this->Register->get_register_currency_denominations()->result_array(),
				'register_log_id' => $register_log_id,
			));
		}
	}
	

	function edit_register($register_log_id) 
	{
		$payment_types =  $this->config->item('track_payment_types') ? unserialize($this->config->item('track_payment_types')) : array();
		$payment_sales = array();
		
		$cash_register = $this->Register->get_existing_register_log($register_log_id);
		foreach($payment_types as $payment_type)
		{
			$payment_sales[$payment_type] = $this->Sale->get_payment_sales_total_for_shift($cash_register->shift_start, date("Y-m-d H:i:s"),$payment_type);
		}

		$continueUrl = $this->input->get('continue');
		if ($this->input->post('closing_amount') != '') {
			
			
			$this->Register->update_existing_register_log(array('notes' => $this->input->post('notes')), $register_log_id);
			
			$this->Register->insert_denoms($cash_register,$this->input->post('denoms'),'close');
			
			foreach($this->input->post('closing_amount') as $payment_type=>$closing_amount)
			{
				$this->Register->update_register_log_payment($register_log_id,$payment_type,array('close_amount' => $closing_amount));
			}
			
			foreach($this->input->post('opening_amount') as $payment_type=>$open_amount)
			{
				$this->Register->update_register_log_payment($register_log_id,$payment_type,array('open_amount' => $open_amount));
			}

			redirect(site_url("reports/register_log_details/$register_log_id"));
				
		} 
		else
		{
			if (!empty($payment_types))
			{
				$open_amounts = $this->Register->get_opening_amounts($register_log_id);
				$total_payment_additions = $this->Register->get_total_payment_additions($register_log_id);
				$total_payment_subtractions = $this->Register->get_total_payment_subtractions($register_log_id);				
				$closeout_amounts = $this->Register->get_closing_amounts($register_log_id);
			}
			
			$this->load->view('sales/closing_amount', array(
				'continue'=>$continueUrl ? "?continue=$continueUrl" : '',
				'open_amounts' => $open_amounts,
				'notes'=>$cash_register->notes,
				'closeout_amounts'=>$closeout_amounts,
				'payment_sales' => $payment_sales,
				'total_payment_additions' => $total_payment_additions,
				'total_payment_subtractions' => $total_payment_subtractions,
				'denominations' => $this->Register->get_register_currency_denominations()->result_array(),
				'register_log_id' => $register_log_id,
				'update' => true,
				'open_amount_editable' => true,
			));
		}
	}
	
	function item_search()
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		if(!$this->config->item('speed_up_search_queries'))
		{
			$suggestions = $this->Item->get_item_search_suggestions($this->input->get('term'),0,'unit_price',$this->config->item('items_per_search_suggestions') ? (int)$this->config->item('items_per_search_suggestions') : 20, TRUE);
			$suggestions = array_merge($suggestions, $this->Item_kit->get_item_kit_search_suggestions_sales_recv($this->input->get('term'),0,'unit_price', 100, TRUE));
		}
		else
		{
			$suggestions = $this->Item->get_item_search_suggestions_without_variations($this->input->get('term'),0,$this->config->item('items_per_search_suggestions') ? (int)$this->config->item('items_per_search_suggestions') : 20,'unit_price', TRUE);
			$suggestions = array_merge($suggestions, $this->Item_kit->get_item_kit_search_suggestions_sales_recv($this->input->get('term'),0,'unit_price', 100, TRUE));
			
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
	function customer_search()
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		$suggestions = $this->Customer->get_customer_search_suggestions($this->input->get('term'),0,100);
		
		if ($this->config->item('enable_customer_quick_add'))
		{
			$suggestions[] = array('subtitle' => '','avatar' => base_url()."assets/img/user.png",'value' => 'QUICK_ADD|'.$this->input->get('term'), 'label' => lang('customers_add_new_customer').' '.$this->input->get('term'));
		}
		
		echo json_encode(H($suggestions));
	}

	function select_customer()
	{
		if ($this->config->item('enable_customer_quick_add') && strpos($this->input->post('customer'),'QUICK_ADD|') !== FALSE)
		{
			$_POST['customer'] = str_replace('QUICK_ADD|','',$_POST['customer']);
			$_POST['customer'] = str_replace('|FORCE_PERSON_ID|','',$_POST['customer']);
			$this->load->helper('text');
			list($first_name,$last_name) = split_name($this->input->post('customer'));
			$person_data = array('first_name' => $first_name,'last_name' => $last_name);
			$customer_data = array();
			$this->Customer->save_customer($person_data, $customer_data);
			$_POST['customer'] =  $person_data['person_id'];
		}		
				
		$data = $this->cart->select_customer($this->input->post("customer"));
		
		$items = $this->cart->get_items();
		$run_tier_change = FALSE;
		
		//Remove any rules
		foreach($items as $line=>$item)
		{
			if (!empty($item->rule))
			{
				if ($this->Price_rule->is_rule_excluded_by_tier($item->rule))
				{
					$this->cart->get_item($line)->rule = array();
					$this->cart->get_item($line)->unit_price = $this->cart->get_item($line)->regular_price;
					$this->cart->get_item($line)->discount = 0;
					$run_tier_change = TRUE;
				}
			}
		}
		
		if ($run_tier_change)
		{
			$this->cart->determine_new_prices_for_tier_change();
		}
		
		$this->cart->save();
		$this->_reload($data);
	}

	function change_mode($mode = false, $redirect = false)
	{
		$previous_mode = $this->cart->get_mode();
		
		$mode = $mode === FALSE ? $this->input->post("mode") : $mode;
		
		if ($previous_mode == 'store_account_payment' && ($mode == 'sale' || $mode == 'return'))
		{
			$this->cart->empty_items();
		}
		
		$this->cart->set_mode($mode);
		
		if ($mode == 'store_account_payment')
		{
			$store_account_payment_item_id = $this->Item->create_or_update_store_account_item();
			$this->cart->empty_items();
			$this->cart->add_item(new PHPPOSCartItemSale(array('cost_price' => 0,'unit_price' => 0,'scan' => $store_account_payment_item_id.'|FORCE_ITEM_ID|','cart' => $this->cart)));
			$this->cart->save();
		}
		
		if ($mode == 'purchase_points')
		{
			$purchase_points_item_id = $this->Item->create_or_update_purchase_points_item();
			$this->cart->empty_items();
			$this->cart->add_item(new PHPPOSCartItemSale(array('scan' => $purchase_points_item_id.'|FORCE_ITEM_ID|','cart' => $this->cart)));
			$this->cart->save();
		}
		
		if ($redirect)
		{
			redirect('sales');
		}
		else
		{
			$data = array();
			if ($previous_mode == 'sale' && $mode =='return')
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
			elseif($previous_mode == 'return' && $mode =='sale')
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
			$this->cart->save();
			$this->_reload($data);
		}
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
	
	function set_comment() 
	{
 	  $this->cart->comment = $this->input->post('comment');
		$this->cart->save();
	}

	function set_selected_payment()
	{
		$this->cart->selected_payment = $this->input->post('payment');
		$this->cart->save();
	}
	
	function set_change_cart_date() 
	{
 	  $this->cart->change_cart_date = $this->input->post('change_cart_date');
		$this->cart->save();
	}
	
	function set_change_date_enable() 
	{
		$this->cart->change_date_enable = $this->input->post('change_date_enable');
	  if (!$this->cart->change_cart_date)
	  {
	 	 $this->cart->change_cart_date = date(get_date_format());
	  }
		$this->cart->save();
	}
	
	function set_comment_on_receipt() 
	{
		$this->cart->show_comment_on_receipt = $this->input->post('show_comment_on_receipt');
		$this->cart->save();
	}
	
	function set_email_receipt()
	{
 	  $this->cart->email_receipt = $this->input->post('email_receipt');
		$this->cart->save();
	}

	function set_sms_receipt()
	{
 	  $this->cart->sms_receipt = $this->input->post('sms_receipt');
		$this->cart->save();
	}

	function set_save_credit_card_info() 
	{
 	  $this->cart->save_credit_card_info = $this->input->post('save_credit_card_info');
		$this->cart->save();
	}
	
	function set_use_saved_cc_info()
	{
 	  $this->cart->use_cc_saved_info = $this->input->post('use_saved_cc_info');
		$this->cart->save();
	}
	
	function set_prompt_for_card()
	{
 	  $this->cart->prompt_for_card = $this->input->post('prompt_for_card');
		$this->cart->save();
	}
	
	function set_tier_id() 
	{
	  $data = array();
		$this->cart->previous_tier_id = $this->cart->selected_tier_id;
		$this->cart->selected_tier_id = $this->input->post('tier_id');

		$items = $this->cart->get_items();
		
		//Remove any rules
		foreach($items as $line=>$item)
		{
			if (!empty($item->rule))
			{
				if ($this->Price_rule->is_rule_excluded_by_tier($item->rule))
				{
					$this->cart->get_item($line)->rule = array();
					$this->cart->get_item($line)->unit_price = $this->cart->get_item($line)->regular_price;
					$this->cart->get_item($line)->discount = 0;
				}
			}
		}
		
		$this->cart->determine_new_prices_for_tier_change();
		
	  foreach($items as $line=>$item)
	  {
	  	  if ($item->below_cost_price())
	  	  {
				  if ($this->config->item('do_not_allow_below_cost'))
				  {
						//switch previous tier to what we posted and the current tier to what we had before; then determine new prices
					  $this->cart->selected_tier_id = $this->cart->previous_tier_id;
						$this->cart->previous_tier_id = $this->input->post('tier_id');
						$this->cart->determine_new_prices_for_tier_change();
					  $data['error'] = lang('sales_selling_item_below_cost');
						break;
				  }
				  else
				  {
					  $data['warning'] = lang('sales_selling_item_below_cost');
				  }
  			}			
	  }
	  
	  $this->cart->save();
		$this->_reload($data);
	}

	function set_sold_by_employee_id() 
	{
		$this->cart->sold_by_employee_id = $this->input->post('sold_by_employee_id') ? $this->input->post('sold_by_employee_id') : NULL;
		
		if ($this->config->item('default_sales_person') != 'not_set' && !$this->cart->sold_by_employee_id)
		{
			$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
			$this->cart->sold_by_employee_id = $employee_id;
		}		
		
		$this->cart->save();
	}

	function payment_check($amount)
	{
		return ($amount != '0' || $this->cart->get_total() == 0) && is_numeric($amount);
	}
	
	function search_coupons()
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		
		$suggestions = $this->Price_rule->search_coupons($this->input->get('term'));
		$result = array();
				
		foreach ($suggestions as $k=>$v) 
		{
			if(!empty($v['coupon_code']))
			{
				$result[$k] = array('value' => $v['value'], 'label'=> $v['label'] . ' - ' . $v['coupon_code']); 
			}
		}		
		
		echo json_encode(H($result));
	}
	
	function set_coupons()
	{
		$data = array();
		$this->cart->set_coupons($this->input->post('coupons'));
		$this->cart->save();
		$this->_reload($data);
	}

	//Alain Multiple Payments
	function add_payment()
	{		
		//Percent of amount due
		if(strpos($this->input->post('amount_tendered'),'%') !== FALSE)
		{
			$percentage = (float)$this->input->post('amount_tendered');
			$_POST['amount_tendered'] = $this->cart->get_amount_due()*($percentage/100);
		}
		
		$data=array();
		$this->form_validation->set_rules('amount_tendered', 'lang:sales_amount_tendered', 'required|callback_payment_check');
		
		if($this->input->post('payment_type') !== lang('common_giftcard'))
		{
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
		}	
		
		if ($this->cart->has_series_packages() && !$this->cart->customer_id) 
		{
          $data['error']=lang('sales_customer_required_store_account');
          $this->_reload($data);
          return;
		}
		
		if ($this->cart->get_mode() == 'purchase_points'  && !$this->cart->customer_id) 
		{
          $data['error']=lang('sales_customer_required_store_account');
          $this->_reload($data);
          return;
		}
		
		if (($this->input->post('payment_type') == lang('common_store_account') && !$this->cart->customer_id) ||
			($this->cart->get_mode() == 'store_account_payment' && !$this->cart->customer_id)) 
		{
				$data['error']=lang('sales_customer_required_store_account');
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
			
		$this->load->helper('sale');
		if((is_sale_integrated_cc_processing($this->cart) && $this->input->post('payment_type') ==lang('common_credit')) || is_sale_integrated_ebt_sale($this->cart))
		{
			$data['error']=lang('sales_process_card_first');
			$this->_reload($data);
			return;
		}
		
		if((is_sale_integrated_giftcard_processing($this->cart) && $this->input->post('payment_type') ==lang('common_integrated_gift_card')) || is_sale_integrated_giftcard_processing($this->cart))
		{
			$data['error']=lang('sales_process_card_first');
			$this->_reload($data);
			return;
		}

		if((is_sale_integrated_ebt_sale($this->cart) && ($this->input->post('payment_type') == lang('common_ebt') ||  $this->input->post('payment_type') == lang('common_ebt_cash'))) || is_sale_integrated_cc_processing($this->cart))
		{
			$data['error']=lang('sales_process_card_first');
			$this->_reload($data);
			return;
		}
		
		if(($this->input->post('payment_type') == lang('common_ebt') && ($this->input->post('amount_tendered') + $this->cart->get_payment_amount(lang('common_wic'))+ $this->cart->get_payment_amount(lang('common_ebt'))) > $this->cart->get_ebt_total_amount_to_charge()+1e-6) || ($this->input->post('payment_type') == lang('common_wic') && ($this->input->post('amount_tendered') +  $this->cart->get_payment_amount(lang('common_ebt')) + $this->cart->get_payment_amount(lang('common_wic'))) > $this->cart->get_ebt_total_amount_to_charge()+1e-6))
		{
			$data['error']=lang('sales_ebt_too_high');
			$this->_reload($data);
			return;
		}
		
		if ($this->config->item('select_sales_person_during_sale') && !$this->cart->sold_by_employee_id)
		{
			$data['error']=lang('sales_must_select_sales_person');
			$this->_reload($data);
			return;			
		}
		
				
		$payment_type=$this->input->post('payment_type');

		if ( $payment_type == lang('common_points') )
		{
			$customer_info = $this->Customer->get_info($this->cart->customer_id);
			if ($this->input->post('amount_tendered') > to_currency_no_money($customer_info->points) || $this->input->post('amount_tendered') <=0 || $this->cart->get_amount_due() <= 0)
			{
				$data['error']=lang('sales_points_to_much');
				$this->_reload($data);
				return;
				
			}
			
			if ($this->config->item('minimum_points_to_redeem') && $customer_info->points < $this->config->item('minimum_points_to_redeem'))
			{
				$data['error']=lang('sales_points_to_little');
				$this->_reload($data);
				return;
				
			}
			
			$max_points = ceil($this->cart->get_amount_due() / $this->config->item('point_value'));
			$payment_amount = min($max_points * $this->config->item('point_value'), to_currency_no_money($this->input->post('amount_tendered') * $this->config->item('point_value')), $this->cart->get_amount_due());
		}
		elseif ( $payment_type == lang('common_giftcard') )
		{
			if(!$this->Giftcard->exists($this->Giftcard->get_giftcard_id($this->input->post('amount_tendered'))) || $this->Giftcard->is_integrated($this->Giftcard->get_giftcard_id($this->input->post('amount_tendered'))) || $this->Giftcard->is_inactive($this->Giftcard->get_giftcard_id($this->input->post('amount_tendered'))))
			{
				$data['error']=lang('sales_giftcard_does_not_exist');
				
				if ($this->cart->get_total() < 0)
				{
					$data['prompt_to_create_giftcard'] = $this->input->post('amount_tendered');
				}
				
				$this->_reload($data);
				return;
			}
			
			$payment_type=$this->input->post('payment_type').':'.$this->input->post('amount_tendered');
			$current_payments_with_giftcard = $this->cart->get_payment_amount($payment_type);
			$cur_giftcard_value = $this->Giftcard->get_giftcard_value( $this->input->post('amount_tendered') ) - $current_payments_with_giftcard;
			if ( $cur_giftcard_value <= 0 && $this->cart->get_total() > 0)
			{
				$data['error']=lang('sales_giftcard_balance_is').' '.to_currency( $this->Giftcard->get_giftcard_value( $this->input->post('amount_tendered') ) ).' !';
				$this->_reload($data);
				return;
			}
			elseif ( ( $this->Giftcard->get_giftcard_value( $this->input->post('amount_tendered') ) - $this->cart->get_total() ) > 0 )
			{
				$data['warning']=lang('sales_giftcard_balance_is').' '.to_currency( $this->Giftcard->get_giftcard_value( $this->input->post('amount_tendered') ) - $this->cart->get_total() ).' !';
			}
			$payment_amount=min( $this->cart->get_amount_due(), $this->Giftcard->get_giftcard_value( $this->input->post('amount_tendered') ) );
		}
		else
		{
			$payment_amount=$this->input->post('amount_tendered');
		}
		
		if (!$this->cart->validate_payment($payment_type,$payment_amount))
		{
			$data['error']=lang('common_unable_to_add_payment');
  		$this->_reload($data);
  		return;
		}
		
		$markup_markdown = $this->config->item('markup_markdown');
		
		if($markup_markdown && $this->cart->get_total() > 0 && !$this->Location->get_info_for_key('disable_markup_markdown'))
		{
			$markup_markdown_config = unserialize($markup_markdown);
			
			if (isset($markup_markdown_config[$this->input->post('payment_type')]) && $markup_markdown_config[$this->input->post('payment_type')])
			{
				$fee_percent = $markup_markdown_config[$this->input->post('payment_type')];
	
				if ($fee_percent > 0)
				{
					$fee_item_id = $this->Item->create_or_update_fee_item();
				}
				else
				{
					$fee_item_id = $this->Item->create_or_update_flat_discount_item();
				}
				
				$total_payment_amount = $this->cart->get_payments_total($this->input->post('payment_type')) + $payment_amount;
				
				$total_before_adding_fee = $this->cart->get_total() - ( $this->cart->get_total() - $total_payment_amount);
				$fee_item_in_cart_already = $this->cart->find_similiar_item(new PHPPOSCartItemSale(array('cart' => $this->cart,'scan' => $fee_item_id.'|FORCE_ITEM_ID|')));
				
				if ($fee_item_in_cart_already)
				{
					$fee_already_added = $fee_item_in_cart_already->get_total();
				}
				else
				{
					$fee_already_added = 0;
				}
				$fee_amount = to_currency_no_money(($this->cart->get_total() - ( $this->cart->get_total() - $total_payment_amount) - $fee_already_added) *($fee_percent/100));
	
				$fee_item = new PHPPOSCartItemSale(array('cart' => $this->cart,'scan' => $fee_item_id.'|FORCE_ITEM_ID|','cost_price' => 0 ,'unit_price' =>$fee_amount ,'description' => $this->input->post('payment_type').' '.lang('common_fee'),'quantity' => 1));
	
				if ($fee_item_in_cart = $this->cart->find_similiar_item($fee_item))
				{
					$this->cart->delete_item($this->cart->get_item_index($fee_item_in_cart));				
				}
				$this->cart->add_item($fee_item);
				
				//Only chnage payment amount of the amount is greater than or eqaul than the total (round to fix floating point comparision rounding)
				if (round($payment_amount,2) >= round($total_before_adding_fee,2))
				{
					$payment_amount += $fee_amount;
				}
			}
		}		
		if( !$this->cart->add_payment(new PHPPOSCartPaymentSale(array('payment_type' => $payment_type, 'payment_amount' =>$payment_amount))))
		{
			$data['error']=lang('common_unable_to_add_payment');
		}
		
		$this->cart->save();
		$this->_reload($data);
	}
	
	function create_return_on_giftcard()
	{		
		$giftcard_data = array(
		'giftcard_number'=>$this->input->post('giftcard_number'),
		'value'=>0,
		'customer_id'=>$this->cart->customer_id ? $this->cart->customer_id : NULL,
		);
		
		$this->Giftcard->save($giftcard_data);
		$payment_type = lang('common_giftcard').':'.$giftcard_data['giftcard_number'];
		$payment_amount = $this->cart->get_amount_due();
		$this->cart->add_payment(new PHPPOSCartPaymentSale(array('payment_type' => $payment_type, 'payment_amount' =>$payment_amount)));
		$this->cart->save();
	}

	//Alain Multiple Payments
	function delete_payment($payment_id)
	{
		$this->cart->delete_payment($payment_id);
		$this->cart->save();
		$this->_reload();
	}


	function add_giftcard()
	{
		$barcode_scan_data = $this->input->post("item");
		$quantity = $this->input->post("quantity");
		
		$item_to_add = new PHPPOSCartItemSale(array('cart' => $this,'scan' => $barcode_scan_data,'quantity' => 1,'unit_price' => null,'cost_price' => null));
		$this->cart->add_item($item_to_add);
		$this->cart->save();
		$this->_reload();
	}
	
	function add()
	{				
		$barcode_scan_data = $this->input->post("item");
		$quantity = $this->input->post("quantity");
		
		$this->cart->sort_clean();
		if($this->cart->is_valid_receipt($barcode_scan_data) && $this->cart->get_mode()=='sale')
		{
			$this->_edit_or_suspend_sale($barcode_scan_data);
			$this->_reload();
			return;
		}
		$this->cart->process_barcode_scan($barcode_scan_data,array('quantity' => $quantity,'run_price_rules' => TRUE));
		$this->cart->save();
		$this->_reload();
	}
	
	private function _edit_or_suspend_sale($receipt_sale_id)
	{
		
		if (!$this->Employee->has_module_action_permission('sales', 'edit_sale', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			$data['error']=lang('common_error');
			$this->_reload($data);
			return;
		}
				
		$pieces = explode(' ',$receipt_sale_id);
		
		if(count($pieces)==2 && strtolower($pieces[0]) == strtolower($this->config->item('sale_prefix') ? $this->config->item('sale_prefix') : 'POS'))
		{
			$sale_id = $pieces[1];
		}
		else
		{
			$sale_id = $receipt_sale_id;
		}
		
		$this->cart->destroy();
		$this->cart = PHPPOSCartSale::get_instance_from_sale_id($sale_id,'sale', TRUE);
		
		$this->cart->save();
	}
	
	public function edit_item_variation($line)
	{
		$this->cart->was_last_edit_quantity = false;
		
		if ($item = $this->cart->get_item($line))
		{
			$variation_id = $this->input->post('value');
			$item->variation_id = $variation_id;
			$this->load->model('Item_variations');
			$item->variation_name = $this->Item_variations ->get_variation_name($variation_id);
			
			//Get new price when switching variations
			$item->unit_price = $item->get_price_for_item();
			$cur_item_variation_info = $this->Item_variations->get_info($item->variation_id);
			$cur_item_variation_location_info = $this->Item_variation_location->get_info($item->variation_id);
			
			if ($cur_item_variation_location_info->cost_price)
			{
			 $item->cost_price = $cur_item_variation_location_info->cost_price;				
			}
			elseif ($cur_item_variation_info->cost_price)
			{
				 $item->cost_price = $cur_item_variation_info->cost_price;
			}

			if ($cur_item_variation_location_info->unit_price)
			{
				$item->regular_price = $cur_item_variation_location_info->unit_price;				
			}
			elseif ($cur_item_variation_info->unit_price)
			{
				 $item->regular_price = $cur_item_variation_info->unit_price;
			}

			$this->cart->save();
		}
		
		$this->_reload();
	}
	
	function edit_line_total($line)
	{
		
		
		$this->cart->was_last_edit_quantity = false;
		
		$data = array();
		$item = $this->cart->get_item($line);
		//Have a copy of item before we change so we can revert
		$item_before_edit = clone $item;
		
		$total =$this->input->post('value');
		
		if ($total < 0 && !$this->Employee->has_module_action_permission('sales', 'process_returns', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			$data['error']=lang('sales_not_allowed_returns');
			$this->_reload($data);
			return;
		}
		
		$item->unit_price = -1*((100*$total)/($item->quantity*($item->discount-100)));
		$item->has_edit_price = TRUE;
		
		$can_override_price_adjustments = $this->Employee->get_logged_in_employee_info()->override_price_adjustments;
		
		$max = $this->cart->get_item($line)->max_edit_price;
		$min = $this->cart->get_item($line)->min_edit_price;

		if(!$can_override_price_adjustments && isset($min) && floatval($item->unit_price) < floatval($min))
		{
			$item->unit_price = $min;
			$data['warning'] = lang('sales_could_not_set_item_price_bellow_min')." ".to_currency($min);
		}

		if(!$can_override_price_adjustments && isset($max) && floatval($item->unit_price) > floatval($max))
		{
			$item->unit_price = $max;
			$data['warning'] = lang('sales_could_not_set_item_price_above_max')." ".to_currency($max);
		}
		
		$can_edit = TRUE;
		
		if ($item->below_cost_price())
		{
			if ($this->config->item('do_not_allow_below_cost'))
			{
				$can_edit = FALSE;
				$data['error'] = lang('sales_selling_item_below_cost');
			}
			else
			{
				$data['warning'] = lang('sales_selling_item_below_cost');
			}
		}
		
		//Revert back to previous item
		if (!$can_edit)
		{
			$item->unit_price = $item_before_edit->unit_price;
			
		}
		else
		{
			$params = array('line' => $line);
			$this->cart->do_price_rules($params);
		}
		
		
		
		$this->cart->save();
		$this->_reload($data);
	}
	
	function edit_item($line, $sub_line = 0)
	{
		$can_override_price_adjustments = $this->Employee->get_logged_in_employee_info()->override_price_adjustments;
		$this->cart->was_last_edit_quantity = false;
		
		$data= array();

		if($this->input->post("name"))
		{
			$variable = $this->input->post("name");
			$$variable = $this->input->post("value");
		}
		
		//Do edit fist... we can revert at the end if we aren't allowed to edit
		$item = $this->cart->get_item($line);
		$this->cart->sort_clean();
		
		if(!$item)
		{
			$this->_reload($data);
			return;
		}
				
		if ($variable == 'quantity' && $quantity < 0 && !$this->Employee->has_module_action_permission('sales', 'process_returns', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			$data['error']=lang('sales_not_allowed_returns');
			$this->_reload($data);
			return;
		}
		
		if ($variable == 'unit_price' && $unit_price < 0 && !$this->Employee->has_module_action_permission('sales', 'process_returns', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			$data['error']=lang('sales_not_allowed_returns');
			$this->_reload($data);
			return;
		}

		if ($variable == 'modifier_price' && $modifier_price < 0 && !$this->Employee->has_module_action_permission('sales', 'process_returns', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			$data['error']=lang('sales_not_allowed_returns');
			$this->_reload($data);
			return;
		}
		
		//Have a copy of item before we change so we can revert
		$item_before_edit = clone $item;
		
		//Do change first
		try
		{
			if($variable == "modifier_price"){
				$modifier_item_id = $sub_line;

				$modifier_item_info = $this->Item_modifier->get_modifier_item_info($modifier_item_id);
				$display_name = to_currency($$variable).': '.$modifier_item_info['modifier_name'].' > '.$modifier_item_info['modifier_item_name'];
				$item->modifier_items[$modifier_item_id]['display_name'] = $display_name;
				$item->modifier_items[$modifier_item_id]['unit_price'] = $$variable;

			}else{
				$item->$variable = $$variable;
			}
			
			if ($variable == 'quantity')
			{
				$this->cart->was_last_edit_quantity = true;
			}
			
			if ($variable == 'unit_price')
			{
				$item->has_edit_price = TRUE;
			}
			
			if ($variable == 'tier_id')
			{
				$this->load->model('Tier');
				$info = $this->Tier->get_info($item->tier_id);
				$item->tier_name = $info->name;
				if(property_exists($item,'item_kit_id'))
				{
					$item->unit_price = $item->get_price_for_item_kit();		
				}
				else
				{
					$item->unit_price = $item->get_price_for_item();		
				}
			}
			
			if($variable == 'quantity_unit_id')
			{				
				$qui = $this->Item->get_quantity_unit_info($$variable);
				
				$cur_item_info = $this->Item->get_info($item->item_id);
				$cur_item_location_info = $this->Item_location->get_info($item->item_id);
	
				$this->load->model('Item_variations');
				$this->load->model('Item_variation_location');
	
				$cur_item_variation_info = $this->Item_variations->get_info($item->variation_id);
				$cur_item_variation_location_info = $this->Item_variation_location->get_info($item->variation_id);
				
				if ($qui !== NULL)
				{
					$item->quantity_unit_quantity = $qui->unit_quantity;
				
					if ($qui->unit_price !== NULL)
					{
						$item->unit_price = $qui->unit_price;
					}
					else
					{
						$item->unit_price = $item->get_price_for_item();		
					}
					
					$item->regular_price = $item->unit_price;

					if ($qui->cost_price !== NULL)
					{
						$item->cost_price = $qui->cost_price;
					}
					else
					{
						if (($cur_item_variation_info && $cur_item_variation_info->cost_price) || ($cur_item_variation_location_info&& $cur_item_variation_location_info->cost_price))
						{
							$item->cost_price = $cur_item_variation_location_info->cost_price ? $cur_item_variation_location_info->cost_price : $cur_item_variation_info->cost_price;
						}
						else
						{
							$item->cost_price = ($cur_item_location_info && $cur_item_location_info->cost_price) ? $cur_item_location_info->cost_price : $cur_item_info->cost_price;
						}
						
						$item->cost_price = $item->cost_price*$item->quantity_unit_quantity;		
					}
					
					$this->cart->determine_new_prices_for_tier_change();
										
				}
				else //Didn't select quantity unit; reset to be empty
				{
					$item->quantity_unit_quantity = NULL;
					$item->$variable = NULL;
					$item->unit_price = $item->get_price_for_item();	
					$item->regular_price = $item->unit_price;
										
					if (($cur_item_variation_info && $cur_item_variation_info->cost_price) || ($cur_item_variation_location_info && $cur_item_variation_location_info->cost_price))
					{
						$item->cost_price = $cur_item_variation_location_info->cost_price ? $cur_item_variation_location_info->cost_price : $cur_item_variation_info->cost_price;
					}
					else
					{
						$item->cost_price = ($cur_item_location_info && $cur_item_location_info->cost_price) ? $cur_item_location_info->cost_price : $cur_item_info->cost_price;
					}
				}
			}
			
		}
		catch(Exception $e)
		{
			$this->_reload($data);
			return;
		}
		
		if(isset($serialnumber))
		{
			$serial_number_price = $this->Item_serial_number->get_price_for_serial($serialnumber);
			if ($serial_number_price !== FALSE)
			{
				$item->unit_price = $serial_number_price;
			}

			$serial_number_cost_price = $this->Item_serial_number->get_cost_price_for_serial($serialnumber);
			if ($serial_number_cost_price !== FALSE)
			{
				$item->cost_price = $serial_number_cost_price;
			}
		}
		
		
		if (isset($discount) && $discount !== NULL)
		{
			if($discount == '')
			{
				$item->discount = 0;
			}
			
			$max_discount = $this->cart->get_item($line)->max_discount_percent;
			
			//Try employee
			if (!$can_override_price_adjustments && $max_discount === NULL)
			{
				$max_discount = $this->Employee->get_logged_in_employee_info()->max_discount_percent;
			}
			
			//Try globally
			if (!$can_override_price_adjustments && $max_discount === NULL)
			{
				$max_discount = $this->config->item('max_discount_percent') !== '' ? $this->config->item('max_discount_percent') : NULL;
			}
			
			if(!$can_override_price_adjustments & $max_discount!==NULL && floatval($discount) > floatval($max_discount))
			{
				$item->discount = $max_discount;
				$data['warning'] = lang('sales_could_not_discount_item_above_max')." ".to_percent($max_discount);
			}

		}

		$can_edit = TRUE;

		if ($this->config->item('do_not_allow_out_of_stock_items_to_be_sold'))
		{
			if (isset($quantity))
			{
				$current_item = $this->cart->get_item($line);

				if ($this->cart->get_mode() !='estimate' && $current_item->out_of_stock())
				{
					$can_edit = FALSE;
				}
			}			

			if (!$can_edit)
			{
				$data['error']=lang('sales_unable_to_add_item_out_of_stock');
			}
		}
		
		if ($item->only_integer && $item->quantity != (int)$item->quantity)
		{
			$data['error']=lang('common_must_be_whole_number');
			$can_edit = FALSE;
		}		
		

		if($can_edit && isset($unit_price))
		{
			$max = $this->cart->get_item($line)->max_edit_price;
			$min = $this->cart->get_item($line)->min_edit_price;

			if(!$can_override_price_adjustments && isset($min) && floatval($unit_price) < floatval($min))
			{
				$item->unit_price = $min;
				$data['warning'] = lang('sales_could_not_set_item_price_bellow_min')." ".to_currency($min);
			}

			if(!$can_override_price_adjustments && isset($max) && floatval($unit_price) > floatval($max))
			{
				$item->unit_price = $max;
				$data['warning'] = lang('sales_could_not_set_item_price_above_max')." ".to_currency($max);
			}
		}

		if($this->cart->get_item($line)->out_of_stock() && !$this->config->item('do_not_allow_out_of_stock_items_to_be_sold'))
		{
			$data['warning'] = lang('sales_quantity_less_than_zero');
		}
		
		if ($item->below_cost_price())
		{
			if ($this->config->item('do_not_allow_below_cost'))
			{
				$can_edit = FALSE;
				$data['error'] = lang('sales_selling_item_below_cost');
			}
			else
			{
				$data['warning'] = lang('sales_selling_item_below_cost');
			}
		}
		

		//Revert back to previous item
		if (!$can_edit)
		{
			if($variable == "modifier_price"){
				$modifier_item_id = $sub_line;
				$item->modifier_items[$modifier_item_id]['unit_price'] = $item_before_edit->modifier_items[$modifier_item_id]['unit_price'];
			}else{
				$item->$variable = $item_before_edit->$variable;
			}
			
			if ($variable == 'quantity_unit_id')
			{
				$item->unit_price = $item_before_edit->unit_price;
				$item->cost_price = $item_before_edit->cost_price;
			}
		}
		else
		{
			$params = array('line' => $line);
			$this->cart->do_price_rules($params);
		}
		
		
		//Reset so we don't break price rules when adding an item after an edit
		$this->cart->was_last_edit_quantity = false;
		
		$this->cart->save();
		$this->_reload($data);
	}
	
	function delete_item($item_line)
	{
		if ($this->cart->get_item($item_line) !== FALSE)
		{		
			if(!$this->cart->is_price_rule_discount_line($item_line))
			{
				$cart_item = $this->cart->get_item($item_line);
			
				if(!$this->config->item('do_not_group_same_items')  && !$cart_item->is_serialized)
				{
					if(property_exists($cart_item,'item_kit_id'))
					{
						$price_rule_params = array('item_kit' => $cart_item);
					} 
					else 
					{
						$price_rule_params = array('item' => $cart_item);
					}
				}
			}
		
			$this->cart->delete_item($item_line);
			
			if(isset($price_rule_params))
			{
				$price_rule_params['delete'] = TRUE;
				$this->cart->do_price_rules($price_rule_params);
			}
			
			$this->cart->save();
			
		}
		$this->_reload();
	}

	function delete_customer()
	{
		$this->cart->customer_id = NULL;
		$this->cart->previous_tier_id = $this->cart->selected_tier_id;
		$this->cart->selected_tier_id = 0;
		
		if($this->cart->previous_tier_id!=$this->cart->selected_tier_id)
		{
			$this->cart->determine_new_prices_for_tier_change();
		}
		
		$this->cart->delete_payment($this->cart->get_payment_ids(lang('common_points')));
		
		$this->cart->save();
		$this->_reload();
	}
			
	function start_cc_processing()
	{
		if ($this->config->item('test_mode'))
		{
			$this->_reload(array('error' => lang('common_in_test_mode')), false);
			return;
		}
		
		if ($this->config->item('do_not_allow_item_with_variations_to_be_sold_without_selecting_variation') && !$this->cart->do_all_variation_items_have_variation_selected())
		{
			$this->_reload(array('error' => lang('common_you_did_not_select_variations_for_applicable_variation_items')), false);
			return;
		}		
		
		$cc_amount = round($this->cart->get_payment_amount(lang('common_credit')),2);
		$total = round($this->cart->get_total(),2);		
		
		if ($total >=0 && $cc_amount > $total)
		{
			$this->_reload(array('error' => lang('sales_credit_card_payment_is_greater_than_total_cannot_complete')), false);
		}
		elseif ($total < 0 && $cc_amount < $total)
		{
			$this->_reload(array('error' => lang('sales_cannot_refund_more_than_sale_total')), false);
		}
		else
		{
			$credit_card_processor = $this->_get_cc_processor();
			
			if ($credit_card_processor)
			{
				$credit_card_processor->start_cc_processing();
			}
			else
			{
				$this->_reload(array('error' => lang('sales_credit_card_processing_is_down')), false);
				return;
			}
		}		
	}
	
	public function start_cc_processing_coreclear2()
	{
		$credit_card_processor = $this->_get_cc_processor();
		
		if ($credit_card_processor)
		{
			$credit_card_processor->do_start_cc_processing();
		}
		else
		{
			$this->_reload(array('error' => lang('sales_credit_card_processing_is_down')), false);
			return;
		}
	}
	
	
	public function start_cc_processing_card_connect()
	{
		$credit_card_processor = $this->_get_cc_processor();
		
		if ($credit_card_processor)
		{
			$credit_card_processor->do_start_cc_processing();
		}
		else
		{
			$this->_reload(array('error' => lang('sales_credit_card_processing_is_down')), false);
			return;
		}
	}
	
	function start_cc_processing_trans_cloud()
	{
		$credit_card_processor = $this->_get_cc_processor();
		
		if ($credit_card_processor)
		{
			$credit_card_processor->do_start_cc_processing();
		}
		else
		{
			$this->_reload(array('error' => lang('sales_credit_card_processing_is_down')), false);
			return;
		}
	}
	
	function finish_cc_processing()
	{
		$credit_card_processor = $this->_get_cc_processor();
		if ($credit_card_processor)
		{
			$credit_card_processor->finish_cc_processing();			
		}
		else
		{
			$this->_reload(array('error' => lang('sales_credit_card_processing_is_down')), false);
			return;
		}
	}
	
	function finish_cc_processing_saved_card()
	{
		$credit_card_processor = $this->_get_cc_processor();
	
		if ($credit_card_processor)
		{
			$credit_card_processor->finish_cc_processing_saved_card();
		}
		else
		{
			$this->_reload(array('error' => lang('sales_credit_card_processing_is_down')), false);
			return;
		}
	}
	
	function get_emv_ebt_balance($type="EBT")
	{
		$credit_card_processor = $this->_get_cc_processor();
		if ($credit_card_processor && method_exists($credit_card_processor,'get_emv_ebt_balance'))
		{
			$credit_card_processor->get_emv_ebt_balance($type);
		}
		else
		{
			$this->_reload(array('error' => lang('sales_credit_card_processing_is_down')), false);
			return;
		}
		
	}
	function reset_pin_pad()
	{
		$credit_card_processor = $this->_get_cc_processor();
	
		if ($credit_card_processor && method_exists($credit_card_processor,'pad_reset'))
		{
			$credit_card_processor->pad_reset();
		}
		else
		{
			$this->_reload(array('error' => lang('sales_credit_card_processing_is_down')), false);
			return;
		}
		
	}
	
	function cancel_cc_processing()
	{
		$credit_card_processor = $this->_get_cc_processor();
		
		if ($credit_card_processor)
		{
			$credit_card_processor->cancel_cc_processing();
		}
		else
		{
			$this->_reload(array('error' => lang('sales_credit_card_processing_is_down')), false);
			return;
		}
	}
	
	function set_sequence_no_emv()
	{
		if ($this->input->post('sequence_no'))
		{
			$this->session->set_userdata('sequence_no',$this->input->post('sequence_no'));
		}
	}
	
	function declined()
	{
		$this->load->model('Register_cart');
		
		$customer_id=$this->cart->customer_id;
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		$sold_by_employee_id=$this->cart->sold_by_employee_id;
		$emp_info=$this->Employee->get_info($employee_id);
		$sale_emp_info=$this->Employee->get_info($sold_by_employee_id);
		
		$data['is_sale'] = FALSE;
		$data['total']=$this->cart->get_total();
		$data['receipt_title']= $this->config->item('override_receipt_title') ? $this->config->item('override_receipt_title') : lang('sales_receipt');
		$data['sales_card_statement']= $this->config->item('override_signature_text') ? $this->config->item('override_signature_text') : lang('sales_card_statement','',array(),TRUE);
		
		$data['transaction_time'] = date(get_date_format().' '.get_time_format());
		$data['payments']=$this->cart->get_payments();
		$data['register_name'] = $this->Register->get_register_name($this->Employee->get_logged_in_employee_current_register_id());
		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name.($sold_by_employee_id && $sold_by_employee_id != $employee_id ? '/'. $sale_emp_info->first_name.' '.$sale_emp_info->last_name: '');
		
		if($customer_id)
		{
			$cust_info=$this->Customer->get_info($customer_id);
			$data['customer']=$cust_info->first_name.' '.$cust_info->last_name.($cust_info->account_number==''  ? '':' - '.$cust_info->account_number);
			$data['customer_company']= $cust_info->company_name;
			$data['customer_address_1'] = $cust_info->address_1;
			$data['customer_address_2'] = $cust_info->address_2;
			$data['customer_city'] = $cust_info->city;
			$data['customer_state'] = $cust_info->state;
			$data['customer_zip'] = $cust_info->zip;
			$data['customer_country'] = $cust_info->country;
			$data['customer_phone'] = $cust_info->phone_number;
			$data['customer_email'] = $cust_info->email;
		}
		
		$data['auth_code'] = $this->session->userdata('auth_code') ? $this->session->userdata('auth_code') : '';
		$data['ref_no'] = $this->session->userdata('ref_no') ? $this->session->userdata('ref_no') : '';
		$data['entry_method'] = $this->session->userdata('entry_method') ? $this->session->userdata('entry_method') : '';
		$data['aid'] = $this->session->userdata('aid') ? $this->session->userdata('aid') : '';
		$data['tvr'] = $this->session->userdata('tvr') ? $this->session->userdata('tvr') : '';
		$data['iad'] = $this->session->userdata('iad') ? $this->session->userdata('iad') : '';
		$data['tsi'] = $this->session->userdata('tsi') ? $this->session->userdata('tsi') : '';
		$data['arc'] = $this->session->userdata('arc') ? $this->session->userdata('arc') : '';
		$data['cvm'] = $this->session->userdata('cvm') ? $this->session->userdata('cvm') : '';
		$data['tran_type'] = $this->session->userdata('tran_type') ? $this->session->userdata('tran_type') : '';
		$data['application_label'] = $this->session->userdata('application_label') ? $this->session->userdata('application_label') : '';
		$data['masked_account'] = $this->session->userdata('masked_account') ? $this->session->userdata('masked_account') : '';
		$data['text_response'] = $this->session->userdata('text_response') ? $this->session->userdata('text_response') : '';
		$this->cart->clear_cc_info();
		$this->load->view("sales/receipt_decline",$data);
	}
	
	function complete()
	{
		if (!$this->Employee->has_module_action_permission('sales', 'complete_sale', $this->Employee->get_logged_in_employee_info()->person_id))
		{		
			$this->_reload(array('error' => lang('sales_you_do_not_have_permission_to_complete_sales')), false);
			return;				
		}
		
		$email_send = false;
		$sms_receipt = false;

		if ($this->config->item('sort_receipt_column'))
		{
			$this->cart->sort_items($this->config->item('sort_receipt_column'));
		}

		$current_location = $this->Employee->get_logged_in_employee_current_location_id();
		for ($k = 1; $k <= NUMBER_OF_PEOPLE_CUSTOM_FIELDS; $k++) { 
			$custom_field = $this->Sale->get_custom_field($k);
			if ($custom_field !== FALSE) {
				if($this->Sale->get_custom_field($k,'required') && in_array($current_location,$this->Sale->get_custom_field($k,'locations')) && !$this->cart->{"custom_field_${k}_value"}){
					$this->_reload(array('error' => $custom_field.' '.lang('is_required')), false);
					return;
				}
			}
		}

		$data = $this->_get_shared_data();
		
		if($this->config->item('do_not_allow_sales_with_zero_value')){
			if($data['total'] == 0){
				$this->session->set_flashdata('error_if_total_is_zero', lang('common_error_if_total_is_zero'));
				redirect('sales');
			};
		}
	
		if ($this->cart->get_mode() == 'estimate')
		{
			$data['sale_type'] = lang('common_estimate');
		}
		$this->load->helper('sale');
		$this->lang->load('deliveries');
		
		if ($this->config->item('do_not_allow_item_with_variations_to_be_sold_without_selecting_variation') && !$this->cart->do_all_variation_items_have_variation_selected())
		{
			$this->_reload(array('error' => lang('common_you_did_not_select_variations_for_applicable_variation_items')), false);
			return;
		}
		
		if ($this->cart->get_mode() != 'return' && $this->cart->get_mode() != 'estimate' && $this->config->item('do_not_allow_out_of_stock_items_to_be_sold'))
		{
			foreach($this->cart->get_items() as $item)
			{
				if($item->out_of_stock())
				{
					$this->_reload(array('error' => lang('sales_one_or_more_out_of_stock_items')), false);
					return;
				}	
			}
		}
		
		///Make sure we have actually processed a transaction before compelting sale
		if (is_sale_integrated_cc_processing($this->cart) && !$this->session->userdata('CC_SUCCESS'))
		{
			$this->_reload(array('error' => lang('sales_credit_card_processing_is_down')), false);
			return;
		}
		
		
		if (empty($data['cart_items']))
		{
			redirect('sales');
		}
			
		if (!$this->_payments_cover_total())
		{
			$this->_reload(array('error' => lang('sales_cannot_complete_sale_as_payments_do_not_cover_total')), false);
			return;
		}
		
		
		$tier_id = $this->cart->selected_tier_id;
		$tier_info = $this->Tier->get_info($tier_id);
		$exchange_rate = $this->cart->get_exchange_rate() ? $this->cart->get_exchange_rate() : 1;
		
		$data['tier'] = $tier_info->name;
		$data['register_name'] = $this->Register->get_register_name($this->Employee->get_logged_in_employee_current_register_id());
		$data['is_sale'] = TRUE;
		$data['receipt_title']= $this->config->item('override_receipt_title') ? $this->config->item('override_receipt_title') : ( !$this->cart->suspended ? lang('sales_receipt') : '');
		$data['sales_card_statement']= $this->config->item('override_signature_text') ? $this->config->item('override_signature_text') : lang('sales_card_statement','',array(),TRUE);
		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;
		$customer_id=$this->cart->customer_id;
		$sold_by_employee_id=$this->cart->sold_by_employee_id;
		$emp_info=$this->Employee->get_info($employee_id);
		$sale_emp_info=$this->Employee->get_info($sold_by_employee_id);
		$data['is_sale_cash_payment'] = $this->cart->has_cash_payment();
		$data['amount_change']=$this->cart->get_amount_due() * -1;
		$this->session->set_userdata('amount_change', $data['amount_change']);
		
		$store_account_in_all_languages = get_all_language_values_for_key('common_store_account','common');
		
		$data['balance'] = 0;
		//Add up balances for all languages
		foreach($store_account_in_all_languages as $store_account_lang)
		{
				//Thanks Mike for math help on how to convert exchange rate back to get correct balance
				$data['balance']+= $this->cart->get_payment_amount($store_account_lang)*pow($exchange_rate,-1);
		}

		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name.($sold_by_employee_id && $sold_by_employee_id != $employee_id ? '/'. $sale_emp_info->first_name.' '.$sale_emp_info->last_name: '');
		$data['employee_firstname']=$emp_info->first_name.($sold_by_employee_id && $sold_by_employee_id != $employee_id ? '/'. $sale_emp_info->first_name: '');
		$data['ref_no'] = '';
		$data['auth_code'] = '';
		$data['discount_exists'] = $this->_does_discount_exists($data['cart_items']);
		$data['can_email_receipt'] = !$this->cart->email_receipt;
		$data['can_sms_receipt'] = !$this->cart->sms_receipt;
		$masked_account = $this->session->userdata('masked_account') ? $this->session->userdata('masked_account') : '';
		$card_issuer = $this->session->userdata('card_issuer') ? $this->session->userdata('card_issuer') : '';
		$auth_code = $this->session->userdata('auth_code') ? $this->session->userdata('auth_code') : '';
		$ref_no = $this->session->userdata('ref_no') ? $this->session->userdata('ref_no') : '';
		$cc_token = $this->session->userdata('cc_token') ? $this->session->userdata('cc_token') : '';
		$acq_ref_data = $this->session->userdata('acq_ref_data') ? $this->session->userdata('acq_ref_data') : '';
		$process_data = $this->session->userdata('process_data') ? $this->session->userdata('process_data') : '';
		$entry_method = $this->session->userdata('entry_method') ? $this->session->userdata('entry_method') : '';
		$aid = $this->session->userdata('aid') ? $this->session->userdata('aid') : '';
		$tvr = $this->session->userdata('tvr') ? $this->session->userdata('tvr') : '';
		$iad = $this->session->userdata('iad') ? $this->session->userdata('iad') : '';
		$tsi = $this->session->userdata('tsi') ? $this->session->userdata('tsi') : '';
		$arc = $this->session->userdata('arc') ? $this->session->userdata('arc') : '';
		$cvm = $this->session->userdata('cvm') ? $this->session->userdata('cvm') : '';
		$tran_type = $this->session->userdata('tran_type') ? $this->session->userdata('tran_type') : '';
		$application_label = $this->session->userdata('application_label') ? $this->session->userdata('application_label') : '';
		
		if ($ref_no)
		{
			if (count($this->cart->get_payment_ids(lang('common_credit'))) || count($this->cart->get_payment_ids(lang('common_ebt'))) || count($this->cart->get_payment_ids(lang('common_ebt_cash'))))
			{
				$cc_payment_id = current($this->cart->get_payment_ids(lang('common_credit')));
				if ($cc_payment_id !== FALSE)
				{
					$cc_payment = $data['payments'][$cc_payment_id];
					$this->cart->edit_payment($cc_payment_id, array('payment_type' => $cc_payment->payment_type, 'payment_amount' => $cc_payment->payment_amount,'payment_date' => $cc_payment->payment_date, 'truncated_card' => $masked_account, 'card_issuer' => $card_issuer,'auth_code' => $auth_code, 'ref_no' => $ref_no, 'cc_token' => $cc_token, 'acq_ref_data' => $acq_ref_data, 'process_data' => $process_data, 'entry_method' => $entry_method, 'aid' => $aid, 'tvr' => $tvr, 'iad' => $iad, 'tsi' => $tsi,'arc' => $arc, 'cvm' => $cvm,'tran_type' => $tran_type,'application_label' => $application_label));
				}
				
				$ebt_payment_id = current($this->cart->get_payment_ids(lang('common_ebt')));
				if ($ebt_payment_id !== FALSE)
				{
					$ebt_payment = $data['payments'][$ebt_payment_id];
					
					$ebt_voucher_no = $this->cart->ebt_voucher_no;
					$ebt_auth_code = $this->cart->ebt_auth_code;
						
					$this->cart->edit_payment($ebt_payment_id, array('payment_type' => $ebt_payment->payment_type, 'payment_amount' => $ebt_payment->payment_amount,'payment_date' => $ebt_payment->payment_date, 'truncated_card' => $masked_account, 'card_issuer' => $card_issuer,'auth_code' => $auth_code, 'ref_no' => $ref_no, 'cc_token' => $cc_token, 'acq_ref_data' => $acq_ref_data, 'process_data' => $process_data, 'entry_method' => $entry_method, 'aid' => $aid, 'tvr' => $tvr, 'iad' => $iad, 'tsi' => $tsi,'arc' => $arc, 'cvm' => $cvm,'tran_type' => $tran_type,'application_label' => $application_label,'ebt_voucher_no' => $ebt_voucher_no,'ebt_auth_code' => $ebt_auth_code));
					
					$data['ebt_balance'] = $this->session->userdata('ebt_balance');
					
				}
				
				$ebt_cash_payment_id = current($this->cart->get_payment_ids(lang('common_ebt_cash')));
				if ($ebt_cash_payment_id !== FALSE)
				{
					$ebt_cash_payment = $data['payments'][$ebt_cash_payment_id];
					$this->cart->edit_payment($ebt_cash_payment_id, array('payment_type' => $ebt_cash_payment->payment_type, 'payment_amount' => $ebt_cash_payment->payment_amount,'payment_date' => $ebt_cash_payment->payment_date, 'truncated_card' => $masked_account, 'card_issuer' => $card_issuer,'auth_code' => $auth_code, 'ref_no' => $ref_no, 'cc_token' => $cc_token, 'acq_ref_data' => $acq_ref_data, 'process_data' => $process_data, 'entry_method' => $entry_method, 'aid' => $aid, 'tvr' => $tvr, 'iad' => $iad, 'tsi' => $tsi,'arc' => $arc, 'cvm' => $cvm,'tran_type' => $tran_type,'application_label' => $application_label));
					
					$data['ebt_balance'] = $this->session->userdata('ebt_balance');
					
				}
				
				//Make sure our payments has the latest change to masked_account
				$data['payments'] = $this->cart->get_payments();
			}
		}
				
		$old_date = $this->cart->get_previous_receipt_id()  ? $this->Sale->get_info($this->cart->get_previous_receipt_id())->row_array() : false;
		$old_date=  $old_date ? date(get_date_format().' '.get_time_format(), strtotime($old_date['sale_time'])) : date(get_date_format().' '.get_time_format());
	
		
		$suspended_change_sale_id=$this->cart->get_previous_receipt_id();
				
		$data['store_account_payment'] = $this->cart->get_mode() == 'store_account_payment' ? 1 : 0;
		$data['is_purchase_points'] = $this->cart->get_mode() == 'purchase_points' ? 1 : 0;
		//If we have a suspended sale, update the date for the sale
		$data['change_cart_date'] = FALSE;
		
		if ($this->cart->change_date_enable)
		{
			$data['change_cart_date'] = $this->cart->change_cart_date;
			$this->cart->change_date_enable = TRUE;
			$this->cart->change_cart_date = $data['change_cart_date'];
		}
		elseif ($this->cart->get_previous_receipt_id() && $this->cart->suspended && $this->config->item('change_sale_date_when_completing_suspended_sale'))
		{
			$data['change_cart_date'] = date('Y-m-d H:i:s');
			$this->cart->change_date_enable = TRUE;
			$this->cart->change_cart_date = $data['change_cart_date'];
		}

				
		$data['transaction_time']= $this->cart->change_date_enable ?  date(get_date_format().' '.get_time_format(), strtotime($this->cart->change_cart_date)) : $old_date;
		
		$this->cart->suspended = 0;

		//SAVE sale to database
		$sale_id_raw = $this->Sale->save($this->cart); 
		$saved_sale_info = $this->Sale->get_info($sale_id_raw)->row_array();
		if (isset($saved_sale_info['signature_image_id']))
		{
			$data['signature_file_id'] = $saved_sale_info['signature_image_id'];
		}
		
		$tip_amount = $this->session->userdata('tip_amount');
			
		if ($tip_amount)
		{
			$sale_data = array('tip' => $tip_amount);
			$this->Sale->update($sale_data, $sale_id_raw);
		}
		
		//Set exchange details in so receipt has correct info on it (Sale->save clears it out but we need for receipt)
		if ($data['exchange_name'])
		{
			$this->cart->set_exchange_details($this->Sale->get_exchange_details($sale_id_raw));
			for($k=0;$k<count($data['payments']);$k++)
			{
				$data['payments'][$k]->payment_amount = $data['payments'][$k]->payment_amount*$exchange_rate;
			}
			
		}
		
		$data['sale_id']=$this->config->item('sale_prefix').' '.$sale_id_raw;
		$data['sale_id_raw']=$sale_id_raw;
		
		$data['disable_loyalty'] = 0;
		
		if($customer_id)
		{
			$cust_info=$this->Customer->get_info($customer_id);
			$data['customer']=$cust_info->first_name.' '.$cust_info->last_name.($cust_info->account_number==''  ? '':' - '.$cust_info->account_number);
			$data['customer_company']= $cust_info->company_name;
			$data['customer_address_1'] = $cust_info->address_1;
			$data['customer_address_2'] = $cust_info->address_2;
			$data['customer_city'] = $cust_info->city;
			$data['customer_state'] = $cust_info->state;
			$data['customer_zip'] = $cust_info->zip;
			$data['customer_country'] = $cust_info->country;
			$data['customer_phone'] = $cust_info->phone_number;
			$data['customer_email'] = $cust_info->email;			
			$data['customer_points'] = $cust_info->points;			
		  	$data['sales_until_discount'] = ($this->config->item('number_of_sales_for_discount') ? $this->config->item('number_of_sales_for_discount') : 0) - $cust_info->current_sales_for_discount;
 			$data['disable_loyalty'] = $cust_info->disable_loyalty;
		}
				
		if($customer_id )
		{
			$cust_info=$this->Customer->get_info($customer_id);
			if($this->config->item('customers_store_accounts'))
			{	
				$data['customer_balance_for_sale'] = $cust_info->balance;
			}
		}
		
		
		if ($data['sale_id'] == $this->config->item('sale_prefix').' -1')
		{
			$data['error_message'] = '';
			$this->load->helper('sale');
			if (is_sale_integrated_cc_processing($this->cart))
			{
				$this->cart->change_credit_card_payments_to_partial();
				$data['error_message'].='<span class="text-success">'.lang('sales_credit_card_transaction_completed_successfully').'. </span><br /<br />';
			}
			$data['error_message'] .= '<span class="text-danger">'.lang('sales_transaction_failed').'</span>';
			$data['error_message'] .= '<br /><br />'.anchor('sales','&laquo; '.lang('sales_register'));
			$data['error_message'] .= '<br /><br />'.anchor('sales/complete',lang('common_try_again'). ' &raquo;');
		}
		else
		{			

			$this->session->unset_userdata('scroll_to');
			
			if ($this->session->userdata('CC_SUCCESS'))
			{
				$credit_card_processor = $this->_get_cc_processor();
		
				if ($credit_card_processor)
				{
					$cc_processor_class_name = strtoupper(get_class($credit_card_processor));
					$cc_processor_parent_class_name = strtoupper(get_parent_class($credit_card_processor));
			
					if ($cc_processor_parent_class_name == 'DATACAPUSBPROCESSOR')
					{
						$data['reset_params'] = $credit_card_processor->get_emv_pad_reset_params();
					}
					
					if ($cc_processor_parent_class_name == 'DATACAPTRANSCLOUDPROCESSOR')
					{
						$data['trans_cloud_reset'] = TRUE;
					}
				}		
			}
			
			
			if ($this->cart->email_receipt && !empty($cust_info->email))
			{
				$email_send = true;
			}

			if ($this->cart->sms_receipt && !empty($cust_info->phone_number))
			{
				$sms_receipt = true;
			}
			
		}
		
		if($this->cart->get_has_delivery())
		{
			$data['delivery_person_info'] = $this->cart->get_delivery_person_info();
						
			$data['delivery_info'] = $this->cart->get_delivery_info();
		}
		
		if($email_send === true || (isset($cust_info) && $cust_info->auto_email_receipt === 1)){
			
			if($this->config->item('enable_pdf_receipts')){
				$receipt_data = $this->load->view("sales/receipt_html", $data, true);
				
				if($this->config->item('receipt_download_filename_prefix')){
					$filename = $this->config->item('receipt_download_filename_prefix').'_receipt_'.$sale_id_raw.'.pdf';
				}else{
					$filename = 'receipt_'.$sale_id_raw.'.pdf';
				}
				$this->load->library("m_pdf");
				$pdf_content = $this->m_pdf->generate_pdf($receipt_data);
			}
			
			$this->load->library('email');
			$config['mailtype'] = 'html';
			$this->email->initialize($config);
			$this->email->from($this->Location->get_info_for_key('email') ? $this->Location->get_info_for_key('email') : 'no-reply@mg.phppointofsale.com', $this->config->item('company'));
			$this->email->to($cust_info->email);
			
			if($this->Location->get_info_for_key('cc_email'))
			{
				$this->email->cc($this->Location->get_info_for_key('cc_email'));
			}
			
			if($this->Location->get_info_for_key('bcc_email'))
			{
				$this->email->bcc($this->Location->get_info_for_key('bcc_email'));
			}
			
			$this->email->subject($this->config->item('emailed_receipt_subject') ? $this->config->item('emailed_receipt_subject') : lang('sales_receipt'));
			
			if($this->config->item('enable_pdf_receipts')){
				if(isset($pdf_content) && $pdf_content){
					$this->email->attach($pdf_content, 'attachment', $filename, 'application/pdf');
					$this->email->message(nl2br($this->config->item('pdf_receipt_message')));
				}
			}else{
				$this->email->message($this->load->view("sales/receipt_email",$data, true));	
			}
			$this->email->send();
			$data['email_sent'] = TRUE;
		}

		if($sms_receipt || (isset($cust_info) && $cust_info->always_sms_receipt)){
			$this->Sale->sms_receipt($sale_id_raw);
		}
		
		if ($this->Location->get_info_for_key('email_sales_email'))
		{
			if($this->config->item('enable_pdf_receipts')){
				$receipt_data = $this->load->view("sales/receipt_html", $data, true);
				
				if($this->config->item('receipt_download_filename_prefix')){
					$filename = $this->config->item('receipt_download_filename_prefix').'_receipt_'.$sale_id_raw.'.pdf';
				}else{
					$filename = 'receipt_'.$sale_id_raw.'.pdf';
				}
				$this->load->library("m_pdf");
					$pdf_content = $this->m_pdf->generate_pdf($receipt_data);
				}
			
			$this->load->library('email');
			$config['mailtype'] = 'html';
			$this->email->initialize($config);
			$this->email->from($this->Location->get_info_for_key('email') ? $this->Location->get_info_for_key('email') : 'no-reply@mg.phppointofsale.com', $this->config->item('company'));
			$this->email->to($this->Location->get_info_for_key('email_sales_email'));
			
			if($this->Location->get_info_for_key('cc_email'))
			{
				$this->email->cc($this->Location->get_info_for_key('cc_email'));
			}
			
			if($this->Location->get_info_for_key('bcc_email'))
			{
				$this->email->bcc($this->Location->get_info_for_key('bcc_email'));
			}
			
			$this->email->subject($this->config->item('emailed_receipt_subject') ? $this->config->item('emailed_receipt_subject') : lang('sales_receipt'));
			
			if($this->config->item('enable_pdf_receipts')){
				if(isset($pdf_content) && $pdf_content){
					$this->email->attach($pdf_content, 'attachment', $filename, 'application/pdf');
					$this->email->message(nl2br($this->config->item('pdf_receipt_message')));
				}
			}else{
				$this->email->message($this->load->view("sales/receipt_email",$data, true));	
			}
			$this->email->send();
		}
		
		$this->load->view("sales/receipt",$data);
		
		if ($data['sale_id'] != $this->config->item('sale_prefix').' -1')
		{
			$this->cart->destroy();
			$this->cart->save();
			$this->Appconfig->save('wizard_create_sale',1);				
		}
		
		//We need to reset this data because is already gone when saving sale
		$final_cart_data = array();
		$final_cart_data['subtotal'] = $data['subtotal'];
		$final_cart_data['total'] = $data['total'];
		$final_cart_data['tax'] = $data['total'] - $data['subtotal'];
		$final_cart_data['exchange_rate'] = $data['exchange_rate'];
		$final_cart_data['exchange_rate'] = $data['exchange_rate'];
		$final_cart_data['exchange_name'] = $data['exchange_name'];
		$final_cart_data['exchange_symbol'] = $data['exchange_symbol'];
		$final_cart_data['exchange_symbol_location'] = $data['exchange_symbol_location'];
		$final_cart_data['exchange_number_of_decimals'] = $data['exchange_number_of_decimals'];
		$final_cart_data['exchange_thousands_separator'] = $data['exchange_thousands_separator'];
		$final_cart_data['exchange_decimal_point'] = $data['exchange_decimal_point'];
		//Update cutomer facing display
		$this->Register_cart->set_data($final_cart_data,$this->Employee->get_logged_in_employee_current_register_id());
		$this->Register_cart->add_data(array('can_email' => $data['can_email_receipt'], 'can_sms' => $data['can_sms_receipt'], 'sale_id' => $sale_id_raw),$this->Employee->get_logged_in_employee_current_register_id());		
	}
	
	function download_receipt($sale_id)
	{
		$receipt_cart = PHPPOSCartSale::get_instance_from_sale_id($sale_id);
		if ($this->config->item('sort_receipt_column'))
		{
			$receipt_cart->sort_items($this->config->item('sort_receipt_column'));
		}
		
		$data = $this->_get_shared_data();
		$data = array_merge($data,$receipt_cart->to_array());
		$this->lang->load('deliveries');
		$sale_info = $this->Sale->get_info($sale_id)->row_array();
		$data['deleted'] = $sale_info['deleted'];
		$data['is_sale_cash_payment'] = $receipt_cart->has_cash_payment();
		$tier_id = $sale_info['tier_id'];
		$tier_info = $this->Tier->get_info($tier_id);
		$data['tier'] = $tier_info->name;
		$data['register_name'] = $this->Register->get_register_name($sale_info['register_id']);
		$data['receipt_title']= $this->config->item('override_receipt_title') ? $this->config->item('override_receipt_title') : lang('sales_receipt');
		$data['sales_card_statement']= $this->config->item('override_signature_text') ? $this->config->item('override_signature_text') : lang('sales_card_statement','',array(),TRUE);
		
		$data['transaction_time']= date(get_date_format().' '.get_time_format(), strtotime($sale_info['sale_time']));
		$data['override_location_id'] = $sale_info['location_id'];
		$data['discount_exists'] = $this->_does_discount_exists($data['cart_items']);
		$customer_id=$receipt_cart->customer_id;
		$emp_info=$this->Employee->get_info($sale_info['employee_id']);
		$sold_by_employee_id=$sale_info['sold_by_employee_id'];
		$sale_emp_info=$this->Employee->get_info($sold_by_employee_id);
		
		$data['payment_type']=$sale_info['payment_type'];
		$data['amount_change']=$receipt_cart->get_amount_due_round($sale_id) * -1;
		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name.($sold_by_employee_id && $sold_by_employee_id != $sale_info['employee_id'] ? '/'. $sale_emp_info->first_name.' '.$sale_emp_info->last_name: '');
		$data['employee_firstname']=$emp_info->first_name.($sold_by_employee_id && $sold_by_employee_id != $sale_info['employee_id'] ? '/'. $sale_emp_info->first_name: '');
		
		$data['ref_no'] = $sale_info['cc_ref_no'];
		$data['auth_code'] = $sale_info['auth_code'];
		
		$exchange_rate = $receipt_cart->get_exchange_rate() ? $receipt_cart->get_exchange_rate() : 1;
		
		$data['disable_loyalty'] = 0;
		
					
		$data['sale_id']=$this->config->item('sale_prefix').' '.$sale_id;
		$data['sale_id_raw']=$sale_id;
		$data['store_account_payment'] = FALSE;
		
		foreach($data['cart_items'] as $item)
		{
			if ($item->name == lang('common_store_account_payment'))
			{
				$data['store_account_payment'] = TRUE;
				break;
			}
		}
		
		if ($sale_info['suspended'] > 0)
		{
			if ($sale_info['suspended'] == 1)
			{
				$data['sale_type'] = ($this->config->item('user_configured_layaway_name') ? $this->config->item('user_configured_layaway_name') : lang('common_layaway'));
			}
			elseif ($sale_info['suspended'] == 2)
			{
				$data['sale_type'] = lang('common_estimate');				
			}
			else
			{
				$this->load->model('Sale_types');
				$data['sale_type'] = $this->Sale_types->get_info($sale_info['suspended'])->name;				
			}

		}
		
		if($receipt_cart->get_has_delivery())
		{
			$data['delivery_person_info'] = $receipt_cart->get_delivery_person_info();
						
			$data['delivery_info'] = $receipt_cart->get_delivery_info();
		}
		
		
		$data['signature_file_id'] = $sale_info['signature_image_id'];
		$receipt_data = $this->load->view("sales/receipt_html", $data, true);
		if($this->config->item('receipt_download_filename_prefix')){
			$filename = $this->config->item('receipt_download_filename_prefix').'_receipt_'.$sale_id.'.pdf';
		}else{
			$filename = 'receipt_'.$sale_id.'.pdf';
		}

		$this->load->library("m_pdf");
		$pdf_content = $this->m_pdf->generate_pdf($receipt_data,TRUE, $filename);
		
	}
	
	function sms_receipt($sale_id)
	{
		$this->Sale->sms_receipt($sale_id);
	}
	
	function email_receipt($sale_id)
	{
		
		$receipt_cart = PHPPOSCartSale::get_instance_from_sale_id($sale_id);
		if ($this->config->item('sort_receipt_column'))
		{
			$receipt_cart->sort_items($this->config->item('sort_receipt_column'));
		}
		
		$data = $this->_get_shared_data();
		$data = array_merge($data,$receipt_cart->to_array());
		$this->lang->load('deliveries');
		$sale_info = $this->Sale->get_info($sale_id)->row_array();
		$data['deleted'] = $sale_info['deleted'];
		$data['is_sale_cash_payment'] = $receipt_cart->has_cash_payment();
		$tier_id = $sale_info['tier_id'];
		$tier_info = $this->Tier->get_info($tier_id);
		$data['tier'] = $tier_info->name;
		$data['register_name'] = $this->Register->get_register_name($sale_info['register_id']);
		$data['receipt_title']= $this->config->item('override_receipt_title') ? $this->config->item('override_receipt_title') : lang('sales_receipt');
		$data['sales_card_statement']= $this->config->item('override_signature_text') ? $this->config->item('override_signature_text') : lang('sales_card_statement','',array(),TRUE);
		
		$data['transaction_time']= date(get_date_format().' '.get_time_format(), strtotime($sale_info['sale_time']));
		$data['override_location_id'] = $sale_info['location_id'];
		$data['discount_exists'] = $this->_does_discount_exists($data['cart_items']);
		$customer_id=$receipt_cart->customer_id;
		$emp_info=$this->Employee->get_info($sale_info['employee_id']);
		$sold_by_employee_id=$sale_info['sold_by_employee_id'];
		$sale_emp_info=$this->Employee->get_info($sold_by_employee_id);
		
		$data['payment_type']=$sale_info['payment_type'];
		$data['amount_change']=$receipt_cart->get_amount_due_round($sale_id) * -1;
		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name.($sold_by_employee_id && $sold_by_employee_id != $sale_info['employee_id'] ? '/'. $sale_emp_info->first_name.' '.$sale_emp_info->last_name: '');
		$data['employee_firstname']=$emp_info->first_name.($sold_by_employee_id && $sold_by_employee_id != $sale_info['employee_id'] ? '/'. $sale_emp_info->first_name: '');

		$data['ref_no'] = $sale_info['cc_ref_no'];
		$data['auth_code'] = $sale_info['auth_code'];
		
		$exchange_rate = $receipt_cart->get_exchange_rate() ? $receipt_cart->get_exchange_rate() : 1;
		
		$data['disable_loyalty'] = 0;
		
		
		$data['sale_id']=$this->config->item('sale_prefix').' '.$sale_id;
		$data['sale_id_raw']=$sale_id;
		$data['store_account_payment'] = FALSE;
		
		foreach($data['cart_items'] as $item)
		{
			if ($item->name == lang('common_store_account_payment'))
			{
				$data['store_account_payment'] = TRUE;
				break;
			}
		}
		
		if ($sale_info['suspended'] > 0)
		{
			if ($sale_info['suspended'] == 1)
			{
				$data['sale_type'] = ($this->config->item('user_configured_layaway_name') ? $this->config->item('user_configured_layaway_name') : lang('common_layaway'));
			}
			elseif ($sale_info['suspended'] == 2)
			{
				$data['sale_type'] = lang('common_estimate');				
			}
			else
			{
				$this->load->model('Sale_types');
				$data['sale_type'] = $this->Sale_types->get_info($sale_info['suspended'])->name;				
			}

		}
		
		if($receipt_cart->get_has_delivery())
		{
			$data['delivery_person_info'] = $receipt_cart->get_delivery_person_info();
						
			$data['delivery_info'] = $receipt_cart->get_delivery_info();
		}
		
		if (!empty($data['customer_email']))
		{
			$this->load->library('email');
			$config['mailtype'] = 'html';				
			$this->email->initialize($config);
			$this->email->from($this->Location->get_info_for_key('email') ? $this->Location->get_info_for_key('email') : 'no-reply@mg.phppointofsale.com', $this->config->item('company'));
			$this->email->to($data['customer_email']); 
			
			if($this->Location->get_info_for_key('cc_email'))
			{
				$this->email->cc($this->Location->get_info_for_key('cc_email'));
			}
			
			if($this->Location->get_info_for_key('bcc_email'))
			{
				$this->email->bcc($this->Location->get_info_for_key('bcc_email'));
			}

			$this->email->subject($sale_info['suspended'] == 2 ? lang('common_estimate') : ($this->config->item('emailed_receipt_subject') ? $this->config->item('emailed_receipt_subject') : lang('sales_receipt')));
			
			if($this->config->item('enable_pdf_receipts')){
				$data['signature_file_id'] = $sale_info['signature_image_id'];
				$receipt_data = $this->load->view("sales/receipt_html", $data, true);
				
				if($this->config->item('receipt_download_filename_prefix')){
					$filename = $this->config->item('receipt_download_filename_prefix').'_receipt_'.$sale_id.'.pdf';
				}else{
					$filename = 'receipt_'.$sale_id.'.pdf';
				}
		    $this->load->library("m_pdf");
				$pdf_content = $this->m_pdf->generate_pdf($receipt_data);
								
				if(isset($pdf_content) && $pdf_content){
					$this->email->attach($pdf_content, 'attachment', $filename, 'application/pdf');
					$this->email->message(nl2br($this->config->item('pdf_receipt_message')));
				}
			}else{
				$this->email->message($this->load->view("sales/receipt_email",$data, true));	
			}
			$this->email->send();
		}
	}
	
	function receipt_validate()
	{
		if ($this->cart->is_valid_receipt($this->input->post('sale_id')))
		{
			$sale_id = substr(strtolower($this->input->post('sale_id')), strpos(strtolower($this->input->post('sale_id')),$this->config->item('sale_prefix').' ') + strlen(strtolower($this->config->item('sale_prefix')).' '));
		}
		else
		{
			$sale_id = $this->input->post('sale_id');
		}
		
		$sale_info = $this->Sale->get_info($sale_id)->row_array();
		if(!$sale_info)
		{
			echo json_encode(array('success'=>false,'message'=>lang('sales_sale_id_not_found')));
			die();
		}
		else
		{
			echo json_encode(array('success'=>true,'sale_id' => $sale_id));
			die();
		}
	}

	function receipt($sale_id)
	{		
		$receipt_cart = PHPPOSCartSale::get_instance_from_sale_id($sale_id);
		
		if ($receipt_cart->suspended && !$this->Employee->has_module_action_permission('sales', 'view_suspended_receipt', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			redirect('no_access/'.$this->module_id);
		}
		
		if ($this->config->item('sort_receipt_column'))
		{
			$receipt_cart->sort_items($this->config->item('sort_receipt_column'));
		}
		
		$data = $this->_get_shared_data();
		
		$data = array_merge($data,$receipt_cart->to_array());
		$data['is_sale'] = FALSE;
		$sale_info = $this->Sale->get_info($sale_id)->row_array();
		$data['is_sale_cash_payment'] = $this->cart->has_cash_payment();
		$data['show_payment_times'] = TRUE;
		$data['signature_file_id'] = $sale_info['signature_image_id'];
		
		$tier_id = $sale_info['tier_id'];
		$tier_info = $this->Tier->get_info($tier_id);
		$data['tier'] = $tier_info->name;
		$data['register_name'] = $this->Register->get_register_name($sale_info['register_id']);
		$data['override_location_id'] = $sale_info['location_id'];
		$data['deleted'] = $sale_info['deleted'];

		$data['receipt_title']= $this->config->item('override_receipt_title') ? $this->config->item('override_receipt_title') : ( !$receipt_cart->suspended ? lang('sales_receipt') : '');
		$data['sales_card_statement']= $this->config->item('override_signature_text') ? $this->config->item('override_signature_text') : lang('sales_card_statement','',array(),TRUE);
		
		$data['transaction_time']= date(get_date_format().' '.get_time_format(), strtotime($sale_info['sale_time']));
		$customer_id=$this->cart->customer_id;
		
		$emp_info=$this->Employee->get_info($sale_info['employee_id']);
		$sold_by_employee_id=$sale_info['sold_by_employee_id'];
		$sale_emp_info=$this->Employee->get_info($sold_by_employee_id);
		$data['payment_type']=$sale_info['payment_type'];
		$data['amount_change']=$receipt_cart->get_amount_due() * -1;
		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name.($sold_by_employee_id && $sold_by_employee_id != $sale_info['employee_id'] ? '/'. $sale_emp_info->first_name.' '.$sale_emp_info->last_name: '');
		$data['employee_firstname']=$emp_info->first_name.($sold_by_employee_id && $sold_by_employee_id != $sale_info['employee_id'] ? '/'. $sale_emp_info->first_name: '');
		$data['ref_no'] = $sale_info['cc_ref_no'];
		$data['auth_code'] = $sale_info['auth_code'];
		$data['discount_exists'] = $this->_does_discount_exists($data['cart_items']);
		$data['disable_loyalty'] = 0;
		$data['sale_id']=$this->config->item('sale_prefix').' '.$sale_id;
		$data['sale_id_raw']=$sale_id;
		$data['store_account_payment'] = FALSE;
		$data['is_purchase_points'] = FALSE;
		
		foreach($data['cart_items'] as $item)
		{
			if ($item->name == lang('common_store_account_payment'))
			{
				$data['store_account_payment'] = TRUE;
				break;
			}
		}

		foreach($data['cart_items'] as $item)
		{
			if ($item->name == lang('common_purchase_points'))
			{
				$data['is_purchase_points'] = TRUE;
				break;
			}
		}
		
		if ($sale_info['suspended'] > 0)
		{
			if ($sale_info['suspended'] == 1)
			{
				$data['sale_type'] = ($this->config->item('user_configured_layaway_name') ? $this->config->item('user_configured_layaway_name') : lang('common_layaway'));
			}
			elseif ($sale_info['suspended'] == 2)
			{
				$data['sale_type'] = lang('common_estimate');				
			}
			else
			{
				$this->load->model('Sale_types');
				$data['sale_type'] = $this->Sale_types->get_info($sale_info['suspended'])->name;				
			}
		}
		
		$exchange_rate = $receipt_cart->get_exchange_rate() ? $receipt_cart->get_exchange_rate() : 1;
		
		if($receipt_cart->get_has_delivery())
		{
			$data['delivery_person_info'] = $receipt_cart->get_delivery_person_info();
						
			$data['delivery_info'] = $receipt_cart->get_delivery_info();
		}
		
		$this->load->view("sales/receipt",$data);
	}

	function fulfillment($sale_id)
	{
		$sale_info = $this->Sale->get_info($sale_id)->row_array();
		$data['total'] = $sale_info['total'];
		$data['override_location_id'] = $sale_info['location_id'];
		$data['transaction_time']= date(get_date_format().' '.get_time_format(), strtotime($sale_info['sale_time']));
		$customer_id=$sale_info['customer_id'];
		
		$emp_info=$this->Employee->get_info($sale_info['employee_id']);
		$data['employee']=$emp_info->first_name.' '.$emp_info->last_name;
		
		if($customer_id)
		{
			$cust_info=$this->Customer->get_info($customer_id);
			$data['customer']=$cust_info->first_name.' '.$cust_info->last_name.($cust_info->account_number==''  ? '':' - '.$cust_info->account_number);
			$data['customer_company']= $cust_info->company_name;
			$data['customer_address_1'] = $cust_info->address_1;
			$data['customer_address_2'] = $cust_info->address_2;
			$data['customer_city'] = $cust_info->city;
			$data['customer_state'] = $cust_info->state;
			$data['customer_zip'] = $cust_info->zip;
			$data['customer_country'] = $cust_info->country;
			$data['customer_phone'] = $cust_info->phone_number;
			$data['customer_email'] = $cust_info->email;
		}
		
		$data['sale_id']=$this->config->item('sale_prefix').' '.$sale_id;
		$data['sale_id_raw']=$sale_id;
		$data['comment']=$sale_info['comment'];
		$data['show_comment_on_receipt']=$sale_info['show_comment_on_receipt'];
		$data['sale_id_raw']=$sale_id;
		$data['sales_items'] = $this->Sale->get_sale_items_ordered_by_category($sale_id)->result_array();
		$data['sales_item_kits'] = $this->Sale->get_sale_item_kits_ordered_by_category($sale_id)->result_array();
		$data['discount_exists'] = $this->_does_discount_exists($data['sales_items']) || $this->_does_discount_exists($data['sales_item_kits']);
				
		$this->load->model('Delivery');
		$this->load->model('Person');
		
		$delivery = $this->Delivery->get_info_by_sale_id($sale_id);
		
		if($delivery->num_rows()==1)
		{
			$data['delivery_info'] = $delivery->row_array();			
			$data['delivery_person_info'] = (array)$this->Person->get_info($this->Delivery->get_delivery_person_id($sale_id));
		}
		
		$this->load->view("sales/fulfillment",$data);
	}
	
	function _does_discount_exists($cart)
	{
		foreach($cart as $line=>$item)
		{
			if( (isset($item->discount) && $item->discount >0 ) || (is_array($item) && isset($item['discount_percent']) && $item['discount_percent'] >0 ) )
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	
	function edit($sale_id)
	{
		$data = array();

		$data['sale_info'] = $this->Sale->get_info($sale_id)->row_array();
		$data['is_ecommerce'] = $data['sale_info']['is_ecommerce'];
		if ($data['sale_info']['customer_id'])
		{
			$customer = $this->Customer->get_info($data['sale_info']['customer_id']);			
			$data['selected_customer_name'] = $customer->first_name . ' '. $customer->last_name;
			$data['selected_customer_email'] = $customer->email;
		}
		else
		{
			$data['selected_customer_name'] = lang('common_none');
		}
		
		$data['employees'] = array();
		foreach ($this->Employee->get_all()->result() as $employee)
		{
			$data['employees'][$employee->person_id] = $employee->first_name . ' '. $employee->last_name;
		}

		
		$data['store_account_payment'] = $data['sale_info']['store_account_payment'];
		$data['is_purchase_points'] = $data['sale_info']['is_purchase_points'];
		$data['store_account_charge'] = $this->Sale->get_store_account_payment_total($sale_id) != 0 ? true : false;
		
		
		$this->load->view('sales/edit', $data);
	}
	
	function delete_sale_only($sale_id)
	{
		$this->check_action_permission('delete_sale');
		if ($this->Sale->delete($sale_id))
		{			
			echo json_encode(array('success'=>true,'message'=>lang('sales_successfully_deleted')));
		}
		else
		{
			echo json_encode(array('success'=>true,'message'=>lang('sales_unsuccessfully_deleted')));
		}
	}
	
	function delete($sale_id)
	{
		$this->check_action_permission('delete_sale');
		
		if (!$this->input->post('do_delete') || $this->Sale->is_sale_deleted($sale_id))
		{
			$this->load->view('sales/delete', array('success' => false));
			return;
		}
		
		$data = array();
				
		$can_delete = TRUE;
		
		if ($this->input->post('sales_void_and_refund_credit_card') || $this->input->post('sales_void_and_cancel_return'))
		{					
			$credit_card_processor = $this->_get_cc_processor();
			if ($credit_card_processor)
			{
				$cc_processor_class_name = strtoupper(get_class($credit_card_processor));
				$cc_processor_parent_class_name = strtoupper(get_parent_class($credit_card_processor));
				if ($cc_processor_class_name == 'CARDCONNECTPROCESSOR' || $cc_processor_class_name == 'MERCURYHOSTEDCHECKOUTPROCESSOR' || $cc_processor_parent_class_name == 'DATACAPTRANSCLOUDPROCESSOR' || $cc_processor_class_name=='STRIPEPROCESSOR' || $cc_processor_class_name=='BRAINTREEPROCESSOR' || $cc_processor_class_name=='CORECLEARBLOCKCHYPPROCESSOR')
				{
					if ($this->input->post('sales_void_and_refund_credit_card'))
					{
						$can_delete = $credit_card_processor->void_sale($sale_id);
					}
					elseif($this->input->post('sales_void_and_cancel_return'))
					{
						$can_delete = $credit_card_processor->void_return($sale_id);
					}
					
					if ($can_delete && $this->Sale->delete($sale_id))
					{			
						$data['success'] = true;
						if ($this->input->post('clear_sale'))
						{
							$this->cart->destroy();
							$this->cart->save();
						}
						$data['sale_id'] = $sale_id;
					}
					else
					{
						$data['success'] = false;
					}
		
					$this->load->view('sales/delete', $data);
				}
				elseif ($cc_processor_parent_class_name =='DATACAPUSBPROCESSOR')
				{					
					if ($this->input->post('sales_void_and_refund_credit_card'))
					{
						$can_delete = $credit_card_processor->void_sale($sale_id);
					}
					elseif($this->input->post('sales_void_and_cancel_return'))
					{
						$can_delete = $credit_card_processor->void_return($sale_id);
					}	
					
					if ($can_delete && $this->input->post('clear_sale'))
					{
						$this->cart->destroy();
						$this->cart->save();
					}
					
				}
			}
		}
		else
		{
			if ($this->Sale->delete($sale_id))
			{			
				$data['success'] = true;
				if ($this->input->post('clear_sale'))
				{
					$this->cart->destroy();
					$this->cart->save();
				}
				$data['sale_id'] = $sale_id;
			}
			else
			{
				$data['success'] = false;
			}

			$this->load->view('sales/delete', $data);
		}
	}
	
	function undelete($sale_id)
	{
		if (!$this->input->post('do_undelete') || $this->Sale->is_sale_undeleted($sale_id))
		{
			$this->load->view('sales/undelete', array('success' => false));
			return;
		}
		$data = array();
		
		if ($this->Sale->undelete($sale_id))
		{
			$data['success'] = true;
		}
		else
		{
			$data['success'] = false;
		}
		
		$this->load->view('sales/undelete', $data);
		
	}
	
	function save($sale_id)
	{
		$sale_data = array(
			'sale_time' => date('Y-m-d H:i:s', strtotime($this->input->post('date'))),
			'customer_id' => $this->input->post('customer_id') ? $this->input->post('customer_id') : null,
			'employee_id' => $this->input->post('employee_id'),
			'comment' => $this->input->post('comment'),
			'show_comment_on_receipt' => $this->input->post('show_comment_on_receipt') ? 1 : 0
		);

		$sale_info = $this->Sale->get_info($sale_id)->row_array();
		
		
		if ($this->Sale->update($sale_data, $sale_id))
		{
			echo json_encode(array('success'=>true,'message'=>lang('sales_successfully_updated')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('sales_unsuccessfully_updated')));
		}
	}
	
	function _payments_cover_total()
	{
		$total_payments = 0;

		foreach($this->cart->get_payments() as $payment)
		{
			$total_payments += $payment->payment_amount;
		}

		/* Changed the conditional to account for floating point rounding */
		if ( ( $this->cart->get_mode() == 'sale' || $this->cart->get_mode() == 'store_account_payment' ) && ( ( to_currency_no_money( $this->cart->get_total() ) - $total_payments ) > 1e-6 ) )
		{
			return false;
		}
		return true;
	}
	
	function redeem_discount()
	{
		$customer_id = $this->cart->customer_id;
		
		if ($customer_id)
		{
			 $cust_info = $this->Customer->get_info($customer_id);
		   $sales_until_discount = ($this->config->item('number_of_sales_for_discount') ? $this->config->item('number_of_sales_for_discount') : 0) - $cust_info->current_sales_for_discount;
			
			if ($sales_until_discount <= 0)
			{
				$discount_all_percent = $this->config->item('discount_percent_earned');
				$this->cart->redeem_discount = 1;
				$this->cart->discount_all($discount_all_percent);
				
	 	 	  foreach($this->cart->get_items() as $item)
	 	 	  {
	 	 	  	  if ($item->below_cost_price())
	 	 	  	  { 
		 	 			  if ($this->config->item('do_not_allow_below_cost'))
		 	 			  {
								$this->cart->discount_all(0);
								$this->cart->redeem_discount = 0;
		 	 				  $data['error'] = lang('sales_selling_item_below_cost');
		 	 			  }
		 	 			  else
		 	 			  {
		 	 				  $data['warning'] = lang('sales_selling_item_below_cost');
		 	 			  }
							$this->cart->save();
	 	 			  	$this->_reload($data);
							return;
	 	 		 	 }
	 	 	  }
				
			}
		}
		$this->cart->save();
		$this->_reload();
	}
	
	function unredeem_discount()
	{
		$this->cart->redeem_discount = 0;
		$this->cart->discount_all(0);
		$this->cart->save();
		$this->_reload();
	}
	
	function set_ebt_voucher_no()
	{
		$this->cart->ebt_voucher_no = $this->input->post('ebt_voucher_no');
		$this->cart->save();
	}

	function set_ebt_voucher()
	{
		$this->cart->ebt_voucher = $this->input->post('ebt_voucher');
		$this->cart->save();
	}
	
		
	function set_ebt_auth_code()
	{
		$this->cart->ebt_auth_code = $this->input->post('ebt_auth_code');
		$this->cart->save();
	}
	
	function reload()
	{
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
		
		$config['base_url'] = site_url('sales/paginate');
		$config['per_page'] = $this->cart->limit; 
		$config['uri_segment'] = -1; //Set this to non possible url so it doesn't use URL
		
		//Undocumented feature to get page
		$config['cur_page'] = $this->cart->offset; 
		
		$config['total_rows'] = count($the_cart_items);
		
		
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		
		$data['is_tax_inclusive'] = $this->cart->is_tax_inclusive();
		$data['ebt_total'] = $this->cart->get_ebt_total_amount_to_charge() - $this->cart->get_payment_amount(lang('common_wic')) - $this->cart->get_payment_amount(lang('common_ebt'));
		
		$person_info = $this->Employee->get_logged_in_employee_info();
		$modes = array('sale'=>lang('sales_sale'),'return'=>lang('sales_return'), 'estimate' => lang('common_estimate'));
		
		if (!$this->Employee->has_module_action_permission('sales', 'process_returns', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			unset($modes['return']);
		}
		
		$can_receive_store_account_payment = $this->Employee->has_module_action_permission('sales', 'receive_store_account_payment', $this->Employee->get_logged_in_employee_info()->person_id);		
		
		if($this->config->item('customers_store_accounts') && $can_receive_store_account_payment) 
		{
			$modes['store_account_payment'] = lang('common_store_account_payment');
		}
				
		if ($this->config->item('enable_customer_loyalty_system') && $this->config->item('loyalty_option') == 'advanced' &&  count(explode(":",$this->config->item('spend_to_point_ratio'),2)) == 2)
		{
			$modes['purchase_points'] = lang('sales_purchase_points');
		}
		
		$data['has_coupons_for_today'] = $this->Sale->has_coupons_for_today();
		$data['sale_id_of_edit_or_suspended_sale'] = $this->cart->get_previous_receipt_id();
		
		if (!$data['sale_id_of_edit_or_suspended_sale'])
		{
			$data['sale_id_of_edit_or_suspended_sale'] = '';
			$data['was_cc_return'] = 0;
			$data['was_cc_sale'] = 0;
		}
		else
		{
			$sale_info = $this->Sale->get_info($data['sale_id_of_edit_or_suspended_sale'])->row();	
			$data['was_cc_return'] = $this->Sale->can_void_cc_return($data['sale_id_of_edit_or_suspended_sale'])  ? 1 : 0;
			$data['was_cc_sale'] = $this->Sale->can_void_cc_sale($data['sale_id_of_edit_or_suspended_sale']) ? 1 : 0;
		}
		
		$data['coupons'] = array();
		$data['has_discount'] = $this->cart->has_discount();
		$data['modes']= $modes;
		$data['line_for_flat_discount_item'] = $this->cart->get_index_for_flat_discount_item();
		$data['discount_all_percent'] = $this->cart->get_discount_all_percent();
		$data['discount_all_fixed'] = $this->cart->get_discount_all_fixed();
		$data['items_module_allowed'] = $this->Employee->has_module_permission('items', $person_info->person_id);
		$data['current_location'] = $this->Employee->get_logged_in_employee_current_location_id();
		if (!$this->session->userdata('foreign_language_to_cur_language_sales'))
		{
			$this->load->helper('directory');
			$language_folder = directory_map(APPPATH.'language',1);

			$languages = array();

			foreach($language_folder as $language_folder)
			{
				$languages[] = substr($language_folder,0,strlen($language_folder)-1);
			}

			$cur_lang = array();
			foreach($this->Sale->get_payment_options_with_language_keys() as $cur_lang_value => $lang_key)
			{
				$cur_lang[$lang_key] = $cur_lang_value;
			}


			foreach($languages as $language)
			{
				$this->lang->load('common', $language);

				foreach($this->Sale->get_payment_options_with_language_keys() as $cur_lang_value => $lang_key)
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
			
			$this->session->set_userdata('foreign_language_to_cur_language_sales', $foreign_language_to_cur_language);
			//Switch back
			$this->lang->switch_to($this->config->item('language'));
		}
		else
		{
			$foreign_language_to_cur_language = $this->session->userdata('foreign_language_to_cur_language_sales');
		}

		$default_payment_type_translated = false;
		if (isset($foreign_language_to_cur_language[$this->config->item('default_payment_type')]))
		{
			$default_payment_type_translated = $foreign_language_to_cur_language[$this->config->item('default_payment_type')];
		}
		else
		{
			$default_payment_type_translated = $this->config->item('default_payment_type');
		}
		
		$data['default_payment_type'] = $default_payment_type_translated ? $default_payment_type_translated : lang('common_cash');
		
		$data['is_over_credit_limit'] = false;
		$data['fullscreen'] = $this->session->userdata('fullscreen');
		$data['redeem'] = $this->cart->redeem_discount;
		
		$customer_id=$this->cart->customer_id;
		
		if ($customer_id)
		{
			$cust_info=$this->Customer->get_info($customer_id, TRUE);

			$customer_giftcards=$this->Giftcard->get_customer_giftcards($customer_id);

		}
		
		$data['prompt_for_card'] = $this->cart->prompt_for_card;
		$data['cc_processor_class_name'] = $this->_get_cc_processor() ? strtoupper(get_class($this->_get_cc_processor())) : '';
		$data['cc_processor_parent_class_name'] = $this->_get_cc_processor() ? strtoupper(get_parent_class($this->_get_cc_processor())) : '';
		
		$data['ebt_voucher'] = $this->cart->ebt_voucher;
		$data['ebt_voucher_no'] = $this->cart->ebt_voucher_no;
		$data['ebt_auth_code'] = $this->cart->ebt_auth_code;
		
		
		if ($this->config->item('select_sales_person_during_sale'))
		{
			$employees = array('' => lang('common_not_set'));
			
			foreach($this->Employee->get_all()->result() as $employee)
			{
				if ($this->Employee->is_employee_authenticated($employee->person_id, $this->Employee->get_logged_in_employee_current_location_id()))
				{
					$employees[$employee->person_id] = $employee->first_name.' '.$employee->last_name;
				}
			}
			$data['employees'] = $employees;
			$data['selected_sold_by_employee_id'] = $this->cart->sold_by_employee_id;
		}
		
		$tiers = array();

		$tiers[0] = lang('common_none');
		foreach($this->Tier->get_all()->result() as $tier)
		{
			$tiers[$tier->id]=$tier->name;
		}
		
		$data['tiers'] = $tiers;
		
		$data['payment_options'] = $this->Sale->get_payment_options($this->cart);
		if($customer_id)
		{
			$data['customer']=$cust_info->first_name.' '.$cust_info->last_name.($cust_info->company_name==''  ? '':' ('.$cust_info->company_name.')');
			$data['customer_email']=$cust_info->email;
			$data['customer_phone']=$cust_info->phone_number;
			$data['customer_internal_notes'] = $cust_info->internal_notes;
			
			$this->load->model('Customer');
			$data['customer_has_address'] = $this->Customer->does_customer_have_address($customer_id);
			$data['customer_balance'] = $cust_info->balance;
			$data['customer_credit_limit'] = $cust_info->credit_limit;
			$data['is_over_credit_limit'] = $this->Customer->is_over_credit_limit($customer_id,$this->cart->get_payment_amount(lang('common_store_account')));
			$data['customer_id']=$customer_id;
			$data['customer_cc_token'] = $cust_info->cc_token;
			$data['customer_cc_preview'] = $cust_info->cc_preview;
			$data['save_credit_card_info'] = $this->cart->save_credit_card_info;
			$data['use_saved_cc_info'] = $this->cart->use_cc_saved_info;
			$data['avatar']=$cust_info->image_id ?  app_file_url($cust_info->image_id) : base_url()."assets/img/user.png"; //can be changed to  base_url()."img/avatar.png" if it is required
			if(count($customer_giftcards))
			{
				$data['customer_giftcards'] = $customer_giftcards;
	
			}
			$data['disable_loyalty'] = $cust_info->disable_loyalty;
			$data['auto_email_receipt'] = $cust_info->auto_email_receipt;
			$data['always_sms_receipt'] = $cust_info->always_sms_receipt;
			
			$data['points'] = to_currency_no_money($cust_info->points);
			$data['sales_until_discount'] = ($this->config->item('number_of_sales_for_discount')) ? (float)$this->config->item('number_of_sales_for_discount') - (float)$cust_info->current_sales_for_discount : 0;
		}
		$data['customer_required_check'] = (!$this->config->item('require_customer_for_sale') || ($this->config->item('require_customer_for_sale') && isset($customer_id) && $customer_id));
		$data['suspended_sale_customer_required_check'] = (!$this->config->item('require_customer_for_suspended_sale') || ($this->config->item('require_customer_for_suspended_sale') && isset($customer_id) && $customer_id));
		$data['payments_cover_total'] = $this->_payments_cover_total();
				
		if ($data['mode'] == 'store_account_payment' && $customer_id)
		{
			$sale_ids = $this->Sale->get_unpaid_store_account_sale_ids($customer_id);
			
			$paid_sales = $this->cart->get_paid_store_account_ids();
									
			$data['unpaid_store_account_sales'] = $this->Sale->get_unpaid_store_account_sales($sale_ids);
			
			for($k=0;$k<count($data['unpaid_store_account_sales']);$k++)
			{
				if (isset($paid_sales[$data['unpaid_store_account_sales'][$k]['sale_id']]))
				{
					$data['unpaid_store_account_sales'][$k]['paid'] = TRUE;
				}
			}
		}
		
		
		//fixing this for arabic
		if (is_rtl_lang())
		{
		  $data['discount_editable_placement'] = $this->agent->is_mobile() && !$this->agent->is_tablet() ? 'top' : 'right';
		}
		else
		{
			$data['discount_editable_placement'] = $this->agent->is_mobile() && !$this->agent->is_tablet() ? 'top' : 'left';
		}
		
		
		$payment_options = array_values($this->Sale->get_payment_options($this->cart));

		$markup_markdown = $this->config->item('markup_markdown');
		$markup_markdown_config = unserialize($markup_markdown);
		
	
		$data['markup_predictions'] = array();
		if($markup_markdown && $this->cart->get_total() > 0 && !$this->Location->get_info_for_key('disable_markup_markdown'))
		{			
			foreach($payment_options as $payment_type)
			{
				if (isset($markup_markdown_config[$payment_type]))
				{
					$fee_percent = $markup_markdown_config[$payment_type];
					$fee_amount = $this->cart->get_total()*($fee_percent/100);
			
					$data['markup_predictions'][$payment_type] = array('amount' => $fee_amount,'id' => md5($payment_type));
				}
				else
				{
					$data['markup_predictions'][$payment_type] = array('amount' => 0,'id' => md5($payment_type));
				}
			}
		}						

 		$credit_card_processor = $this->_get_cc_processor();

		if ($credit_card_processor && method_exists($credit_card_processor, 'update_transaction_display'))
		{
			$data['update_transaction_display'] = TRUE;
		}

  	if ($is_ajax)
		{
			$this->load->view("sales/register",$data);
		}
		else
		{
			if ($this->config->item('quick_variation_grid'))
			{
				$this->load->view("sales/register_initial_quick",$data);					
			}
			else
			{
				$this->load->view("sales/register_initial",$data);
			}
		}
		
	}
	
	function update_transaction_display()
	{
		session_write_close();
 		$credit_card_processor = $this->_get_cc_processor();

		if ($credit_card_processor && method_exists($credit_card_processor, 'update_transaction_display'))
		{
			$credit_card_processor->update_transaction_display($this->cart);
		}
		
	}
		
	function pay_store_account_sale($sale_id, $amount)
	{
		$this->cart->add_paid_store_account_payment_id($sale_id,$amount);
		$cart = $this->cart->get_items();
		foreach($cart as $item)
		{
			if ($item->name == lang('common_store_account_payment'))
			{
				$item->unit_price += $amount; 
				break;
			}
		}
		$comment = lang('sales_pays_sales'). ' - '.implode(', ',array_keys($this->cart->get_paid_store_account_ids()));
			
		$this->cart->comment = $comment;
		$this->cart->save();
		$this->_reload();
	}
	
	function toggle_pay_all_store_account()
	{
		$sale_ids = $this->Sale->get_unpaid_store_account_sale_ids($this->cart->customer_id);
		
		$unpaid_sales = $this->Sale->get_unpaid_store_account_sales($sale_ids);
		
		if(count($this->cart->get_paid_store_account_ids()) ==  0)
		{
			$amount_to_pay = 0;
		
			foreach($unpaid_sales as $unpaid_sale)
			{
					$this->cart->add_paid_store_account_payment_id($unpaid_sale['sale_id'],$unpaid_sale['payment_amount']);
					$amount_to_pay +=$unpaid_sale['payment_amount'];
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
			
			$comment = lang('sales_pays_sales'). ' - '.implode(', ',array_keys($this->cart->get_paid_store_account_ids()));
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
	
	function delete_store_account_sale($sale_id, $amount)
	{
		if (isset($this->cart->paid_store_account_amounts[$sale_id]))
		{
			$amount = $this->cart->paid_store_account_amounts[$sale_id];
		}
		
		$this->cart->delete_paid_store_account_id($sale_id);
		$cart = $this->cart->get_items();
		foreach($cart as $item)
		{
			if ($item->name == lang('common_store_account_payment'))
			{				
				$item->unit_price -= $amount; 
				break;
			}
		}
		$comment = lang('sales_pays_sales'). ' - '.implode(', ',array_keys($this->cart->get_paid_store_account_ids()));
			
		$this->cart->comment = $comment;
		$this->cart->save();
    $this->_reload();
	}
	

	//this method also used for customer wise suspended sales also
	function customer_recent_sales($customer_id)
	{
		$data['customer'] = $this->Customer->get_info($customer_id)->first_name.' '.$this->Customer->get_info($customer_id)->last_name;
		$data['customer_comments'] = $this->Customer->get_info($customer_id)->comments;
		$data['customer_id'] = $customer_id;
		
		$data['recent_sales'] = $this->Sale->get_recent_sales_for_customer($customer_id);


		$data['suspended_sales'] = $this->Sale->get_all_suspended(NULL, $customer_id);

		$this->load->view("sales/customer_recent_sales", $data);
	}


  function cancel_sale()
  {
	 if ($this->Location->get_info_for_key('enable_credit_card_processing'))
	 {
 		$credit_card_processor = $this->_get_cc_processor();
		 
		 if ($credit_card_processor && method_exists($credit_card_processor, 'void_partial_transactions'))
		 {
			 if (!$credit_card_processor->void_partial_transactions())
			 {
		 	 	 $this->cart->destroy();
				 $this->cart->save();
				 $this->_reload(array('error' => lang('sales_attempted_to_reverse_transactions_failed_please_contact_support')), true);
				 return;
			 }
 			 }
	 }
	 
	 	$this->cart->destroy();
		$this->cart->save();
	 	$this->_reload();
	}
	
	function clear_sale()
	{
   	$this->cart->destroy();
		$this->cart->save();
   	$this->_reload();
	}
	
	function suspend($suspend_type = 1)
	{
		if (!$this->Employee->has_module_action_permission('sales', 'suspend_sale', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			$this->_reload(array('error' => lang('sales_you_do_not_have_permission_to_suspend_sales')), false);
			return;				
		}
		
		if ($this->config->item('sort_receipt_column'))
		{
			$this->cart->sort_items($this->config->item('sort_receipt_column'));
		}
		
		$data = $this->_get_shared_data();
		$data['receipt_title']= $this->config->item('override_receipt_title') ? $this->config->item('override_receipt_title') : '';
		$data['transaction_time']= date(get_date_format().' '.get_time_format());
		$data['sales_card_statement']= $this->config->item('override_signature_text') ? $this->config->item('override_signature_text') : lang('sales_card_statement','',array(),TRUE);
		
		$exchange_rate = $this->cart->get_exchange_rate() ? $this->cart->get_exchange_rate() : 1;		
		$store_account_in_all_languages = get_all_language_values_for_key('common_store_account','common');
		
		$data['balance'] = 0;
		//Add up balances for all languages
		foreach($store_account_in_all_languages as $store_account_lang)
		{
			//Thanks Mike for math help on how to convert exchange rate back to get correct balance
			$data['balance']+= $this->cart->get_payment_amount($store_account_lang)*pow($exchange_rate,-1);
		}
		
		$this->cart->suspended = $suspend_type;
		//SAVE sale to database
		
		$sale_date = $this->config->item('change_sale_date_when_suspending') ? date('Y-m-d H:i:s') : FALSE;
		if ($this->cart->change_date_enable)
		{
			$sale_date = $this->cart->change_cart_date;			
		}
		
		if ($sale_date !== FALSE)
		{
			$this->cart->change_date_enable = TRUE;
			$this->cart->change_cart_date = $sale_date;
		}		
	
		$sale_id = $this->Sale->save($this->cart);
		
		if ($sale_id == $this->config->item('sale_prefix').' -1')
		{
			$this->_reload(array('error' => lang('sales_transaction_failed')));
			return;
		}
		
		
		$this->cart->destroy();
		$this->cart->save();
		
		if ($this->config->item('show_receipt_after_suspending_sale'))
		{
			redirect('sales/receipt/'.$sale_id);
		}
		else
		{
			$this->_reload(array('success' => lang('sales_successfully_suspended_sale')));
		}
	}
	
	
	function batch_sale()
	{
		$this->load->view("sales/batch");
	}
	
	function _excel_get_header_row()
	{
		return array(lang('common_item_id').'/'.lang('common_item_number').'/'.lang('common_product_id'),lang('common_unit_price'),lang('common_quantity'),lang('common_discount_percent'),lang('common_description'),lang('common_serial_number'));
	}
	
	function excel()
	{	
		$this->load->helper('report');
		$header_row = $this->_excel_get_header_row();
		$this->load->helper('spreadsheet');
		array_to_spreadsheet(array($header_row),'batch_sale_export.'.($this->config->item('spreadsheet_format') == 'XLSX' ? 'xlsx' : 'csv'));
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
		//$this->check_action_permission('add_update');
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
						$price = null;
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

					$description = $sheet->getCellByColumnAndRow(4, $k);
					if (!$description)
					{
						$description = null;
					}
					
					$serial_number = $sheet->getCellByColumnAndRow(5, $k);
					if (!$serial_number)
					{
						$serial_number = null;
					}
					
					
					if($this->cart->is_valid_item_kit($item_id))
					{						
						if (!$this->cart->add_item_kit(new PHPPOSCartItemKitSale(array('unit_price' => $price, 'description' => $description,'discount' => $discount,'cart' => $this->cart,'scan' => $item_id,'quantity' => $quantity))))
						{
							$this->cart->empty_items();
							$this->cart->save();
							echo json_encode( array('success'=>false,'message'=>lang('batch_sales_error')));
							return;
						}
					}
					else
					{
						$item_to_add = new PHPPOSCartItemSale(array('unit_price' => $price, 'description' => $description,'discount' => $discount,'cart' => $this->cart,'scan' => $item_id,'quantity' => $quantity,'serialnumber' => $serial_number));
						
						if($item_to_add->item_id && !$this->cart->add_item($item_to_add))
						{
							$this->cart->empty_items();
							$this->cart->save();
							echo json_encode( array('success'=>false,'message'=>lang('batch_sales_error')));
							$this->db->trans_complete();
							return;
						}
					}
				}
			}
			else 
			{
				$this->cart->save();
				echo json_encode( array('success'=>false,'message'=>lang('common_upload_file_not_supported_format')));
				$this->db->trans_complete();
				return;
			}
		}
		$this->cart->save();
		$this->db->trans_complete();
		echo json_encode(array('success'=>true,'message'=>lang('sales_import_successfull')));
		
	}
	
	function giftcard_exists_and_balance()
	{
		$response = NULL;
		
		if($this->Giftcard->get_giftcard_id($this->input->get('giftcard_number')))
		{
			$giftcard_info = $this->Giftcard->get_info($this->Giftcard->get_giftcard_id($this->input->get('giftcard_number')));
			$response['exists'] = TRUE;
			$response['value'] = to_currency($giftcard_info->value);
		}
		else
		{
			$response['exists'] = FALSE;			
		}
		
		echo json_encode($response);
	}
	
	
	function new_integrated_giftcard()
	{
		if (!$this->Employee->has_module_action_permission('giftcards', 'add_update', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			redirect('no_access/'.$this->module_id);
		}
		
		$data = array();
		$this->load->view("sales/integrated_giftcard_form",$data);
	}
	
	function new_giftcard()
	{
		if (!$this->Employee->has_module_action_permission('giftcards', 'add_update', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			redirect('no_access/'.$this->module_id);
		}
		
		$data = array();
		$data['item_id']=$this->Item->get_item_id(lang('common_giftcard'));
		$this->load->view("sales/giftcard_form",$data);
	}
	
	function set_search_suspended_sale_types()
	{
		$this->session->set_userdata('search_suspended_sale_types',$this->input->post('suspended_types'));
	}
	
	function suspended()
	{
		$data = array();
		$data['controller_name'] = strtolower(get_class());
		$table_data = $this->Sale->get_all_suspended($this->session->userdata('search_suspended_sale_types'));
		$data['manage_table'] = get_suspended_sales_manage_table($table_data, $this);
		$data['suspended_sale_types'] = $this->Sale_types->get_all(!$this->config->item('ecommerce_platform') ? $this->config->item('ecommerce_suspended_sale_type_id') : NULL)->result_array();
		$data['default_columns'] = $this->Sale->get_suspended_sales_default_columns();
		$data['selected_columns'] = $this->Employee->get_suspended_sales_columns_to_display();
		
		$data['all_columns'] = array_merge($data['selected_columns'], $this->Sale->get_suspended_sales_displayable_columns());		
		$this->load->view('sales/suspended', $data);
	}
	
	function get_default_columns(){
		return array('item_id','item_number','name','category_id','cost_price','unit_price','quantity');
	}
	
	function save_column_prefs()
	{
		$this->load->model('Employee_appconfig');
		
		if ($this->input->post('columns'))
		{
			$this->Employee_appconfig->save('suspended_sales_column_prefs',serialize($this->input->post('columns')));
		}
		else
		{
			$this->Employee_appconfig->delete('suspended_sales_column_prefs');			
		}
	}
	
	function reload_table(){
		$data['controller_name'] = strtolower(get_class());
		$table_data = $this->Sale->get_all_suspended($this->session->userdata('search_suspended_sale_types'));
		echo get_suspended_sales_manage_table($table_data, $this);
	}
	
	function change_sale($sale_id)
	{
		$this->check_action_permission('edit_sale');
		$this->cart->destroy();
		$this->cart = PHPPOSCartSale::get_instance_from_sale_id($sale_id,'sale', TRUE);
		
		if ($this->Location->get_info_for_key('enable_credit_card_processing'))
		{
			$this->cart->change_credit_card_payments_to_partial();				
		}
		
		$this->cart->save();
		$this->_reload(array(), false);
	}
	
	function clone_sale($sale_id)
	{
		if ($this->config->item('disable_sale_cloning'))
		{
			$this->_reload(array(), false);
			return;
		}
		
		$this->cart->destroy();
		$this->cart = PHPPOSCartSale::get_instance_from_sale_id($sale_id,'sale', TRUE);
		$this->cart->sale_id = NULL;
		$this->cart->payments = array();
		$this->cart->suspended = 0;
		$this->cart->save();

		$track_payment_types = unserialize($this->config->item('track_payment_types'));
		if ($this->config->item('track_payment_types') && !empty($track_payment_types)){
			if (!$this->Register->is_register_log_open()) {
				redirect(site_url('sales'));
			}
		}

		$this->_reload(array(), false);
	}
	
		
	function unsuspend($sale_id = 0)
	{
		$this->check_action_permission('edit_suspended_sale');
		
		$sale_id = $this->input->post('suspended_sale_id') ? $this->input->post('suspended_sale_id') : $sale_id;
		$this->cart->destroy();
		$this->cart = PHPPOSCartSale::get_instance_from_sale_id($sale_id,'sale', TRUE);
		
		if($this->config->item('automatically_email_receipt'))
		{
			$this->cart->email_receipt = 1;
		}

		if($this->config->item('automatically_sms_receipt_that_works_like_automatically_email_receipt'))
		{
			$this->cart->sms_receipt = 1;
		}

		$this->cart->save();
		$this->_reload(array(), false);
	}
	
	function delete_suspended_sale()
	{
		$this->check_action_permission('delete_suspended_sale');
		$suspended_sale_id = $this->input->post('suspended_sale_id');
		if ($suspended_sale_id)
		{
			$this->cart->is_editing_previous = FALSE;
			$this->cart->sale_id = NULL;
			
			if(is_array($suspended_sale_id)){

				foreach($suspended_sale_id as $sale_id){
					if (!$this->Sale->is_sale_deleted($sale_id))
					{
						$this->Sale->delete($sale_id);
					}
				}

			}else{
				if (!$this->Sale->is_sale_deleted($suspended_sale_id))
				{
					$this->Sale->delete($suspended_sale_id);
				}
			}
		}
		if($this->input->post('redirection')){
			redirect($this->input->post('redirection'));
			return false;
		}
    	redirect('sales/suspended');
	}
	
	function discount_all()
	{
		$discount_all_percent = (float)$this->input->post('discount_all_percent');

		if($this->input->post('name')=="discount_all_percent")
		{
			$discount_all_percent = (float)$this->input->post('value');
			$result = $this->cart->discount_all($discount_all_percent);
			if(!$result)
			{
			  $data['error'] = lang('sales_could_not_discount_item_above_max').' '.lang('sales_the_items_in_the_cart');
 			  $this->_reload($data);
				return;
			}
			
	 	  foreach($this->cart->get_items() as $item)
	 	  {
	 	  	  if ($item->below_cost_price())
	 	  	  { 
		 			  if ($this->config->item('do_not_allow_below_cost'))
		 			  {
		 				  $this->cart->discount_all(0);
		 				  $data['error'] = lang('sales_selling_item_below_cost');
							break;
		 			  }
		 			  else
		 			  {
		 				  $data['warning'] = lang('sales_selling_item_below_cost');
		 			  }
	 		  }
	 	  }			
		}
		elseif ($this->input->post('name') == 'discount_all_flat')
		{
			$item_id = $this->Item->create_or_update_flat_discount_item($this->cart->is_tax_inclusive() ? 1 : 0);
			$description =  strpos($this->input->post('value'), '%',0) ? lang('sales_discount_percent').': '.$this->input->post('value') : '';			
			$discount_amount = strpos($this->input->post('value'), '%',0) !== FALSE ? (($this->cart->get_subtotal() + $this->cart->get_discount_all_fixed()) * (float)$this->input->post('value')/100) : (float)$this->input->post('value');
			$discount_item = new PHPPOSCartItemSale(array('cart' => $this->cart,'scan' => $item_id.'|FORCE_ITEM_ID|','cost_price' => 0 ,'unit_price' => to_currency_no_money($discount_amount),'description' => $description,'quantity' => -1));
			
			if ($discount_item_in_cart = $this->cart->find_similiar_item($discount_item))
			{
				$this->cart->delete_item($this->cart->get_item_index($discount_item_in_cart));				
			}
			$this->cart->add_item($discount_item);
		}
		
		$this->cart->save();
		$this->_reload();
	}
	
	function discount_reason()
	{
		$discount_reason = $this->input->post('value');
		$this->cart->discount_reason = $discount_reason;
		$this->cart->save();
		$this->_reload();
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
		$config['base_url'] = site_url('sales/categories_and_items/'.($category_id ? $category_id : 0));
		$config['uri_segment'] = 4;
		$config['per_page'] = $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14; 
		
		$categories_and_items_response = array();
		$this->load->model('Appfile');
		foreach($categories as $id=>$value)
		{
			$categories_and_items_response[] = array('id' => $id, 'name' => $value['name'], 'color' => $value['color'], 'image_id' => $value['image_id'],'image_timestamp' => $this->Appfile->get_file_timestamp($value['image_id']),'type' => 'category');
		}
		
		//Items
		$items = array();
		
		$items_offset = ($offset - $categories_count > 0 ? $offset - $categories_count : 0);		
		$items_result = $this->Item->get_all_by_category($category_id, $this->config->item('hide_out_of_stock_grid') ? TRUE : FALSE, $items_offset, $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14)->result();
		
		foreach($items_result as $item)
		{
			$img_src = "";
			if ($item->image_id != 'no_image' && trim($item->image_id) != '') {
				$img_src = cacheable_app_file_url($item->image_id);
			}
			
			$size = $item->size ? ' - '.$item->size : '';
			
			if (strpos($item->item_id, 'KIT') === 0)
			{
				$price_to_use = $this->Item_kit->get_sale_price(array('item_kit_id' => str_replace('KIT','',$item->item_id)));
			}
			else
			{
				$price_to_use = $this->Item->get_sale_price(array('item_id' => $item->item_id));
			}
			
			$categories_and_items_response[] = array(
				'id' => $item->item_id,
				'name' => character_limiter($item->name, 58).$size,				
				'image_src' => 	$img_src,
				'has_variations' => count($this->Item_variations->get_variations($item->item_id)) > 0 ? TRUE : FALSE,
				'type' => 'item',		
				'price' => $price_to_use != '0.00' ? to_currency($price_to_use) : FALSE,
				'regular_price' => to_currency($item->unit_price),	
				'different_price' => $price_to_use != $item->unit_price,	
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
			
			if ($variation['unit_price'])
			{
				$price_to_use = $variation['unit_price'];
			}
			else
			{
				$price_to_use = ($cur_item_location_info && $cur_item_location_info->unit_price) ? $cur_item_location_info->unit_price : $cur_item_info->unit_price;
			}
			
			$cur_item_variation_location_info = $this->Item_variation_location->get_info($variation_id);
			
			if (!($this->config->item('hide_out_of_stock_grid') && $cur_item_variation_location_info->quantity <=0))
			{
			
			$variations[] = array(
				'id' => $item_id.'#'.$variation_id,
				'name' => $variation['name'] ? $variation['name'] : implode(', ', array_column($variation['attributes'],'label')),				
				'image_src' => 	$img_src,
				'type' => 'variation',		
				'has_variations' => FALSE,
				'price' => $price_to_use !== FALSE ? to_currency($price_to_use) : FALSE,		
				);	
			}
		}

		
		echo json_encode(H($variations));
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
		$categories = $this->Category->get_all($parent_id,FALSE, $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14, $offset);
		
		$categories_count = $this->Category->count_all($parent_id);		
		$config['base_url'] = site_url('sales/categories/'.($parent_id ? $parent_id : 0));
		$config['uri_segment'] = 4;
		$config['total_rows'] = $categories_count;
		$config['per_page'] = $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14; 
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		
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
	
	function tags($offset = 0)
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		
		$tags = $this->Tag->get_all($this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14, $offset,'name','asc',FALSE);
		
		$tags_count = $this->Tag->count_all(FALSE);		
		$config['base_url'] = site_url('sales/tags');
		$config['uri_segment'] = 3;
		$config['total_rows'] = $tags_count;
		$config['per_page'] = $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14; 
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		
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

	function suppliers($offset = 0)
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		
		$suppliers = $this->Supplier->get_all(0, $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14, $offset);
		
		$suppliers_count = $this->Supplier->count_all();		
		$config['base_url'] = site_url('sales/suppliers');
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
	
	
	function tag_items($tag_id, $offset = 0)
	{
		$this->load->model('Item_variations');
		
		//allow parallel searchs to improve performance.
		session_write_close();
		
		$config['base_url'] = site_url('sales/tag_items/'.($tag_id ? $tag_id : 0));
		$config['uri_segment'] = 4;
		$config['per_page'] = $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14; 
		
				
		//Items
		$items = array();
		
		$items_result = $this->Item->get_all_by_tag($tag_id, $this->config->item('hide_out_of_stock_grid') ? TRUE : FALSE, $offset, $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14)->result();
		
		
		foreach($items_result as $item)
		{
			$img_src = "";
			if ($item->image_id != 'no_image' && trim($item->image_id) != '') {
				$img_src = cacheable_app_file_url($item->image_id);
			}

			if (strpos($item->item_id, 'KIT') === 0)
			{
				$price_to_use = $this->Item_kit->get_sale_price(array('item_kit_id' => str_replace('KIT','',$item->item_id)));	
			}
			else
			{
				$price_to_use = $this->Item->get_sale_price(array('item_id' => $item->item_id));	
			}

			$items[] = array(
				'id' => $item->item_id,
				'name' => character_limiter($item->name, 58),				
				'image_src' => 	$img_src,
				'type' => 'item',		
				'has_variations' => count($this->Item_variations->get_variations($item->item_id)) > 0 ? TRUE : FALSE,
				'price' => $price_to_use != '0.00' ? to_currency($price_to_use) : FALSE,
				'regular_price' => to_currency($item->unit_price),	
				'different_price' => $price_to_use != $item->unit_price,	
			);	
		}
	
		$items_count = $this->Item->count_all_by_tag($tag_id);
		
		$data = array();
		$data['items'] = H($items);
		$config['total_rows'] = $items_count;
		$this->load->library('pagination');$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		
		echo json_encode($data);
	}
	
	function favorite_items($offset = 0)
	{
		$this->load->model('Item_variations');
		
		//allow parallel searchs to improve performance.
		session_write_close();
		
		$config['base_url'] = site_url('sales/favorite_items/');
		$config['uri_segment'] = 3;
		$config['per_page'] = $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid') : 14; 
		
		//Items
		$items = array();
		
		$items_result = $this->Item->get_all_favorite_items($this->config->item('hide_out_of_stock_grid') ? TRUE : FALSE, $offset, $this->config->item('number_of_items_in_grid') ? $this->config->item('number_of_items_in_grid')+4 : 18)->result();
		
		
		foreach($items_result as $item)
		{
			$size = $item->size ? ' - '.$item->size : '';
			
			$img_src = "";
			if ($item->image_id != 'no_image' && trim($item->image_id) != '') {
				$img_src = cacheable_app_file_url($item->image_id);
			}

			if (strpos($item->item_id, 'KIT') === 0)
			{
				$price_to_use = $this->Item_kit->get_sale_price(array('item_kit_id' => str_replace('KIT','',$item->item_id)));	
			}
			else
			{
				$price_to_use = $this->Item->get_sale_price(array('item_id' => $item->item_id));	
			}
			
			$items[] = array(
				'id' => $item->item_id,
				'name' => character_limiter($item->name, 58).$size,				
				'image_src' => 	$img_src,
				'type' => 'item',		
				'has_variations' => count($this->Item_variations->get_variations($item->item_id)) > 0 ? TRUE : FALSE,
				'price' => $price_to_use != '0.00' ? to_currency($price_to_use) : FALSE,
				'regular_price' => to_currency($item->unit_price),	
				'different_price' => $price_to_use != $item->unit_price,	
			);	
		}
	
		$items_count = $this->Item->count_all_favorite_items();
		
		$data = array();
		$data['items'] = H($items);
		$config['total_rows'] = $items_count;
		$this->load->library('pagination');$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		
		echo json_encode($data);
	}
	
	function supplier_items($supplier_id, $offset = 0)
	{
		$this->load->model('Item_variations');
		
		//allow parallel searchs to improve performance.
		session_write_close();
		
		$config['base_url'] = site_url('sales/supplier_items/'.($supplier_id ? $supplier_id : 0));
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

			$price_to_use = $this->Item->get_sale_price(array('item_id' => $item->item_id));	
	
			$items[] = array(
				'id' => $item->item_id,
				'name' => character_limiter($item->name, 58),				
				'image_src' => 	$img_src,
				'type' => 'item',		
				'has_variations' => count($this->Item_variations->get_variations($item->item_id)) > 0 ? TRUE : FALSE,
				'price' => $price_to_use != '0.00' ? to_currency($price_to_use) : FALSE,
				'regular_price' => to_currency($item->unit_price),	
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
		
	function delete_tax($name)
	{
		$this->check_action_permission('delete_taxes');
		$name = rawurldecode($name);
		$this->cart->add_excluded_tax($name);
		$this->cart->save();
		$this->_reload();
	}

	function view_receipt_modal()
	{
		$this->load->view('sales/lookup_modal');
	}
	
	function set_delivery()
	{
		$this->cart->set_has_delivery($this->input->post('delivery'));
		
		if($this->input->post('delivery') == '0')
		{
			$index_to_delete = $this->cart->get_index_for_delivery_item();
			if ($index_to_delete !== FALSE)
			{
				$this->cart->delete_item($index_to_delete);
			}
		}
		
		$data = array();
		$this->cart->save();
		
		$this->_reload($data);
	}
	
	function set_delivery_info()
	{
	  $data = array();
		$this->cart->set_has_delivery(1);
		
 	  $this->cart->set_delivery_person_info($this->input->post('delivery_person_info'));
 	  $this->cart->set_delivery_info($this->input->post('delivery_info'));
		$this->cart->set_delivery_tax_group_id($this->input->post('delivery_tax_group_id'));
		
		$delivery_item_id = $this->Item->create_or_update_delivery_item();
		
		$delivery_fee = $this->input->post('delivery_fee');
		
		if ($delivery_fee)
		{
			$delivery_item = new PHPPOSCartItemSale(array('cart' => $this->cart,'scan' => $delivery_item_id.'|FORCE_ITEM_ID|','cost_price' => to_currency_no_money($delivery_fee) ,'unit_price' => to_currency_no_money($delivery_fee),'quantity' => 1));
		
			if ($delivery_item_in_cart = $this->cart->find_similiar_item($delivery_item))
			{
				$this->cart->delete_item($this->cart->get_item_index($delivery_item_in_cart));				
			}
		
			$this->cart->add_item($delivery_item);
		}
		
		$this->cart->save();
  	$this->_reload($data);
	}
	
	function view_delivery_modal()
	{
		$this->lang->load('deliveries');
		$this->load->model('Tax_class');
		$this->load->model('Shipping_zone');
		$this->load->model('Zip');
		$this->load->model('Delivery');
		
		$tax_classes = array();
		$tax_classes[''] = lang('common_none');
		
		foreach($this->Tax_class->get_all()->result_array() as $tax_class)
		{
			$tax_classes[$tax_class['id']] = $tax_class['name'];
		}
		
		$delivery_info = $this->cart->get_delivery_info();
		
		if (empty($delivery_info))
		{
			$delivery_info['comment'] = '';
			$delivery_info['sale_id'] = '';
			$delivery_info['tracking_number'] = '';
			$delivery_info['is_pickup'] = 0;
			$delivery_info['delivery_employee_person_id'] = $this->config->item('default_employee_for_deliveries') ? $this->config->item('default_employee_for_deliveries') : $this->Employee->get_logged_in_employee_info()->person_id;
			$delivery_info['status'] = NULL;
			$delivery_info['category_id'] = NULL;
			$delivery_info['duration'] = 30;
		}
		
		$delivery_person_info = $this->cart->get_delivery_person_info();
		
		$customer_info = $this->Customer->get_info($this->cart->customer_id);
		
		if (empty($delivery_person_info))
		{
			$delivery_person_info['first_name'] = $customer_info->first_name;
			$delivery_person_info['last_name'] = $customer_info->last_name;
			$delivery_person_info['phone_number'] = $customer_info->phone_number;
			$delivery_person_info['email'] = $customer_info->email;
			$delivery_person_info['address_1'] = $customer_info->address_1;
			$delivery_person_info['address_2'] = $customer_info->address_2;
			$delivery_person_info['city'] = $customer_info->city;
			$delivery_person_info['state'] = $customer_info->state;
			$delivery_person_info['zip'] = $customer_info->zip;
			$delivery_person_info['country'] = $customer_info->country;
						
			$this->cart->set_delivery_person_info($delivery_person_info);
		}
		
		$zip_lookup = $this->Zip->lookup($delivery_person_info['zip'])->row_array();
		$zip_zones = array();
		
		foreach($this->Zip->get_all()->result_array() as $zip)
		{
			$zip_zones[$zip['name']] = $zip['shipping_zone_id'];
		}
		
		$shipping_zones = array();
		$shipping_zones['0'] =lang('common_none');
		
		$shipping_zone_id = isset($delivery_info['shipping_zone_id']) ? $delivery_info['shipping_zone_id'] : (isset($zip_lookup['shipping_zone_id']) ? $zip_lookup['shipping_zone_id'] : '');
		
		foreach($this->Shipping_zone->get_all()->result_array() as $shipping_zone)
		{
			$shipping_zones[$shipping_zone['id']] = $shipping_zone['name'];
		}
		
		$shipping_zone_info = array();
		foreach($this->Shipping_zone->get_all()->result_array() as $shipping_zone)
		{
			$shipping_zone_info[$shipping_zone['id']] = array('name' => $shipping_zone['name'], 'fee' => $shipping_zone['fee'], 'tax_class_id' => $shipping_zone['tax_class_id']);
		}
		
		$delivery_tax_group_id = $this->cart->get_delivery_tax_group_id();
		if($delivery_tax_group_id === NULL)//haven't set group
		{
			$delivery_tax_group_id = $customer_info->tax_class_id;
		}
		
		$delivery_providers = $this->Shipping_provider->get_all()->result_array();
		$delivery_methods = $this->Shipping_method->get_all()->result_array();
		
		$providers_with_methods = array();
		
		foreach($delivery_providers as $provider)
		{
			$providers_with_methods[$provider['id']] = $provider;
		}
		
		foreach($delivery_methods as $method)
		{
			$providers_with_methods[$method['shipping_provider_id']]['methods'][] = $method;			
		}
		
		$delivery_fee = $this->cart->get_delivery_item_price_in_cart();
		
		$deliveries_status[''] = lang('common_none');
		
		foreach($this->Delivery->get_all_statuses() as $id => $row)
		{
			$deliveries_status[$id] = $row['name'];
		}

		$data['deliveries_status'] = $deliveries_status;

		$categories = $this->Delivery_category->get_all();
		
		$this->load->view('sales/delivery_modal', array('delivery_person_info' => $delivery_person_info, 'delivery_info' => $delivery_info, 'providers_with_methods' => $providers_with_methods, 'tax_classes' => $tax_classes, 'delivery_tax_group_id' => $delivery_tax_group_id, 'shipping_zone_id' => $shipping_zone_id, 'shipping_zone_info' => $shipping_zone_info, 'shipping_zones' => $shipping_zones, 'delivery_fee' => $delivery_fee, 'zip_zones' => $zip_zones, 'deliveries_status' => $deliveries_status, 'categories' => $categories));
	}
	
	function sig_save($sale_register_id_display = false)
	{
		$this->load->model('Register_cart');
		
		$this->load->model('Appfile');
		$sale_id = $this->input->post('sale_id');
		$sale_info = $this->Sale->get_info($sale_id)->row_array();
		
		//If we have a signature delete it
		if ($sale_info['signature_image_id'])
		{
			$this->Sale->update(array('signature_image_id' => NULL), $sale_id);
			$this->Appfile->delete($sale_info['signature_image_id']);
		}
		
		$image = base64_decode($this->input->post('image'));
    	$image_file_id = $this->Appfile->save('signature_'.$sale_id.'.png', $image);
		$this->Sale->update(array('signature_image_id' => $image_file_id), $sale_id);
		
		$this->Register_cart->remove_data('sale_id',$sale_register_id_display ? $sale_register_id_display : $this->Employee->get_logged_in_employee_current_register_id());		
		
		echo json_encode(array('file_id' => $image_file_id, 'file_timestamp' => $this->Appfile->get_file_timestamp($image_file_id)));
	}
	
	function customer_display($register_id = false)
	{
		if (!$register_id)
		{
			$register_id = $this->Employee->get_logged_in_employee_current_register_id();
		}
		
		if ($this->Register->exists($register_id))
		{
			
			$this->load->view('sales/customer_display_initial', array('register_id' => $register_id,'fullscreen_customer_display'=> $this->session->userdata('fullscreen_customer_display')));
		}
	}
	
	function customer_display_update($register_id = false)
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		$this->load->model('Register_cart');
		
		if (!$register_id)
		{
			$register_id = $this->Employee->get_logged_in_employee_current_register_id();
		}
		
		if ($this->Register->exists($register_id))
		{
			$data = $this->Register_cart->get_data($register_id);
			$data['mode'] = "sale";
			
			if (isset($data['sale_id']))
			{
				$sale_info = $this->Sale->get_info($data['sale_id'])->row_array();
				$customer_id = $sale_info['customer_id'];
				
				if($customer_id)
				{
					$cust_info=$this->Customer->get_info($customer_id);
					$data['customer']=$cust_info->first_name.' '.$cust_info->last_name.($cust_info->account_number==''  ? '':' - '.$cust_info->account_number);
					$data['customer_company']= $cust_info->company_name;
					$data['customer_email'] = $cust_info->email;			
				}		
			}
		
			$data['fullscreen_customer_display'] = $this->session->userdata('fullscreen_customer_display');
			
			$this->load->view("sales/customer_display",$data);
		}
	}
	
	function customer_display_info($register_id)
	{
		
		//allow parallel searchs to improve performance.
		session_write_close();
		
		$this->load->model('Register_cart');
		
		$return = array();
		$return['sale_id'] = false;
		
		if (!$register_id)
		{
			$register_id = $this->Employee->get_logged_in_employee_current_register_id();
		}
		
		if ($this->Register->exists($register_id))
		{
			$data = $this->Register_cart->get_data($register_id);
			if (isset($data['sale_id']))
			{
				
				$sale_info = $this->Sale->get_info($data['sale_id'])->row_array();
				$return['sale_id'] = $data['sale_id'];
				$signature_needed = $this->config->item('capture_sig_for_all_payments') || (strpos($sale_info['payment_type'],lang('common_credit')) !== FALSE )||  (strpos($sale_info['payment_type'],lang('common_store_account')) !== FALSE );
				$return['signature_needed'] = $signature_needed;
				
			}
		}		
		echo json_encode(H($return));
		
	}
	
	function open_drawer()
	{
		$this->load->view('sales/open_drawer');
	}
	
	function disable_test_mode()
	{
		$this->load->helper('demo');
		if (!is_on_demo_host())
		{
			$this->Appconfig->save('test_mode','0');
		}
		
		redirect(site_url('sales'));	
	}
	
	function enable_test_mode()
	{
		$this->load->helper('demo');
		if (!is_on_demo_host())
		{
			$this->Appconfig->save('test_mode','1');
		}
		redirect(site_url('sales'));	
	}
	
	function create_po($sale_id)
	{
		$this->load->model('Sale');
		$this->load->model('Item_location');
		$this->load->model('Item');
				
				
		require_once (APPPATH."models/cart/PHPPOSCartRecv.php");
		$cart = PHPPOSCartRecv::get_instance('receiving');
    $cart->destroy();
				
				
		$items = $this->Sale->get_sale_items($sale_id)->result_array(); 
		
		$item_ids = array();
		
		foreach($items as $item)
		{
			$item_id = $item['item_id'];
			$item_ids[$item_id] = TRUE;
		}
		
		foreach(array_keys($item_ids) as $item_id)
		{
			$quantity_to_add= 1;
			$cur_item_location_info = $this->Item_location->get_info($item_id);
			$cur_item_info = $this->Item->get_info($item_id);
			$replenish_level = ($cur_item_location_info && $cur_item_location_info->replenish_level) ? $cur_item_location_info->replenish_level : $cur_item_info->replenish_level;
			$reorder_level = ($cur_item_location_info && $cur_item_location_info->reorder_level) ? $cur_item_location_info->reorder_level : $cur_item_info->reorder_level;
			$quantity_to_add = ($replenish_level ? $replenish_level : $reorder_level) - $cur_item_location_info->quantity;
			$cart->add_item(new PHPPOSCartItemRecv(array(
				'scan' => $item_id.'|FORCE_ITEM_ID|',
				'quantity' => max(0,$quantity_to_add),
				'cart' => $cart,
			)));
			
		}

		$cart->is_po = TRUE;
		$cart->set_mode('purchase_order');	
		$cart->save();

		redirect('receivings');
	}
	
	function exchange_to()
	{
		$data = array();
		$rate = $this->input->post('rate');
		$this->cart->set_exchange_details($rate);
		$this->cart->save();
  	$this->_reload($data);
	}
	
	private function _get_shared_data()
	{
		$data = $this->cart->to_array();
		
		$modes = array('sale'=>lang('sales_sale'),'return'=>lang('sales_return'), 'estimate' => lang('common_estimate'));
		
		if (!$this->Employee->has_module_action_permission('sales', 'process_returns', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			unset($modes['return']);
		}
		if($this->config->item('customers_store_accounts')) 
		{
			$modes['store_account_payment'] = lang('common_store_account_payment');
		}
		$data['modes'] = $modes;
		
		foreach($this->view_data as $key=>$value)
		{
			$data[$key] = $value;
		}
		
		$this->load->model('Sale_types');
		$data['additional_sale_types_suspended'] = $this->Sale_types->get_all(!$this->config->item('ecommerce_platform') ? $this->config->item('ecommerce_suspended_sale_type_id') : NULL)->result_array();
		return $data;
	}
	
	function _is_dob_in_correct_format($dob)
	{
		$format = get_date_format_extended();
		$pattern = "/[^\-\/]/";
		$format = preg_replace($pattern, "#", $format);
		$dob_format = preg_replace($pattern, "#", $dob);
		
		return $dob_format == $format;
	}
	
	function save_dob($line_just_added)
	{
		$data = array();
		$dob = $this->input->post('dob');
		
		if ($this->config->item('strict_age_format_check'))
		{
			if (!$this->_is_dob_in_correct_format($dob))
			{
				$message = lang('sales_age_date_format_incorrect'). ' ('.get_date_format_extended().')';
				$this->cart->delete_item($line_just_added);
				$this->cart->save();
				echo json_encode(array('success' =>false,'message' => $message));
				return;
			}
		}
		$age = round((time()-strtotime($dob))/(3600*24*365.25));
		$this->cart->age = $age;
		$age_verified = $this->cart->is_cart_age_verified();
		
		if (!$age_verified)
		{
			$this->cart->delete_item($line_just_added);
		}
		
		$this->cart->save();
		
		if ($age_verified)
		{
			$message = lang('sales_age_verify_success');
		}
		else
		{
			$message = lang('sales_age_verify_error');
		}
		echo json_encode(array('success' =>$age_verified,'message' => $message));
	}
	function opened_customer_facing_display()
	{
		$this->session->set_userdata('opened_customer_facing_display', TRUE);
	}
	
	function add_integrated_giftcard()
	{
		if (!$this->Employee->has_module_action_permission('giftcards', 'add_update', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			redirect('no_access/'.$this->module_id);
		}
		
		$giftcard_data = array(
		'giftcard_number'=>$this->input->post('giftcard_number'),
		'value'=>$this->input->post('value'),
		'customer_id'=>$this->cart->customer_id ? $this->cart->customer_id : NULL,
		'integrated_gift_card'=> 1,
		'integrated_auth_code' => $this->input->post('integrated_auth_code') ? $this->input->post('integrated_auth_code') : NULL,
		);
		
		$this->Giftcard->save($giftcard_data);
		
		$this->load->model('Item');
		$this->load->model('Category');
		$item_id = $this->Item->create_or_update_integrated_gift_card_item($this->input->post('value'),$this->input->post('giftcard_number'));	
		
				
		echo json_encode(array('success'=>true,'item_id'=>$item_id));
			
	}
	
	function do_refill_integrated_giftcard()
	{
		if (!$this->Employee->has_module_action_permission('giftcards', 'add_update', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			redirect('no_access/'.$this->module_id);
		}
						
		$this->Giftcard->add_giftcard_balance($this->input->post('giftcard_number'),$this->input->post('value'));
		$this->load->model('Item');
		$this->load->model('Category');
		$item_id = $this->Item->create_or_update_integrated_gift_card_item($this->input->post('value'),$this->input->post('giftcard_number'));	
				
		echo json_encode(array('success'=>true,'item_id'=>$item_id));
	}
	
	function refill_integrated_giftcard()
	{
		if (!$this->Employee->has_module_action_permission('giftcards', 'add_update', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			redirect('no_access/'.$this->module_id);
		}
		
		$data = array('refill' => TRUE);
		$this->load->view("sales/integrated_giftcard_form",$data);
	}
	
	function custom_fields()
	{
		$this->lang->load('config');
		$fields_prefs = $this->config->item('sale_custom_field_prefs') ? unserialize($this->config->item('sale_custom_field_prefs')) : array();
		$data = array_merge(array('controller_name' => strtolower(get_class())),$fields_prefs);
		$locations_list = $this->Location->get_all()->result();
		$data['locations'] = $locations_list;
		$this->load->view('custom_fields',$data);
	}
	
	function save_custom_fields()
	{
		$this->load->model('Appconfig');
		$this->Appconfig->save('sale_custom_field_prefs',serialize($this->input->post()));
	}
	
	function save_custom_field()
	{
		$k = str_replace(array('custom_field_','_value'),array('',''),$this->input->post('name'));
		if ($this->Sale->get_custom_field($k) !== FALSE)
		{		
			if($this->Sale->get_custom_field($k,'type') == 'date')
			{
				$this->cart->{$this->input->post('name')} = (string)strtotime($this->input->post('value'));
			}
			elseif($this->Sale->get_custom_field($k,'type') == 'image')
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
			elseif($this->Sale->get_custom_field($k,'type') == 'file')
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
	
	function set_internal_notes()
	{
		$person_data = array();
		$customer_data = array('internal_notes' => $this->input->post('internal_notes'));
		$this->load->model('Customer');
		$this->Customer->save_customer($person_data,$customer_data,$this->cart->customer_id);
	}
	
	function edit_subtotal()
	{
		$new_subtotal = $this->input->post('value');
		$this->cart->edit_subtotal($new_subtotal);
		$this->cart->save();
		$this->_reload(array());
	}
	function get_attribute_values() {
		/*
		** Destroy Session if already Exits 
		** Fetch Variations for Edit 
		** Use function in sales/register.php
		*/
		$this->session->unset_userdata('editable_popup');
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
		$editable_popup 		= 	$this->session->set_userdata('editable_popup',$attr_id); 
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
			echo "<a href='javascript:fetch_attr_values(".htmlspecialchars(json_encode(trim($key)), ENT_QUOTES).");' class='btn btn-primary popup_button' style='margin:5px;' id='attri_".htmlspecialchars(trim($key))."'>".trim($key)."</a>";
		}
		
		$this->session->set_userdata('show_model',$attributes_final_array);
		return ;
	}
	
	function get_attributes_values() {
		$current_location = $this->Employee->get_logged_in_employee_current_location_id();

		$attr_id 		= 	$_REQUEST["attr_id"];
		$check_attr 	= 	explode(",",$attr_id);
		$count 			=  	count($check_attr);
		$get_data 		= 	$this->session->userdata('popup');
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
					
					if ($this->session->userdata('editable_popup') !== NULL)
					{
						$line_no 	= $this->session->userdata('editable_popup');
						$this->session->unset_userdata('editable_popup');
					}
					else
					{						
						$line_no 	= count($line_no) -1;
					}
					
					$this->db->from('location_item_variations');
					$this->db->where('item_variation_id', $index);
					$this->db->where('location_id', $current_location);
					$query = $this->db->get();
					$variation_tbl = $query->row();
					if($variation_tbl)
						$variation_qty = $variation_tbl->quantity;
					else
						$variation_qty = 0;

					if($this->config->item('do_not_allow_out_of_stock_items_to_be_sold') && $variation_qty <= 0) {
						self::delete_item($line_no);
						echo '<script>show_feedback("error","'.lang('sales_unable_to_add_item_out_of_stock').'");</script>';
					}else {
						$_POST["name"] 	= "variation";
						$_POST["value"] = $index;
						self::edit_item_variation($line_no);
					}
					echo '<script>
						
						$("#choose_var").modal("hide");
						setTimeout(function()
						{
 							jQuery("#register_container").load("'.site_url('sales/reload').'");
						},200);
						</script>';
					die;
				} else { 
					echo "<a href='javascript:fetch_attr_value(".htmlspecialchars(json_encode(trim($link.",".$index)), ENT_QUOTES).");' class='btn btn-success popup_button' style='margin:5px;' id='attri_".htmlspecialchars(trim($index), ENT_QUOTES)."'>".trim($index)."</a>";
				}
				
			}
		}
		
		function delete_custom_field_value($k)
		{
			$this->cart->{"custom_field_${k}_value"} = NULL;
			$this->cart->save();
		}
		
		function enter_tips()
		{
			$this->load->view('sales/enter_tips');
		}
		
		function save_tip($sale_id)
		{
			$can_save_tip = TRUE;
			
			$tip_amount = $this->input->post('tip');
			if ($this->Sale->can_cc_tip_sale($sale_id))
			{
				
				$credit_card_processor = $this->_get_cc_processor();
				if ($credit_card_processor && ($result = $credit_card_processor->tip($sale_id,$tip_amount))!== TRUE)
				{
					$can_save_tip = FALSE;
				}
			}
			
			if ($can_save_tip)
			{
				$sale_data = array('tip' => $tip_amount);
				if (!$this->Sale->update($sale_data, $sale_id))
				{
					echo $result;
					$this->output->set_status_header(400);	
				}
			}
			else
			{
				echo $result;
				$this->output->set_status_header(400);	
			}
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
		
		function customers_offline_data($limit = 100,$offset=0)
		{
			session_write_close();
			$this->load->helper('array');
			$customers = $this->Customer->get_all('',0,$limit,$offset);
			
			$return = array();
			while($customer = $customers->unbuffered_row('array'))
			{				
				 $return[]=array(
					'first_name' => $customer['first_name'],
					'last_name' => $customer['last_name'],
					'account_number' => $customer['account_number'],
					'person_id' => $customer['person_id'],

				);
				
				
			}
			
			echo json_encode($return);
		}
				
				
		function items_offline_data($limit=100,$offset = 0)
		{
			session_write_close();
			$this->load->helper('array');
			$this->load->model('Item_modifier');
			$this->load->model('Item_variations');
			
			$items = $this->Item->get_all_offline($limit,$offset);
						
			$return = array();
			
			while($item = $items->unbuffered_row('array'))
			{
				$modifiers = array();
				
					
				foreach($this->Item_modifier->get_modifiers_for_item(new PHPPOSCartItemSale(array('scan' => $item['item_id'].'|FORCE_ITEM_ID|','cart' => new PHPPOSCartSale())))->result_array() as $modifier)
				{
					foreach($this->Item_modifier->get_modifier_items($modifier['id'])->result_array() as $modifier_item)
					{
						$modifiers[] = array(
							'modifier_id' => $modifier['id'],
							'modifier_name' => $modifier['name'],

							'modifier_item_id' => $modifier_item['id'],
							'modifier_item_name' => $modifier_item['name'],
							
							'cost_price' => $modifier_item['cost_price'],
							'unit_price' => $modifier_item['unit_price'],
						
						);						
					}
				}
				
				
				$item['modifiers'] = $modifiers;
				
				
				$variations = array();
				foreach($this->Item_variations->get_variations($item['item_id']) as $variation_id => $variation)
				{
					
					$variations[] = array(
						'variation_id' => $variation_id,
						'name' => $variation['name'] ? $variation['name'] : implode(', ', array_column($variation['attributes'],'label')),
						'attributes' =>$variation['attributes'],
						'cost_price' => $variation['cost_price'],
						'unit_price' => $variation['unit_price'],
						'promo_price' => $variation['promo_price'],
						'start_date' => $variation['start_date'],
						'end_date' => $variation['end_date'],
					);
					
				}
				
				$item['variations'] = $variations;
				$this->load->model('Item_taxes_finder');
				
				if(strtoupper(substr($item['item_id'], 0, 3)) == 'KIT')
				{
					$item['taxes'] = $this->Item_kit_taxes_finder->get_info(str_replace('KIT ','',$item['item_id']));					
				}
				else
				{
					$item['taxes'] = $this->Item_taxes_finder->get_info($item['item_id']);					
				}
				
				$return[] = $item;
			}
						
			
			echo json_encode($return);
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
		
		function save_modifiers($line)
		{
			$item = $this->cart->get_item($line);
			
			$modifiers = $this->input->post('modifiers') ? $this->input->post('modifiers') : array();
			$item->modifier_items = array();
			foreach($modifiers as $modifier_item_id)
			{
				$modifier_item_info = $this->Item_modifier->get_modifier_item_info($modifier_item_id);
				$display_name = to_currency($modifier_item_info['unit_price']).': '.$modifier_item_info['modifier_name'].' > '.$modifier_item_info['modifier_item_name'];
				$item->modifier_items[$modifier_item_id] = array('display_name' => $display_name,'unit_price' => $modifier_item_info['unit_price'],'cost_price' => $modifier_item_info['cost_price']);
			}
			
			$this->cart->save();
			$data = array();
			$this->_reload($data);
		}

		function get_modifiers()
		{
			$line = $this->input->get('line');
			$item = $this->cart->get_item($line);
			
			echo '<div class="container"><form action="'.site_url('sales/save_modifiers/'.$line).'" id="modifier_form" method="POST">';
				foreach($this->Item_modifier->get_modifiers_for_item($item)->result_array() as $modifier)
				{
					foreach($this->Item_modifier->get_modifier_items($modifier['id'])->result_array() as $modifier_item)
					{
						echo '<div class="row">';
					echo form_label($modifier['name'].' > '.$modifier_item['name'].': '.to_currency($modifier_item['unit_price']), '',array('class'=>'col-sm-4 col-md-4 col-lg-4 control-label wide')); 
					echo form_checkbox(array(
						'name'=>'modifiers[]',
						'id'=>'modifier_'.$modifier_item['id'],
						'class' => 'modifier',
						'value'=>$modifier_item['id'],
						'checked'=>$this->cart->get_item($line)->has_modifier_item($modifier_item['id'])));
					echo '<label for="modifier_'.$modifier_item['id'].'"><span></span></label></div>';

				}
			}
			
			echo '<input type="submit" class="btn btn-primary" /></form><script>$("#modifier_form").ajaxForm({target: "#register_container", beforeSubmit: function(){jQuery("#choose_modifiers").modal("hide");}, success: itemScannedSuccess});</script>';
		}
				
		function receipts()
		{
			session_write_close();
			$date = $this->input->get('date');
			$location_id = $this->input->get('location_id');
			$sale_type = $this->input->get('sale_type');
			
			$this->load->view('sales/receipt_date_selector');
			
			foreach($this->Sale->get_sale_ids_for_date($date,$location_id,$sale_type) as $sale_id)
			{
				$receipt_cart = PHPPOSCartSale::get_instance_from_sale_id($sale_id);
				if ($this->config->item('sort_receipt_column'))
				{
					$receipt_cart->sort_items($this->config->item('sort_receipt_column'));
				}
		
				$data = $this->_get_shared_data();
		
				$data = array_merge($data,$receipt_cart->to_array());
				$data['is_sale'] = FALSE;
				$sale_info = $this->Sale->get_info($sale_id)->row_array();
				$data['is_sale_cash_payment'] = $this->cart->has_cash_payment();
				$data['show_payment_times'] = TRUE;
				$data['signature_file_id'] = $sale_info['signature_image_id'];
		
				$tier_id = $sale_info['tier_id'];
				$tier_info = $this->Tier->get_info($tier_id);
				$data['tier'] = $tier_info->name;
				$data['register_name'] = $this->Register->get_register_name($sale_info['register_id']);
				$data['override_location_id'] = $sale_info['location_id'];
				$data['deleted'] = $sale_info['deleted'];

				$data['receipt_title']= $this->config->item('override_receipt_title') ? $this->config->item('override_receipt_title') : ( !$receipt_cart->suspended ? lang('sales_receipt') : '');
				$data['sales_card_statement']= $this->config->item('override_signature_text') ? $this->config->item('override_signature_text') : lang('sales_card_statement','',array(),TRUE);
				
				$data['transaction_time']= date(get_date_format().' '.get_time_format(), strtotime($sale_info['sale_time']));
				$customer_id=$this->cart->customer_id;
		
				$emp_info=$this->Employee->get_info($sale_info['employee_id']);
				$sold_by_employee_id=$sale_info['sold_by_employee_id'];
				$sale_emp_info=$this->Employee->get_info($sold_by_employee_id);
				$data['payment_type']=$sale_info['payment_type'];
				$data['amount_change']=$receipt_cart->get_amount_due() * -1;
				$data['employee']=$emp_info->first_name.' '.$emp_info->last_name.($sold_by_employee_id && $sold_by_employee_id != $sale_info['employee_id'] ? '/'. $sale_emp_info->first_name.' '.$sale_emp_info->last_name: '');
				$data['ref_no'] = $sale_info['cc_ref_no'];
				$data['auth_code'] = $sale_info['auth_code'];
				$data['discount_exists'] = $this->_does_discount_exists($data['cart_items']);
				$data['disable_loyalty'] = 0;
				$data['sale_id']=$this->config->item('sale_prefix').' '.$sale_id;
				$data['sale_id_raw']=$sale_id;
				$data['store_account_payment'] = FALSE;
				$data['is_purchase_points'] = FALSE;
		
				foreach($data['cart_items'] as $item)
				{
					if ($item->name == lang('common_store_account_payment'))
					{
						$data['store_account_payment'] = TRUE;
						break;
					}
				}

				foreach($data['cart_items'] as $item)
				{
					if ($item->name == lang('common_purchase_points'))
					{
						$data['is_purchase_points'] = TRUE;
						break;
					}
				}
		
				if ($sale_info['suspended'] > 0)
				{
					if ($sale_info['suspended'] == 1)
					{
						$data['sale_type'] = ($this->config->item('user_configured_layaway_name') ? $this->config->item('user_configured_layaway_name') : lang('common_layaway'));
					}
					elseif ($sale_info['suspended'] == 2)
					{
						$data['sale_type'] = lang('common_estimate');				
					}
					else
					{
						$this->load->model('Sale_types');
						$data['sale_type'] = $this->Sale_types->get_info($sale_info['suspended'])->name;				
					}
				}
		
				$exchange_rate = $receipt_cart->get_exchange_rate() ? $receipt_cart->get_exchange_rate() : 1;
		
				if($receipt_cart->get_has_delivery())
				{
					$data['delivery_person_info'] = $receipt_cart->get_delivery_person_info();
						
					$data['delivery_info'] = $receipt_cart->get_delivery_info();
				}
			
				$data['standalone'] = TRUE;
				$this->load->view('sales/receipt',$data);
			}
		
		}
		
		function sync_offline_sales()
		{
			$sales = json_decode($this->input->post('offline_sales'), TRUE);
			
			foreach($sales as $offline_sale)
			{
				$offline_sale_cart = new PHPPOSCartSale();
				
				$offline_sale_cart->location_id = $this->Employee->get_logged_in_employee_current_location_id();;
				date_default_timezone_set($this->Location->get_info_for_key('timezone',$offline_sale_cart->location_id));
			
				$offline_sale_cart->register_id = $this->Employee->get_logged_in_employee_current_register_id();
				
				if (!$offline_sale_cart->register_id)
				{
					$offline_sale_cart->register_id = $this->Employee->getDefaultRegister($this->Employee->get_logged_in_employee_info()->person_id,$this->Employee->get_logged_in_employee_current_location_id());
				}
				$offline_sale_cart->employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
			
				if (isset($offline_sale['customer']['person_id']) && $offline_sale['customer']['person_id'])
				{
					$offline_sale_cart->customer_id = $offline_sale['customer']['person_id'];
				}
			
				if (isset($offline_sale['payments']))
				{
					foreach($offline_sale['payments'] as $payment)
					{
						$offline_sale_cart->add_payment(new PHPPOSCartPaymentSale(array(
							'payment_type' => $payment['type'],
							'payment_amount' => $payment['amount'],
							'payment_date' => date('Y-m-d H:i:s'),
						)));
					}
				}
			
				if (isset($offline_sale['items']))
				{
					foreach($offline_sale['items'] as $item)
					{
						if (isset($item['modifiers']) && is_array($item['modifiers']))
						{
							$modifier_items = array();
						
							foreach($item['modifiers'] as $mi)
							{
								$display_name = to_currency($mi['unit_price']).': '.$mi['modifier_item_name'];
								$modifier_items[$mi['modifier_item_id']] = array('display_name' => $display_name, 'unit_price' => $mi['unit_price'],'cost_price' => $mi['cost_price']);
							}
						
							$item['modifier_items'] = $modifier_items;
						}
				
						$cur_item_info = $this->Item->get_info($item['item_id']);
						$cur_item_location_info = $this->Item_location->get_info($item['item_id'],$offline_sale_cart->location_id);
						$cur_item_variation_info = $this->Item_variations->get_info(isset($item['variation_id']) ? $item['variation_id'] : -1);
					
						$item['type'] = 'sale';
						if ($cur_item_variation_info && $cur_item_variation_info->unit_price)
						{
							$item['regular_price'] = $cur_item_variation_info->unit_price;
						}
						else
						{
							$item['regular_price'] = ($cur_item_location_info && $cur_item_location_info->unit_price) ? $cur_item_location_info->unit_price : $cur_item_info->unit_price;
						}
											
						$cart_item_to_add = array();
						$cart_item_to_add['cart'] = $offline_sale_cart;
						
						$cart_item_to_add['scan'] = $item['item_id'].(isset($item['selected_variation']) && $item['selected_variation'] ? '#'.$item['selected_variation'] : '').'|FORCE_ITEM_ID|';

						$cart_item_to_add['unit_price'] = $item['price'];
						$cart_item_to_add['discount'] = $item['discount_percent'];
						$cart_item_to_add['description'] = $item['description'];
						$cart_item_to_add['quantity'] = $item['quantity'];
						$cart_item_to_add['modifier_items'] =  array();
						
						if (isset($item['selected_item_modifiers']))
						{
							foreach(array_keys($item['selected_item_modifiers']) as $mid)
							{
								$mi = $this->Item_modifier->get_modifier_item_info($mid);
								$display_name = to_currency($mi['unit_price']).': '.$mi['modifier_item_name'];
							
								$cart_item_to_add['modifier_items'][$mid] = array('display_name' => $display_name, 'unit_price' => $mi['unit_price'],'cost_price' => $mi['cost_price']);
							}
						}
						
						if(strtoupper(substr($item['item_id'], 0, 3)) == 'KIT')
						{
							$item_to_add = new PHPPOSCartItemKitSale($cart_item_to_add);							
						}
						else
						{
							$item_to_add = new PHPPOSCartItemSale($cart_item_to_add);
						}
						$offline_sale_cart->add_item($item_to_add);
						
					}
				}
			
				$sale_id = $this->Sale->save($offline_sale_cart);
				$sale_ids[] = $sale_id;
			}
			echo json_encode(array('success' => TRUE,'sale_ids' => $sale_ids));
		}
		
		function view_transaction_history()
		{
			$this->check_action_permission('view_edit_transaction_history');
			$credit_card_processor = $this->_get_cc_processor();

			if (!$credit_card_processor || !method_exists($credit_card_processor,'get_transaction_history'))
			{
				$this->_reload(array('error' => lang('sales_credit_card_processing_is_down')), false);
				return;
			}
		
			$start_date = $this->input->get('start_date');
			$end_date = $this->input->get('end_date');
			$params = [
				'startDate' => date('c',strtotime($start_date ? $start_date : '-2 days')),
				'endDate' => date('c',strtotime($end_date ? $end_date : '+1 day')),
			    'maxResults' => 50,
				'startIndex' => 0,
			];
		
			$all_transactions = array();
		
			$transactions = $credit_card_processor->get_transaction_history($params);
			$all_transactions = array_merge($all_transactions,$transactions['transactions']);
			$total_transactions = $transactions['totalResultCount'];
		
			$total_pages = ceil($total_transactions/$params['maxResults']);
			for($startIndex=$params['maxResults'];$startIndex<$params['maxResults']*$total_pages;$startIndex+=$params['maxResults'])
			{
				$params['startIndex'] = $startIndex;
				$transactions = $credit_card_processor->get_transaction_history($params);
				$all_transactions = array_merge($all_transactions,$transactions['transactions']);
			
			}
		
			$all_transaction_ids = array_column($all_transactions,'transactionId');
		
			//This will pre-warm cache so we don't make a ton of database queries
			$this->Sale->get_sale_id_from_payment_ref_no($all_transaction_ids);
			$data = array(
				'start_date' => date(get_date_format(),strtotime($start_date ? $start_date : '-2 days')),
				'end_date' => date(get_date_format(),strtotime($start_date ? $end_date : '+1 day')),
				'transactions' => $all_transactions,
				'length_dropdown' => range($params['maxResults'], $total_pages*$params['maxResults'],10)
			);
		
				
			$this->load->view('sales/blockchyp_transaction_history',$data);		
		}
		
		function batches()
		{
			$credit_card_processor = $this->_get_cc_processor();

			if ($credit_card_processor)
			{
				$cc_processor_class_name = strtoupper(get_class($credit_card_processor));
			}
		
			$is_core_clear_processor =  strpos(strtoupper($this->Location->get_info_for_key('credit_card_processor')), 'CORE') !== false;
			if (!$credit_card_processor || !$is_core_clear_processor)
			{
				$this->load->view('coreclear/coreclear_info');
				return;
			}
		
			if ($cc_processor_class_name != 'CORECLEARBLOCKCHYPPROCESSOR')
			{
				$this->load->view('coreclear/coreclear_transaction_history_not_supported');
				return;
			}
		
			$start_date = $this->input->get('start_date');
			$end_date = $this->input->get('end_date');
			$params = [
				'startDate' => date('c',strtotime($start_date ? $start_date : '-10 days')),
				'endDate' => date('c',strtotime($end_date ? $end_date : '+1 day')),
			    'maxResults' => 50,
				'startIndex' => 0,
			];
		
			$all_batches = array();
		
			$batches = $credit_card_processor->get_batch_history($params);
			$all_batches = array_merge($all_batches,$batches['batches']);
			$total_batches = $batches['totalResultCount'];
		
			$total_pages = ceil($total_batches/$params['maxResults']);
			for($startIndex=$params['maxResults'];$startIndex<$params['maxResults']*$total_pages;$startIndex+=$params['maxResults'])
			{
				$params['startIndex'] = $startIndex;
				$batches = $credit_card_processor->get_batch_history($params);
				$all_batches = array_merge($all_batches,$batches['batches']);
			
			}
				
			$data = array(
				'start_date' => date(get_date_format(),strtotime($start_date ? $start_date : '-10 days')),
				'end_date' => date(get_date_format(),strtotime($start_date ? $end_date : '+1 day')),
				'batches' => $all_batches,
				'length_dropdown' => range($params['maxResults'], $total_pages*$params['maxResults'],10)
			);
		
				
			$this->load->view('sales/blockchyp_batch_history',$data);		
		}
	
	
		function batch_details()
		{
			$credit_card_processor = $this->_get_cc_processor();

			if ($credit_card_processor)
			{
				$cc_processor_class_name = strtoupper(get_class($credit_card_processor));
			}
		
			$is_core_clear_processor =  strpos(strtoupper($this->Location->get_info_for_key('credit_card_processor')), 'CORE') !== false;
			if (!$credit_card_processor || !$is_core_clear_processor)
			{
				$this->load->view('coreclear/coreclear_info');
				return;
			}
		
			if ($cc_processor_class_name != 'CORECLEARBLOCKCHYPPROCESSOR')
			{
				$this->load->view('sales/coreclear_transaction_history_not_supported');
				return;
			}
		
			echo json_encode($credit_card_processor->get_batch_details(array('batchId' => $this->input->post('batch_id'))));
		}
	
		function get_transastions_for_batch()
		{
			$credit_card_processor = $this->_get_cc_processor();

			if ($credit_card_processor)
			{
				$cc_processor_class_name = strtoupper(get_class($credit_card_processor));
			}
		
			$is_core_clear_processor =  strpos(strtoupper($this->Location->get_info_for_key('credit_card_processor')), 'CORE') !== false;
			if (!$credit_card_processor || !$is_core_clear_processor)
			{
				$this->load->view('coreclear/coreclear_info');
				return;
			}
		
			if ($cc_processor_class_name != 'CORECLEARBLOCKCHYPPROCESSOR')
			{
				$this->load->view('coreclear/coreclear_transaction_history_not_supported');
				return;
			}
		
			$batch_id = $this->input->post('batch_id');
		
			$params = [
				'batchId' => $batch_id,
			    'maxResults' => 50,
				'startIndex' => 0,
			];
		
			$headers = array(
			array('data'=>lang('common_date'), 'align'=> 'left'),
			array('data'=>lang('common_id'), 'align'=> 'left'),
			array('data'=>lang('common_sale_id'), 'align'=> 'left'),
			array('data'=>lang('common_approved'), 'align'=> 'left'),
			array('data'=>lang('sales_response_description'), 'align'=> 'left'),
			array('data'=>lang('sales_card_holder'), 'align'=> 'left'),
			array('data'=>lang('common_amount'), 'align'=> 'left'),
			array('data'=>lang('sales_transaction_type'), 'align'=> 'left'),
			array('data'=>lang('sales_entry_method'), 'align'=> 'left'),
			array('data'=>lang('common_payment_type'), 'align'=> 'left'),
			array('data'=>lang('sales_masked_card'), 'align'=> 'left'),
			);
		
		
			$all_transactions = array();
	
			$transactions = $credit_card_processor->get_transaction_history($params);
			$all_transactions = array_merge($all_transactions,$transactions['transactions']);
			$total_transactions = $transactions['totalResultCount'];
	
			$total_pages = ceil($total_transactions/$params['maxResults']);
			for($startIndex=$params['maxResults'];$startIndex<$params['maxResults']*$total_pages;$startIndex+=$params['maxResults'])
			{
				$params['startIndex'] = $startIndex;
				$transactions = $credit_card_processor->get_transaction_history($params);
				$all_transactions = array_merge($all_transactions,$transactions['transactions']);
			}
	
			$all_transaction_ids = array_column($all_transactions,'transactionId');
	
			//This will pre-warm cache so we don't make a ton of database queries
			$this->Sale->get_sale_id_from_payment_ref_no($all_transaction_ids);
		
			$details_data = array();
		
			foreach($all_transactions as $transaction)
			{
				$details_data_row = array();
				$details_data_row[] = array('data'=> date(get_date_format().' '.get_time_format(),strtotime($transaction['timestamp'])), 'align'=>'left');
				$details_data_row[] = array('data'=> $transaction['transactionId'],'align'=>'left');
			
				if ($sale_id = $this->Sale->get_sale_id_from_payment_ref_no($transaction['transactionId']))
				{
					$details_data_row[] = array('data'=> anchor('sales/receipt/'.$sale_id,$this->config->item('sale_prefix').' '.$sale_id, array('target' => '_blank')), 'align'=>'left');
				}
				else
				{
					$details_data_row[] = array('data'=> lang('common_unknown'), 'align'=>'left');
				}
				$details_data_row[] = array('data'=> $transaction['approved'] ? lang('common_yes') : lang('common_no'), 'align'=>'left');
				$details_data_row[] = array('data'=> $transaction['responseDescription'], 'align'=>'left');
				$details_data_row[] = array('data'=> $transaction['cardHolder'], 'align'=>'left');
				$details_data_row[] = array('data'=> to_currency(make_currency_no_money($transaction['authorizedAmount'])), 'align'=>'left');
				$details_data_row[] = array('data'=> $transaction['transactionType'], 'align'=>'left');
				$details_data_row[] = array('data'=> $transaction['entryMethod'], 'align'=>'left');
				$details_data_row[] = array('data'=> $transaction['paymentType'], 'align'=>'left');
				$details_data_row[] = array('data'=> $transaction['maskedPan'], 'align'=>'left');
			
				$details_data[] = $details_data_row;
			
			}
		
		
			$data=array(
			"headers" => $headers,
			"details_data" => $details_data
			);
		
			echo json_encode($data);
		}
		
	
		function void_return_by_transaction_id($transactionId)
		{
			$this->check_action_permission('view_edit_transaction_history');
			$credit_card_processor = $this->_get_cc_processor();

			if (!$credit_card_processor || !method_exists($credit_card_processor,'void_return_transaction_by_id'))
			{
				$this->_reload(array('error' => lang('sales_credit_card_processing_is_down')), false);
				return;
			}
		
			$start_date=$this->input->post('start_date');
			$end_date=$this->input->post('end_date');
			$amount = $this->input->post('amount');
			if ($response = $credit_card_processor->void_return_transaction_by_id($transactionId,$amount))
			{
				$sale_id = $this->input->post('sale_id');
				$this->load->model('Processing_logging');
				$log_data = array(
					'return_time' => date('Y-m-d H:i:s'),
					'employee_id' => $this->Employee->get_logged_in_employee_info()->person_id,
					'orig_voided_processor_transaction_id' => $transactionId,
					'voided_processor_transaction_id' => $response['transactionId'],
					'amount' => $response['authorizedAmount'],
					'sale_id' => $sale_id ? $sale_id : NULL,
				);	
			
				$this->Processing_logging->insert_log($log_data);
			
				$success = rawurlencode(lang('sales_success_void_transaction').' '.$transactionId);
				redirect("sales/view_transaction_history?success=$success&start_date=$start_date&end_date=$end_date");
			}
			else
			{
				$error = rawurlencode(lang('sales_cannot_void_transaction').' '.$transactionId);
				redirect("sales/view_transaction_history?error=$error&start_date=$start_date&end_date=$end_date");
			}
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
    function update_sales_item_order(){
		$list = $this->input->post("item_lines");

		foreach($list as $item_line){
			$item_id = $item_line['item_id'];
			$sale_id = $item_line['sale_id'];
			$item_class = $item_line['item_class'];
			$receipt_line_sort_order = $item_line['receipt_line_sort_order'];
			$this->Sale->sale_item_receipt_line_sort_order_update($sale_id, $item_id, $item_class, $receipt_line_sort_order);
		}

		$result = array(
			'state' => 1
		);
		echo json_encode($result);
		exit;
	}
}
?>