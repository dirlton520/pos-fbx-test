<?php
require_once ("Secure_area.php");
require_once ("interfaces/Idata_controller.php");

class Appointments extends Secure_area implements Idata_controller
{
	function __construct()
	{
		parent::__construct('appointments');	
		$this->lang->load('module');	
		$this->lang->load('items');	
		$this->lang->load('appointments');	
	}
	
	function sorting()
	{
		$this->load->model('Appointment');
		$this->lang->load('appointments');
		
		$this->check_action_permission('search');
		$params = $this->session->userdata('appointments_search_data') ? $this->session->userdata('appointments_search_data') : array('order_col' => 'start_time', 'order_dir' => 'desc','deleted' => 0);
		$search = $this->input->post('search') ? $this->input->post('search') : "";
		$deleted = $this->input->post('deleted') ? $this->input->post('deleted') : $params['deleted'];
		
		$per_page = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : $params['order_col'];
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): $params['order_dir'];
		
		$item_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search,'deleted' => $deleted);
		
		$this->session->set_userdata("appointments_search_data",$item_search_data);
		
		if ($search)
		{
			$config['total_rows'] = $this->Appointment->search_count_all($search,$deleted);
			$table_data = $this->Appointment->search($search, $deleted,$per_page, $this->input->post('offset') ? $this->input->post('offset') : 0, $order_col, $order_dir);
		}
		else
		{
			$config['total_rows'] = $this->Appointment->count_all($deleted);
			$table_data = $this->Appointment->get_all($deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $order_col,$order_dir);
		}
		
		$config['base_url'] = site_url('appointments/sorting');
		$config['per_page'] = $per_page; 
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		
		$data['manage_table'] = get_appointments_manage_table_data_rows($table_data, $this);
		
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination'], 'total_rows' => $config['total_rows']));
	}	
	

	function index($offset=0)
	{
		$this->check_action_permission('search');
		$this->check_action_permission('search');
		$this->load->model('Appointment');
		$this->lang->load('appointments');
		
		$params = $this->session->userdata('appointments_search_data') ? $this->session->userdata('appointments_search_data') : array('offset' => 0, 'order_col' => 'start_time', 'order_dir' => 'desc', 'search' => FALSE,'deleted' => 0);
		
		if ($offset != $params['offset'])
		{
		   redirect('appointments/index/'.$params['offset']);
		}
		
		$config['base_url'] = site_url('appointments/sorting');
		$config['per_page'] = $this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20; 
		
		$data['controller_name']=strtolower(get_class());
		$data['per_page'] = $config['per_page'];
		
		$data['search'] = isset($params['search']) && $params['search'] ? $params['search'] : "";
		$data['deleted'] = $params['deleted'];
		$data['default_start_date'] = '';
		$data['default_end_date'] = '';
		
		if ($data['search'])
		{
			$config['total_rows'] = $this->Appointment->search_count_all($data['search'],$params['deleted']);
			$table_data = $this->Appointment->search($data['search'],$params['deleted'],$data['per_page'],$params['offset'],$params['order_col'],$params['order_dir']);
		}
		else
		{	
			$config['total_rows'] = $this->Appointment->count_all($params['deleted']);
			$table_data = $this->Appointment->get_all($params['deleted'],$data['per_page'], $params['offset'],$params['order_col'],$params['order_dir']);
		}
				
		$data['total_rows'] = $config['total_rows'];
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		
		$data['pagination'] = $this->pagination->create_links();
		$data['order_col'] = $params['order_col'];
		$data['order_dir'] = $params['order_dir'];
		$data['manage_table']=get_appointments_manage_table($table_data,$this);
		$this->load->view('appointments/manage',$data);
	}
	
	function clear_state()
	{
		$params = $this->session->userdata('appointments_search_data');
		$this->session->set_userdata('appointments_search_data',  array('offset' => 0, 'order_col' => 'start_time', 'order_dir' => 'desc', 'search' => FALSE,'deleted' => $params['deleted']));
		redirect('appointments');
	}
	
	
	function suggest()
	{
		$this->load->model('Appointment');
		$this->check_action_permission('search');
		//allow parallel searchs to improve performance.
		session_write_close();
		$params = $this->session->userdata('appointments_search_data') ? $this->session->userdata('appointments_search_data') : array('deleted' => 0);
		$suggestions = $this->Appointment->get_search_suggestions($this->input->get('term'),$params['deleted'],100);
		echo json_encode($suggestions);
	}	

	/*
	Gives search suggestions based on what is being searched for
	*/
	function search()
	{
		$this->load->model('Appointment');
		$this->check_action_permission('search');
		$params = $this->session->userdata('appointments_search_data');
		
		$search=$this->input->post('search') ? $this->input->post('search') : "";
		$per_page=$this->config->item('number_of_items_per_page') ? (int)$this->config->item('number_of_items_per_page') : 20;
		$offset = $this->input->post('offset') ? $this->input->post('offset') : 0;
		$order_col = $this->input->post('order_col') ? $this->input->post('order_col') : 'start_time';
		$order_dir = $this->input->post('order_dir') ? $this->input->post('order_dir'): 'desc';
		$deleted = $this->input->post('deleted') ? $this->input->post('deleted'): $params['deleted'];
		
		$appointments_search_data = array('offset' => $offset, 'order_col' => $order_col, 'order_dir' => $order_dir, 'search' => $search, 'deleted' => $deleted);
		$this->session->set_userdata("appointments_search_data",$appointments_search_data);
		
		if ($search)
		{
			$config['total_rows'] = $this->Appointment->search_count_all($search,$deleted);
			$table_data = $this->Appointment->search($search,$deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'start_time' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'desc');
		}
		else
		{
			$config['total_rows'] = $this->Appointment->count_all($deleted);
			$table_data = $this->Appointment->get_all($deleted,$per_page,$this->input->post('offset') ? $this->input->post('offset') : 0, $this->input->post('order_col') ? $this->input->post('order_col') : 'start_time' ,$this->input->post('order_dir') ? $this->input->post('order_dir'): 'desc');
		}
		
		$config['base_url'] = site_url('appointments/sorting');
		
		$config['per_page'] = $per_page;
		
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		$data['pagination'] = $this->pagination->create_links();
		$data['manage_table']=get_appointments_manage_table_data_rows($table_data,$this);
		echo json_encode(array('manage_table' => $data['manage_table'], 'pagination' => $data['pagination'],'total_rows' => $config['total_rows']));
	}
	
	/*
	Loads the price rule edit form
	*/
	function view($appointment_id=-1,$redirect_code=0)
	{
		if ($appointment_id == -1)
		{
			$this->check_action_permission('add');			
		}
		else
		{
			$this->check_action_permission('edit');
		}
		$this->load->model('Appointment');
		$data = array();
		$data['appointment_info'] = $this->Appointment->get_info($appointment_id);
		if ($data['appointment_info']->person_id)
		{
			$person = $this->Person->get_info($data['appointment_info']->person_id);			
			$data['selected_person_name'] = $person->first_name . ' '. $person->last_name;
		}			
		
		$data['categories'] = array();
		
		foreach($this->Appointment->get_all_categories() as $category_id => $cat)
		{
				$data['categories'][$category_id] = $cat['name'];
		}
		
		$employees = array('' => lang('common_none'));

		foreach($this->Employee->get_all()->result() as $employee)
		{
			$employees[$employee->person_id] = $employee->first_name .' '.$employee->last_name;
		}
		$data['employees'] = $employees;
		$this->load->view("appointments/form",$data);
		
		
	}
	
	function save($appointment_id=-1)
	{
		if ($appointment_id == -1)
		{
			$this->check_action_permission('add');			
		}
		else
		{
			$this->check_action_permission('edit');
		}
		$this->load->model('Appointment');
		
		$start_day = date('Y-m-d',strtotime($this->input->post('start_time')));
		$end_time = $this->input->post('end_time');
		
		$appointment_data = array(
			'start_time' =>  date('Y-m-d H:i:s',strtotime($this->input->post('start_time'))),
			'end_time' => date('Y-m-d H:i:s',strtotime($start_day.' '.$end_time)),
			'notes' => $this->input->post('notes'),
			'person_id' => $this->input->post('person_id') ? $this->input->post('person_id') : NULL,
			'employee_id' => $this->input->post('employee_id') ? $this->input->post('employee_id') : NULL,
			'location_id' => $this->Employee->get_logged_in_employee_current_location_id(),
			'appointments_type_id' => $this->input->post('appointments_type_id') ? $this->input->post('appointments_type_id') : NULL,
		);
		
		if($this->Appointment->save($appointment_data,$appointment_id)){
			$id = $appointment_id == -1 ? $appointment_data['id'] : $appointment_id;

			$this->Appointment->send_email($appointment_data);
			echo json_encode(array('success' => true, 'message' => lang('common_success'), 'id' => $id, 'redirect' => 1));
		}else{
			echo json_encode(array('success' => false, 'message' => lang('common_error'), 'id' => false));
		}
	}
	
	function delete()
	{
		$this->check_action_permission('delete');
		$appointments_to_delete=$this->input->post('ids');
		
		if($this->Appointment->delete_list($appointments_to_delete))
		{
			echo json_encode(array('success'=>true,'message'=>lang('appointments_successful_deleted').' '.
			count($appointments_to_delete).' '.lang('appointments_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('appointments_cannot_be_deleted')));
		}
		
	}
	
	function undelete()
	{
		$this->check_action_permission('delete');
		$appointments_to_delete=$this->input->post('ids');
		
		if($this->Appointment->undelete_list($appointments_to_delete))
		{
			echo json_encode(array('success'=>true,'message'=>lang('appointments_successful_undeleted').' '.
			count($appointments_to_delete).' '.lang('appointments_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('appointments_cannot_be_undeleted')));
		}
	}
		
	
	function calendar($year = '', $month='',$week='',$day='')
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
		
		$day_url = site_url("appointments/calendar/$year/$month/-1/{day}");
				$prefs = array(
					'show_next_prev'  => TRUE,
					'next_prev_url'   => site_url("appointments/calendar"),
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
										
										
				$params = $this->session->userdata('appointments_search_data') ? $this->session->userdata('appointments_search_data') : array('deleted' => 0);
										
				foreach($this->Appointment->get_all_for_range($params['deleted'],$start_date,$end_date)->result() as $row)
				{
					$cur_day = date('j',strtotime($row->start_time));
					$start_time = $row->start_time;
					$end_time = $row->end_time;
					$calendar_data_days[$cur_day][] = array('id' => $row->id,'type' => H($row->type),'person' =>H($row->person),'employee' =>H($row->employee), 'start_time' => $start_time, 'end_time' => $end_time,'notes' => $row->notes);
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
								$url = site_url('appointments/view/'.$data_point['id']);
								
								$entry .= '<a href="'.$url.'" class="list-group-item">';
								$entry .= '<h4 class="list-group-item-heading">'.date(get_time_format(), strtotime($data_point['start_time'])).' - '.date(get_time_format(), strtotime($data_point['end_time'])).'</h4>';
								$entry .= '<p class="list-group-item-text">'.$data_point['type'].'</p>';
								$entry .= '<p class="list-group-item-text">'.lang('appointments_appointment_person').': '.$data_point['person'].'</p>';
								$entry .= '<p class="list-group-item-text">'.lang('common_employee').': '.$data_point['employee'].'</p>';
								$entry .= '<p class="list-group-item-text">'.nl2br($data_point['notes']).'</p>';
								$entry .= '</a>';
							} 
							else 
							{
								
								$entry.= anchor('appointments/view/'.$data_point['id'],$data_point['person'].' '.date(get_time_format(), strtotime($data_point['start_time'])).' - '.date(get_time_format(), strtotime($data_point['end_time'])))	.'<br />';
								
							}
						}	
			
						$calendar_data[$cur_day] = $entry;
					}
				}
				$this->load->library('calendar',$prefs);
				
				$daily_url = site_url("appointments/calendar/$year/$month/-1/$day");
				$weekly_url = site_url("appointments/calendar");
				$monthly_url = '';
				
				$this->load->view('appointments/calendar',array('monthly_url' =>site_url("appointments/calendar/$year/$month"), 'weekly_url' =>site_url("appointments/calendar/$year/$month/$url_week"), 'daily_url' => site_url("appointments/calendar/$year/$month/-1/$url_day"),'controller_name' => $controller_name,'month' => $month,'year'=>$year,'week' => $week,'day' => $day,'calendar' => $this->calendar->generate($year,$month,$week,$day,$calendar_data), 'selected_date' => $selected_date, 'deleted' => $params['deleted']));
				
		 }
	
 	function toggle_show_deleted($deleted=0)
 	{
 		$this->check_action_permission('search');
		$params = $this->session->userdata('appointments_search_data') ? $this->session->userdata('appointments_search_data') : array('order_col' => 'start_time', 'order_dir' => 'desc','deleted' => 0);
 		$params['deleted'] = $deleted;
		$params['offset'] = 0;
		
 		$this->session->set_userdata("appointments_search_data",$params);
		
	}
	
	function suggest_person()
	{
		//allow parallel searchs to improve performance.
		session_write_close();
		$suggestions = $this->Person->get_person_search_suggestions($this->input->get('term'),100);
		echo json_encode(H($suggestions));
	}
	
	
	function manage_appointment_types()
	{
		$this->check_action_permission('add');
		$categories = $this->Appointment->get_all_categories();
		$data = array('categories' => $categories, 'category_list' => $this->_category_list());

		$this->load->view('appointments/categories',$data);
	}
	
	function save_category($category_id = -1)
	{		
		$this->check_action_permission('add');
		$category_name = $this->input->post('category_name');
		
		if ($this->Appointment->save_category($category_name, $category_id))
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
		$this->check_action_permission('delete');		
		$category_id = $this->input->post('category_id');
		if($this->Appointment->delete_category($category_id))
		{
			if (isset($this->ecom_model))
			{	
				$this->ecom_model->delete_category($category_id);
			}
			
			echo json_encode(array('success'=>true,'message'=>lang('items_successful_deleted')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('items_cannot_be_deleted')));
		}
	}
	
	function category_list()
	{
		echo $this->_category_list();
	}
	
	function _category_list()
	{
		$categories = $this->Appointment->get_all_categories();
     	$return = '<ul>';
		foreach($categories as $category_id => $category) 
		{
			$return .='<li>'.H($category['name']).
					'<a href="javascript:void(0);" class="edit_category" data-name = "'.H($category['name']).'" data-category_id="'.$category_id.'">['.lang('common_edit').']</a> '.
					'<a href="javascript:void(0);" class="delete_category" data-category_id="'.$category_id.'">['.lang('common_delete').']</a> ';
			 $return .='</li>';
		}
     	$return .='</ul>';
		
		return $return;
	}
	
}
?>
