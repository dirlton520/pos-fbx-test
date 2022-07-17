<?php
require_once ("Secure_area.php");

class Work_orders extends Secure_area
{
	function __construct()
	{
		parent::__construct('work_orders');	
		$this->load->model('Work_order');
		$this->load->model('Employee');
		$this->load->model('Sale');
		$this->load->model('Customer');
		$this->load->model('Category');
		$this->load->model('Appfile');
		$this->load->model('Location');
		$this->load->model('Tier');
		$this->load->model('Item');
		$this->load->model('Item_kit');
		$this->load->model('Item_kit_taxes');
		$this->load->model('Item_location_taxes');
		$this->load->model('Item_taxes');
		$this->load->model('Item_taxes_finder');
		$this->load->model('Item_kit_taxes_finder');
		$this->load->model('Item_location');
		$this->load->model('Item_variations');
		$this->load->model('Item_serial_number');
		$this->load->model('Manufacturer');
		$this->load->model('Item_attribute_value');
		$this->load->model('Item_attribute');
		$this->load->model('Employee_appconfig');
		$this->load->model('Sale_types');

		$this->load->helper('work_order');

		$this->lang->load('work_orders');
		$this->lang->load('module');
		$this->lang->load('sales');	
		$this->load->helper('text');

	}

	function index($offset=0, $open_new=0)
	{
		$this->check_action_permission('search');
		$data['open_new'] = $open_new;
		
		$params = $this->session->userdata('work_orders_search_data') ? $this->session->userdata('work_orders_search_data') : array('offset' => 0, 'order_col' => 'id', 'order_dir' => 'desc', 'search' => FALSE,'deleted' => 0,'status' => '','technician' => '','hide_completed_work_orders' => $this->Employee_appconfig->get('hide_completed_work_orders'));
		
		if ($offset != $params['offset'])
		{
		   redirect('work_orders/index/'.$params['offset']);
		}
		
		$config['base_url'] = site_url('work_orders/sorting');
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		
		$data['controller_name']=strtolower(get_class());
		$data['per_page'] = $config['per_page'];
		
		$data['search'] = $params['search'] ? $params['search'] : "";
		$data['deleted'] = $params['deleted'];
		$data['status'] = $params['status'] ? $params['status'] : "";
		$data['technician'] = $params['technician'] ? $params['technician'] : "";
		$data['hide_completed_work_orders'] = $params['hide_completed_work_orders'] ? $params['hide_completed_work_orders'] : "";

		if ($data['search'] || $data['status'] || $data['technician'] || $data['hide_completed_work_orders'])
		{
			$config['total_rows'] = $this->Work_order->search_count_all($data['search'],$params['deleted'],10000,$data['status'],$data['technician'],$data['hide_completed_work_orders']);
			$table_data = $this->Work_order->search($data['search'],$params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir'],$data['status'],$data['technician'],$data['hide_completed_work_orders']);
		}
		else
		{	
			$config['total_rows'] = $this->Work_order->count_all($params['deleted']);
			$table_data = $this->Work_order->get_all($params['deleted'],$data['per_page'], $params['offset'],$params['order_col'],$params['order_dir']);
		}
				
		$data['total_rows'] = $config['total_rows'];
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		
		$data['pagination'] = $this->pagination->create_links();
		$data['order_col'] = $params['order_col'];
		$data['order_dir'] = $params['order_dir'];
		$data['manage_table']= $dataset = get_work_orders_manage_table($table_data,$this);
		
		$data['default_columns'] = $this->Work_order->get_default_columns();
		$data['selected_columns'] = $this->Employee->get_work_order_columns_to_display();
		$data['all_columns'] = array_merge($data['selected_columns'],$this->Work_order->get_displayable_columns());
		
		$change_status_array = array(''=>lang('work_orders_change_status'));
		$search_status_array = array(''=>lang('common_all'));

		$all_statuses = $this->Work_order->get_all_statuses();
		foreach($all_statuses as $id => $row)
		{
			$change_status_array[$id] = $row['name'];
			$search_status_array[$id] = $row['name'];
		}
		
		$data['change_status_array'] = $change_status_array;
		$data['search_status_array'] = $search_status_array;
		
		$employees = array('' => lang('common_all'));

		foreach($this->Employee->get_all(0,10000,0,'first_name')->result() as $employee)
		{
			$employees[$employee->person_id] = $employee->first_name .' '.$employee->last_name;
		}
		$data['employees'] = $employees;

		$data['status_boxes'] = $this->Work_order->get_work_orders_by_status();
		
		$data['item_id_for_new'] = $this->session->userdata('item_id_for_new') ? $this->session->userdata('item_id_for_new') : '';
		$data['item_serial_number_for_new'] = $this->session->userdata('item_serial_number_for_new') ? $this->session->userdata('item_serial_number_for_new') : '';
		$data['customer_id_for_new'] = $this->session->userdata('customer_id_for_new') ? $this->session->userdata('customer_id_for_new') : '';
		
		if($data['item_id_for_new']){
			$item_info = $this->Item->get_info($data['item_id_for_new']);
			$data['item_info'] = $item_info;
			$data['category_full_path'] = $this->Category->get_full_path($item_info->category_id);
		}

		if($data['customer_id_for_new']){
			$data['customer_info'] = $this->Customer->get_info($data['customer_id_for_new']);
		}
		
		$this->load->view('work_orders/manage', $data);
	}
	
	function clear_state()
	{
		$params = array('offset' => 0, 'order_col' => 'id', 'order_dir' => 'desc', 'search' => FALSE,'deleted' => 0,'status' => '','technician' => '','hide_completed_work_orders' => $this->Employee_appconfig->get('hide_completed_work_orders'));
		$this->session->set_userdata('work_orders_search_data', $params);
		redirect('work_orders');
	}
	
	
	function search()
	{
		$this->check_action_permission('search');
		$params = $this->session->userdata('work_orders_search_data');
		
		$search=$this->input->post('search') ? $this->input->post('search') : "";
		$status = $this->input->post('status');
		$technician = $this->input->post('technician');
		$hide_completed_work_orders = $this->input->post('hide_completed_work_orders') ? 1 : 0;
		$per_page=$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : 'id';
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): 'desc';
		$deleted = $this->input->post('deleted') ? $this->input->post('deleted'): (isset($params['deleted']) && $params['deleted'] ? 1 : 0);
		
		$work_orders_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search, 'deleted' => $deleted,'status'=>$status,'technician'=>$technician,'hide_completed_work_orders'=>$hide_completed_work_orders);
		$this->session->set_userdata("work_orders_search_data",$work_orders_search_data);
		
