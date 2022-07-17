<?php
require_once ("Secure_area.php");
class Home extends Secure_area 
{
	function __construct()
	{
		parent::__construct();	
		$this->load->helper('report');
		$this->lang->load('module');
		$this->lang->load('home');
		$this->load->model('Item');
		$this->load->model('Item_kit');
		$this->load->model('Supplier');
		$this->load->model('Customer');
		$this->load->model('Employee');
		$this->load->model('Giftcard');
		$this->load->model('Sale');
		$this->load->helper('cloud');
		$this->load->helper('text');
		$this->load->model('Appfile');
	}
	
	function index($choose_location=0)
	{		
		require_once (APPPATH.'models/reports/Report.php');
		
		if (!$choose_location && $this->config->item('timeclock') && !$this->Employee->is_clocked_in() && !$this->Employee->get_logged_in_employee_info()->not_required_to_clock_in)
		{
			redirect('timeclocks');
		}


		$data['choose_location'] = $choose_location;
		
		$data['total_items']=$this->Item->count_all();
		$data['total_item_kits']=$this->Item_kit->count_all();
		$data['total_suppliers']=$this->Supplier->count_all();
		$data['total_customers']=$this->Customer->count_all();
		$data['total_employees']=$this->Employee->count_all();
		$data['total_locations']=$this->Location->count_all();
		$data['total_giftcards']=$this->Giftcard->count_all();
		$data['total_sales']=$this->Sale->count_all();
		$data['saved_reports'] = Report::get_saved_reports();
		
		$current_location = $this->Location->get_info($this->Employee->get_logged_in_employee_current_location_id());
		$current_location_id = $this->Employee->get_logged_in_employee_current_location_id();
		$data['message']  = "";
		
		if ($this->Employee->has_module_action_permission('reports', 'view_dashboard_stats', $this->Employee->get_logged_in_employee_info()->person_id))
		{	
			$data['month_sale'] = $this->sales_widget();
		}
		$this->load->helper('demo');
		$data['can_show_mercury_activate'] = (!is_on_demo_host() && !$this->config->item('mercury_activate_seen')) && !$this->Location->get_info_for_key('enable_credit_card_processing');		
		$data['can_show_setup_wizard'] = !$this->config->item('shown_setup_wizard');
		$data['can_show_feedback_promotion'] = !$this->config->item('shown_feedback_message');		
		$data['can_show_reseller_promotion'] = !$this->config->item('reseller_activate_seen');
		$data['can_show_bluejay'] = !$this->config->item('bluejay_seen');
		if (is_on_phppos_host())
		{
			$this->lang->load('login');
			$site_db = $this->load->database('site', TRUE);
			
			if (!is_on_demo_host())
			{
				$data['announcement'] = get_cloud_announcement($site_db);
			}
			
			if (is_subscription_cancelled($site_db) || is_subscription_failed($site_db) || is_in_trial($site_db))
			{
				$data['cloud_customer_info'] = get_cloud_customer_info($site_db);
				
				if (is_in_trial($site_db))
				{
						$data['trial_on']  = TRUE;
				}
				elseif (is_subscription_failed($site_db))
				{
					$data['subscription_payment_failed']  = TRUE;
				}
				elseif (is_subscription_cancelled_within_grace_period($site_db))
				{
					$data['subscription_cancelled_within_5_days']  = TRUE;
				}
			}
		}
		
				
		$start_date = date('Y-m-d 00:0:00');
		$end_date = date('Y-m-d 23:59:59',strtotime('+30 days'));
		$this->db->select('locations.name as location_name, items.name, SUM(quantity_purchased) as quantity_expiring,items.size,receivings_items.expire_date, categories.id as category_id,categories.name as category, company_name, item_number, product_id, 
		'.$this->db->dbprefix('receivings_items').'.item_unit_price as cost_price, 
		IFNULL('.$this->db->dbprefix('location_items').'.unit_price, '.$this->db->dbprefix('items').'.unit_price) as unit_price,
		SUM(quantity) as quantity, 
		IFNULL('.$this->db->dbprefix('location_items').'.reorder_level, '.$this->db->dbprefix('items').'.reorder_level) as reorder_level, 
		items.description', FALSE);
		$this->db->from('items');
		$this->db->join('receivings_items', 'receivings_items.item_id = items.item_id');
		$this->db->join('receivings', 'receivings_items.receiving_id = receivings.receiving_id');
		$this->db->join('suppliers', 'items.supplier_id = suppliers.person_id', 'left outer');
		$this->db->join('categories', 'items.category_id = categories.id', 'left outer');
		$this->db->join('locations', 'locations.location_id = receivings.location_id');
		$this->db->join('location_items', "location_items.item_id = items.item_id and location_items.location_id = $current_location_id", 'left');

		$this->db->where('items.deleted', 0);
		$this->db->where('items.system_item',0);
		
		$this->db->where('receivings.location_id', $current_location_id);
			
		$this->db->where('receivings_items.expire_date >=', $start_date);
		$this->db->where('receivings_items.expire_date <=', $end_date);

		$this->db->group_by('receivings_items.receiving_id,receivings_items.item_id,receivings_items.line');
		$this->db->order_by('receivings_items.expire_date');
		
		$expire_result = $this->db->get()->result_array();
		$data['expiring_items'] = $expire_result;
		
		if (isset($site_db) && $site_db)
		{
			$site_db->close();
		}
		$this->load->view("home",$data);
	}
	
	function dismiss_setup_wizard()
	{
		$this->Appconfig->save('shown_setup_wizard',1);
	}
	
	function dismiss_feedback_message()
	{
		$this->Appconfig->save('shown_feedback_message',1);
	}

	function dismiss_mercury_message()
	{
		$this->Appconfig->mark_mercury_activate(true);
	}
	
	function dismiss_reseller_message()
	{
		$this->Appconfig->mark_reseller_message(true);		
	}
	
	function dismiss_bluejay_message()
	{
		$this->Appconfig->mark_bluejay_message(true);		
	}	

	function logout()
	{
		$this->Employee->logout();
	}
	
	function set_employee_current_location_id()
	{
		$this->Employee->set_employee_current_location_id($this->input->post('employee_current_location_id'));
		
		//Clear out logged in register when we switch locations
		$this->Employee->set_employee_current_register_id(null);
	}

	function get_employee_current_location_id()
	{
		
		$current_location = $this->Location->get_info($this->Employee->get_logged_in_employee_current_location_id());

		echo $current_location->current_announcement;

	}
	
	function keep_alive()
	{
		//Set keep alive session to prevent logging out
		$this->session->set_userdata("keep_alive",time());
		echo $this->session->userdata('keep_alive');
	}
	
	function set_fullscreen($on = 0)
	{
		$this->session->set_userdata("fullscreen",$on);		
	}
		
	function view_item_modal($item_id)
	{
		$this->lang->load('items');
		$this->lang->load('receivings');
		$this->load->model('Tier');
		$this->load->model('Category');
		$this->load->model('Manufacturer');
		$this->load->model('Tag');
		$this->load->model('Item_location');
		$this->load->model('Item_taxes_finder');
		$this->load->model('Item_location_taxes');
		$this->load->model('Receiving');
		$this->load->model('Item_taxes');
		$this->load->model('Additional_item_numbers');
		$this->load->model('Item_variations');
		$this->load->model('Item_variation_location');
		
		$data['redirect'] = $this->input->get('redirect');
			
		$data['item_info'] = $this->Item->get_info($item_id);
		$data['item_images'] = $this->Item->get_item_images($item_id);
		$data['item_variations'] = $this->Item_variations->get_variations($item_id);
		$data['item_variation_location'] = $this->Item_variation_location->get_variations_with_quantity($item_id);
		
		$data['additional_item_numbers'] = $this->Additional_item_numbers->get_item_numbers($item_id);
		
		$data['tier_prices'] = array();
		
		foreach($this->Tier->get_all()->result() as $tier)
		{
			$tier_id = $tier->id;
			$tier_price = $this->Item->get_tier_price_row($tier_id,$item_id);
			
			if ($tier_price)
			{
				$value = $tier_price->unit_price !== NULL ? to_currency($tier_price->unit_price) : $tier_price->percent_off.'%';			
				$data['tier_prices'][] = array('name' => $tier->name, 'value' => $value);
			}
		}
		
		$data['category'] = $this->Category->get_full_path($data['item_info']->category_id);
		$data['manufacturer'] = $this->Manufacturer->get_info($data['item_info']->manufacturer_id)->name;
		$logged_in_employee_info=$this->Employee->get_logged_in_employee_info();
		
	
		if ($this->Employee->has_module_action_permission('items', 'view_inventory_at_all_locations', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			//Make all locations authed for modal to see all locations inventory
			$authed_locations = array();
			
			foreach($this->Location->get_all()->result_array() as $all_loc)
			{
				$authed_locations[] = $all_loc['location_id'];
			}
		}
		else
		{
			$authed_locations = $this->Employee->get_authenticated_location_ids($logged_in_employee_info->person_id);
		}
		
		$data['item_location_info']=$this->Item_location->get_info($item_id);
		
		$data['authed_locations'] = $authed_locations;
		foreach($authed_locations as $authed_location_id)
		{
			$data['item_location_info_all'][$authed_location_id]=$this->Item_location->get_info($item_id,$authed_location_id);
			$data['reorder_level'][$authed_location_id] = ($data['item_location_info_all'][$authed_location_id] && $data['item_location_info_all'][$authed_location_id]->reorder_level) ? $data['item_location_info_all'][$authed_location_id]->reorder_level : $data['item_info']->reorder_level;
		}
		foreach($authed_locations as $authed_location_id)
		{
			foreach(array_keys($data['item_variations']) as $variation_id)
			{
				$data['item_variation_location_info_all'][$authed_location_id][$variation_id]=$this->Item_variation_location->get_info($variation_id,$authed_location_id);
			}
		}
		
		$data['item_tax_info']=$this->Item_taxes_finder->get_info($item_id);
		
		if ($supplier_id = $this->Item->get_info($item_id)->supplier_id)
		{
			$supplier = $this->Supplier->get_info($supplier_id);
			$data['supplier'] = $supplier->company_name . ' ('.$supplier->first_name.' '.$supplier->last_name.')';
		}
		
		$data['suspended_receivings'] = $this->Receiving->get_suspended_receivings_for_item($item_id);		
		$this->load->view("items/items_modal",$data);
	}
	
	// Function to show the modal window when clicked on kit name
	function view_item_kit_modal($item_kit_id)
	{
		$this->lang->load('item_kits');
		$this->lang->load('items');
		$this->lang->load('receivings');
		$this->load->model('Item');
		$this->load->model('Item_kit');
		$this->load->model('Item_kit_items');
		$this->load->model('Tier');
		$this->load->model('Category');
		$this->load->model('Manufacturer');
		$this->load->model('Tag');
		$this->load->model('Item_kit_location');
		$this->load->model('Item_kit_taxes_finder');
		$this->load->model('Item_kit_location_taxes');
		$this->load->model('Receiving');
		$this->load->model('Item_kit_taxes');
		
		$data['redirect'] = $this->input->get('redirect');
		
		// Fetching Kit information using kit_id
		$data['item_kit_info']=$this->Item_kit->get_info($item_kit_id);
		
		$tier_prices = $this->Item->get_all_tiers_prices();
		
		$data['tier_prices'] = array();
		foreach($this->Tier->get_all()->result() as $tier)
		{
			$tier_id = $tier->id;
			$tier_price = $this->Item_kit->get_tier_price_row($tier_id,$item_kit_id);
			
			if ($tier_price)
			{
				$value = $tier_price->unit_price !== NULL ? to_currency($tier_price->unit_price) : $tier_price->percent_off.'%';			
				$data['tier_prices'][] = array('name' => $tier->name, 'value' => $value);
			}
		}
		
		$data['manufacturer'] = $this->Manufacturer->get_info($data['item_kit_info']->manufacturer_id)->name;
		$data['category'] = $this->Category->get_full_path($data['item_kit_info']->category_id);
		
		//$data['item_kit_location_info']=$this->Item_kit_location->get_info($item_kit_id);
		
		
		$this->load->view("item_kits/items_modal",$data);
	}

	function sales_widget($type = 'monthly')
	{
		$day = array();
		$count = array();

		if($type == 'monthly')
		{
			$start_date = date('Y-m-d', mktime(0,0,0,date("m"),1,date("Y"))).' 00:00:00';
			$end_date = date('Y-m-d').' 23:59:59';
		}
		else
		{
			$current_week = strtotime("-0 week +1 day");
			$current_start_week = strtotime("last monday midnight",$current_week);
			$current_end_week = strtotime("next sunday",$current_start_week);

			$start_date = date("Y-m-d",$current_start_week).' 00:00:00';
			$end_date = date("Y-m-d",$current_end_week).' 23:59:59';
		}

		$return = $this->Sale->get_sales_amount_for_range($start_date, $end_date);	

		foreach ($return as $key => $value) {
			if($type == 'monthly')
			{
				$day[] = date('d',strtotime($value['sale_date']));	
			}
			else
			{
				$day[] = lang('common_'.strtolower(date('l',strtotime($value['sale_date']))));
			}
			$amount[] = $value['sale_amount'];
		}	

		
		if(empty($return))
		{
			$day = array(0);
			$amount = array(0);
			$data['message'] = lang('common_not_found');
		}
		$data['day'] = json_encode($day);
		$data['amount'] = json_encode($amount);
		
		if($this->input->is_ajax_request())
		{
			if(empty($return))
			{
				echo json_encode(array('message'=>lang('common_not_found')));
				die();
			}
		    echo json_encode(array('day'=>$day,'amount'=>$amount));
		    die();
		}
		return $data;
	}
	
	function enable_test_mode()
	{
		$this->load->helper('demo');
		if (!is_on_demo_host())
		{
			$this->Appconfig->save('test_mode','1');
		}
		redirect('home');
	}
	
	function disable_test_mode()
	{
		$this->load->helper('demo');
		if (!is_on_demo_host())
		{
			$this->Appconfig->save('test_mode','0');
		}
		redirect('home');	
	}
	
	function dismiss_test_mode()
	{
		$this->Appconfig->save('hide_test_mode_home','1');		
	}
	
	function edit_profile()
	{
		if (!$this->Employee->has_module_action_permission('employees', 'edit_profile', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			redirect('no_access/home');
		}
		
		$data = array();
		$employee_person_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$data['person_info']=$this->Employee->get_info($employee_person_id);
		$data['controller_name']=strtolower(get_class());
		
		$this->load->view('edit_profile', $data);
		
	}
	
	function do_edit_profile()
	{
		if (!$this->Employee->has_module_action_permission('employees', 'edit_profile', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			redirect('no_access/home');
		}
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		
		$person_data = array(
		'first_name'=>$this->input->post('first_name'),
		'last_name'=>$this->input->post('last_name'),
		'email'=>$this->input->post('email'),
		'phone_number'=>$this->input->post('phone_number'),
		'address_1'=>$this->input->post('address_1'),
		'address_2'=>$this->input->post('address_2'),
		'city'=>$this->input->post('city'),
		'state'=>$this->input->post('state'),
		'zip'=>$this->input->post('zip'),
		'country'=>$this->input->post('country'),
		'comments'=>$this->input->post('comments')
		);
		//Password has been changed OR first time password set
		if($this->input->post('password')!='')
		{
			$employee_data=array(
			'username'=>$this->input->post('username'),
			'password'=>md5($this->input->post('password'))
			);
		}
		else //Password not changed
		{
			$employee_data=array('username'=>$this->input->post('username'));
		}
		
		
		$this->load->helper('directory');
		
		$valid_languages = str_replace(DIRECTORY_SEPARATOR,'',directory_map(APPPATH.'language/', 1));
		$employee_data=array_merge($employee_data,array('language'=>in_array($this->input->post('language'), $valid_languages) ? $this->input->post('language') : 'english'));
		$this->load->helper('demo');
		if ( (is_on_demo_host()) && $employee_id == 1)
		{
			//failure
			echo json_encode(array('success'=>false,'message'=>lang('common_employees_error_updating_demo_admin'),'person_id'=>-1));
		}
		elseif($this->Employee->save_profile($person_data,$employee_data, $employee_id))
		{
			$success_message = '';
			
			//New employee
			if($employee_id==-1)
			{
				$success_message = lang('common_employees_successful_adding').' '.$person_data['first_name'].' '.$person_data['last_name'];
				echo json_encode(array('success'=>true,'message'=>$success_message,'person_id'=>$employee_data['person_id']));
			}
			else //previous employee
			{
				$success_message = lang('common_employees_successful_updating').' '.$person_data['first_name'].' '.$person_data['last_name'];
				$this->session->set_flashdata('manage_success_message', $success_message);
				echo json_encode(array('success'=>true,'message'=>$success_message,'person_id'=>$employee_id));
			}
			
			$employee_info = $this->Employee->get_info($employee_id);
			
			//Delete Image
			if($this->input->post('del_image') && $employee_id != -1)
			{
			    if($employee_info->image_id != null)
			    {
			 		$this->load->model('Appfile');
					$this->Person->update_image(NULL,$employee_id);
					$this->Appfile->delete($employee_info->image_id);
			    }
			}

			//Save Image File
			if(!empty($_FILES["image_id"]) && $_FILES["image_id"]["error"] == UPLOAD_ERR_OK)
			{
			    $allowed_extensions = array('png', 'jpg', 'jpeg', 'gif');
				$extension = strtolower(pathinfo($_FILES["image_id"]["name"], PATHINFO_EXTENSION));
			    if (in_array($extension, $allowed_extensions))
			    {
				    $config['image_library'] = 'gd2';
				    $config['source_image']	= $_FILES["image_id"]["tmp_name"];
				    $config['create_thumb'] = FALSE;
				    $config['maintain_ratio'] = TRUE;
				    $config['width']	 = 1200;
				    $config['height']	= 900;
				    $this->load->library('image_lib', $config); 
				    $this->image_lib->resize();
					 $this->load->model('Appfile');
				    $image_file_id = $this->Appfile->save($_FILES["image_id"]["name"], file_get_contents($_FILES["image_id"]["tmp_name"]), NULL, $employee_info->image_id);
			    }
						if($employee_id==-1)
						{
			    			$this->Person->update_image($image_file_id,$employee_data['person_id']);
						}
						else
						{
							$this->Person->update_image($image_file_id,$employee_id);
		    			
						}
			}
		}
		else//failure
		{	
			echo json_encode(array('success'=>false,'message'=>lang('common_employees_error_adding_updating').' '.
			$person_data['first_name'].' '.$person_data['last_name'],'person_id'=>-1));
		}
	}
	
	function get_ecommerce_sync_progress()
	{	
		if ($this->config->item("ecommerce_platform"))
		{
			require_once (APPPATH."models/interfaces/Ecom.php");
			$ecom_model = Ecom::get_ecom_model();
			
			$progress = $ecom_model->get_sync_progress();
			echo json_encode(array('running' => $this->Appconfig->get_raw_ecommerce_cron_running() ? $this->Appconfig->get_raw_ecommerce_cron_running() : FALSE,'percent_complete' => $progress['percent_complete'],'message' => $progress['message']));
		}
		else
		{
			echo json_encode(array('running' => FALSE,'progress' =>0,'message' => ''));
		}
	}
	
	function get_qb_sync_progress()
	{	
		$this->load->model('QuickbooksModel');
		$progress = $this->QuickbooksModel->get_sync_progress();
		echo json_encode(array('running' => $this->Appconfig->get_raw_qb_cron_running() ? $this->Appconfig->get_raw_qb_cron_running() : FALSE,'percent_complete' => $progress['percent_complete'],'message' => $progress['message']));
	}
	
	function reset_barcode_labels()
	{
		$this->load->model('Appconfig');
		$this->Appconfig->save('barcode_width','');
		$this->Appconfig->save('barcode_height','');
		$this->Appconfig->save('scale','');
		$this->Appconfig->save('thickness','');
		$this->Appconfig->save('font_size','');
		$this->Appconfig->save('overall_font_size','');
		$this->Appconfig->save('zerofill_barcode','');
		redirect($_SERVER['HTTP_REFERER'] ? strtok($_SERVER['HTTP_REFERER'], '?') : site_url('items'));
	}
	
	function save_barcode_settings()
	{
		$this->load->model('Appconfig');
		$saved_name = $this->input->get('saved_name');
		foreach($this->input->get() as $var=>$value)
		{
			$this->Appconfig->save($var,$value);
		}
		
		if ($saved_name)
		{
			$this->Appconfig->save('barcoded_labels_'.$saved_name,serialize($this->input->get()));
		}
	}
	
	function save_scroll()
	{
		$save_scroll = $this->input->get('scroll_to');
		$this->session->set_userdata('scroll_to',$save_scroll);
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
	
	function offline($ignore_timestamp='0')
	{
		$this->load->model('Appconfig');
		
		$data  = array();
		
		$data['default_payment_type'] = $this->config->item('default_payment_type') ? $this->config->item('default_payment_type') : lang('common_cash');
		
		$payment_options=array(
			lang('common_cash') => lang('common_cash'),
			lang('common_check') => lang('common_check'),
			lang('common_debit') => lang('common_debit'),
			lang('common_credit') => lang('common_credit')
			);
			
		foreach($this->Appconfig->get_additional_payment_types() as $additional_payment_type)
		{
			$payment_options[$additional_payment_type] = $additional_payment_type;
		}
			
		
		$data['payment_options'] = $payment_options;
		
		$this->load->view('offline',$data);
	}
	
	function datatable_language()
	{
		$this->load->model('Employee');
		$table_lang = $this->Employee->datatable_language();
		echo $table_lang;
	}
}
?>