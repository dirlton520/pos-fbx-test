<?php
require_once ("Secure_area.php");
require_once ("interfaces/Idata_controller.php");

class Deliveries extends Secure_area implements Idata_controller
{
	function __construct()
	{
		parent::__construct('deliveries');	
		$this->lang->load('deliveries');
		$this->load->model('Delivery');
		$this->load->model('Delivery_category');
		$this->load->model('Shipping_provider');
		$this->load->model('Shipping_method');
		
		$this->load->model('Person');
		$this->lang->load('deliveries');
		$this->load->helper('order');
		
		$this->lang->load('module');	
		$this->load->model('Sale');
		$this->load->model('Location');
		$this->load->model('Item');
		$this->load->model('Item_kit');
		$this->load->model('Item_variations');
		$this->lang->load('work_orders');
	}

	function index($offset=0)
	{
		
		$this->check_action_permission('search');
		$this->load->model('Delivery');
		$this->lang->load('deliveries');
		
		$params = $this->session->userdata('deliveries_orders_search_data') ? $this->session->userdata('deliveries_orders_search_data') : array('offset' => 0, 'order_col' => 'estimated_shipping_date', 'order_dir' => 'asc', 'search' => FALSE,'deleted' => 0);
		
		if ($offset != $params['offset'])
		{
		   redirect('deliveries/index/'.$params['offset']);
		}
		
		$config['base_url'] = site_url('deliveries/sorting');
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		
		$data['controller_name']=strtolower(get_class());
		$data['per_page'] = $config['per_page'];
		
		$data['search'] = $params['search'] ? $params['search'] : "";
		$data['filters'] = $this->session->userdata('orders_fitlers');
		$data['deleted'] = $params['deleted'];
		$data['default_start_date'] = '';
		$data['default_end_date'] = '';
		
		if ($data['search'])
		{
			$config['total_rows'] = $this->Delivery->search_count_all($data['search'],$params['deleted'],$data['filters']);
			$table_data = $this->Delivery->search($data['search'],$params['deleted'],$data['filters'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		else
		{	
			$config['total_rows'] = $this->Delivery->count_all($params['deleted']);
			$table_data = $this->Delivery->get_all($params['deleted'],$data['per_page'], $params['offset'],$params['order_col'],$params['order_dir'],$data['filters']);
		}
		
		
		$data['total_rows'] = $config['total_rows'];
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		
		$data['pagination'] = $this->pagination->create_links();
		$data['order_col'] = $params['order_col'];
		$data['order_dir'] = $params['order_dir'];
		$data['manage_table']= $dataset = get_orders_manage_table($table_data,$this);
		
		$data['default_columns'] = $this->Delivery->get_default_columns();
		$data['selected_columns'] = $this->Employee->get_sale_order_columns_to_display();
		$data['all_columns'] = array_merge($data['selected_columns'],$this->Delivery->get_displayable_columns());
		$data['date_fields'] = array(
			'estimated_delivery_or_pickup_date' => lang('deliveries_estimated_delivery_or_pickup_date'),
			'actual_shipping_date' => lang('deliveries_actual_shipping_date'),
			'actual_delivery_or_pickup_date' => lang('deliveries_actual_delivery_or_pickup_date'),
			'sale_time' => lang('common_sale_date'),
		);
		$all_locations =  $this->Location->get_all()->result();
		
		$locations = array();
		
		foreach($all_locations as $location)
		{
			if ($this->Employee->is_location_authenticated($location->location_id))
			{
				$locations[] = $location;
			}
		}
		$data['locations'] = $locations;

		$data['delivery_statuses'] = $this->Delivery->get_all_statuses();
		$data['delivery_categories'] = $this->Delivery->get_all_categories();
		
		$this->load->view('deliveries/manage', $data);
	}
	
	function clear_state()
	{
		$params = $this->session->userdata('deliveries_orders_search_data');
		$this->session->set_userdata('deliveries_orders_search_data', array('offset' => 0, 'order_col' => 'estimated_shipping_date', 'order_dir' => 'asc', 'search' => FALSE,'deleted' => $params['deleted']));
		redirect('deliveries');
	}
	
	
	function search()
	{
		$this->load->model('Delivery');
		$this->check_action_permission('search');
		$params = $this->session->userdata('deliveries_orders_search_data');
		
		$search=$this->input->post('search') ? $this->input->post('search') : "";
		$per_page=$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : 'estimated_shipping_date';
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc';
		$deleted = $this->input->post('deleted') ? $this->input->post('deleted'): $params['deleted'];
		
		$deliveries_orders_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search, 'deleted' => $deleted);
		$this->session->set_userdata("deliveries_orders_search_data",$deliveries_orders_search_data);
		$data['filters'] = $this->session->userdata('orders_fitlers');
		
		if ($search)
		{
			$config['total_rows'] = $this->Delivery->search_count_all($search,$deleted,$data['filters']);
			$table_data = $this->Delivery->search($search,$deleted,$data['filters'],$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'estimated_shipping_date' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc');
		}
		else
		{
			$config['total_rows'] = $this->Delivery->count_all($deleted);
			$table_data = $this->Delivery->get_all($deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'estimated_shipping_date' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'asc',$data['filters']);
		}
		
		$config['base_url'] = site_url('deliveries/sorting');
		
		$config['per_page'] = $per_page;
		
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['manage_table']=get_orders_manage_table_data_rows($table_data,$this);
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination'],'total_rows' => $config['total_rows']));
	}
	
	function sorting()
	{
		$this->load->model('Delivery');
		$this->lang->load('deliveries');
		
		$this->check_action_permission('search');
		$params = $this->session->userdata('deliveries_orders_search_data') ? $this->session->userdata('deliveries_orders_search_data') : array('order_col' => 'estimated_shipping_date', 'order_dir' => 'asc','deleted' => 0);
		$search = $this->input->post('search') ? $this->input->post('search') : "";
		$deleted = $this->input->post('deleted') ? $this->input->post('deleted') : $params['deleted'];
		$category_id = $this->input->post('category_id');
		$fields = $this->input->post('fields') ? $this->input->post('fields') : 'all';
		
		$per_page = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : $params['order_col'];
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): $params['order_dir'];
		
		$item_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search,'deleted' => $deleted);
		
		$this->session->set_userdata("deliveries_orders_search_data",$item_search_data);
		$data['filters'] = $this->session->userdata('orders_fitlers');
		
		if ($search)
		{
			$config['total_rows'] = $this->Delivery->search_count_all($search,$deleted,$data['filters']);
			$table_data = $this->Delivery->search($search, $deleted,$data['filters'],$per_page, $this->input->post('offset') ? $this->input->post('offset') : 0, $order_col, $order_dir);
		}
		else
		{
			$config['total_rows'] = $this->Delivery->count_all($deleted);
			$table_data = $this->Delivery->get_all($deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $order_col,$order_dir,$data['filters']);
		}
		
		$config['base_url'] = site_url('deliveries/sorting');
		$config['per_page'] = $per_page; 
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		
		$this->load->model('Employee_appconfig');
		$data['default_columns'] = $this->Delivery->get_default_columns();
		$data['manage_table'] = get_orders_manage_table_data_rows($table_data, $this);
		
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination'], 'total_rows' => $config['total_rows']));
	}	

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$this->load->model('Delivery');
		$this->check_action_permission('search');
		//allow parallel searchs to improve performance.
		session_write_close();
		$params = $this->session->userdata('deliveries_orders_search_data') ? $this->session->userdata('deliveries_orders_search_data') : array('deleted' => 0);
		$suggestions = $this->Delivery->get_search_suggestions($this->input->get('term'),$params['deleted'],100);
		echo json_encode($suggestions);
	}
	
