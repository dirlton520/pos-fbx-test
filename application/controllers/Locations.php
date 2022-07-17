<?php
require_once ("Secure_area.php");
require_once ("interfaces/Idata_controller.php");
require_once (APPPATH."libraries/blockchyp/vendor/autoload.php");
require_once (APPPATH."traits/creditcardProcessingTrait.php");

use \BlockChyp\BlockChyp;

class Locations extends Secure_area implements Idata_controller
{
	use creditcardProcessingTrait;
	function __construct()
	{
		parent::__construct('locations');
		$this->lang->load('locations');
		$this->lang->load('module');		
		
	}
	
	function index($offset=0)
	{		
		$params = $this->session->userdata('locations_search_data') ? $this->session->userdata('locations_search_data') : array('offset' => 0, 'order_col' => 'location_id', 'order_dir' => 'asc', 'search' => FALSE,'deleted' => 0);
		
		if ($offset!=$params['offset'])
		{
		   redirect('locations/index/'.$params['offset']);
		}
		
		$this->check_action_permission('search');
		
		$config['base_url'] = site_url('locations/sorting');
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		$data['controller_name']=strtolower(get_class());
		$data['per_page'] = $config['per_page'];
		$data['search'] = isset($params['search']) ? $params['search'] : "";
		$data['deleted'] = $params['deleted'];
		if ($data['search'])
		{
			$config['total_rows'] = $this->Location->search_count_all($data['search'],$params['deleted']);
			$table_data = $this->Location->search($data['search'],$params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		else
		{
			$config['total_rows'] = $this->Location->count_all($params['deleted']);
			$table_data = $this->Location->get_all($params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		$this->load->library('pagination');$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['order_col'] = $params['order_col'];
		$data['order_dir'] = $params['order_dir'];
		$data['total_rows'] = $config['total_rows'];
		$data['manage_table']=get_locations_manage_table($table_data,$this);
		$this->load->view('locations/manage',$data);
	}
	
	function sorting()
	{
		$this->check_action_permission('search');
		$params = $this->session->userdata('locations_search_data');
		
		$search=$this->input->post('search') ? $this->input->post('search') : "";
		$per_page=$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;

		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : 'name';
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc';
		$deleted = $this->input->post('deleted') ? $this->input->post('deleted'): $params['deleted'];

		$locations_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search,'deleted' => $deleted);
		$this->session->set_userdata("locations_search_data",$locations_search_data);

		if ($search)
		{
			$config['total_rows'] = $this->Location->search_count_all($search,$deleted);
			$table_data = $this->Location->search($search,$deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'name' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc');
		}
		else
		{
			$config['total_rows'] = $this->Location->count_all($deleted);
			$table_data = $this->Location->get_all($deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'name' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc');
		}
		$config['base_url'] = site_url('items/sorting');
		$config['per_page'] = $per_page; 
		$this->load->library('pagination');$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['manage_table']=get_locations_manage_table_data_rows($table_data,$this);
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination'],'total_rows' => $config['total_rows']));	
	}

	function search()
	{
		$this->check_action_permission('search');
		$params = $this->session->userdata('locations_search_data');
		
		$search=$this->input->post('search');
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : 'name';
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc';
		$deleted = $this->input->post('deleted') ? $this->input->post('deleted'): $params['deleted'];

		$locations_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search,'deleted' => $deleted);
		$this->session->set_userdata("locations_search_data",$locations_search_data);
		$per_page=$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$search_data=$this->Location->search($search,$deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'name' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc');
		$config['base_url'] = site_url('locations/search');
		$config['total_rows'] = $this->Location->search_count_all($search,$deleted);
		$config['per_page'] = $per_page ;
		$this->load->library('pagination');$this->pagination->initialize($config);				
		$data['pagination'] = $this->pagination->create_links();
		$data['manage_table']=get_locations_manage_table_data_rows($search_data,$this);
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination'],'total_rows' => $config['total_rows']));
	}

	function clear_state()
	{
		$params = $this->session->userdata('locations_search_data');
		$this->session->set_userdata('locations_search_data', array('offset' => 0, 'order_col' => 'location_id', 'order_dir' => 'asc', 'search' => FALSE,'deleted' => $params['deleted']));
		redirect('locations');
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		$params = $this->session->userdata('locations_search_data') ? $this->session->userdata('locations_search_data') : array('deleted' => 0);
		$suggestions = $this->Location->get_search_suggestions($this->input->get('term'),$params['deleted'],100);
		echo json_encode(H($suggestions));
	}
	

	function view($location_id=-1,$redirect=false)
	{
		$this->check_action_permission('add_update');
		$this->load->model('Tax_class');
		$location_info = $this->Location->get_info($location_id);
		$data = array();
		$data['needs_auth'] = FALSE;
		
		$this->load->helper('demo');
		if (!is_on_demo_host())
		{
			if (!$location_info->location_id && !$this->session->flashdata('has_location_auth'))
			{
				$data['needs_auth'] = TRUE;
			}
		}
		if ($this->session->flashdata('purchase_email'))
		{
			$data['purchase_email'] = $this->session->flashdata('purchase_email');
		}
		else
		{
			$data['purchase_email'] = '';
		}
		
		$data['tax_classes'] = array();
		$data['tax_classes'][''] = lang('common_none');
		
		foreach($this->Tax_class->get_all()->result_array() as $tax_class)
		{
			$data['tax_classes'][$tax_class['id']] = $tax_class['name'];
		}
		
		
		$data['location_info']=$location_info;
		$data['registers'] = $this->Register->get_all($location_id);
		
		$data['all_timezones'] = $this->_get_timezones();
		$data['redirect']=$redirect;
		
		$data['employees'] = array();
		foreach ($this->Employee->get_all()->result() as $employee)
		{
			$has_access = $this->Employee->is_employee_authenticated($employee->person_id, $location_id);
			$data['employees'][$employee->person_id] = array('name' => $employee->first_name . ' '. $employee->last_name, 'has_access' => $has_access);
		}
		
		$this->load->view("locations/form",$data);
	}
	
	//http://stackoverflow.com/questions/1727077/generating-a-drop-down-list-of-timezones-with-php
	function _get_timezones()
	{
		$timezones = DateTimeZone::listIdentifiers();
		$timezone_offsets = array();
		
		foreach($timezones as $timezone)
		{
		    $tz = new DateTimeZone($timezone);
		    $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
		}

		// sort timezone by offset
		asort($timezone_offsets);

		$timezone_list = array();
		foreach($timezone_offsets as $timezone => $offset)
		{
		    $offset_prefix = $offset < 0 ? '-' : '+';
		    $offset_formatted = gmdate('H:i', abs($offset) );
		    $pretty_offset = "UTC${offset_prefix}${offset_formatted}";
			

			$current_time = '';
			$date = new DateTime();
			$date->setTimezone(new DateTimeZone($timezone));
			if (method_exists($date, 'setTimestamp'))
			{
				$date->setTimestamp(time());
				$current_time = $date->format(get_time_format());
			}
			$timezone_list[$timezone] = "(${pretty_offset}) $timezone $current_time";
		}

		return $timezone_list;
	}
	
	function check_auth()
	{
		$this->form_validation->set_rules('purchase_email', 'lang:locations_purchase_email', 'callback_location_auth_check');
	    $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
		
		if($this->form_validation->run() !== FALSE)
		{
			$this->session->set_flashdata('has_location_auth', TRUE);
			$this->session->set_flashdata('purchase_email', $this->input->post('purchase_email'));
			redirect('locations/view/-1');
		}
		else
		{
			$data  = array();
			$data['location_info']=$this->Location->get_info(-1);
			
			$data['needs_auth'] = TRUE;
			$this->load->view("locations/form", $data);
		}
	}
	
	function location_auth_check($email)
	{
		if (!$this->does_have_valid_number_of_locations_for_an_additional_location($email))
		{
			$this->form_validation->set_message('location_auth_check', lang('locations_invalid_email_or_dont_have_auth'));
			return FALSE;
		}
		
		return TRUE;
	}

	//Does the validation for valid Locations
	//NOTE: If you modify this function you are breaking the terms of license
	function does_have_valid_number_of_locations_for_an_additional_location($email)
	{
		$current_location_count = $this->Location->count_all();
		$auth_url = (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? 'http://phppointofsalestaging.com/allowed_stores.php?email='.rawurlencode($email): 'http://phppointofsale.com/allowed_stores.php?email='.rawurlencode($email);
		
        $ch = curl_init(); 
		//Don't verify ssl...just in case a server doesn't have the ability to verify
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
        curl_setopt($ch, CURLOPT_URL, $auth_url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $authenticated_locations_count = (int)curl_exec($ch); 
        curl_close($ch);		
		
		return ($authenticated_locations_count >= ($current_location_count + 1));
	}
	
	function save_emv_data($location_id=false)
	{
		$this->load->helper('demo');
		if (!is_on_demo_host())
		{
			if ($location_id)
			{
				$location_data = array(
				'credit_card_processor' => $this->input->post('credit_card_processor'),
				'enable_credit_card_processing'=>1,
				'emv_merchant_id'=>$this->input->post('emv_merchant_id'),
				'net_e_pay_server' => $this->input->post('net_e_pay_server'),
				'com_port'=>$this->input->post('com_port'),
				'listener_port'=>$this->input->post('listener_port'),
				'secure_device_override_emv'=>$this->input->post('secure_device_override_emv'),
				'secure_device_override_non_emv'=>$this->input->post('secure_device_override_non_emv'),
				'ebt_integrated' => $this->input->post('ebt_integrated') ? 1 : 0,		
				'integrated_gift_cards' => $this->input->post('integrated_gift_cards') ? 1 : 0,		
			);
				
				$this->Location->save($location_data,$location_id);
				
				if ($register_0_terminal_id = $this->input->post('terminal_id'))
				{
					$this->load->model('Register');
					$register_default = $this->Register->get_default_register_info($location_id);
					$register_data = array('emv_terminal_id' => $register_0_terminal_id);
					$this->Register->save($register_data,$register_default->register_id);
				}
			}
			
			$emv_param_download_init_params = false;
			
			if ($this->Location->get_info_for_key('credit_card_processor', false, false) == 'mercury' || !$this->Location->get_info_for_key('credit_card_processor', false, false))
			{
				require_once (APPPATH.'libraries/Mercuryemvusbprocessor.php');
				$credit_card_processor = new MercuryEMVUSBProcessor($this);
				$emv_param_download_init_params = $credit_card_processor->get_emv_param_download_params();
			}
			elseif ($this->Location->get_info_for_key('credit_card_processor', false, false) == 'heartland')
			{
				require_once (APPPATH.'libraries/Heartlandemvusbprocessor.php');
				$credit_card_processor = new Heartlandemvusbprocessor($this);
				$emv_param_download_init_params = $credit_card_processor->get_emv_param_download_params();			
			}
			elseif ($this->Location->get_info_for_key('credit_card_processor', false, false) == 'evo')
			{
				require_once (APPPATH.'libraries/Evoemvusbprocessor.php');
				$credit_card_processor = new Evoemvusbprocessor($this);
				$emv_param_download_init_params = $credit_card_processor->get_emv_param_download_params();			
			}
			elseif ($this->Location->get_info_for_key('credit_card_processor', false, false) == 'worldpay')
			{
				require_once (APPPATH.'libraries/Worldpayemvusbprocessor.php');
				$credit_card_processor = new Worldpayemvusbprocessor($this);
				$emv_param_download_init_params = $credit_card_processor->get_emv_param_download_params();			
			}
			elseif ($this->Location->get_info_for_key('credit_card_processor', false, false) == 'firstdata')
			{
				require_once (APPPATH.'libraries/Firstdataemvusbprocessor.php');
				$credit_card_processor = new Firstdataemvusbprocessor($this);
				$emv_param_download_init_params = $credit_card_processor->get_emv_param_download_params();			
			}
			elseif ($this->Location->get_info_for_key('credit_card_processor', false, false) == 'other_usb')
			{
				require_once (APPPATH.'libraries/Otheremvusbprocessor.php');
				$credit_card_processor = new Otheremvusbprocessor($this);
				$emv_param_download_init_params = $credit_card_processor->get_emv_param_download_params();
			}
			else //Default to Mercury just so we have something.... (most likley going to be caught in if statements above)
			{
				require_once (APPPATH.'libraries/Mercuryemvusbprocessor.php');
				$credit_card_processor = new MercuryEMVUSBProcessor($this);
				$emv_param_download_init_params = $credit_card_processor->get_emv_param_download_params();
			}
			
			echo json_encode(array('success'=>true, 'emv_param_download_init_params' => $emv_param_download_init_params));
			return;
		}
		
		echo json_encode(array('success'=>false));
	}

	function save($location_id=-1)
	{
		$this->check_action_permission('add_update');
				
		$location_data = array(
		'name'=>$this->input->post('name'),
		'color' => $this->input->post('color') ? $this->input->post('color') : NULL,
		'company' => $this->input->post('company') ? $this->input->post('company') : NULL,
		'website' => $this->input->post('website') ? $this->input->post('website') : NULL,
		'address'=>$this->input->post('address'),
		'phone'=>$this->input->post('phone'),
		'fax'=>$this->input->post('fax'),
		'email'=>$this->input->post('email'),
		'cc_email'=>$this->input->post('cc_email'),
		'bcc_email'=>$this->input->post('bcc_email'),
		'return_policy'=>$this->input->post('return_policy'),
		'receive_stock_alert' => $this->input->post('receive_stock_alert') ? 1 : 0,
		'stock_alert_email'=>$this->input->post('stock_alert_email'),
		'timezone'=>$this->input->post('timezone'),
		'mailchimp_api_key'=>$this->input->post('mailchimp_api_key'),
		'platformly_api_key'=>$this->input->post('platformly_api_key'),
		'platformly_project_id' => $this->input->post('platformly_project_id'),
		'enable_credit_card_processing'=>$this->input->post('enable_credit_card_processing') ? 1 : 0,
		'credit_card_processor' => $this->input->post('credit_card_processor'),		
		'stripe_public' => $this->input->post('stripe_public'),		
		'stripe_private' => $this->input->post('stripe_private'),	
		'braintree_merchant_id' => $this->input->post('braintree_merchant_id'),	
		'braintree_public_key' => $this->input->post('braintree_public_key'),	
		'braintree_private_key' => $this->input->post('braintree_private_key'),	
		'stripe_currency_code' => $this->input->post('stripe_currency_code'),
		'hosted_checkout_merchant_id'=>$this->input->post('hosted_checkout_merchant_id'),
		'hosted_checkout_merchant_password'=>$this->input->request('hosted_checkout_merchant_password'),//Use REQUEST to avoid url encoding that causes issues
		'emv_merchant_id'=>$this->input->post('emv_merchant_id'),
		'net_e_pay_server' => $this->input->post('net_e_pay_server'),
		'com_port'=>$this->input->post('com_port'),
		'listener_port'=>$this->input->post('listener_port'),
		'secure_device_override_emv'=>$this->input->post('secure_device_override_emv'),
		'secure_device_override_non_emv'=>$this->input->post('secure_device_override_non_emv'),
		'square_currency_code' => $this->input->post('square_currency_code'),
		'square_location_id' => $this->input->post('square_location_id'),
		'square_currency_multiplier' => $this->input->post('square_currency_multiplier'),
		'ebt_integrated' => $this->input->post('ebt_integrated') ? 1 : 0,
		'integrated_gift_cards' => $this->input->post('integrated_gift_cards') ? 1 : 0,		
		'tax_class_id'=> $this->input->post('override_default_tax') && $this->input->post('tax_class') ? $this->input->post('tax_class') : NULL,
		'default_tax_1_rate'=>$this->input->post('override_default_tax') && $this->input->post('default_tax_1_rate') && is_numeric($this->input->post('default_tax_1_rate')) ?  $this->input->post('default_tax_1_rate') : NULL ,		
		'default_tax_1_name'=>$this->input->post('default_tax_1_name'),		
		'default_tax_2_rate'=>$this->input->post('override_default_tax') && $this->input->post('default_tax_2_rate') && is_numeric($this->input->post('default_tax_2_rate')) ?  $this->input->post('default_tax_2_rate') : NULL ,		
		'default_tax_2_name'=>$this->input->post('default_tax_2_name'),
		'default_tax_2_cumulative' => $this->input->post('default_tax_2_cumulative') ? 1 : 0,
		'default_tax_3_rate'=>$this->input->post('override_default_tax') && $this->input->post('default_tax_3_rate') && is_numeric($this->input->post('default_tax_3_rate')) ?  $this->input->post('default_tax_3_rate') : NULL ,		
		'default_tax_3_name'=>$this->input->post('default_tax_3_name'),		
		'default_tax_4_rate'=>$this->input->post('override_default_tax') && $this->input->post('default_tax_4_rate') && is_numeric($this->input->post('default_tax_4_rate')) ?  $this->input->post('default_tax_4_rate') : NULL ,		
		'default_tax_4_name'=>$this->input->post('default_tax_4_name'),		
		'default_tax_5_rate'=>$this->input->post('override_default_tax') && $this->input->post('default_tax_5_rate') && is_numeric($this->input->post('default_tax_5_rate')) ?  $this->input->post('default_tax_5_rate') : NULL ,		
		'default_tax_5_name'=>$this->input->post('default_tax_5_name'),	
		'email_sales_email' => $this->input->post('email_sales_email'),	
		'email_receivings_email' => $this->input->post('email_receivings_email'),	
		'stock_alerts_just_order_level' => $this->input->post('stock_alerts_just_order_level') ? 1 : 0,
		'tax_id' => $this->input->post('tax_id'),
		'disable_markup_markdown' => $this->input->post('disable_markup_markdown') ? 1 : 0,
		'card_connect_mid' => $this->input->post('card_connect_mid'),
		'card_connect_rest_username' => $this->input->post('card_connect_rest_username'),
		'card_connect_rest_password' => $this->input->post('card_connect_rest_password'),
		'default_mailchimp_lists' => serialize($this->input->post('default_mailchimp_lists')),
		'twilio_sid'=>$this->input->post('twilio_sid'),
		'twilio_token'=>$this->input->post('twilio_token'),
		'twilio_sms_from'=>$this->input->post('twilio_sms_from'),
		'auto_reports_email' => $this->input->post('auto_reports_email'),
		'auto_reports_email_time' => $this->input->post('auto_reports_email_time'),
		'auto_reports_day' => $this->input->post('auto_reports_day'),
		'disable_confirmation_option_for_emv_credit_card' => $this->input->post('disable_confirmation_option_for_emv_credit_card') ? 1 : 0,
		'blockchyp_api_key' => $this->input->post('blockchyp_api_key'),
		'blockchyp_bearer_token' => $this->input->post('blockchyp_bearer_token'),
		'blockchyp_signing_key' => $this->input->post('blockchyp_signing_key'),
		'blockchyp_test_mode' => $this->input->post('blockchyp_test_mode') ? 1 : 0,
	);
	
	
		$redirect = $this->input->post('redirect');
		$employees = $this->input->post('employees') ? $this->input->post('employees') : array();
		
		$this->load->helper('demo');
		if (is_on_demo_host())
		{			
			unset($location_data['enable_credit_card_processing']);
			unset($location_data['hosted_checkout_merchant_id']);
			unset($location_data['hosted_checkout_merchant_password']);
			unset($location_data['stripe_public']);
			unset($location_data['stripe_private']);
			unset($location_data['stripe_currency_code']);
			unset($location_data['braintree_merchant_id']);
			unset($location_data['braintree_public_key']);
			unset($location_data['braintree_private_key']);			
			unset($location_data['emv_merchant_id']);
			unset($location_data['net_e_pay_server']);
			unset($location_data['com_port']);
			unset($location_data['listener_port']);
			unset($location_data['card_connect_mid']);
			unset($location_data['card_connect_rest_username']);
			unset($location_data['card_connect_rest_password']);
			unset($location_data['secure_device_override_emv']);
			unset($location_data['secure_device_override_non_emv']);
			unset($location_data['square_currency_code']);
			unset($location_data['square_location_id']);
			unset($location_data['square_currency_multiplier']);
			unset($location_data['ebt_integrated']);
			unset($location_data['integrated_gift_cards']);
			unset($location_data['mailchimp_api_key']);
			unset($locations_data['platformly_api_key']);
			unset($locations_data['disable_confirmation_option_for_emv_credit_card']);
			
			if ($location_id == 1)
			{
				unset($location_data['color']);
			}
			
			//Make sure demo admin user is always included
			if(!in_array(1, $employees))
			{
				$employees[] = 1;
			}
		}
		
		//Make sure we always have an employee with access
		if (empty($employees))
		{
			$employees[] = 1;
		}
		
		if ($location_id == -1)
		{
			//If we have a purcahse email, do a an auth check
			$purchase_email = $this->input->post('purchase_email');
		
			$this->load->helper('demo');
			if (!is_on_demo_host() && (!$purchase_email || !$this->does_have_valid_number_of_locations_for_an_additional_location($purchase_email)))
			{
				echo json_encode(array('success'=>false,'message'=>lang('locations_error_adding_updating')));
				die();
			}
		}
				
		if($this->Location->save($location_data,$location_id) && $this->Location->assign_employees_to_location($location_id != -1 ? $location_id : $location_data['location_id'],$employees))
		{
			if(!empty($_FILES["company_logo"]) && $_FILES["company_logo"]["error"] == UPLOAD_ERR_OK && !is_on_demo_host())
			{
				$this->load->model('Appfile');
				
				$allowed_extensions = array('png', 'jpg', 'jpeg', 'gif');
				$extension = strtolower(pathinfo($_FILES["company_logo"]["name"], PATHINFO_EXTENSION));
			
				if (in_array($extension, $allowed_extensions))
				{
					$config['image_library'] = 'gd2';
					$config['source_image']	= $_FILES["company_logo"]["tmp_name"];
					$config['create_thumb'] = FALSE;
					$config['maintain_ratio'] = TRUE;
					$config['width']	 = 255;
					$config['height']	= 90;
					$this->load->library('image_lib', $config); 
					$this->image_lib->resize();
					$company_logo = $this->Appfile->save($_FILES["company_logo"]["name"], file_get_contents($_FILES["company_logo"]["tmp_name"]), NULL, $this->Location->get_info_for_key('company_logo',$location_id != -1 ? $location_id : $location_data['location_id'], false));
					$update_logo_data = array('company_logo' => $company_logo);
					$this->Location->save($update_logo_data,$location_id != -1 ? $location_id : $location_data['location_id']);
					
				}
			}
			elseif($this->input->post('delete_logo'))
			{
				$this->load->model('Appfile');
				$update_logo_data = array('company_logo' => NULL);
				$this->Location->save($update_logo_data,$location_id != -1 ? $location_id : $location_data['location_id']);
				$this->Appfile->delete($this->Location->get_info_for_key('company_logo'));
			}
		
			
			$success_message = '';
			
			//New item
			if($location_id==-1)
			{
				$this->_save_registers($location_data['location_id'], $this->input->post('registers_to_edit'), $this->input->post('registers_to_add'), $this->input->post('registers_to_delete'));
				$success_message = H(lang('locations_successful_adding').' '.$location_data['name']);
				echo json_encode(array('success'=>true,'message'=>$success_message,'location_id'=>$location_data['location_id']));
				$this->Appconfig->save('wizard_configure_locations',1);
			}
			else //previous item
			{
				$this->_save_registers($location_id, $this->input->post('registers_to_edit'), $this->input->post('registers_to_add'), $this->input->post('registers_to_delete'));
				$success_message = H(lang('locations_successful_updating').' '.$location_data['name']);
				$this->session->set_flashdata('manage_success_message', $success_message);
				echo json_encode(array('success'=>true,'message'=>$success_message,'location_id'=>$location_id,'redirect'=>$redirect));
				$this->Appconfig->save('wizard_configure_locations',1);
				
			}
			
		}
		else//failure
		{
			echo json_encode(array('success'=>false,'message'=>lang('locations_error_adding_updating')));
		}

	}
	
	function mercury_ip_tran_emv_test_mode()
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		
		if ($this->Location->get_info_for_key('credit_card_processor') == 'mercury' || !$this->Location->get_info_for_key('credit_card_processor'))
		{
			require_once(APPPATH.'libraries/Mercuryemvtranscloudprocessor.php');
			$credit_card_processor = new Mercuryemvtranscloudprocessor($this);
		}
		elseif ($this->Location->get_info_for_key('credit_card_processor') == 'heartland')
		{
			require_once(APPPATH.'libraries/Heartlandemvtranscloudprocessor.php');
			$credit_card_processor = new Heartlandemvtranscloudprocessor($this);
		}
		
		$response = $credit_card_processor->do_test_mode($this->input->post('device_id'),$this->input->post('merchant_id'));
		
		if ($response && property_exists($response->RStream,'CmdResponse'))
		{
			$CmdStatus = $response->RStream->CmdResponse->CmdStatus;
			$TextResponse = $response->RStream->CmdResponse->TextResponse;
		}
		elseif ($response && property_exists($response,'RStream'))
		{
			$CmdStatus = $response->RStream->CmdStatus;
			$TextResponse = $response->RStream->TextResponse;
		}
		else
		{
			$CmdStatus = 'Error';
			$TextResponse = '';		
		}
		
		$return = array();
		$return['message'] = $TextResponse;
		if ($CmdStatus == 'Error')
		{
			$return['success'] = FALSE;
		}
		else
		{
			$return['success'] = TRUE;			
		}
		
		echo json_encode(H($return));
		
	}
	
	function mercury_ip_tran_update_parameters()
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		
		if ($this->Location->get_info_for_key('credit_card_processor') == 'mercury' || !$this->Location->get_info_for_key('credit_card_processor'))
		{
			require_once(APPPATH.'libraries/Mercuryemvtranscloudprocessor.php');
			$credit_card_processor = new Mercuryemvtranscloudprocessor($this);
		}
		elseif ($this->Location->get_info_for_key('credit_card_processor') == 'heartland')
		{
			require_once(APPPATH.'libraries/Heartlandemvtranscloudprocessor.php');
			$credit_card_processor = new Heartlandemvtranscloudprocessor($this);
		}
		
		$response = $credit_card_processor->do_update_parameters($this->input->post('device_id'),$this->input->post('merchant_id'));
		
		if ($response && property_exists($response->RStream,'CmdResponse'))
		{
			$CmdStatus = $response->RStream->CmdResponse->CmdStatus;
			$TextResponse = $response->RStream->CmdResponse->TextResponse;
		}
		elseif ($response && property_exists($response,'RStream'))
		{
			$CmdStatus = $response->RStream->CmdStatus;
			$TextResponse = $response->RStream->TextResponse;
		}
		else
		{
			$CmdStatus = 'Error';
			$TextResponse = '';		
		}
		
		$return = array();
		$return['message'] = $TextResponse;
		if ($CmdStatus == 'Error')
		{
			$return['success'] = FALSE;
		}
		else
		{
			$return['success'] = TRUE;			
		}
		
		echo json_encode(H($return));
	}
	
	function mercury_ip_tran_emv_param_download()
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		
		if ($this->Location->get_info_for_key('credit_card_processor') == 'mercury' || !$this->Location->get_info_for_key('credit_card_processor'))
		{
			require_once(APPPATH.'libraries/Mercuryemvtranscloudprocessor.php');
			$credit_card_processor = new Mercuryemvtranscloudprocessor($this);
		}
		elseif ($this->Location->get_info_for_key('credit_card_processor') == 'heartland')
		{
			require_once(APPPATH.'libraries/Heartlandemvtranscloudprocessor.php');
			$credit_card_processor = new Heartlandemvtranscloudprocessor($this);
		}
		
		$response = $credit_card_processor->do_emv_param_download_params($this->input->post('device_id'),$this->input->post('merchant_id'));
		
		if ($response && property_exists($response->RStream,'CmdResponse'))
		{
			$CmdStatus = $response->RStream->CmdResponse->CmdStatus;
			$TextResponse = $response->RStream->CmdResponse->TextResponse;
		}
		elseif ($response && property_exists($response,'RStream'))
		{
			$CmdStatus = $response->RStream->CmdStatus;
			$TextResponse = $response->RStream->TextResponse;
		}
		else
		{
			$CmdStatus = 'Error';
			$TextResponse = '';		
		}
		
		$return = array();
		$return['message'] = $TextResponse;
		if ($CmdStatus == 'Error')
		{
			$return['success'] = FALSE;
		}
		else
		{
			$return['success'] = TRUE;			
		}
		
		echo json_encode(H($return));
	}
	
	function _save_registers($location_id, $registers_to_edit, $registers_to_add, $registers_to_delete)
	{		
		if ($registers_to_edit)
		{
			foreach($registers_to_edit as $register_id => $register)
			{
				if ($register['name'])
				{
					$register_data = array('emv_pinpad_ip' => $register['emv_pinpad_ip'],'emv_pinpad_port' => $register['emv_pinpad_port'],'card_connect_hsn' => $register['card_connect_hsn'], 'name' => $register['name'], 'iptran_device_id' => $register['iptran_device_id'], 'emv_terminal_id' => $register['emv_terminal_id'],'location_id' => $location_id);
					$this->Register->save($register_data, $register_id);
				}
			}
		}
		
		if ($registers_to_add)
		{
			foreach($registers_to_add as $register)
			{
				if ($register['name'])
				{
					$register_data = array('emv_pinpad_ip' => $register['emv_pinpad_ip'],'emv_pinpad_port' => $register['emv_pinpad_port'],'card_connect_hsn' => $register['card_connect_hsn'],'name' => $register['name'], 'iptran_device_id' => $register['iptran_device_id'], 'emv_terminal_id' => $register['emv_terminal_id'], 'location_id' => $location_id);
					$this->Register->save($register_data);
				}
			}
		}
		
		if ($registers_to_delete)
		{
			foreach($registers_to_delete as $register_id)
			{
				$this->Register->delete($register_id);
			}
		}
		
		//If we aren't editing any registers and aren't adding any, then we need to add a register so we always have done
		if ($registers_to_edit === NULL && $registers_to_add === NULL)
		{
			$register_data = array('name' => lang('locations_default'), 'location_id' => $location_id);
			$this->Register->save($register_data);
		}
		
		return TRUE;
	}
	
	function delete()
	{
		$this->check_action_permission('delete');
		
		$locations_to_delete=$this->input->post('ids');
		
		$this->load->helper('demo');
		//Don't let location 1 to be deleted
		if (is_on_demo_host())
		{
			$default_location_index = array_search(1, $locations_to_delete);
			
			if ($default_location_index !== FALSE)
			{
				unset($locations_to_delete[$default_location_index]);
				$locations_to_delete = array_values($locations_to_delete);
			}
		}
		
		if($this->Location->delete_list($locations_to_delete))
		{
			
			echo json_encode(array('success'=>true,'message'=>lang('locations_successful_deleted').' '.lang('locations_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('locations_cannot_be_deleted')));
		}
	}
	
	function undelete()
	{
		$this->check_action_permission('delete');
		$locations_to_undelete=$this->input->post('ids');
		if($this->Location->undelete_list($locations_to_undelete))
		{
			
			echo json_encode(array('success'=>true,'message'=>lang('locations_successful_undeleted').' '.lang('locations_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('locations_cannot_be_undeleted')));
		}
	}
	function toggle_show_deleted($deleted=0)
	{
		$this->check_action_permission('search');
		
		$params = $this->session->userdata('locations_search_data') ? $this->session->userdata('locations_search_data') : array('offset' => 0, 'order_col' => 'location_id', 'order_dir' => 'asc', 'search' => FALSE,'deleted' => 0);
		$params['deleted'] = $deleted;
		$params['offset'] = 0;
		
		$this->session->set_userdata("locations_search_data",$params);
	}	
}
?>