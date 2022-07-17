<?php
require_once ("Secure_area.php");
require_once ("interfaces/Idata_controller.php");
class Item_kits extends Secure_area implements Idata_controller
{
	function __construct()
	{
		parent::__construct('item_kits');
		$this->lang->load('item_kits');
		$this->lang->load('items');
		$this->lang->load('module');
		$this->load->model('Item_kit');
		$this->load->model('Item_kit_items');
		$this->load->model('Category');
		$this->load->model('Tag');
		$this->load->model('Item_modifier');
		
		
	}

	function custom_fields()
	{
		$this->lang->load('config');
		$fields_prefs = $this->config->item('item_kit_custom_field_prefs') ? unserialize($this->config->item('item_kit_custom_field_prefs')) : array();
		$data = array_merge(array('controller_name' => strtolower(get_class())),$fields_prefs);
		$locations_list = $this->Location->get_all()->result();
		$data['locations'] = $locations_list;
		$this->load->view('custom_fields',$data);
	}
	
	function save_custom_fields()
	{
		$this->load->model('Appconfig');
		$this->Appconfig->save('item_kit_custom_field_prefs',serialize($this->input->post()));
	}


	function index($offset=0)
	{
		$params = $this->session->userdata('item_kits_search_data') ? $this->session->userdata('item_kits_search_data') : array('offset' => 0, 'order_col' => 'item_kit_id', 'order_dir' => 'desc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => 0);
		if ($offset!=$params['offset'])
		{
		   redirect('item_kits/index/'.$params['offset']);
		}
		$this->check_action_permission('search');
		$config['base_url'] = site_url('item_kits/sorting');
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		
		$data['controller_name']=strtolower(get_class());
		$data['per_page'] = $config['per_page'];
		$data['deleted'] = $params['deleted'];
		$data['search'] = $params['search'] ? $params['search'] : "";
		$data['category_id'] = $params['category_id'] ? $params['category_id'] : "";
		$data['categories'][''] = lang('common_all');
		$categories = $this->Category->sort_categories_and_sub_categories($this->Category->get_all_categories_and_sub_categories());
		$data['fields'] = $params['fields'] ? $params['fields'] : "all";
		
		foreach($categories as $key=>$value)
		{
			$name = $this->config->item('show_full_category_path') ? str_repeat('&nbsp;&nbsp;', $value['depth']).$this->Category->get_full_path($key) : str_repeat('&nbsp;&nbsp;', $value['depth']).$value['name'];
			$data['categories'][$key] = $name;
		}
		
		if ($data['search'] || $data['category_id'])
		{
			$config['total_rows'] = $this->Item_kit->search_count_all($data['search'],$params['deleted'],$data['category_id']);
			$table_data = $this->Item_kit->search($data['search'],$params['deleted'],$data['category_id'], $data['per_page'],$params['offset'],$params['order_col'],$params['order_dir'],$data['fields']);
		}
		else
		{
			$config['total_rows'] = $this->Item_kit->count_all($params['deleted']);
			$table_data = $this->Item_kit->get_all($params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		
		$data['total_rows'] = $config['total_rows'];
		$this->load->library('pagination');$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['order_col'] = $params['order_col'];
		$data['order_dir'] = $params['order_dir'];
		$data['manage_table']=get_item_kits_manage_table($table_data,$this);
		$this->load->model('Employee_appconfig');
		$data['default_columns'] = $this->Item_kit->get_default_columns();
		$data['selected_columns'] = $this->Employee->get_item_kit_columns_to_display();
		$data['all_columns'] = array_merge($data['selected_columns'], $this->Item_kit->get_displayable_columns());		
		
		$this->load->view('item_kits/manage',$data);
	}
	
	function sorting()
	{
		$this->check_action_permission('search');
		$params = $this->session->userdata('item_kits_search_data') ? $this->session->userdata('item_kits_search_data') : array('offset' => 0, 'order_col' => 'item_kit_id', 'order_dir' => 'desc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => 0);
		$search=$this->input->post('search') ? $this->input->post('search') : "";
		$per_page=$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		$category_id = $this->input->post('category_id');
		$fields = $this->input->post('fields') ? $this->input->post('fields') : 'all';
		
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : $params['order_col'];
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): $params['order_dir'];
		$deleted = $this->input->post('deleted') ? $this->input->post('deleted') : $params['deleted'];
		
		$item_kits_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search,'category_id' => $category_id, 'fields' => $fields,'deleted' => $deleted);
		
		
		$this->session->set_userdata("item_kits_search_data",$item_kits_search_data);
		if ($search)
		{
			$config['total_rows'] = $this->Item_kit->search_count_all($search,$deleted,$category_id);
			$table_data = $this->Item_kit->search($search,$deleted,$category_id, $per_page,$this->input->post('offset') ? $this->input->post('offset') : 0,$order_col, $order_dir, $fields);
		}
		else
		{
			$config['total_rows'] = $this->Item_kit->count_all($deleted);
			$table_data = $this->Item_kit->get_all($deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0,$order_col, $order_dir);
		}
		$config['base_url'] = site_url('item_kits/sorting');
		$config['per_page'] = $per_page; 
		$this->load->library('pagination');$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['manage_table']=get_Item_kits_manage_table_data_rows($table_data,$this);
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination'],'total_rows' => $config['total_rows']));	
	}
	

	/* added for excel expert */
	function excel_export() {
		$this->check_action_permission('excel_export');
		ini_set('memory_limit','1024M');
		$has_cost_price_permission = $this->Employee->has_module_action_permission('item_kits','see_cost_price', $this->Employee->get_logged_in_employee_info()->person_id);
		
		set_time_limit(0);
		ini_set('max_input_time','-1');
		$this->load->model('Item_kit_taxes_finder');
		$this->load->model('Item_kit_location');
		$this->load->model('Manufacturer');
		
		$params = $this->session->userdata('item_kits_search_data') ? $this->session->userdata('item_kits_search_data') : array('offset' => 0, 'order_col' => 'name', 'order_dir' => 'asc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => 0);
		
		$search = $params['search'] ? $params['search'] : "";
		$category_id = $params['category_id'] ? $params['category_id'] : "";
		
		//Filter based on search
		if ($search || $category_id)
		{
			$data = $this->Item_kit->search($search,$params['deleted'],$category_id,$this->Item_kit->search_count_all($search, $params['deleted'],$category_id,10000, $params['fields']),0,$params['order_col'],$params['order_dir'], $params['fields'])->result_object();
		}
		else
		{
			$data = $this->Item_kit->get_all($params['deleted'])->result_object();
		}
		
		
		$this->load->helper('report');
		$rows = array();
		$row = array(lang('common_item_number'),lang('common_product_id'), lang('item_kits_name'),lang('common_category'),lang('common_manufacturer'),lang('common_allow_price_override_regardless_of_permissions'),lang('common_only_integer'),lang('common_is_barcoded'),lang('common_is_favorite'),lang('common_inactive'),lang('common_cost_price'),lang('common_unit_price'),lang('item_kits_tax_1_name'),lang('item_kits_tax_1_percent'),lang('item_kits_tax_2_name'),lang('item_kits_tax_2_percent'),lang('item_kits_tax_2_cummulative'),lang('item_kits_description'));
		
		
		if(!$has_cost_price_permission)
		{
			//remove cost price from array
			unset($row[5]);
		}
		
		if ($this->config->item('enable_customer_loyalty_system') && $this->config->item('loyalty_option') == 'advanced')
		{
			$row[] = lang('common_disable_loyalty');
		}
		
		if ($this->config->item('loyalty_option') == 'advanced')
		{
			$row[] = lang('common_loyalty_multiplier');
		}
		
		if($this->config->item("verify_age_for_products"))
		{		
			$row[] = lang('common_requires_age_verification');
			$row[] = lang('common_required_age');
		}
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if ($this->Item_kit->get_custom_field($k) !== FALSE)
			{
				$row[] = $this->Item_kit->get_custom_field($k);
			}
		}
		
		$rows[] = $row;
		
		$categories = $this->Category->get_all_categories_and_sub_categories_as_indexed_by_category_id();
		$manufacturers = array();
		
	 foreach($this->Manufacturer->get_all() as $id => $row)
	 {
	 	 	$manufacturers[$id] = $row['name'];
	 
	 }
		foreach ($data as $r) {
			$taxdata = $this->Item_kit_taxes_finder->get_info($r->item_kit_id);
			if (sizeof($taxdata) >= 2) {
				$r->taxn = $taxdata[0]['name'];
				$r->taxp = $taxdata[0]['percent'];
				$r->taxn1 = $taxdata[1]['name'];
				$r->taxp1 = $taxdata[1]['percent'];
				$r->cumulative = $taxdata[1]['cumulative'] ? 'y' : '';
			} else if (sizeof($taxdata) == 1) {
				$r->taxn = $taxdata[0]['name'];
				$r->taxp = $taxdata[0]['percent'];
				$r->taxn1 = '';
				$r->taxp1 = '';
				$r->cumulative = '';
			} else {
				$r->taxn = '';
				$r->taxp = '';
				$r->taxn1 = '';
				$r->taxp1 = '';
				$r->cumulative = '';
			}
			
			$row = array(
				$r->item_kit_number,
				$r->product_id,
				$r->name,
				isset($categories[$r->category_id]) ? $categories[$r->category_id] : '',
				isset($manufacturers[$r->manufacturer_id]) ? $manufacturers[$r->manufacturer_id] : '',
				$r->allow_price_override_regardless_of_permissions ? 'y' : '',
				$r->only_integer ? 'y' : '',
				$r->is_barcoded ? 'y' : '',
				$r->is_favorite ? 'y' : '',
				$r->item_kit_inactive ? 'y' : 'n',
				to_currency_no_money($r->cost_price),
				to_currency_no_money($r->unit_price),
				$r->taxn,
				$r->taxp,
				$r->taxn1,
				$r->taxp1,
				$r->cumulative,
				$r->description,
			);
			
			if(!$has_cost_price_permission)
			{
				//remove cost price from array
				unset($row[5]);
			}
			
			if ($this->config->item('enable_customer_loyalty_system') && $this->config->item('loyalty_option') == 'advanced')
			{
				$row[] = $r->disable_loyalty ? 'y' : '';				
			}
			
			if ($this->config->item('loyalty_option') == 'advanced')
			{
				$row[] = $r->loyalty_multiplier ? $r->loyalty_multiplier : NULL;				
			}
			
			if($this->config->item("verify_age_for_products"))
			{
				$row[] = $r->verify_age ? 'y' : '';
				$row[] = $r->required_age;
			}
			
			for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
			{
				$type = $this->Item_kit->get_custom_field($k,'type');
				$name = $this->Item_kit->get_custom_field($k,'name');
			
				if ($name !== FALSE)
				{
					if ($type == 'date')
					{
						$row[] = date(get_date_format(),$r->{"custom_field_{$k}_value"});
					}
					elseif($type=='checkbox')
					{
						$row[] = $r->{"custom_field_{$k}_value"} ? '1' : '0';					
					}
					else
					{
						$row[] = $r->{"custom_field_{$k}_value"};				
					}
				}
			}
			
			$rows[] = $row;		
		}
		
		$this->load->helper('spreadsheet');
		array_to_spreadsheet($rows,'itemkits_export.'.($this->config->item('spreadsheet_format') == 'XLSX' ? 'xlsx' : 'csv'));
		exit;
	}
	
	function item_search()
	{
		$this->load->model('Item');
		//allow parallel searchs to improve performance.
		session_write_close();
		$suggestions = $this->Item->get_item_search_suggestions($this->input->get('term'),0,'unit_price',25);
		$suggestions = array_merge($suggestions, $this->Item_kit->get_item_kit_search_suggestions_sales_recv($this->input->get('term'),0,'unit_price', 100));
		echo json_encode(H($suggestions));
	}

	function search()
	{
		$this->check_action_permission('search');
		$params = $this->session->userdata('item_kits_search_data');
		
		$search=$this->input->post('search');
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : 'name';
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc';
		$category_id = $this->input->post('category_id');
		$fields = $this->input->post('fields') ? $this->input->post('fields') : 'all';
		$deleted = isset($params['deleted']) ? $params['deleted'] : 0;

		$item_kits_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search,'category_id' => $category_id, 'fields' => $fields,'deleted' => $deleted);
		
		$this->session->set_userdata("item_kits_search_data",$item_kits_search_data);
		$per_page=$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$search_data=$this->Item_kit->search($search,$deleted,$category_id,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'name' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc',$fields);
		$config['base_url'] = site_url('item_kits/search');
		$config['total_rows'] = $this->Item_kit->search_count_all($search,$deleted,$category_id);
		$config['per_page'] = $per_page ;
		
		$this->load->library('pagination');$this->pagination->initialize($config);				
		$data['pagination'] = $this->pagination->create_links();
		$data['manage_table']=get_Item_kits_manage_table_data_rows($search_data,$this);
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination'],'total_rows' => $config['total_rows']));
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		$params = $this->session->userdata('item_kits_search_data') ? $this->session->userdata('item_kits_search_data') : array('deleted' => 0);
		$suggestions = $this->Item_kit->get_manage_item_kits_search_suggestions($this->input->get('term'),$params['deleted'],100);
		echo json_encode(H($suggestions));
	}
	
	function check_duplicate()
	{
		echo json_encode(array('duplicate'=>$this->Item_kit->check_duplicate($this->input->post('term'))));

	}
	
	function _get_item_kit_data($item_kit_id)
	{
		$this->load->model('Tax_class');
		
		$data = array();
		$data['tax_classes'] = array();
		$data['tax_classes'][''] = lang('common_none');
		
		foreach($this->Tax_class->get_all()->result_array() as $tax_class)
		{
			$data['tax_classes'][$tax_class['id']] = $tax_class['name'];
		}
		
		$data['controller_name']=strtolower(get_class());
		$data['item_kit_info']=$this->Item_kit->get_info($item_kit_id);
		$data['item_kit_items'] = $this->Item_kit_items->get_info($item_kit_id);
		$data['item_kit_item_kits'] = $this->Item_kit_items->get_info_kits($item_kit_id);
		$data['tags'] = implode(',',$this->Tag->get_tags_for_item_kit($item_kit_id));
		$data['other_locations'] = array();
		
		$data['categories'][''] = lang('common_select_category');
		
		$categories = $this->Category->sort_categories_and_sub_categories($this->Category->get_all_categories_and_sub_categories());
		foreach($categories as $key=>$value)
		{
			$name = $this->config->item('show_full_category_path') ? str_repeat('&nbsp;&nbsp;', $value['depth']).$this->Category->get_full_path($key) : str_repeat('&nbsp;&nbsp;', $value['depth']).$value['name'];
			$data['categories'][$key] = $name;
		}
		
		$this->load->model('Manufacturer');
		$manufacturers = array('-1' => lang('common_none'));
		
		foreach($this->Manufacturer->get_all() as $id => $row)
		{
			$manufacturers[$id] = $row['name'];
		}
		
		$data['manufacturers'] = $manufacturers;
		$data['selected_manufacturer'] = $this->Item_kit->get_info($item_kit_id)->manufacturer_id;
		
		
		$data['item_kit_tax_info']=$this->Item_kit_taxes->get_info($item_kit_id);
		$data['tiers']=$this->Tier->get_all()->result();		
		
		$data['tier_prices'] = array();
		$data['tier_type_options'] = array('unit_price' => lang('common_fixed_price'), 'percent_off' => lang('common_percent_off'), 'cost_plus_percent' => lang('common_cost_plus_percent'),'cost_plus_fixed_amount' => lang('common_cost_plus_fixed_amount'));
		
		
		foreach($this->Location->get_all()->result() as $location)
		{
			if($this->Employee->is_location_authenticated($location->location_id))
			{				
				$data['locations'][] = $location;
				$data['location_item_kits'][$location->location_id] = $this->Item_kit_location->get_info($item_kit_id,$location->location_id);
				$data['location_taxes'][$location->location_id] = $this->Item_kit_location_taxes->get_info($item_kit_id, $location->location_id);
								
				foreach($data['tiers'] as $tier)
				{					
					$tier_prices = $this->Item_kit_location->get_tier_price_row($tier->id,$data['item_kit_info']->item_kit_id, $location->location_id);
					if (!empty($tier_prices))
					{
						$data['location_tier_prices'][$location->location_id][$tier->id] = $tier_prices;
					}
					else
					{
						$data['location_tier_prices'][$location->location_id][$tier->id] = FALSE;			
					}
				}
			}else{
				$data['other_locations'][] = $location;
			}
			
		}
		
		foreach($data['tiers'] as $tier)
		{
			$tier_prices = $this->Item_kit->get_tier_price_row($tier->id,$data['item_kit_info']->item_kit_id);
			
			if (!empty($tier_prices))
			{
				$data['tier_prices'][$tier->id] = $tier_prices;
			}
			else
			{
				$data['tier_prices'][$tier->id] = FALSE;			
			}
		}
		$decimals = $this->Appconfig->get_raw_number_of_decimals();
		$decimals = $decimals !== NULL && $decimals!= '' ? $decimals : 2;
		$data['decimals'] = $decimals;
		
		if ($item_kit_id != -1)
		{
			$data['next_item_kit_id'] = $this->Item_kit->get_next_id($item_kit_id);
			$data['prev_item_kit_id'] = $this->Item_kit->get_prev_id($item_kit_id);;
		}
		
		$data['item_kit_images']=$this->Item_kit->get_item_kit_images($item_kit_id);
		
		return $data;
	}
	
	function view($item_kit_id=-1)
	{
 	 	$this->load->model('Appfile');
		
		$this->load->model('Item_variations');
		$this->load->model('Item_kit_items');
		$this->load->model('Item_kit_taxes');
		$this->load->model('Tier');
		$this->load->model('Item');
		$this->load->model('Item_kit_location');
		$this->load->model('Item_kit_location_taxes');
		$this->load->model('Supplier');
		$this->load->model('Item_kit_taxes_finder');
		
		$this->check_action_permission('add_update');	
		$data = $this->_get_item_kit_data($item_kit_id);
		
		$data['category'] = $this->Category->get_full_path($data['item_kit_info']->category_id);
		
		$data['redirect'] = $this->input->get('redirect');
		$progression = $this->input->get('progression');
		$quick_edit = $this->input->get('quick_edit');
		$data['progression'] = !empty($progression);
		$data['quick_edit'] = !empty($quick_edit);
		$data['current_location'] = $this->Employee->get_logged_in_employee_current_location_id();
		
		$this->load->view("item_kits/form",$data);
	}
	
	function items($item_kit_id=-1)
	{
		$this->load->model('Item_variations');
		$this->load->model('Item_kit_items');
		$this->load->model('Item_kit_taxes');
		$this->load->model('Tier');
		$this->load->model('Item');
		$this->load->model('Item_kit_location');
		$this->load->model('Item_kit_location_taxes');
		$this->load->model('Supplier');
		$this->load->model('Item_kit_taxes_finder');
		
		$this->check_action_permission('add_update');	
		$data = $this->_get_item_kit_data($item_kit_id);
		
		$data['category'] = $this->Category->get_full_path($data['item_kit_info']->category_id);
		
		$data['redirect'] = $this->input->get('redirect');
		$progression = $this->input->get('progression');
		$quick_edit = $this->input->get('quick_edit');
		$data['progression'] = !empty($progression);
		$data['quick_edit'] = !empty($quick_edit);
		
		$this->load->view("item_kits/items",$data);
	}
	
	function pricing($item_kit_id=-1)
	{
		$this->check_action_permission('edit_prices');
		
		$this->load->model('Item_kit_items');
		$this->load->model('Item_kit_taxes');
		$this->load->model('Tier');
		$this->load->model('Item');
		$this->load->model('Item_kit_location');
		$this->load->model('Item_kit_location_taxes');
		$this->load->model('Supplier');
		$this->load->model('Item_kit_taxes_finder');
		
		$this->check_action_permission('add_update');	
		$data = $this->_get_item_kit_data($item_kit_id);
		
		$data['category'] = $this->Category->get_full_path($data['item_kit_info']->category_id);
		
		$data['redirect'] = $this->input->get('redirect');
		$data['progression'] = $this->input->get('progression');
		$data['quick_edit'] = $this->input->get('quick_edit');
		
		$this->load->view("item_kits/pricing",$data);
	}
	
	
	function location_settings($item_kit_id=-1)
	{
		$this->load->model('Item_kit_items');
		$this->load->model('Item_kit_taxes');
		$this->load->model('Tier');
		$this->load->model('Item');
		$this->load->model('Item_kit_location');
		$this->load->model('Item_kit_location_taxes');
		$this->load->model('Supplier');
		$this->load->model('Item_kit_taxes_finder');
		
		$this->check_action_permission('add_update');	
		$data = $this->_get_item_kit_data($item_kit_id);
		
		$data['category'] = $this->Category->get_full_path($data['item_kit_info']->category_id);
		
		$data['redirect'] = $this->input->get('redirect');
		$progression = $this->input->get('progression');
		$quick_edit = $this->input->get('quick_edit');
		$data['progression'] = !empty($progression);
		$data['quick_edit'] = !empty($quick_edit);
		
		$this->load->view("item_kits/locations",$data);
	}
	
	function clone_item_kit($item_kit_id)
	{
		$this->load->model('Item_kit_items');
		$this->load->model('Item_kit_taxes');
		$this->load->model('Tier');
		$this->load->model('Item');
		$this->load->model('Item_kit_location');
		$this->load->model('Item_kit_location_taxes');
		$this->load->model('Supplier');
		$this->load->model('Item_kit_taxes_finder');
		
		$this->check_action_permission('add_update');
		
		$redirect = $this->input->get('redirect');
		$data = $this->_get_item_kit_data($item_kit_id);
		$data['item_kit_info']->item_kit_number = '';
		$data['item_kit_info']->product_id = '';		
		$data['redirect']=2;
		
		$item_kit_data = array(
			'item_kit_number'=>$data['item_kit_info']->item_kit_number=='' ? null:$data['item_kit_info']->item_kit_number,
			'product_id'=>$data['item_kit_info']->product_id=='' ? null:$data['item_kit_info']->product_id,
			'name'=>$data['item_kit_info']->name,
			'category_id'=>$data['item_kit_info']->category_id == '' ? null : $data['item_kit_info']->category_id,
			'manufacturer_id'=>$data['item_kit_info']->manufacturer_id== -1 || $data['item_kit_info']->manufacturer_id == '' ? null:$data['item_kit_info']->manufacturer_id,
			'description'=>$data['item_kit_info']->description,
			'is_ebt_item'=> $data['item_kit_info']->is_ebt_item ? 1 : 0,
			'verify_age'=> $data['item_kit_info']->verify_age ? 1 : 0,
			'required_age'=> $data['item_kit_info']->required_age ? $data['item_kit_info']->required_age : NULL,
			'tax_included'=>$data['item_kit_info']->tax_included ? $data['item_kit_info']->tax_included : 0,
			'unit_price'=>$data['item_kit_info']->unit_price=='' ? null:$data['item_kit_info']->unit_price,
			'cost_price'=>$data['item_kit_info']->cost_price=='' ? null:$data['item_kit_info']->cost_price,
			'min_edit_price'=>$data['item_kit_info']->min_edit_price !== '' ? $data['item_kit_info']->min_edit_price : NULL,
			'max_edit_price'=>$data['item_kit_info']->max_edit_price !== '' ? $data['item_kit_info']->max_edit_price : NULL,
			'max_discount_percent'=>$data['item_kit_info']->max_discount_percent !== '' ? $data['item_kit_info']->max_discount_percent : NULL,
			'change_cost_price' => $data['item_kit_info']->change_cost_price ? $data['item_kit_info']->change_cost_price : 0,
			'override_default_tax'=> $data['item_kit_info']->override_default_tax ? $data['item_kit_info']->override_default_tax : 0,
			'tax_class_id'=> $data['item_kit_info']->tax_class_id ? $data['item_kit_info']->tax_class_id : NULL,		
			'allow_price_override_regardless_of_permissions' => $data['item_kit_info']->allow_price_override_regardless_of_permissions ? 1 : 0,
			'only_integer' => $data['item_kit_info']->only_integer ? 1 : 0,
			'is_barcoded' => $data['item_kit_info']->is_barcoded ? 1 : 0,
			'item_kit_inactive' => $data['item_kit_info']->item_kit_inactive ? 1 : 0,
			'is_favorite' => $data['item_kit_info']->is_favorite ? 1 : 0,
		);
		
		if ($this->config->item('enable_customer_loyalty_system') && $this->config->item('loyalty_option') == 'advanced')
		{
			$data['item_kit_info']->disable_loyalty = $data['item_kit_info']->disable_loyalty ? $data['item_kit_info']->disable_loyalty : 0;
		}
		
		if ($this->config->item('loyalty_option') == 'advanced')
		{
			$data['item_kit_info']->loyalty_multiplier = $data['item_kit_info']->loyalty_multiplier ? $data['item_kit_info']->loyalty_multiplier : NULL;
		}		
		
		$item_kit_data['commission_percent'] = NULL;
		$item_kit_data['commission_fixed'] = NULL;
		$item_kit_data['commission_percent_type'] = '';
		

		if($this->Item_kit->save($item_kit_data))
		{
			$item_kit_items = array();
			foreach($data['item_kit_items'] as $item_kit_item)
			{
				$item_kit_items[] = array(
					'item_id' => $item_kit_item->item_id,
					'item_variation_id' => $item_kit_item->item_variation_id,
					'quantity' => $item_kit_item->quantity
					);
			}
			$this->Item_kit_items->save($item_kit_items, $item_kit_data['item_kit_id']);
			
			$this->Tag->save_tags_for_item_kit($item_kit_data['item_kit_id'], $data['tags']);
		}
		
		redirect("item_kits/view/".$item_kit_data['item_kit_id']."?redirect=$redirect");
	}
	
	function save_item_kit_location($item_kit_id=-1)
	{
		$this->check_action_permission('add_update');
		
		$redirect = $this->input->post('redirect');
		$progression_post = $this->input->post('progression');
		$quick_edit_post = $this->input->post('quick_edit_post');
		$progression= !empty($progression_post) ? 1 : null;
		$quick_edit= !empty($quick_edit_post) ? 1 : null;
		
		$this->load->model('Item_kit_location');
		$this->load->model('Item_kit_location_taxes');
		
		$item_kit_info = $this->Item_kit->get_info($item_kit_id);
		
		if ($this->input->post('locations'))
		{
			foreach($this->input->post('locations') as $location_id => $item_kit_location_data)
			{		        
				if($this->Employee->is_location_authenticated($location_id)){

					$override_prices = isset($item_kit_location_data['override_prices']) && $item_kit_location_data['override_prices'];
			
					$data = array(
						'location_id' => $location_id,
						'item_kit_id' => $item_kit_id,
						'cost_price' => $override_prices && $item_kit_location_data['cost_price'] != '' ? $item_kit_location_data['cost_price'] : NULL,
						'unit_price' => $override_prices && $item_kit_location_data['unit_price'] != '' ? $item_kit_location_data['unit_price'] : NULL,
						'override_default_tax'=> isset($item_kit_location_data['override_default_tax'] ) && $item_kit_location_data['override_default_tax'] != '' ? $item_kit_location_data['override_default_tax'] : 0,
						'tax_class_id'=> isset($item_kit_location_data['tax_class']) && $item_kit_location_data['tax_class'] ? $item_kit_location_data['tax_class'] : NULL,
					);
					
					$this->Item_kit_location->save($data, $item_kit_id,$location_id);
					
					if (isset($item_kit_location_data['ban_from_location']) && $item_kit_location_data['ban_from_location'])
					{
						$this->Item_kit->add_ban_item_kit($item_kit_id,$location_id);
					}
					else
					{
						$this->Item_kit->remove_ban_item_kit($item_kit_id,$location_id);
					}
					
					if (isset($item_kit_location_data['hide_from_grid']) && $item_kit_location_data['hide_from_grid'])
					{
						$this->Item_kit->add_hidden_item_kit($item_kit_id,$location_id);
					}
					else
					{
						$this->Item_kit->remove_hidden_item_kit($item_kit_id,$location_id);
					}
					

					if (isset($item_kit_location_data['item_tier']))
					{
						$tier_type = $item_kit_location_data['tier_type'];

						foreach($item_kit_location_data['item_tier'] as $tier_id => $price_or_percent)
						{
							//If we are overriding prices and we have a price/percent, add..otherwise delete
							if ($override_prices && $price_or_percent !== '')
							{				
								$tier_data=array('tier_id'=>$tier_id);
								$tier_data['item_kit_id'] = isset($item_data['item_kit_id']) ? $item_data['item_kit_id'] : $item_kit_id;
								$tier_data['location_id'] = $location_id;
							
								if ($tier_type[$tier_id] == 'unit_price')
								{
									$tier_data['unit_price'] = $price_or_percent;
									$tier_data['percent_off'] = NULL;
									$tier_data['cost_plus_percent'] = NULL;
									$tier_data['cost_plus_fixed_amount'] = NULL;
								}
								elseif($tier_type[$tier_id] == 'percent_off')
								{
									$tier_data['percent_off'] = (float)$price_or_percent;
									$tier_data['unit_price'] = NULL;
									$tier_data['cost_plus_percent'] = NULL;
									$tier_data['cost_plus_fixed_amount'] = NULL;
								}
								elseif($tier_type[$tier_id] == 'cost_plus_percent')
								{
									$tier_data['percent_off'] = NULL;
									$tier_data['unit_price'] = NULL;
									$tier_data['cost_plus_percent'] = (float)$price_or_percent;
									$tier_data['cost_plus_fixed_amount'] = NULL;
								}
								elseif($tier_type[$tier_id] == 'cost_plus_fixed_amount')
								{
									$tier_data['percent_off'] = NULL;
									$tier_data['unit_price'] = NULL;
									$tier_data['cost_plus_percent'] = NULL;
									$tier_data['cost_plus_fixed_amount'] = (float)$price_or_percent;
								}

								$this->Item_kit_location->save_item_tiers($tier_data,$item_kit_id, $location_id);
							}
							else
							{
								$this->Item_kit_location->delete_tier_price($tier_id, $item_kit_id, $location_id);
							}

						}
					}
									
					$location_items_taxes_data = array();
				
					$tax_names = $item_kit_location_data['tax_names'];
					$tax_percents = $item_kit_location_data['tax_percents'];
					$tax_cumulatives = $item_kit_location_data['tax_cumulatives'];
					for($k=0;$k<count($tax_percents);$k++)
					{
						if (is_numeric($tax_percents[$k]))
						{
							$location_items_taxes_data[] = array('name'=>$tax_names[$k], 'percent'=>$tax_percents[$k], 'cumulative' => isset($tax_cumulatives[$k]) ? $tax_cumulatives[$k] : '0' );
						}
					}
					$this->Item_kit_location_taxes->save($location_items_taxes_data, $item_kit_id, $location_id);
				}else{

					if (isset($item_kit_location_data['ban_from_location']) && $item_kit_location_data['ban_from_location'])
					{
						$this->Item_kit->add_ban_item_kit($item_kit_id,$location_id);
					}
					else
					{
						$this->Item_kit->remove_ban_item_kit($item_kit_id,$location_id);
					}
				}
			}
		}
		
		$success_message = H(lang('item_kits_successful_updating').' '.$item_kit_info->name);
		$this->session->set_flashdata('manage_success_message', $success_message);
		echo json_encode(array('success'=>true,'message'=>$success_message,'item_kit_id'=>$item_kit_id, 'redirect' => $redirect, 'progression' => $progression, 'quick_edit' => $quick_edit));
	}
	
	function save_item_kit_pricing($item_kit_id=-1)
	{
		$this->check_action_permission('add_update');
		$this->check_action_permission('edit_prices');
		$redirect = $this->input->post('redirect');
		$progression_post = $this->input->post('progression');
		$quick_edit_post = $this->input->post('quick_edit_post');
		$progression= !empty($progression_post) ? 1 : null;
		$quick_edit= !empty($quick_edit_post) ? 1 : null;
		
		$this->load->model('Item_kit_taxes');
		$this->load->model('Item_kit_items');
		
		$item_kit_info = $this->Item_kit->get_info($item_kit_id);
		
		$item_kit_data = array(
		'tax_included'=>$this->input->post('tax_included') ? $this->input->post('tax_included') : 0,
		'unit_price'=>$this->input->post('unit_price')=='' ? null:$this->input->post('unit_price'),
		'cost_price'=>$this->input->post('cost_price')=='' ? null:$this->input->post('cost_price'),
		'min_edit_price'=>$this->input->post('min_edit_price') !== '' ? $this->input->post('min_edit_price') : NULL,
		'max_edit_price'=>$this->input->post('max_edit_price') !== '' ? $this->input->post('max_edit_price') : NULL,
		'max_discount_percent'=>$this->input->post('max_discount_percent') !== '' ? $this->input->post('max_discount_percent') : NULL,
		'change_cost_price' => $this->input->post('change_cost_price') ? $this->input->post('change_cost_price') : 0,
		'override_default_tax'=> $this->input->post('override_default_tax') ? $this->input->post('override_default_tax') : 0,
		'tax_class_id'=> $this->input->post('tax_class') ? $this->input->post('tax_class') : NULL,
		'allow_price_override_regardless_of_permissions' => $this->input->post('allow_price_override_regardless_of_permissions') ? 1 : 0,
		'only_integer' => $this->input->post('only_integer') ? 1 : 0,
		'disable_from_price_rules' => $this->input->post('disable_from_price_rules') ? 1 : 0,
		'dynamic_pricing' => $this->input->post('dynamic_pricing') ? 1 : 0,
	);
		
		if ($this->input->post('override_default_commission'))
		{
			if ($this->input->post('commission_type') == 'fixed')
			{
				$item_kit_data['commission_fixed'] = (float)$this->input->post('commission_value');
				$item_kit_data['commission_percent_type'] = '';
				$item_kit_data['commission_percent'] = NULL;
			}
			else
			{
				$item_kit_data['commission_percent'] = (float)$this->input->post('commission_value');
				$item_kit_data['commission_percent_type'] = $this->input->post('commission_percent_type');
				$item_kit_data['commission_fixed'] = NULL;
			}
		}
		else
		{
			$item_kit_data['commission_percent'] = NULL;
			$item_kit_data['commission_fixed'] = NULL;
			$item_kit_data['commission_percent_type'] = '';
		}
		
		if($this->Item_kit->save($item_kit_data,$item_kit_id))
		{
			$tier_type = $this->input->post('tier_type');
			
			if ($this->input->post('item_kit_tier'))
			{
				foreach($this->input->post('item_kit_tier') as $tier_id => $price_or_percent)
				{
					if ($price_or_percent !== '')
					{				
						$tier_data=array('tier_id'=>$tier_id);
						$tier_data['item_kit_id'] = isset($item_kit_data['item_kit_id']) ? $item_kit_data['item_kit_id'] : $item_kit_id;

						if ($tier_type[$tier_id] == 'unit_price')
						{
							$tier_data['unit_price'] = $price_or_percent;
							$tier_data['percent_off'] = NULL;
							$tier_data['cost_plus_percent'] = NULL;
							$tier_data['cost_plus_fixed_amount'] = NULL;
						}
						elseif($tier_type[$tier_id] == 'percent_off')
						{
							$tier_data['percent_off'] = (float)$price_or_percent;
							$tier_data['unit_price'] = NULL;
							$tier_data['cost_plus_percent'] = NULL;
							$tier_data['cost_plus_fixed_amount'] = NULL;
						}
						elseif($tier_type[$tier_id] == 'cost_plus_percent')
						{
							$tier_data['percent_off'] = NULL;
							$tier_data['unit_price'] = NULL;
							$tier_data['cost_plus_percent'] = (float)$price_or_percent;
							$tier_data['cost_plus_fixed_amount'] = NULL;
						}
						elseif($tier_type[$tier_id] == 'cost_plus_fixed_amount')
						{
							$tier_data['percent_off'] = NULL;
							$tier_data['unit_price'] = NULL;
							$tier_data['cost_plus_percent'] = NULL;
							$tier_data['cost_plus_fixed_amount'] = (float)$price_or_percent;
						}
			
		
						$this->Item_kit->save_item_tiers($tier_data,$item_kit_id);
					}
					else
					{
						$this->Item_kit->delete_tier_price($tier_id, $item_kit_id);
					}
				}
			}
			
			$item_kits_taxes_data = array();
			$tax_names = $this->input->post('tax_names');
			$tax_percents = $this->input->post('tax_percents');
			$tax_cumulatives = $this->input->post('tax_cumulatives');
		
			for($k=0;$k<count($tax_percents);$k++)
			{
				if (is_numeric($tax_percents[$k]))
				{
					$item_kits_taxes_data[] = array('name'=>$tax_names[$k], 'percent'=>$tax_percents[$k], 'cumulative' => isset($tax_cumulatives[$k]) ? $tax_cumulatives[$k] : '0' );
				}
			}			
		
			$this->Item_kit_taxes->save($item_kits_taxes_data, $item_kit_id);
				
			$success_message = H(lang('item_kits_successful_updating').' '.$item_kit_info->name);
			$this->session->set_flashdata('manage_success_message', $success_message);
			echo json_encode(array('success'=>true, 'message'=>$success_message, 'item_kit_id'=>$item_kit_id, 'redirect' => $redirect, 'progression' => $progression, 'quick_edit' => $quick_edit));
		}
		else//failure
		{
			echo json_encode(array('success'=>false,'message'=>H(lang('item_kits_error_adding_updating').' '.
			$item_kit_info->name),'item_kit_id'=>-1));
		}
	}
	
	function save_items($item_kit_id=-1)
	{
		$this->check_action_permission('add_update');
		
		$redirect = $this->input->post('redirect');
		$progression_post = $this->input->post('progression');
		$quick_edit_post = $this->input->post('quick_edit_post');
		$progression= !empty($progression_post) ? 1 : null;
		$quick_edit= !empty($quick_edit_post) ? 1 : null;
		
		$this->load->model('Item_kit_items');
		
		$item_kit_info = $this->Item_kit->get_info($item_kit_id);
		
		if ($this->input->request('item_kit_item'))
		{
			$item_kit_items = array();
			$item_kit_item_kits = array();
			//Need to use request because of # in variations
			foreach($this->input->request('item_kit_item') as $item_kit_item => $quantity)
			{
				if (strpos($item_kit_item, 'KIT') === 0)
				{
					$item_kit_item_kits[] = array(
						'item_kit_item_kit' => str_replace('KIT','',$item_kit_item),
						'quantity' => $quantity
						);
				}
				else
				{
					$item_kit_item_parts = explode('#',$item_kit_item);
					$item_id = count($item_kit_item_parts) == 2 ? $item_kit_item_parts[0] : $item_kit_item;
					$item_variation_id = isset($item_kit_item_parts[1]) ? $item_kit_item_parts[1] : NULL;
					$item_kit_items[] = array(
						'item_id' => $item_id,
						'item_variation_id' => $item_variation_id,
						'quantity' => $quantity
						);
					}
			}
		
			$this->Item_kit_items->save($item_kit_items, isset($item_kit_data['item_kit_id']) ? $item_kit_data['item_kit_id'] : $item_kit_id);
			$this->Item_kit_items->save_item_kits($item_kit_item_kits, isset($item_kit_data['item_kit_id']) ? $item_kit_data['item_kit_id'] : $item_kit_id);
		}
		else
		{
			$this->Item_kit_items->delete(isset($item_kit_data['item_kit_id']) ? $item_kit_data['item_kit_id'] : $item_kit_id);				
		}
		
		$success_message = H(lang('item_kits_successful_updating').' '.$item_kit_info->name);
		$this->session->set_flashdata('manage_success_message', $success_message);
		echo json_encode(array('success'=>true, 'message'=>$success_message, 'item_kit_id'=>$item_kit_id, 'redirect' => $redirect, 'progression' => $progression, 'quick_edit' => $quick_edit));
	}
		
	function save($item_kit_id=-1)
	{
		$this->check_action_permission('add_update');
		
		$redirect = $this->input->post('redirect');
		$progression_post = $this->input->post('progression');
		$quick_edit_post = $this->input->post('quick_edit_post');
		$progression= !empty($progression_post) ? 1 : null;
		$quick_edit= !empty($quick_edit_post) ? 1 : null;
		
		$this->load->model('Item_kit_taxes');
		$this->load->model('Item_kit_location');
		$this->load->model('Item_kit_location_taxes');
				
		if (!$this->Category->exists($this->input->post('category_id')))
		{
			if (!$category_id = $this->Category->get_category_id($this->input->post('category_id')))
			{
				$category_id = $this->Category->save($this->input->post('category_id'));
			}
		}	
		else
		{
			$category_id = $this->input->post('category_id');
		}
			
		$item_kit_data = array(
			'item_kit_number'=>$this->input->post('item_kit_number')=='' ? null:$this->input->post('item_kit_number'),
			'product_id'=>$this->input->post('product_id')=='' ? null:$this->input->post('product_id'),
			'name'=>$this->input->post('name'),
			'barcode_name'=>$this->input->post('barcode_name'),
			'info_popup'=>$this->input->post('info_popup') ? $this->input->post('info_popup') : NULL,
			'category_id'=>$category_id,
			'manufacturer_id'=>$this->input->post('manufacturer_id')== -1 || $this->input->post('manufacturer_id') == '' ? null:$this->input->post('manufacturer_id'),
			'description'=>$this->input->post('description'),
			'info_popup'=>$this->input->post('info_popup') ? $this->input->post('info_popup') : NULL,
			'is_ebt_item'=> $this->input->post('is_ebt_item') ? $this->input->post('is_ebt_item') : 0,
			'verify_age'=> $this->input->post('verify_age') ? 1 : 0,
			'required_age'=> $this->input->post('verify_age') ? $this->input->post('required_age') : NULL,
			'is_barcoded' => $this->input->post('is_barcoded') ? 1 : 0,
			'item_kit_inactive' => $this->input->post('item_kit_inactive') ? 1 : 0,
			'is_favorite' => $this->input->post('is_favorite') ? 1 : 0,
		);
		
		if ($this->input->post('default_quantity') !== '')
		{
			$item_kit_data['default_quantity'] = $this->input->post('default_quantity');
		}
		else
		{
			$item_kit_data['default_quantity'] = NULL;
		}
		
		
		if ($this->config->item('enable_customer_loyalty_system') && $this->config->item('loyalty_option') == 'advanced')
		{
			$item_kit_data['disable_loyalty'] = $this->input->post('disable_loyalty') ? $this->input->post('disable_loyalty') : 0;
		}
		
		if ($this->config->item('loyalty_option') == 'advanced')
		{
			$item_kit_data['loyalty_multiplier'] = $this->input->post('loyalty_multiplier') ? $this->input->post('loyalty_multiplier') : NULL;
		}
				
				
		//New item commission and prices include tax default values need to be set as database doesn't do this for us
		if ($item_kit_id == -1)
		{
			$item_kit_data['commission_percent'] = NULL;
			$item_kit_data['commission_fixed'] = NULL;
			$item_kit_data['commission_percent_type'] = '';
			$item_kit_data['tax_included'] = $this->config->item('prices_include_tax') ? 1 : 0;
		}
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if ($this->Item_kit->get_custom_field($k) !== FALSE)
			{			
				if ($this->Item_kit->get_custom_field($k,'type') == 'checkbox')
				{
					$item_kit_data["custom_field_{$k}_value"] = $this->input->post("custom_field_{$k}_value");
				}
				elseif($this->Item_kit->get_custom_field($k,'type') == 'date')
				{
					$item_kit_data["custom_field_{$k}_value"] = $this->input->post("custom_field_{$k}_value") !== '' ? strtotime($this->input->post("custom_field_{$k}_value")) : NULL;
				}
				elseif(isset($_FILES["custom_field_{$k}_value"]['tmp_name']) && $_FILES["custom_field_{$k}_value"]['tmp_name'])
				{
					
					if ($this->Item_kit->get_custom_field($k,'type') == 'image')
					{
				    $this->load->library('image_lib');
				
						$allowed_extensions = array('png', 'jpg', 'jpeg', 'gif');
						$extension = strtolower(pathinfo($_FILES["custom_field_{$k}_value"]['name'], PATHINFO_EXTENSION));
				    if (in_array($extension, $allowed_extensions))
				    {
					    $config['image_library'] = 'gd2';
					    $config['source_image']	= $_FILES["custom_field_{$k}_value"]['tmp_name'];
					    $config['create_thumb'] = FALSE;
					    $config['maintain_ratio'] = TRUE;
					    $config['width']	 = 1200;
					    $config['height']	= 900;
							$this->image_lib->initialize($config);
					    $this->image_lib->resize();
				   	 	$this->load->model('Appfile');
					    $image_file_id = $this->Appfile->save($_FILES["custom_field_{$k}_value"]['name'], file_get_contents($_FILES["custom_field_{$k}_value"]['tmp_name']));
							$item_kit_data["custom_field_{$k}_value"] = $image_file_id;
						}
					}
					else
					{
			   	 	$this->load->model('Appfile');
				    $custom_file_id = $this->Appfile->save($_FILES["custom_field_{$k}_value"]['name'], file_get_contents($_FILES["custom_field_{$k}_value"]['tmp_name']));
						$item_kit_data["custom_field_{$k}_value"] = $custom_file_id;
						
					}
					
				}
				elseif($this->Item_kit->get_custom_field($k,'type') != 'image' && $this->Item_kit->get_custom_field($k,'type') != 'file')
				{
					$item_kit_data["custom_field_{$k}_value"] = $this->input->post("custom_field_{$k}_value");
				}
			}
		}
		
				
		if($this->Item_kit->save($item_kit_data,$item_kit_id))
		{
			
			
			if ($this->input->post('secondary_categories'))
			{
				foreach($this->input->post('secondary_categories') as $sec_category_id=>$category_id)
				{
					$this->Item_kit->save_secondory_category(isset($item_kit_data['item_kit_id']) ? $item_kit_data['item_kit_id'] : $item_kit_id,$category_id,$sec_category_id);
				}
			}
			
			if ($this->input->post('secondary_categories_to_delete'))
			{
				foreach($this->input->post('secondary_categories_to_delete') as $sec_category_id_to_delete)
				{
					$this->Item_kit->delete_secondory_category($sec_category_id_to_delete);
				}
			}
			
			
			$this->Tag->save_tags_for_item_kit(isset($item_kit_data['item_kit_id']) ? $item_kit_data['item_kit_id'] : $item_kit_id, $this->input->post('tags'));
			
			$modifier_ids = $this->input->post('modifiers') ? $this->input->post('modifiers') : array();
			$this->Item_modifier->item_kit_save_modifiers(isset($item_kit_data['item_kit_id']) ? $item_kit_data['item_kit_id'] : $item_kit_id,$modifier_ids);
			
			$success_message = '';
			//New item kit
			if($item_kit_id==-1)
			{
				$success_message = H(lang('item_kits_successful_adding').' '.$item_kit_data['name']);
				echo json_encode(array('success'=>true,'message'=>$success_message,'item_kit_id'=>$item_kit_data['item_kit_id'], 'redirect'=>$redirect, 'progression' => $progression, 'quick_edit'=> $quick_edit));
				$item_kit_id = $item_kit_data['item_kit_id'];
			}
			else //previous item
			{
				$success_message = H(lang('item_kits_successful_updating').' '.$item_kit_data['name']);
				$this->session->set_flashdata('manage_success_message', $success_message);
				echo json_encode(array('success'=>true,'message'=>$success_message,'item_kit_id'=>$item_kit_id, 'redirect'=>$redirect, 'progression' => $progression, 'quick_edit'=> $quick_edit));
			}
			
		}
		else//failure
		{
			echo json_encode(array('success'=>false,'message'=>H(lang('item_kits_error_adding_updating').' '.
			$item_kit_data['name']),'item_kit_id'=>-1));
		}

	}
	
	function delete()
	{
		$this->check_action_permission('delete');		
		$item_kits_to_delete=$this->input->post('ids');

		if($this->Item_kit->delete_list($item_kits_to_delete))
		{
			echo json_encode(array('success'=>true,'message'=>lang('item_kits_successful_deleted').' '.
			count($item_kits_to_delete).' '.lang('item_kits_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('item_kits_cannot_be_deleted')));
		}
	}
	
	function undelete()
	{
		$this->check_action_permission('delete');		
		$item_kits_to_undelete=$this->input->post('ids');

		if($this->Item_kit->undelete_list($item_kits_to_undelete))
		{
			echo json_encode(array('success'=>true,'message'=>lang('item_kits_successful_undeleted').' '.
			count($item_kits_to_undelete).' '.lang('item_kits_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('item_kits_cannot_be_undeleted')));
		}
	}

	function clear_state()
	{
		$params = $this->session->userdata('item_kits_search_data');
		$this->session->set_userdata('item_kits_search_data', array('offset' => 0, 'order_col' => 'item_kit_id', 'order_dir' => 'desc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => $params['deleted']));
		redirect('item_kits');
	}

	function generate_barcodes($item_kit_ids, $skip=0)
	{
		$this->load->helper('item_kits');
		$this->load->model('Item_kit_location');
		$this->load->model('Item_kit_taxes');
		$this->load->model('Item_location');
		$this->load->model('Item_kit_location_taxes');
		$this->load->model('Item_kit_taxes_finder');
			
		$data['items'] = get_item_kits_barcode_data($item_kit_ids);
		$data['scale'] = 1;
		$data['skip'] = $skip;
		$this->load->view("barcode_sheet", $data);
	}
	
	function generate_barcode_labels($item_kit_ids)
	{
		$this->load->helper('item_kits');
		$this->load->model('Item_kit_location');
		$this->load->model('Item_kit_taxes');
		$this->load->model('Item_location');
		$this->load->model('Item_kit_location_taxes');
		$this->load->model('Item_kit_taxes_finder');
		$data['items'] = get_item_kits_barcode_data($item_kit_ids);
		$data['scale'] = 1;
		$data['excel_url'] = site_url('item_kits/generate_barcode_labels_excel/'.($item_kit_ids ? $item_kit_ids : '-1'));
		$this->load->view("barcode_labels", $data);
	}
	
	function generate_barcode_labels_excel($item_kit_ids)
	{
		if ($this->input->post('item_kit_id'))
		{
			$item_kit_id = $this->input->post('item_kit_id');
			$quantity = $this->input->post('item_kits_number_of_barcodes');
	
			$item_kit_ids_to_make = array_fill(0, $quantity, $item_kit_id);
			$item_kit_ids = implode('~', $item_kit_ids_to_make);
				
			$skip = 0;
		}
		
		$this->load->model('item_kit_taxes');
		$this->load->model('item_kit_location');
		$this->load->model('item_kit_location_taxes');
		$this->load->model('item_kit_taxes_finder');
		
		
		$this->load->helper('item_kits');
		$data = get_item_kits_barcode_data($item_kit_ids);		
		
		$export_data[] = array(lang('common_item_kit_id'),lang('common_name'),lang('common_unit_price'));
		foreach($data as $row)
		{
			$data = trim(strip_tags($row['name']));
			$price = substr($data,0,strpos($data,' '));
			$name = str_replace($price.' ','',$data);
			$export_data[] = array($row['id'],$name,$price);
		}
		
		$this->load->helper('spreadsheet');
		array_to_spreadsheet($export_data,'barcode_export.'.($this->config->item('spreadsheet_format') == 'XLSX' ? 'xlsx' : 'csv'));
		
	}
	
	
	function tags()
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		$suggestions = $this->Tag->get_tag_suggestions($this->input->get('term'),25);
		echo json_encode(H($suggestions));
	}
	
	function cleanup()
	{
		$this->Item_kit->cleanup();
		echo json_encode(array('success'=>true,'message'=>lang('item_kits_cleanup_sucessful')));
	}
	
	function get_item_info()
	{
		$this->load->model('Item');
		
		if (strpos($this->input->post('item_number'),'#')!== FALSE)
		{
		  $item_identifer_parts = explode('#', $this->input->post('item_number'));
			$item_id = $item_identifer_parts[0];
			$variation_id = $item_identifer_parts[1];
		}
		elseif(!$this->Item->exists(does_contain_only_digits($this->input->post('item_number')) ? (int)$this->input->post('item_number') : -1))	
		{
			$item_id = $this->Item->get_item_id($this->input->post('item_number'));
		}
		else
		{
			$item_id = (int)$this->input->post('item_number');
		}
		
		if ($item_id)
		{
			if (($item_identifer_parts = explode('#',$item_id)) !== false)
			{
				if (isset($item_identifer_parts[1]))
				{
					$item_id = $item_identifer_parts[0];
					$variation_id = $item_identifer_parts[1];
				}	
			}
			$item_info = $this->Item->get_info($item_id);
			
			if (isset($variation_id))
			{
				$this->load->model('Item_variations');
				$item_info->item_id .= '#'.$variation_id;
				$item_info->name.='- '.$this->Item_variations->get_variation_name($variation_id);
			}
			echo json_encode($item_info);
		}
		else
		{
			echo json_encode("");
		}
	}
	
	function save_column_prefs()
	{
		$this->load->model('Employee_appconfig');
		
		if ($this->input->post('columns'))
		{
			$this->Employee_appconfig->save('item_kit_column_prefs',serialize($this->input->post('columns')));
		}
		else
		{
			$this->Employee_appconfig->delete('item_kit_column_prefs');			
		}
	}
	
	
	function reload_table()
	{
		$this->lang->load('items');
		$config['base_url'] = site_url('item_kits/sorting');
		$config['per_page'] = $this->config->item('number_of_item_kits_per_page') ? (int)$this->config->item('number_of_item_kits_per_page') : 20; 
		$params = $this->session->userdata('item_kits_search_data') ? $this->session->userdata('item_kits_search_data') : array('offset' => 0, 'order_col' => 'item_kit_id', 'order_dir' => 'desc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => 0);

		$data['per_page'] = $config['per_page'];
		$data['search'] = $params['search'] ? $params['search'] : "";
		$data['category_id'] = $params['category_id'] ? $params['category_id'] : "";
		
		$data['fields'] = $params['fields'] ? $params['fields'] : "all";
		
		if ($data['search'] || $data['category_id'])
		{
			$config['total_rows'] = $this->Item->search_count_all($data['search'],$params['deleted'], $data['category_id'],10000, $data['fields']);
			$table_data = $this->Item_kit->search($data['search'],$params['deleted'],$data['category_id'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir'], $data['fields']);
		}
		else
		{
			$config['total_rows'] = $this->Item_kit->count_all($params['deleted']);
			$table_data = $this->Item_kit->get_all($params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		
		echo get_item_kits_manage_table($table_data,$this);
	}
	
	function toggle_show_deleted($deleted=0)
	{
		$this->check_action_permission('search');
		
		$params = $this->session->userdata('item_kits_search_data') ? $this->session->userdata('item_kits_search_data') : array('offset' => 0, 'order_col' => 'item_kit_id', 'order_dir' => 'desc', 'search' => FALSE, 'category_id' => FALSE, 'fields' => 'all','deleted' => 0);
		$params['deleted'] = $deleted;
		$params['offset'] = 0;
		
		$this->session->set_userdata("item_kits_search_data",$params);
	}
	
	function delete_custom_field_value($item_kit_id,$k)
	{
		$item_kit_info = $this->Item_kit->get_info($item_kit_id);
		$file_id = $item_kit_info->{"custom_field_{$k}_value"};
		$this->load->model('Appfile');
		$this->Appfile->delete($file_id);
		$item_kit_data = array();
		$item_kit_data["custom_field_{$k}_value"] = NULL;
		$this->Item_kit->save($item_kit_data,$item_kit_id);
	}
	
	function images($item_id=-1)
	{			
		$this->check_action_permission('add_update');
				
		$data = $this->_get_item_kit_data($item_id);
		
		$data['category'] = $this->Category->get_full_path($data['item_kit_info']->category_id);
		$data['redirect'] = $this->input->get('redirect');
		
		$progression = $this->input->get('progression');
		$quick_edit = $this->input->get('quick_edit');
		$data['progression'] = !empty($progression);
		$data['quick_edit'] = !empty($quick_edit);
		
		$this->load->view("item_kits/images",$data);
	}
	
	
	function save_images($item_kit_id=-1)
	{
		$this->check_action_permission('add_update');
		
		$redirect= $this->input->post('redirect');
		$progression_post = $this->input->post('progression');
		$quick_edit_post = $this->input->post('quick_edit_post');
		$progression= !empty($progression_post) ? 1 : null;
		$quick_edit= !empty($quick_edit_post) ? 1 : null;
		
		$this->load->model('Item_kit');
		
		$item_kit_info = $this->Item_kit->get_info($item_kit_id);
		
		//Delete Image
		if($this->input->post('del_images') && $item_kit_id != -1)
		{
			foreach(array_keys($this->input->post('del_images')) as $image_id)
			{
				$this->Item_kit->delete_image($image_id);
			}
		}
		
    $this->load->library('image_lib');
		
		if (isset($_FILES['image_files']))
		{
			$ignore = $this->input->post('ignore');
			
			for($k=0; $k<count($_FILES['image_files']['name']); $k++)
			{
				if(!empty($ignore) && in_array($k, $ignore))
				{
					continue;
				}
				
				$allowed_extensions = array('png', 'jpg', 'jpeg', 'gif');
				$extension = strtolower(pathinfo($_FILES['image_files']['name'][$k], PATHINFO_EXTENSION));
		    if (in_array($extension, $allowed_extensions))
		    {
					
			    $config['image_library'] = 'gd2';
			    $config['source_image']	= $_FILES['image_files']['tmp_name'][$k];
			    $config['create_thumb'] = FALSE;
			    $config['maintain_ratio'] = TRUE;
			    $config['width']	 = 1200;
			    $config['height']	= 900;
					$this->image_lib->initialize($config);
			    $this->image_lib->resize();
		   	 	$this->load->model('Appfile');
			    $image_file_id = $this->Appfile->save($_FILES['image_files']['name'][$k], file_get_contents($_FILES['image_files']['tmp_name'][$k]));
		  		$this->Item_kit->add_image($item_kit_id, $image_file_id);
					$last_image_id = $image_file_id;
				}
			}
		}
		
		$titles = $this->input->post('titles');
		$alt_texts = $this->input->post('alt_texts');
		$variations = $this->input->post('variations');
		$main_images = $this->input->post('main_image');
		
		if ($titles)
		{
			foreach(array_keys($titles) as $image_id)
			{
				$title = $titles[$image_id];
				$alt_text = $alt_texts[$image_id];
				$variation = $variations[$image_id] ? $variations[$image_id] : NULL;
				$main_image = isset($main_images[$image_id]) ? TRUE : FALSE;
  			$this->Item_kit->save_image_metadata($image_id, $title, $alt_text, $variation);
				
				if ($main_image)
				{
					$item_kit_image_data = array('main_image_id' => $image_id);
					
					$this->Item_kit->save($item_kit_image_data,$item_kit_id);
				}
			}
		}
		else
		{
			if ($last_image_id)
			{
				$item_kit_image_data = array('main_image_id' => $last_image_id);
				
				$this->Item_kit->save($item_kit_image_data,$item_kit_id);
			}
		}
				
		$success_message = lang('common_items_successful_updating');
		echo json_encode(array('reload' => isset($_FILES['image_files']) || $this->input->post('del_images'),'success'=>true,'message'=>$success_message,'item_kit_id'=>$item_kit_id,'redirect' => $redirect, 'progression' => $progression));
		
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
	
}
?>