	/*
	Loads the price rule edit form
	*/
	function view($delivery_id=-1,$redirect_code=0)
	{
		$this->load->model('Module_action');
		$this->load->model('Delivery');
		$this->load->model('Appfile');

		$this->check_action_permission('edit');
		$data = $this->_get_delivery_data($delivery_id);
		$data['redirect']= $redirect_code;
		$data['rule_id'] = $delivery_id;
		$data['categories'] = $this->Delivery_category->get_all();
		$data['redirect_code']=$redirect_code;
		$data['status_types'] = $this->Delivery->get_delivery_statuses();
		$data['locations'] = $this->Location->get_all()->result();
		$data['delivery_items'] = $this->get_delivery_items($delivery_id);

		$data['controller_name']=strtolower(get_class());
		$data['files'] = $this->Delivery->get_files($delivery_id)->result();

		$this->load->view('deliveries/form', $data);
	}
	
	private function _get_delivery_data($delivery_id)
	{
		$data = array();
		$data['delivery_info'] = $this->Delivery->get_info($delivery_id)->row_array();
		
		if($data['delivery_info'] == NULL){
			$fields = $this->Delivery->get_delivery_fields();

			foreach ($fields as $field)
			{
				$data['delivery_info'][$field]='';
			}
		}

		$shipping_address_person_id = $data['delivery_info']['shipping_address_person_id'];
		
		$data['delivery_person_info'] = (array)$this->Person->get_info($shipping_address_person_id);
		
		
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
		
		$data['providers_with_methods'] = $providers_with_methods;

		$change_status_array = array(''=>lang('common_none'));

		foreach($this->Delivery->get_all_statuses() as $id => $row)
		{
			$change_status_array[$id] = $row['name'];
		}

		$data['change_status_array'] = $change_status_array;

		return $data;
	}
	