		if ($search || $status || $technician || $hide_completed_work_orders)
		{
			$config['total_rows'] = $this->Work_order->search_count_all($search,$deleted,10000,$status,$technician,$hide_completed_work_orders);
			$table_data = $this->Work_order->search($search,$deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'id' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'desc',$status,$technician,$hide_completed_work_orders);
		}
		else
		{
			$config['total_rows'] = $this->Work_order->count_all($deleted);
			$table_data = $this->Work_order->get_all($deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'id' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'desc');
		}
		
		$config['base_url'] = site_url('work_orders/sorting');
		
		$config['per_page'] = $per_page;
		
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['manage_table']=get_work_orders_manage_table_data_rows($table_data,$this);

		$this->Employee_appconfig->save('hide_completed_work_orders',$hide_completed_work_orders);

		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination'],'total_rows' => $config['total_rows']));
	}
	
	function sorting()
	{
		$this->check_action_permission('search');
		$params = $this->session->userdata('work_orders_search_data') ? $this->session->userdata('work_orders_search_data') : array('order_col' => 'id', 'order_dir' => 'desc','deleted' => 0,'status' => '','technician' => '','hide_completed_work_orders' => $this->Employee_appconfig->get('hide_completed_work_orders'));
		$search = $this->input->post('search') ? $this->input->post('search') : "";
		$deleted = $this->input->post('deleted') ? $this->input->post('deleted') : $params['deleted'];
		$status = $params['status'] ? $params['status'] : "";
		$technician = $params['technician'] ? $params['technician'] : "";
		$hide_completed_work_orders = $params['hide_completed_work_orders'] ? $params['hide_completed_work_orders'] : "";

		$per_page = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : $params['order_col'];
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): $params['order_dir'];
		
		$item_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search,'deleted' => $deleted,'status' => $status,'technician'=>$technician ,'hide_completed_work_orders'=>$hide_completed_work_orders);
		
		$this->session->set_userdata("work_orders_search_data",$item_search_data);
		
		if ($search || $status || $technician || $hide_completed_work_orders)
		{
			$config['total_rows'] = $this->Work_order->search_count_all($search,$deleted,10000,$status,$technician,$hide_completed_work_orders);
			$table_data = $this->Work_order->search($search, $deleted,$per_page, $this->input->post('offset') ? $this->input->post('offset') : 0, $order_col, $order_dir,$status,$technician,$hide_completed_work_orders);
		}
		else
		{
			$config['total_rows'] = $this->Work_order->count_all($deleted);
			$table_data = $this->Work_order->get_all($deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $order_col,$order_dir);
		}
		
		$config['base_url'] = site_url('work_orders/sorting');
		$config['per_page'] = $per_page; 
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		
		$this->load->model('Employee_appconfig');
		$data['default_columns'] = $this->Work_order->get_default_columns();
		$data['manage_table'] = get_work_orders_manage_table_data_rows($table_data, $this);
		
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination'], 'total_rows' => $config['total_rows']));
	}	

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$this->check_action_permission('search');
		//allow parallel searchs to improve performance.
		session_write_close();
		$params = $this->session->userdata('work_orders_search_data') ? $this->session->userdata('work_orders_search_data') : array('deleted' => 0);
		$suggestions = $this->Work_order->get_search_suggestions($this->input->get('term'),$params['deleted'],100);
		echo json_encode($suggestions);
	}
	
	/*
	Loads the Work order edit form
	*/
	function view($work_order_id=-1,$redirect_code=0)
	{
		$this->load->model('Module_action');
		$this->check_action_permission('edit');
		
		$data = $this->_get_work_order_data($work_order_id);
		$data['redirect']= $redirect_code;
		$data['work_order_id'] = $work_order_id;

		$data['redirect_code']=$redirect_code;
		$data['files'] = $this->Work_order->get_files($work_order_id)->result();
		$data['controller_name']=strtolower(get_class());
		$this->load->view('work_orders/form', $data);
	}

	private function _get_work_order_data($work_order_id)
	{
		$data = array();
		$data['work_order_info_object'] = $this->Work_order->get_info($work_order_id)->row();

		$work_order_info = $this->Work_order->get_info($work_order_id)->row_array();

		$data['work_order_info'] = $work_order_info;
		
		$change_status_array = array(''=>lang('work_orders_change_status'));

		foreach($this->Work_order->get_all_statuses() as $id => $row)
		{
			$change_status_array[$id] = $row['name'];
		}

		unset($change_status_array[$work_order_info['status']]);
		$data['change_status_array'] = $change_status_array;

		$data['customer_info'] = $this->Work_order->get_customer_info($work_order_id);
		$data['item_being_repaired_info'] = $this->Work_order->get_item_being_repaired_info($work_order_id);
		$data['notes'] = $this->Work_order->get_sales_items_notes($work_order_id);
		$first_line_note = $this->Work_order->get_first_line_note($work_order_id);

		$data['work_order_images'] = $work_order_info['images'] && unserialize($work_order_info['images']) ? unserialize($work_order_info['images']) : array();
		
		$data['first_line_note'] = $first_line_note;

		$data['work_order_items'] = $this->Work_order->get_work_order_items($work_order_id);
		
		$employees = array('' => lang('common_none'));

		foreach($this->Employee->get_all()->result() as $employee)
		{
			$employees[$employee->person_id] = $employee->first_name .' '.$employee->last_name;
		}
		$data['employees'] = $employees;

		return $data;
	}
	
	function save($work_order_id=-1)
	{
		$this->check_action_permission('edit');

		$work_order_data = array();

		$work_order_data['estimated_repair_date'] = $this->input->post('estimated_repair_date') ? date('Y-m-d H:i:s', strtotime($this->input->post('estimated_repair_date'))) : NULL;
		$work_order_data['estimated_parts'] = $this->input->post('estimated_parts') ? $this->input->post('estimated_parts') : NULL;
		$work_order_data['estimated_labor'] = $this->input->post('estimated_labor') ? $this->input->post('estimated_labor') : NULL;
		$work_order_data['warranty'] = $this->input->post('warranty') ? 1 : 0;
		
		for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++)
		{
			if ($this->Work_order->get_custom_field($k) !== FALSE)
			{
				$work_order_data["custom_field_{$k}_value"] = $this->input->post("custom_field_{$k}_value");
			
				if ($this->Work_order->get_custom_field($k,'type') == 'checkbox')
				{
					$work_order_data["custom_field_{$k}_value"] = $this->input->post("custom_field_{$k}_value");
				}
				elseif($this->Work_order->get_custom_field($k,'type') == 'date')
				{
					$work_order_data["custom_field_{$k}_value"] = strtotime($this->input->post("custom_field_{$k}_value"));
				}
				else
				{
					$work_order_data["custom_field_{$k}_value"] = $this->input->post("custom_field_{$k}_value");
				}
			}
		}

		$this->Work_order->save( $work_order_data, $work_order_id );

		if (isset($_FILES['files']))
		{	$this->load->model('Appfile');
			for($k=0; $k<count($_FILES['files']['name']); $k++)
			{
				if($_FILES['files']['tmp_name'][$k])
				{
					$file_id = $this->Appfile->save($_FILES['files']['name'][$k], file_get_contents($_FILES['files']['tmp_name'][$k]));
					$this->Work_order->add_file($work_order_id==-1 ? $work_order_data['id'] : $work_order_id, $file_id);
				}
			}
		}


		echo json_encode(array('success'=>true));
		
	}
	
	function delete()
	{
		$this->check_action_permission('delete');
		$work_orders_to_delete=$this->input->post('ids');
		
		if($this->Work_order->delete_list($work_orders_to_delete))
		{
			echo json_encode(array('success'=>true,'message'=>lang('work_orders_successful_deleted').' '.
			count($work_orders_to_delete).' '.lang('work_orders_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('work_orders_cannot_be_deleted')));
		}
	}
	
	function undelete()
	{
		$this->check_action_permission('delete');
		$work_orders_to_delete=$this->input->post('ids');
		
		if($this->Work_order->undelete_list($work_orders_to_delete))
		{
			echo json_encode(array('success'=>true,'message'=>lang('work_orders_successful_undeleted').' '.
			count($work_orders_to_delete).' '.lang('work_orders_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('work_orders_cannot_be_undeleted')));
		}
	}
	
	
	function save_column_prefs()
	{
		$this->load->model('Employee_appconfig');
		
		if ($this->input->post('columns'))
		{
			$this->Employee_appconfig->save('work_orders_column_prefs',serialize($this->input->post('columns')));
		}
		else
		{
			$this->Employee_appconfig->delete('work_orders_column_prefs');			
		}
	}
	
	function reload_work_order_table()
	{
		
		$config['base_url'] = site_url('work_orders/sorting');
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		$params = $this->session->userdata('work_orders_search_data') ? $this->session->userdata('work_orders_search_data') : array('offset' => 0, 'order_col' => 'id', 'order_dir' => 'desc', 'search' => FALSE,'deleted' => 0,'status' => '','technician' => '','hide_completed_work_orders' => $this->Employee_appconfig->get('hide_completed_work_orders'));

		$data['per_page'] = $config['per_page'];
		$data['search'] = $params['search'] ? $params['search'] : "";		
		$data['status'] = $params['status'] ? $params['status'] : "";
		$data['technician'] = $params['technician'] ? $params['technician'] : "";
		$data['hide_completed_work_orders'] = $params['hide_completed_work_orders'] ? $params['hide_completed_work_orders'] : "";

		if ($data['search'] || $data['status'] || $data['technician'] || $data['hide_completed_work_orders'])
		{
			$config['total_rows'] = $this->Work_order->search_count_all($data['search'],$params['deleted'],10000,$data['status'],$data['technician'],$data['hide_completed_work_orders']);
			$table_data = $this->Work_order->search($data['search'],$params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir'],$data['status'],$data['technician'],$data['hide_completed_work_orders']);
		}
		else
		{
			$config['total_rows'] = $this->Work_order->count_all($params['deleted']);
			$table_data = $this->Work_order->get_all($params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		
		echo get_work_orders_manage_table($table_data,$this);
	}
			 
	function toggle_show_deleted($deleted=0)
	{
		$this->check_action_permission('search');
		$params = $this->session->userdata('work_orders_search_data') ? $this->session->userdata('work_orders_search_data') :array('offset' => 0, 'order_col' => 'id', 'order_dir' => 'desc', 'search' => FALSE,'deleted' => 0);
		$params['deleted'] = $deleted;
		$params['offset'] = 0;
		
		$this->session->set_userdata("work_orders_search_data",$params);
	}

	function custom_fields()
	{
		$this->lang->load('config');
		$fields_prefs = $this->config->item('work_order_custom_field_prefs') ? unserialize($this->config->item('work_order_custom_field_prefs')) : array();
		$data = array_merge(array('controller_name' => strtolower(get_class())),$fields_prefs);
		$locations_list = $this->Location->get_all()->result();
		$data['locations'] = $locations_list;
		$this->load->view('custom_fields',$data);
	}
	
	function save_custom_fields()
	{
		$this->load->model('Appconfig');
		$this->Appconfig->save('work_order_custom_field_prefs',serialize($this->input->post()));
	}

	function change_status()
	{
		$work_order_ids=$this->input->post('work_order_ids');
		$status = $this->input->post('status');
		$status_changed_work_order_ids = array();
		
		foreach($work_order_ids as $work_order_id)
		{
			$work_order_info = $this->Work_order->get_info($work_order_id)->row();
			if($work_order_info->status != $status){
				$status_changed_work_order_ids[] = $work_order_id;
				$this->Work_order->change_status($work_order_id,$status);
			}
		}
		
		if(count($status_changed_work_order_ids) > 0){
			foreach($status_changed_work_order_ids as $work_order_id)
			{
				$work_order_info = $this->Work_order->get_info($work_order_id)->row();
				$work_order_status_info = $this->Work_order->get_status_info($status);
				if($work_order_status_info->notify_by_email || $work_order_status_info->notify_by_sms){
					$this->load->model('Common');
					$company_name = $this->config->item('company');
					$message = sprintf($this->lang->line('work_orders_work_order_status_update_message'), $company_name,$work_order_id,$work_order_status_info->description?$work_order_status_info->description:$work_order_status_info->name);

					if($work_order_status_info->notify_by_email){
						$customer_email = $this->Customer->get_info($work_order_info->customer_id)->email;
						if($customer_email){
							$subject = lang('work_orders_work_order_status_update');
		
							$this->Common->send_email($customer_email,$subject,$message);
						}
					}
		
					if($work_order_status_info->notify_by_sms){
						$customer_phone_number = $this->Customer->get_info($work_order_info->customer_id)->phone_number;
						if($customer_phone_number){
							$this->Common->send_sms($customer_phone_number,$message);
						}
					}
				}
			}
		}
		
		echo json_encode(array('success'=>true,'message'=>lang('work_orders_successful_changed')));
	}

	function print_work_order($work_order_ids)
	{	
		$result = array();

		$work_order_ids = explode('~', $work_order_ids);
		foreach($work_order_ids as $work_order_id)
		{

			$sale_id = $this->Work_order->get_info($work_order_id)->row()->sale_id;

			$sale_info = $this->Sale->get_info($sale_id)->row_array();

			$tier_id = $sale_info['tier_id'];
			$tier_info = $this->Tier->get_info($tier_id);
			$data['tier'] = $tier_info->name;
			$data['work_order_info'] = $this->Work_order->get_info($work_order_id)->row();
			
			$data['register_name'] = $this->Register->get_register_name($sale_info['register_id']);
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
			else{
				$data['customer']='no_customer!';
				$data['customer_company']= '';
				$data['customer_address_1'] = '';
				$data['customer_address_2'] = '';
				$data['customer_city'] = '';
				$data['customer_state'] = '';
				$data['customer_zip'] = '';
				$data['customer_country'] = '';
				$data['customer_phone'] = '';
				$data['customer_email'] = '';
			}
			
			$data['sale_id']=$this->config->item('sale_prefix').' '.$sale_id;
			$data['sale_id_raw']=$sale_id;
			$data['comment']=$sale_info['comment'];
			$data['show_comment_on_receipt']=$sale_info['show_comment_on_receipt'];
			$data['sales_items'] = $this->Sale->get_sale_items_ordered_by_name($sale_id)->result_array();
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

			$result[] = $data;
		}
		
		$datas['datas'] = $result;
		$datas['sale_type'] = lang('common_workorder');
		
		$this->load->view("work_orders/print_work_order",$datas);
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

	function print_service_tag($work_order_ids)
	{
		$this->load->model('Item_taxes');
		$this->load->model('Item_location');
		$this->load->model('Item_location_taxes');
		$this->load->model('Item_taxes_finder');
		$this->load->helper('items');
		
		$customers = array();
		$items_barcodes = array();

		foreach(explode('~',$work_order_ids) as $work_order_id){
			$item_ids = array();
			$customer_id = $this->Work_order->get_info($work_order_id)->row()->customer_id;
			$customer_info = $this->Customer->get_info($customer_id);
			
			$customer_name = $customer_info->first_name.' '.$customer_info->last_name;
			$customers[count($item_ids)] = array(
				'work_order_id' => $work_order_id,
				'customer_name' => $customer_name
			);

			$sale_id = $this->Work_order->get_info($work_order_id)->row()->sale_id;
			$barcode = lang('common_sale_id').' '.$sale_id;

			foreach($this->Sale->get_sale_items($sale_id)->result() as $item)
			{
				$item_ids[] = $item->item_id;
			}

			if (!empty($item_ids))
			{
				$items_barcodes = array_merge($items_barcodes, get_items_barcode_data(implode('~',$item_ids), $barcode));
			}

		}
		
		$data = array();
				
		$data['customers'] = $customers;
		$data['items'] = $items_barcodes;
		$data['selected_ids'] = $work_order_ids;
		$data['excel_url'] = site_url('work_orders/print_service_tag_excel/'.$work_order_ids );
		$this->load->view("barcode_labels", $data);
	}

	function get_items_raw_print_service_tag($work_order_ids)
	{
		
		$this->load->model('Item_variations');

		$result = array();
		$item_ids = array();

		foreach(explode('~',$work_order_ids) as $work_order_id){
			$data = $this->Work_order->get_raw_print_data($work_order_id);
			$result = array_merge($result,$data);
		}
		
		return $result;
	}

	function raw_print_service_tag($work_order_ids)
	{				
		$this->load->model('Label');

		$data['datas'] = $this->get_items_raw_print_service_tag($work_order_ids);
		$data['selected_ids'] = $work_order_ids;		
		
		$data['label_name'] = $this->Label->get_all();
		$data['raw_is'] = 'work_orders';

		$this->load->model('Employee_appconfig');
		$data['saved_label'] = $this->Employee_appconfig->get('work_orders_label') ? unserialize($this->Employee_appconfig->get('work_orders_label')) : array();


		$this->load->view("raw_print", $data);
	}

	
	function print_service_tag_excel($work_order_ids)
	{

		$this->load->helper('items');
		$export_data[] = array(lang('common_sale_id'), lang('common_item_number'),lang('common_name'), lang('common_description'),lang('common_unit_price'));

		foreach(explode('~',$work_order_ids) as $work_order_id){
			$item_ids = array();
			$items_barcodes = array();

			$sale_id = $this->Work_order->get_info($work_order_id)->row()->sale_id;
			$barcode = lang('common_sale_id').' '.$sale_id;

			foreach($this->Sale->get_sale_items($sale_id)->result() as $item){
				$item_ids[] = $item->item_id;
			}

			if (!empty($item_ids)){
				$items_barcodes = get_items_barcode_data(implode('~',$item_ids));
			}

			foreach($items_barcodes as $row){
				$data = trim(strip_tags($row['name']));
				$price = substr($data,0,strpos($data,' '));
				$name = str_replace($price.' ','',$data);
				$description = $row['description'];
				$export_data[] = array($barcode, $row['id'], $name, $description, $price);
			}
		}
		
		$this->load->helper('spreadsheet');
		array_to_spreadsheet($export_data,'barcode_export.'.($this->config->item('spreadsheet_format') == 'XLSX' ? 'xlsx' : 'csv'));
	}

	function _excel_get_header_row()
	{
		$return = array(lang('common_sale_id'),lang('work_orders_date'),lang('common_status'),lang('common_first_name'),lang('common_last_name'),lang('common_address'),lang('common_city'),lang('common_state'),lang('common_zip'),lang('common_email'),lang('common_phone_number'),lang('work_orders_work_order_id'));
		return $return;
	}

	function excel_export_selected_rows($ids) {
		ini_set('memory_limit','1024M');
		set_time_limit(0);
		ini_set('max_input_time','-1');

		$this->load->helper('report');
		$rows = array();
		$header_row = $this->_excel_get_header_row();
		$rows[] = $header_row;
		
		$ids = explode('~', $ids);
		foreach ($ids as $id)
		{
			$r = $this->Work_order->get_by_id($id);

			$row = array(
				$r->sale_id,
				date_time_to_date($r->sale_time),
				work_order_status($r->status),
				$r->first_name,
				$r->last_name,
				$r->full_address,
				$r->city,
				$r->state,
				$r->zip,
				$r->email,
				$r->phone_number,
				$r->id,
			);
			
			$rows[] = $row;
		}
		$this->load->helper('spreadsheet');
		array_to_spreadsheet($rows,'work_orders_export.'.($this->config->item('spreadsheet_format') == 'XLSX' ? 'xlsx' : 'csv'));
	}

	function save_repaired_item_notes()
	{		
		$data = array();
		
		$line = 0;
		$item_id_being_repaired = $this->input->post('item_id_being_repaired');
		$sale_id = $this->input->post('sale_id');
		$sale_item_note = $this->input->post('sale_item_note');
		$sale_item_detailed_notes = $this->input->post('sale_item_detailed_notes');
		$sale_item_note_internal = $this->input->post('sale_item_note_internal') ? 1 : 0;

		$note_id = $this->input->post('note_id');

		$employee_id=$this->Employee->get_logged_in_employee_info()->person_id;

		$sales_items_notes_data = array
		(
			'sale_id'=>$sale_id,
			'item_id'=>$item_id_being_repaired,
			'line'=>$line,
			'note'=>$sale_item_note,
			'detailed_notes'=>$sale_item_detailed_notes,
			'internal'=>$sale_item_note_internal,
			'employee_id'=>$employee_id,
			'images'=>serialize(array()),
		);
		
		$this->Sale->save_sales_items_notes_data($sales_items_notes_data,$note_id);
	}
	
	function workorder_images_upload(){
		$work_order_id = $this->input->post('work_order_id');
		$work_order_info = $this->Work_order->get_info($work_order_id)->row();

		$exists_images = $work_order_info->images ? unserialize($work_order_info->images) : array();
		$new_images = array();
		
		foreach($_FILES['file']['tmp_name'] as $key => $value) {
			$tempFile = $_FILES['file']['tmp_name'][$key];
			$fileName =  $_FILES['file']['name'][$key];
		    $image_file_id = $this->Appfile->save($fileName, file_get_contents($tempFile));
			$new_images[] = $image_file_id;
		}

		$images = array_merge($exists_images, $new_images);

		$images_data = array(
			'images'=>serialize($images),
		);
		
		$this->Work_order->save($images_data,$work_order_id);
	}

	function delete_work_order_image()
	{
		$work_order_id = $this->input->post('work_order_id');
		$image_index = $this->input->post('image_index');
		
		$work_order_info = $this->Work_order->get_info($work_order_id)->row();
		$images = $work_order_info->images ? unserialize($work_order_info->images) : array();
		
		$this->Appfile->delete($images[$image_index]);
		unset($images[$image_index]);
		$images_data = array(
			'images'=>serialize(array_values($images)),
		);
		
		$this->Work_order->save($images_data,$work_order_id);

		echo json_encode(array('success'=>true,'message'=>lang('work_orders_successful_deleted')));
	}

	function item_search()
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		if(!$this->config->item('speed_up_search_queries'))
		{
			$suggestions = $this->Item->get_item_search_suggestions($this->input->get('term'),0,'unit_price',100,'sales');
			$suggestions = array_merge($suggestions, $this->Item_kit->get_item_kit_search_suggestions_sales_recv($this->input->get('term'),0,'unit_price', 100));
		}
		else
		{
			$suggestions = $this->Item->get_item_search_suggestions_without_variations($this->input->get('term'),0,100,'unit_price');
			$suggestions = array_merge($suggestions, $this->Item_kit->get_item_kit_search_suggestions_sales_recv($this->input->get('term'),0,'unit_price', 100));

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
		
		//Lookup by item id
		if ($item_id = $this->Item->lookup_item_id($this->input->get('term'),array('item_number','item_variation_item_number','product_id','additional_item_numbers','serial_numbers')))
		{
			$item_info = $this->Item->get_info($item_id);
			$suggestions[]=array('value'=> $item_id, 'label' => $item_info->name, 'image' =>  $item_info->main_image_id ?  app_file_url($item_info->main_image_id) : base_url()."assets/img/item.png", 'subtitle' => '');		
		}
		
		echo json_encode(H($suggestions));
	}

	function delete_item($sale_id,$line){
		$this->Sale->delete_item($sale_id,$line);
		$this->Sale->update_sale_statistics($sale_id);
	}

	function add_sale_item(){
		$item_id = $this->input->post('item_id');
		$sale_id = $this->input->post('sale_id');

		$exist_sale_item = $this->Sale->get_sale_item($sale_id,$item_id);
		if($exist_sale_item){
			$this->Sale->sale_item_quantity_update($sale_id,$item_id,$exist_sale_item->quantity_purchased+1);
		}
		else{
			if(!$this->Sale->add_sale_item($sale_id,$item_id)){
				echo json_encode(array('success'=>false,'message'=>lang('work_orders_unable_to_add_item')));
				return;
			}
		}
		echo json_encode(array('success'=>true));
	}

	function edit_sale_item_quantity($sale_id,$item_id,$item_variation_id=false){
		if($item_variation_id){
			$item_id = $item_id.'#'.$item_variation_id;
		}
		$quantity = $this->input->post("value");
		$this->Sale->sale_item_quantity_update($sale_id,$item_id,$quantity);
	}

	function edit_sale_item_unit_price($sale_id,$item_id,$item_variation_id=false){
		if($item_variation_id){
			$item_id = $item_id.'#'.$item_variation_id;
		}
		$unit_price = $this->input->post("value");
		$this->Sale->sale_item_unit_price_update($sale_id,$item_id,$unit_price);
	}

	function select_technician(){
		$work_order_id = $this->input->post('work_order_id');
		$employee_id = $this->input->post('employee_id');
		
		$data = array('employee_id'=>$employee_id);
		
		$this->Work_order->save($data,$work_order_id);

		$technician_info = $this->Employee->get_info($employee_id);

		if($this->config->item('notify_technician_via_email') || $this->config->item('notify_technician_via_sms')){
			$this->load->model('Common');

			if($this->config->item('notify_technician_via_email')){
				$technician_email = $this->Employee->get_info($employee_id)->email;
				if($technician_email){
					$subject = lang('work_orders_you_have_been_assigned_a_work_order');
					$message = lang('work_orders_you_have_been_assigned_work_order').': ';
					$message .= '<a href="'.site_url("work_orders/view/".$work_order_id).'" >'.$work_order_id.'</a>';

					$this->Common->send_email($technician_email,$subject,$message);
				}
			}

			if($this->config->item('notify_technician_via_sms')){
				$technician_phone_number = $technician_info->phone_number;
				if($technician_phone_number){
					$message = lang('work_orders_you_have_been_assigned_work_order').': '.$work_order_id."\n";
					$message .= site_url('work_orders/view/').$work_order_id;
					$this->Common->send_sms($technician_phone_number,$message);
				}
			}
		}
	}

	function remove_technician(){
		$work_order_id = $this->input->post('work_order_id');
		
		$data = array('employee_id'=>NULL);
		
		$this->Work_order->save($data,$work_order_id);

	}

	function manage_statuses()
	{
		$this->check_action_permission('manage_statuses');
		$statuses = $this->Work_order->get_all_statuses();
		$data = array('statuses' => $statuses, 'statuses_list' => $this->_statuses_list());
		
		$data['redirect'] = $this->input->get('redirect');
		
		$this->load->view('work_orders/manage_statuses',$data);		
	
	}
	
	function save_status($status_id = FALSE)
	{
		$this->check_action_permission('manage_statuses');
		$status_name = $this->input->post('status_name');
		$status_color = $this->input->post('status_color');
		$status_sort_order = $this->input->post('status_sort_order');

		$status_data = array(
			'name'=> $status_name,
			'description'=> $this->input->post('status_description'),
			'notify_by_email'=> $this->input->post('notify_by_email') ? 1 : 0,
			'notify_by_sms'=> $this->input->post('notify_by_sms') ? 1 : 0,
			'color'=> $status_color,
			'sort_order'=> $status_sort_order,
		);
		
		if ($this->Work_order->status_save($status_data, $status_id))
		{
			echo json_encode(array('success'=>true,'message'=>lang('work_orders_status_successful_adding').' '.H($status_name)));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('work_orders_status_successful_error')));
		}
	
	}
	
	function delete_status()
	{
		$this->check_action_permission('manage_statuses');
		$status_id = $this->input->post('status_id');
		if($this->Work_order->delete_status($status_id))
		{
			echo json_encode(array('success'=>true,'message'=>lang('work_orders_successful_deleted')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('work_orders_cannot_be_deleted')));
		}
		
	}
	
	function statuses_list()
	{
		echo $this->_statuses_list();
	}
	
	function _statuses_list()
	{
		$statuses = $this->Work_order->get_all_statuses();
     	$return = '<ul>';
		foreach($statuses as $status_id => $status) 
		{
			$return .='<li>'.H($status['name']).
					'<a href="javascript:void(0);" class="edit_status" data-name = "'.H($status['name']).'" data-description = "'.H($status['description']).'" data-notify_by_email = "'.H($status['notify_by_email']).'" data-notify_by_sms = "'.H($status['notify_by_sms']).'" data-color = "'.H($status['color']).'" data-sort_order = "'.H($status['sort_order']).'" data-status_id="'.$status_id.'">['.lang('common_edit').']</a> '.
					'<a href="javascript:void(0);" class="delete_status" data-status_id="'.$status_id.'">['.lang('common_delete').']</a> ';
			 $return .='</li>';
		}
     	$return .='</ul>';
		
		return $return;
	}

	function delete_note()
	{
		$note_id = $this->input->post('note_id');
		if($this->Work_order->delete_note($note_id))
		{
			echo json_encode(array('success'=>true,'message'=>lang('work_orders_successful_deleted')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('work_orders_unable_to_delete')));
		}
		
	}

	function customer_search()
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		$suggestions = $this->Customer->get_customer_search_suggestions($this->input->get('term'),0,100);
		echo json_encode(H($suggestions));
	}

	function select_customer(){
		$person_id = $this->input->post("customer");
		$customer_data = $this->Customer->get_info($person_id);

		$this->session->set_userdata('customer_id_for_new',$person_id);
		echo json_encode(array('customer_data' => $customer_data));
	}

	function select_item_being_repaired(){
		$item_id = $this->input->post("item_id");
		$item_id = strstr($item_id, '#', true) ? strstr($item_id, '#', true): $item_id;
		if(!$item_id){
			$item_id = $this->input->post("item");
		}

		$item_info = $this->Item->get_info($item_id);
		$category_full_path = $this->Category->get_full_path($item_info->category_id);
		$item_tmcm = '';
		
		$this->session->set_userdata('item_id_for_new',$item_id);
		$this->session->set_userdata('item_serial_number_for_new','');
		echo json_encode(array('item_info' => $item_info,'category_full_path'=>$category_full_path,'item_tmcm'=>$item_tmcm));
	}

	function save_new_work_order(){
		$customer_id = $this->input->post("customer_id");
		$item_id = $this->input->post("item_id");
		$serial_number = $this->input->post("item_serial_number");
		
		$missing_fields = array();

		$item_info = $this->Item->get_info($item_id);
		
		$work_order_id = $this->Work_order->save_new_work_order($customer_id,$item_id,$serial_number);

		$this->session->set_userdata('item_id_for_new','');
		$this->session->set_userdata('item_serial_number_for_new','');
		$this->session->set_userdata('customer_id_for_new','');
		echo json_encode(array('success' => true,'work_order_id'=>$work_order_id));

	}

	function add_item_to_session(){
		$item_id = $this->input->post('item_id');
		$this->session->set_userdata('item_id_for_new',$item_id);
	}

	function add_item_serial_number_to_session(){
		$serial_number = $this->input->post('serial_number');
		$this->session->set_userdata('item_serial_number_for_new',$serial_number);
	}

	function add_customer_to_session(){
		$customer_id = $this->input->post('customer_id');
		$this->session->set_userdata('customer_id_for_new',$customer_id);
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
    
    function delete_file($file_id){
        $this->Work_order->delete_file($file_id);
    }
}
?>