	function save($delivery=-1)
	{
		$this->check_action_permission('edit');
		
		$delivery_person_data = array(
			'first_name' => $this->input->post('first_name'),
			'last_name' => $this->input->post('last_name'),
			'email' => $this->input->post('email'),
			'phone_number' => $this->input->post('phone_number'),
			'address_1' => $this->input->post('address_1'),
			'address_2' => $this->input->post('address_2'),
			'city' => $this->input->post('city'),
			'state' => $this->input->post('state'),
			'zip' => $this->input->post('zip'),
			'country' => $this->input->post('country'),
		);

		$delivery_data = array(
			'comment' => $this->input->post('comment'),
			'tracking_number' => $this->input->post('tracking_number'),
			'status' => $this->input->post('status') ? $this->input->post('status') : NULL,
			'estimated_shipping_date' => $this->input->post('estimated_shipping_date') ? date('Y-m-d H:i:s', strtotime($this->input->post('estimated_shipping_date'))) : NULL,
			'actual_shipping_date' => $this->input->post('actual_shipping_date') ? date('Y-m-d H:i:s', strtotime($this->input->post('actual_shipping_date'))) : NULL,
			'estimated_delivery_or_pickup_date' => $this->input->post('estimated_delivery_or_pickup_date') ? date('Y-m-d H:i:s', strtotime($this->input->post('estimated_delivery_or_pickup_date'))) : NULL,
			'actual_delivery_or_pickup_date' => $this->input->post('actual_delivery_or_pickup_date') ? date('Y-m-d H:i:s', strtotime($this->input->post('actual_delivery_or_pickup_date'))) : NULL,
			'delivery_employee_person_id' => $this->input->post('delivery_employee_person_id') ? $this->input->post('delivery_employee_person_id') : NULL,
			'category_id' => $this->input->post('category_id') ? $this->input->post('category_id') : NULL,
			'duration' => $this->input->post('duration') ? $this->input->post('duration') : NULL,
			'location_id' => $this->input->post('location_id') ? $this->input->post('location_id') : NULL,
		);

		if($delivery == -1){
			$person_info = $this->Person->save($delivery_person_data, $person_id=false, $return_data=true);
			$delivery_data['shipping_address_person_id'] = $person_info['person_id'];
		}
		
		$delivery_items = false;
		if($this->input->post('delivery_items')){
			$delivery_items = $this->input->post('delivery_items');
			$delivery_data['delivery_type'] = 'without_sales';
		}else{
			$delivery_data['delivery_type'] = 'with_sales';
		}
		
		$refer = 'deliveries';

		if($this->input->get('redirect')){
			$refer = $this->input->get('redirect');
		}
		

		// Check Delivery Status
		// Email Send If Delivery Status is changed
		$email_send = false;
		$delivery_status = $this->Delivery->get_info($delivery);
		if ($delivery_status->num_rows() == 1) {
			$status_value 	 = $delivery_status->row()->status;
			if($status_value != $this->input->post('status') && $status_value > 0) {
				$email_send = true;
			}
		} else {
			if($this->input->post('status') > 0) {
				$email_send = true;
			}
		}


		//delivery = delivery_id
		if($this->Delivery->save($delivery_data, $delivery, $delivery_items))
		{
			
			$delivery_query = $this->Delivery->get_info($delivery==-1 ? $delivery_data['id'] : $delivery);

			if($delivery_query->num_rows() == 1) {

				$delivery_info 				= $delivery_query->row();
				$shipping_address_person_id = $delivery_info->shipping_address_person_id;
				$this->Person->save($delivery_person_data,$shipping_address_person_id);

				if($email_send == true) {

					$delivery_status_info = $this->Delivery->get_status_info($this->input->post('status'));
					if($delivery_status_info->notify_by_email || $delivery_status_info->notify_by_sms){
						$this->load->model('Common');
						$company_name = $this->config->item('company');
						$message = sprintf($this->lang->line('deliveries_delivery_status_update_message'), $company_name, $delivery, $delivery_status_info->description ? $delivery_status_info->description : $this->Delivery->get_status_name($delivery_status_info->name));
						
						// Render Status Template 
						$status_template = $this->status_email_template($delivery_data['status'], $delivery_info, $delivery_status_info);
			
						if ($status_template) {
							$message = $status_template;
						}
						
						if($delivery_status_info->notify_by_email){
							$customer_email = $this->Person->get_info($shipping_address_person_id)->email;
							if($customer_email){
								$subject = lang('deliveries_delivery_status_update');
			
								$this->Common->send_email($customer_email,$subject,nl2br($message));
							}
						}
			
						if($delivery_status_info->notify_by_sms){
							$customer_phone_number = $this->Person->get_info($shipping_address_person_id)->phone_number;
							if($customer_phone_number){
								$this->Common->send_sms($customer_phone_number,$message);
							}
						}
					}
				}
				if (isset($_FILES['files']))
				{
					$this->load->model('Appfile');
					for($k=0; $k<count($_FILES['files']['name']); $k++)
					{
						if($_FILES['files']['tmp_name'][$k]){				
			   	 			$file_id = $this->Appfile->save($_FILES['files']['name'][$k], file_get_contents($_FILES['files']['tmp_name'][$k]));
			  				$this->Delivery->add_file($delivery==-1 ? $delivery_info->id : $delivery, $file_id);
						}
					}
				}

				$success=lang('deliveries_success');
				$this->session->set_flashdata('success', $success);
				$this->session->unset_userdata('item_info');
				redirect($refer);
			}

			$success=lang('deliveries_success');
			$this->session->set_flashdata('success', $success);
			$this->session->unset_userdata('item_info');
			redirect($refer);
		}
		else
		{
			$error=lang('deliveries_error');
			$this->session->set_flashdata('error', $error);
			$this->session->unset_userdata('item_info');
			redirect($refer);
		}
	}
	
	function delete()
	{
		$this->check_action_permission('delete');
		$deliveries_to_delete=$this->input->post('ids');
		
		if($this->Delivery->delete_list($deliveries_to_delete))
		{
			echo json_encode(array('success'=>true,'message'=>lang('deliveries_successful_deleted').' '.
			count($deliveries_to_delete).' '.lang('deliveries_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('deliveries_cannot_be_deleted')));
		}
	}
	
	function undelete()
	{
		$this->check_action_permission('delete');
		$deliveries_to_delete=$this->input->post('ids');
		
		if($this->Delivery->undelete_list($deliveries_to_delete))
		{
			echo json_encode(array('success'=>true,'message'=>lang('deliveries_successful_undeleted').' '.
			count($deliveries_to_delete).' '.lang('deliveries_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('deliveries_cannot_be_undeleted')));
		}
	}
	
	
	function save_filters()
	{
		$this->session->set_userdata("orders_fitlers",$this->input->post());
		echo json_encode(array('success' => TRUE));
	}
	
	function save_column_prefs()
	{
		$this->load->model('Employee_appconfig');
		
		if ($this->input->post('columns'))
		{
			$this->Employee_appconfig->save('sale_orders_column_prefs',serialize($this->input->post('columns')));
		}
		else
		{
			$this->Employee_appconfig->delete('sale_orders_column_prefs');			
		}
	}
	
	function reload_delivery_table()
	{
		$this->load->model('Delivery');
		
		$config['base_url'] = site_url('deliveries/sorting');
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		$params = $this->session->userdata('deliveries_orders_search_data') ? $this->session->userdata('deliveries_orders_search_data') : array('offset' => 0, 'order_col' => 'estimated_shipping_date', 'order_dir' => 'asc', 'search' => FALSE,'deleted' => 0);

		$data['per_page'] = $config['per_page'];
		$data['search'] = $params['search'] ? $params['search'] : "";		
		$data['filters'] = $this->session->userdata('orders_fitlers');
		
		if ($data['search'])
		{
			$config['total_rows'] = $this->Delivery->search_count_all($data['search'],$params['deleted'],$data['filters']);
			$table_data = $this->Delivery->search($data['search'],$params['deleted'],$data['filters'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		else
		{
			
			$config['total_rows'] = $this->Delivery->count_all($params['deleted']);
			$table_data = $this->Delivery->get_all($params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir'],$data['filters']);
		}
		
		echo get_orders_manage_table($table_data,$this);
	}
	
	function calendar_($date_field, $year = '', $month='',$week='',$day='')
	{
		$this->load->helper('date_helper');
		
		$controller_name = strtolower(get_class());
		
		if (!$year)
		{
			$year = date('Y');
		}
		
		if (!$month)
		{
			$month = date('m');
		}
		
		
		$url_day = $day ? $day : date('d');
				
		$url_week = getWeeks(date("Y-m-d", strtotime("$year-$month-$url_day")), "sunday");
				
		$date_fields = array(
			'estimated_delivery_or_pickup_date' => lang('deliveries_estimated_delivery_or_pickup_date'),
			'actual_shipping_date' => lang('deliveries_actual_shipping_date'),
			'actual_delivery_or_pickup_date' => lang('deliveries_actual_delivery_or_pickup_date'),
			'sale_time' => lang('common_sale_date'),
		);
		
		if(!isset($date_fields[$date_field]))
		{
			$date_field = 'estimated_delivery_or_pickup_date';
		}
		
		$day_url = site_url("deliveries/calendar/$date_field/$year/$month/-1/{day}");
				$prefs = array(
					'show_next_prev'  => TRUE,
					'next_prev_url'   => site_url("deliveries/calendar/$date_field"),
					'template'				=> 
		'
		        {table_open}<table class="calendar" border="1" cellpadding="0" cellspacing="0" width="100%" style="text-align:center;margin: 0 auto;">{/table_open}

		        {heading_row_start}<tr>{/heading_row_start}

		        {heading_previous_cell}<th class="heading_previous_cell" style="text-align:center;"><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
		        {heading_title_cell}<th class="heading_title_cell" colspan="{colspan}" style="text-align:center;">{heading}</th>{/heading_title_cell}
		        {heading_next_cell}<th class="heading_next_cell" style="text-align:center;"><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}

		        {heading_row_end}</tr>{/heading_row_end}

		        {week_row_start}<tr>{/week_row_start}
		        {week_day_cell}<td class="week_day_cell">{week_day}</td>{/week_day_cell}
		        {week_row_end}</tr>{/week_row_end}

		        {cal_row_start}<tr>{/cal_row_start}
		        {cal_cell_start}<td class="cal_cell_start" style="height:140px; vertical-align: top;">{/cal_cell_start}
		        {cal_cell_start_today}<td class="cal_cell_start_today" style=" height:140px; vertical-align: top;background-color:#ddd;">{/cal_cell_start_today}
		        {cal_cell_start_other}<td class="cal_cell_start_other" style="height:140px; vertical-align: top;" class="other-month">{/cal_cell_start_other}

		        {cal_cell_content}<a class="cal_cell_content" href="'.$day_url.'">{day}</a><br />{content}</a>{/cal_cell_content}
		        {cal_cell_content_today}<div class="cal_cell_content_today highlight"><a href="'.$day_url.'">{day}</a><br />{content}</div>{/cal_cell_content_today}

		        {cal_cell_no_content}<a "cal_cell_no_content" href="'.$day_url.'">{day}</a>{/cal_cell_no_content}
		        {cal_cell_no_content_today}<div class="cal_cell_no_content_today highlight"><a href="'.$day_url.'">{day}</a></div>{/cal_cell_no_content_today}

		        {cal_cell_blank}&nbsp;{/cal_cell_blank}

		        {cal_cell_other}<a class="cal_cell_other" href="'.$day_url.'">{day}</a>{/cal_cel_other}

		        {cal_cell_end}</td>{/cal_cell_end}
		        {cal_cell_end_today}</td>{/cal_cell_end_today}
		        {cal_cell_end_other}</td>{/cal_cell_end_other}
		        {cal_row_end}</tr>{/cal_row_end}

		        {table_close}</table>{/table_close}
						'
 					);
				
			 	$calendar_data = array();
				$calender_data_days = array();
				
				//If we are doing monthy calendar fall back to parent place
				if (!$week && !$day)
				{
					$start_date = date("$year-$month-01");
					$end_date = date("$year-$month-t");
				}
		
				//Weekly Calendar
				if ($week && !$day)
				{
					//pull in all events for month; frontend will only show that week
					$start_date = date("$year-$month-01");
					$end_date = date("$year-$month-t");
				}
				
				$selected_date = '';
				//Daily Calendar
				if ($day)
				{
					$selected_date = date(get_date_format(), strtotime("$year-$month-$day"));
					$start_date = date("$year-$month-$day");
					$end_date = date("$year-$month-$day 23:59:59");
				}
										
										
				$params = $this->session->userdata('deliveries_orders_search_data') ? $this->session->userdata('deliveries_orders_search_data') : array('deleted' => 0);
										
				foreach($this->Delivery->get_all_for_range($params['deleted'],$start_date,$end_date,$date_field)->result() as $row)
				{
					$cur_day = date('j',strtotime($row->{$date_field}));
					$time = date(get_time_format(),strtotime($row->{$date_field}));
					$calendar_data_days[$cur_day][] = array('delivery_id' => $row->delivery_id,'name' =>H($row->first_name.' '.$row->last_name), 'time' => $time, 'status' => $row->status);
				}
		
				if (!empty($calendar_data_days))
				{
					foreach($calendar_data_days as $cur_day => $data)
					{
						$entry = '';
						
						
						foreach($data as $data_point)
						{
							if($day)
							{
								$url = site_url('deliveries/view/'.$data_point['delivery_id']);
								
								$entry .= '<a href="'.$url.'" class="list-group-item">';
								$entry .= '<h4 class="list-group-item-heading">'.$data_point['time'].'</h4>';
								$entry .= '<p class="list-group-item-text">'.$data_point['name'].'</p>';
								$entry .= '</a>';
							} 
							else 
							{
								
								$entry.= anchor('deliveries/view/'.$data_point['delivery_id'],$data_point['name'].' '.$data_point['time']).'<br />';
								
							}
						}	
			
						$calendar_data[$cur_day] = $entry;
					}
				}
				$this->load->library('calendar',$prefs);
				
				$daily_url = site_url("deliveries/calendar/$date_field/$year/$month/-1/$day");
				$weekly_url = site_url("deliveries/calendar/$date_field/");
				$monthly_url = '';
				$this->load->view('deliveries/calendar',array('monthly_url' =>site_url("deliveries/calendar/$date_field/$year/$month"), 'weekly_url' =>site_url("deliveries/calendar/$date_field/$year/$month/$url_week"), 'daily_url' => site_url("deliveries/calendar/$date_field/$year/$month/-1/$url_day"),'controller_name' => $controller_name, 'date_field' => $date_field,'month' => $month,'year'=>$year,'week' => $week,'day' => $day,'date_fields' => $date_fields,'calendar' => $this->calendar->generate($year,$month,$week,$day,$calendar_data), 'selected_date' => $selected_date, 'deleted' => $params['deleted']));
				
	}

	function calendar($date_field=null)
	{
	
		if($this->input->post() && $this->input->post('action_type') == 'update_event'){
			$delivery_id = $this->input->post('id');
			$start_date = $this->input->post('start');
			$end_date = $this->input->post('end');
			$date_field = $this->input->post('date_field');

			$to_time = strtotime($end_date);
			$from_time = strtotime($start_date);

			$duration = round(abs($to_time - $from_time) / 60,2). " minute";
			
			$this->delivery->update_event($delivery_id, $start_date, $date_field, $duration);
			exit;
		}

		$date_fields = array(
			'estimated_delivery_or_pickup_date' => lang('deliveries_estimated_delivery_or_pickup_date'),
			'actual_shipping_date' => lang('deliveries_actual_shipping_date'),
			'actual_delivery_or_pickup_date' => lang('deliveries_actual_delivery_or_pickup_date'),
			'sale_time' => lang('common_sale_date'),
		);
		
		if(!isset($date_fields[$date_field]))
		{
			$date_field = 'estimated_delivery_or_pickup_date';
		}
		
		$params = $this->session->userdata('deliveries_orders_search_data') ? $this->session->userdata('deliveries_orders_search_data') : array('deleted' => 0);

		$this->load->view('deliveries/calendar', array( 'selected_date' => '', 'date_fields' => $date_fields, 'date_field' => $date_field, 'deleted' => $params['deleted']));
				
	}

	function get_calendar(){
		$date_field = $this->input->post('date_field');
		$start_date = $this->input->post('start');
		$end_date = $this->input->post('end');

		$params = $this->session->userdata('deliveries_orders_search_data') ? $this->session->userdata('deliveries_orders_search_data') : array('deleted' => 0);

		$data = array();
		foreach($this->Delivery->get_all_for_range($params['deleted'], $start_date, $end_date, $date_field)->result() as $row)
		{

			if($this->config->item('delivery_color_based_on') == "category" && $row->category_color){
				$color = $row->category_color;
			}else{
				$color = $this->Delivery->get_status_info($row->status)->color;
			}
			
			$data[] = array(
				"id" => $row->delivery_id,
				"title" => H($row->first_name.' '.$row->last_name).' '.$row->full_address,
				"start" => $row->{$date_field},
				"end" => date("Y-m-d H:i:s", strtotime($row->{$date_field}." +".$row->duration." minutes")),
				"description" => $row->full_address,
				"status" => $row->status,
				"color" => $color
			);
		}

		echo json_encode($data);
	}
		 
	function toggle_show_deleted($deleted=0)
	{
		$this->check_action_permission('search');
		$params = $this->session->userdata('deliveries_orders_search_data') ? $this->session->userdata('deliveries_orders_search_data') :array('offset' => 0, 'order_col' => 'estimated_shipping_date', 'order_dir' => 'asc', 'search' => FALSE,'deleted' => 0);
		$params['deleted'] = $deleted;
		$params['offset'] = 0;
		
		$this->session->set_userdata("deliveries_orders_search_data",$params);
	}
	
	function set_status($status)
	{
		$this->check_action_permission('edit');
		$ids = $this->input->post('ids');
		$this->Delivery->update_status_bulk($ids,$status);
	}

	function change_status()
	{
		$delivery_ids=$this->input->post('delivery_ids');
		$status = $this->input->post('status');
		$status_changed_delivery_ids = array();
		

		

		foreach($delivery_ids as $delivery_id)
		{
			$delivery_info = $this->Delivery->get_info($delivery_id)->row();

			if($delivery_info->status != $status){
				$status_changed_delivery_ids[] = $delivery_id;

				// Get Sale ID Against Delivery
				$sale_ids[$delivery_id] 		= $delivery_info->sale_id;
				$this->Delivery->change_status($delivery_id,$status);
			}
		}
		

		if(count($status_changed_delivery_ids) > 0){	
			foreach($status_changed_delivery_ids as $delivery_id)
			{
				// Define Sale ID variable against Delivery ID
				$sale_id 		= $this->config->item('sale_prefix').' '.$sale_ids[$delivery_id];
				$delivery_info = $this->Delivery->get_info($delivery_id)->row();
				$delivery_status_info = $this->Delivery->get_status_info($status);
				if($delivery_status_info->notify_by_email || $delivery_status_info->notify_by_sms){
					$this->load->model('Common');
					$company_name = $this->config->item('company');
					$message = sprintf($this->lang->line('deliveries_delivery_status_update_message'), $company_name, $sale_id, $delivery_status_info->description ? $delivery_status_info->description : $this->Delivery->get_status_name($delivery_status_info->name));
					


					// Render Status Template 
					$status_template = $this->status_email_template($status, $delivery_info, $delivery_status_info);
					
					if ($status_template) {
						$message =  $status_template;
					}

					if($delivery_status_info->notify_by_email){
						$customer_email = $this->Person->get_info($delivery_info->shipping_address_person_id)->email;
						if($customer_email){
							$subject = lang('deliveries_delivery_status_update');
							$this->Common->send_email($customer_email,$subject,nl2br($message));
						}
					}
		
					if($delivery_status_info->notify_by_sms){
						$customer_phone_number = $this->Person->get_info($delivery_info->shipping_address_person_id)->phone_number;
						if($customer_phone_number){
							$this->Common->send_sms($customer_phone_number,$message);
						}
					}
				}
			}
		}
		
		echo json_encode(array('success'=>true,'message'=>lang('deliveries_successful_changed')));
	}

	function manage_categories()
	{
		$this->check_action_permission('manage_categories');
		$categories = $this->Delivery_category->get_all();
		$data = array('categories' => $categories, 'category_list' => $this->_category_list());
		$data['redirect'] = $this->input->get('redirect');

		$progression = $this->input->get('progression');
		$quick_edit = $this->input->get('quick_edit');
		$data['progression'] = !empty($progression);
		$data['quick_edit'] = !empty($quick_edit);
		$this->load->view('deliveries/categories',$data);
	}
	
	function save_category($category_id = FALSE)
	{		
		$this->check_action_permission('manage_categories');
		$category_name = $this->input->post('category_name');
		$category_color = $this->input->post('category_color');
		
		if ($this->Delivery_category->save($category_name, $category_color, $category_id))
		{
			echo json_encode(array('success'=>true,'message'=>lang('items_category_successful_adding').' '.H($category_name)));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('items_category_successful_error')));
		}
	}
	
	function delete_category()
	{
		$this->check_action_permission('manage_categories');		
		$category_id = $this->input->post('category_id');
		if($this->Delivery_category->delete($category_id))
		{
			echo json_encode(array('success'=>true, 'message'=>lang('items_successful_deleted')));
		}
		else
		{
			echo json_encode(array('success'=>false, 'message'=>lang('items_cannot_be_deleted')));
		}
	}
	
	function category_list()
	{
		echo $this->_category_list();
	}
	
	function _category_list()
	{
		$categories = $this->Delivery_category->get_all();
		$return = '<ul>';

		foreach($categories as $category_id => $category) 
		{
			$return .='<li>'.H($category['name']).'&nbsp;'.
				'<a href="javascript:void(0);" class="edit_category" data-name="'.H($category['name']).'"  data-category_id="'.$category_id.'" data-color="'.H($category["color"]).'"  >['.lang('common_edit').']</a> '.
				'<a href="javascript:void(0);" class="delete_category" data-category_id="'.$category_id.'">['.lang('common_delete').']</a> ';
			$return .='</li>';
		}

		$return .='</ul>';
		
		return $return;
	}

	//delivery without sales
	function item_search()
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		if(!$this->config->item('speed_up_search_queries'))
		{
			$suggestions = $this->Item->get_item_search_suggestions($this->input->get('term'),0,false,$this->config->item('items_per_search_suggestions') ? (int)$this->config->item('items_per_search_suggestions') : 20, TRUE);
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

	function add_item(){
	
		$item = $this->input->post('item');
		$item_name = $this->input->post('item_label');

		if(!$item){
			return false;
		}

		$item_id = $this->input->post("item_id") ? $this->input->post("item_id") : null;
		$item_variation_id = $this->input->post("item_variation_id") ? $this->input->post("item_variation_id") : null;

		$item_kit_id = null;

		if($this->input->post("is_manual") && !$item_id){
			$this->load->helper("items_helper");
			$data = parse_item_scan_data($item);
			$item_id = $data["item_id"];

			if(!$item_id){
				echo $this->get_delivery_items(false, array("success" => false, "message"=>lang('deliveries_unable_to_add_item')));
				exit;
			}

			$item_variation_id = $data["variation_id"];
			if($data["variation_choices_model"] && !$item_variation_id){
				echo $this->get_delivery_items(false, array("has_variation" => true, "variation_choices_model" => $data["variation_choices_model"], "item_id" => $item_id));
				exit;
			}
		}else{

			if(!$item_id && !$item_variation_id){
				$identifier = explode('#', rawurldecode($item));
				$item_id = $identifier[0];
				$item_variation_id = isset($identifier[1]) ? $identifier[1] : $item_variation_id;
			}
			if (strpos($item_id, 'KIT') !== false) {
				$kit_identifier = explode(' ', $item_id);
				$item_kit_id = $kit_identifier[1];
			}
		}

		if($item_id || $item_kit_id){

			if($item_kit_id){
				$item_kit = $this->Item_kit->get_info($item_kit_id);
				$name = $item_kit->name." (".$this->Item->get_category($item_kit->category_id).")";
			}else if($item_id){
				$item = $this->Item->get_info($item_id);
				$name = $item->name." (".$this->Item->get_category($item->category_id).")";
				
			}else{
				echo $this->get_delivery_items(false, array("success" => false, "message"=>lang('deliveries_unable_to_add_item')));
				exit;
			}
	

			if($this->session->userdata('item_info')){
				$item_info = $this->session->userdata('item_info');
	
				$quantity_updated = false;
				foreach($item_info as $ik => $iv){
					if($iv['item_id'] == $item_id && $iv['item_variation_id'] == $item_variation_id && $iv['item_kit_id'] == $item_kit_id){
						$item_info[$ik]['quantity'] += 1;
						$quantity_updated = true;
					}
				}
	
				if($quantity_updated == false){
					array_push($item_info, array(
						'item_id' => $item_id,
						'item_variation_id' => $item_variation_id,
						'item_kit_id' => $item_kit_id,
						'quantity' => 1,
						'name' => $name,
						'category' => null,
						'variation' => ($item_variation_id) ? $this->Item_variations->get_variation_name($item_variation_id) : NULL
					));
				}
			}else{
				$item_info = array(
					array(
						'item_id' => $item_id,
						'item_variation_id' => $item_variation_id,
						'item_kit_id' => $item_kit_id,
						'quantity' => 1,
						'name' => $name,
						'category' => null,
						'variation' => ($item_variation_id) ? $this->Item_variations->get_variation_name($item_variation_id) : NULL
					)
				);
			}
	
			$this->session->set_userdata('item_info', $item_info);
			echo $this->get_delivery_items();
		}else{
			echo $this->get_delivery_items(false, array("success" => false, "message"=>lang('deliveries_unable_to_add_item')));
		}

	}

	function get_delivery_items($delivery_id = false, $additional_data=array(), $return_result=false){
		if($delivery_id && $delivery_id != -1){
			$item_info = array();
			
			$delivery_items = $this->Delivery->get_delivery_items($delivery_id);
			
			foreach($delivery_items->result() as $item){
				$item_info[] = array(
					'item_id' => $item->item_id,
					'item_variation_id' => $item->item_variation_id,
					'item_kit_id' => null,
					'quantity' => $item->quantity,
					'name' => $item->name,
					'category' => $item->category,
					'variation' => ($item->item_variation_id) ? $this->Item_variations->get_variation_name($item->item_variation_id) : NULL
				);
			}

			$delivery_item_kits = $this->Delivery->get_delivery_item_kits($delivery_id);

			foreach($delivery_item_kits->result() as $item_kit){
				$item_info[] = array(
					'item_id' => null,
					'item_variation_id' => null,
					'item_kit_id' => $item_kit->item_kit_id,
					'quantity' => $item_kit->quantity,
					'name' => $item_kit->name,
					'category' => $item_kit->category,
					'variation' => NULL
				);
			}
			if($return_result==true){
				return $item_info;
			}
			$this->session->set_userdata('item_info',$item_info);
		}

		$data['items'] = $this->session->userdata('item_info');
		$data['additional_data'] = $additional_data;
		return $this->load->view('deliveries/delivery_items', $data, true);
	}

	function delete_delivery_item(){
		$item_id = $this->input->post('item_id');
		$item_kit_id = $this->input->post('item_kit_id');
		$item_variation_id = $this->input->post('item_variation_id');

		$item_info = $this->session->userdata('item_info');

		foreach($item_info as $ik => $iv){
			if($iv['item_id'] == $item_id && $iv['item_variation_id'] == $item_variation_id && $iv['item_kit_id'] == $item_kit_id){
				unset($item_info[$ik]);
			}
		}

		$this->session->set_userdata('item_info', $item_info);
		echo $this->get_delivery_items();

	}

	function edit_item($key){
		$quantity = $this->input->post('value');
		$item_info = $this->session->userdata('item_info');
		$item_info[$key]['quantity'] = $quantity;

		$this->session->set_userdata('item_info', $item_info);
		echo $this->get_delivery_items();
	}

	function manage_statuses()
	{
		$this->check_action_permission('manage_statuses');
		$statuses = $this->Delivery->get_all_statuses();
		$data = array('statuses' => $statuses, 'statuses_list' => $this->_statuses_list());
		
		$data['redirect'] = $this->input->get('redirect');
		
		$this->load->view('deliveries/manage_statuses',$data);		
	
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
			'color'=> $status_color
		);
		
		if ($this->Delivery->status_save($status_data, $status_id))
		{
			echo json_encode(array('success'=>true,'message'=>lang('deliveries_status_successful_adding').' '.H($status_name)));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('deliveries_status_successful_error')));
		}
	
	}
	
	function delete_status()
	{
		$this->check_action_permission('manage_statuses');
		$status_id = $this->input->post('status_id');
		if($this->Delivery->delete_status($status_id))
		{
			echo json_encode(array('success'=>true,'message'=>lang('deliveries_successful_deleted')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('deliveries_cannot_be_deleted')));
		}
		
	}
	
	function statuses_list()
	{
		echo $this->_statuses_list();
	}
	
	function _statuses_list()
	{
		$statuses = $this->Delivery->get_all_statuses();
     	$return = '<ul>';
		foreach($statuses as $status_id => $status) 
		{
			$return .='<li>'.H($status['name']).
					'<a href="javascript:void(0);" class="edit_status" data-name = "'.H($status['name']).'" data-description = "'.H($status['description']).'" data-notify_by_email = "'.H($status['notify_by_email']).'" data-notify_by_sms = "'.H($status['notify_by_sms']).'" data-color = "'.H($status['color']).'" data-status_id="'.$status_id.'">['.lang('common_edit').']</a> '.
					'<a href="javascript:void(0);" class="delete_status" data-status_id="'.$status_id.'">['.lang('common_delete').']</a> ';
			 $return .='</li>';
		}
     	$return .='</ul>';
		
		return $return;
	}

	function view_delivery_modal($delivery_id, $redirect_code=null){
		$this->load->model('Module_action');
		$this->load->model('Delivery');
		$this->check_action_permission('edit');
		$data = $this->_get_delivery_data($delivery_id);
		$data['redirect']= $redirect_code;
		$data['rule_id'] = $delivery_id;
		$data['categories'] = $this->Delivery_category->get_all();
		$data['redirect_code']=$redirect_code;
		$data['status_types'] = $this->Delivery->get_delivery_statuses();
		$data['locations'] = $this->Location->get_all()->result();
		$data['delivery_items'] = $this->get_delivery_items($delivery_id);

		$this->load->view('deliveries/delivery_modal', $data);
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
	
	function delete_file($file_id)
	{
		$this->check_action_permission('add_update');
		$this->Delivery->delete_file($file_id);
	}

	function manage_template()
	{

		$delivery_statuses = $this->Delivery->get_all_statuses();
		// Get Default Status Using 0 ID
		$default_status 			= $this->Delivery->get_status_id(0);
		if ($default_status) {
			$data['default'] = $default_status->content;
		} else {
			$data['default'] = '';
		}

		foreach ($delivery_statuses as $key => $status) {

			if ($status['notify_by_email'] == 1) {

				$data['delivery_statuses'][$key]['name'] 				= $status['name'];
				$data['delivery_statuses'][$key]['description'] 		= $status['description'];
				$data['delivery_statuses'][$key]['notify_by_email'] 	= $status['notify_by_email'];
				$data['delivery_statuses'][$key]['notify_by_sms'] 		= $status['notify_by_sms'];
				$data['delivery_statuses'][$key]['color'] 				= $status['color'];

				$result = $this->Delivery->get_status_id($key);
				if (isset($result)) {
					$data['delivery_statuses'][$key]['data'] = $result->content;
				} else {
					$data['delivery_statuses'][$key]['data'] = '';
				}
			}
		}

		$data['redirect'] = $this->input->get('redirect');

		$this->load->view('deliveries/manage_template',$data);		
	}

	function save_template()
	{
		
		$template_data = array(
			'status_id' =>	$this->input->post('status_id'),
			'content' 	=>	$this->input->post('email_template'),
		);

		// Email Tempalte Text Validate
		if ($template_data['status_id'] == NULL) {
			echo json_encode(array('success'=>false,'message'=>lang('deliveries_email_template_successful_error')));
			return FALSE;
		}
		
		if ($template_data['content'] == NULL) {
			echo json_encode(array('success'=>false,'message'=>lang('deliveries_email_template_successful_error')));
			return FALSE;
		}

		$refer = 'deliveries/manage_template';

		if($this->input->get('redirect')){
			$refer = $this->input->get('redirect');
		}

		if($this->Delivery->save_template($template_data))
		{
			echo json_encode(array('success'=>true,'message'=>lang('deliveries_status_successful_adding')));
			
		}
		else
		{
	
			echo json_encode(array('success'=>false,'message'=>lang('deliveries_email_template_successful_error')));
		
		}
	}

	/**
	* Get Template Content From Database & Replace with Keys
	* 
	* @var $company_name PHP Point of Sale
	* @param Search Values & Replace Value
	*
	* @return Render Template 
	**/

	function status_email_template($status_id, $delivery_info, $delivery_status_info)
	{
		// Search and Replace Template 
		$this->db->select('content');
		$this->db->from('delivery_email_templates');
		$this->db->where('status_id', $status_id);
		$status_template = $this->db->get()->row();

		
		if (empty($status_template)) {
			$this->db->select('content');
			$this->db->from('delivery_email_templates');
			$this->db->where('status_id', 0);
			$status_template = $this->db->get()->row();
		}

		if ($status_template) {

			$sale_info = $this->Sale->get_info($delivery_info->sale_id)->row();

			$ecommerce_id = '';
			if (isset($sale_info->is_ecommerce)) {
				if ($sale_info->is_ecommerce == 1) {
					$ecommerce_id = $sale_info->ecommerce_order_id;
				} else {
					$ecommerce_id = '';
				}
			}
			
			$data    		= $status_template->content;
			$search_replace = array(
								"%company_name%" 						=> $this->config->item('company'),
								"%sale_id%"								=> $this->config->item('sale_prefix').' '.$delivery_info->sale_id,
								"%tracking_number%"						=> $delivery_info->tracking_number,
								"%ecommerce_id%"						=> $ecommerce_id,
								"%delivery_id%"							=> $delivery_info->id,
								"%delivery_status%"						=> $delivery_status_info->name,
								"%estimated_shipping_date%"				=> $delivery_info->estimated_shipping_date,
								"%actual_shipping_date%"				=> $delivery_info->actual_shipping_date,
								"%estimated_delivery_or_pickup_date%"	=> $delivery_info->estimated_delivery_or_pickup_date,
								"%actual_delivery_or_pickup_date%"		=> $delivery_info->actual_delivery_or_pickup_date,
							);

			$return 		=  str_replace(array_keys($search_replace), $search_replace, $data);
			return $return;
		}
		return false;

	}
}


?>
