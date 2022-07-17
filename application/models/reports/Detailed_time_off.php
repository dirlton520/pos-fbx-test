<?php
require_once ("Report.php");
class Detailed_time_off extends Report
{
	function __construct()
	{
		parent::__construct();
		$CI =& get_instance();
		$CI->lang->load('timeclocks');
	}
	
	public function getDataColumns()
	{
		
		$columns = array();
		if (!isset($this->params['is_view_only_self']) || $this->params['is_view_only_self'] == false)
		{
			$columns[] = array('data'=>lang('common_edit'), 'align'=> 'left');
			$columns[] = array('data'=>lang('common_deny'), 'align'=> 'left');
			$columns[] = array('data'=>lang('reports_employee'), 'align'=> 'left');
		}
		$columns[] = array('data'=>lang('common_start_date'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_end_date'), 'align'=> 'left');
		$columns[] = array('data'=>lang('timeclocks_hours_requested_off'), 'align'=> 'left');
		$columns[] = array('data'=>lang('timeclocks_is_paid'), 'align'=> 'left');
		$columns[] = array('data'=>lang('timeclocks_reason'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_approve'), 'align'=> 'left');
		return $columns;
	}
	
	public function getInputData()
	{
		
		$input_data = Report::get_common_report_input_data(FALSE);
		$specific_entity_data = array();
		$specific_entity_data['view']  = 'specific_entity';
		$specific_entity_data['specific_input_name'] = 'employee_id';
		$specific_entity_data['specific_input_label'] = lang('reports_employee');
		$employees = array('' => lang('common_all'));

		foreach($this->Employee->get_all()->result() as $employee)
		{
			$employees[$employee->person_id] = $employee->first_name .' '.$employee->last_name;
		}
		$specific_entity_data['specific_input_data'] = $employees;
		
		
		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array();
			
			$input_params[] = array('view' => 'date_range', 'with_time' => TRUE);
			$input_params[] = $specific_entity_data;
			$input_params[] = array('view' => 'checkbox','checkbox_label' => lang('reports_show_approved'), 'checkbox_name' => 'show_approved');
			$input_params[] = array('view' => 'excel_export');
			$input_params[] = array('view' => 'submit');
		}
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
		
	}
	
	public function getOutputData()
	{
		$this->setupDefaultPagination();
		
		$start_date=$this->params['start_date'];
		$end_date=$this->params['end_date'];
		$employee_id = $this->params['employee_id'];

		$headers = $this->getDataColumns();
		$report_data = $this->getData();

		$tabular_data = array();
		$report_data = $this->getData();

		foreach($report_data as $row)
		{
			$data_row = array();

			$edit=anchor('timeclocks/request_time_off/'.$row['id'].'/?'.$_SERVER['QUERY_STRING'], lang('common_edit'));
			
			$delete=anchor('timeclocks/delete_time_off/'.$row['id'].'?'.$_SERVER['QUERY_STRING'], lang('common_deny'), 
			"onclick='return do_link_confirm(".json_encode(lang('reports_confirm_timeclock_time_off_delete')).", this)'");

			$approve=anchor('timeclocks/approve_time_off/'.$row['id'].'?'.$_SERVER['QUERY_STRING'], lang('common_approve'), 
			"onclick='return do_link_confirm(".json_encode(lang('reports_confirm_timeclock_time_off_approve')).", this)'");

			$data_row[] = array('data'=>$edit, 'align' => 'left');
			$data_row[] = array('data'=>$delete, 'align' => 'left');
			$data_row[] = array('data'=>$row['first_name'].' '.$row['last_name'], 'align' => 'left');
			$data_row[] = array('data'=>date(get_date_format(), strtotime($row['start_day'])), 'align' => 'left');
			$data_row[] = array('data'=>date(get_date_format(), strtotime($row['end_day'])), 'align' => 'left');
			$data_row[] = array('data'=>to_quantity($row['hours_requested']), 'align' => 'left');			
			$data_row[] = array('data'=>boolean_as_string($row['is_paid']), 'align' => 'left');			
			$data_row[] = array('data'=>$row['reason'], 'align' => 'left');
			$data_row[] = array('data'=>!$row['approved'] ? $approve : lang('common_approved'), 'align' => 'center');
					
			$tabular_data[] = $data_row;			
		}

		$employee_info = $this->Employee->get_info($employee_id);

		$data = array(
			"view" => 'tabular',
			"title" => ($employee_id ? $employee_info->first_name . ' '.$employee_info->last_name . ' - ' : ' ').lang('reports_time_off_report'),
			"subtitle" => date(get_date_format(), strtotime($start_date)) .'-'.date(get_date_format(), strtotime($end_date)),
			"headers" => $this->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $this->getSummaryData(),
			"export_excel" => $this->params['export_excel'],
			"pagination" => $this->pagination->create_links(),
		);
		
		return $data;
	}
	
	public function getData()
	{
		$current_location=$this->Employee->get_logged_in_employee_current_location_id();
		$this->db->select('employees_time_off.*, people.first_name, people.last_name');
		$this->db->from('employees_time_off');
		$this->db->where('employee_requested_location_id', $current_location);
		$this->db->join('employees', 'employees.person_id = employees_time_off.employee_requested_person_id');
		$this->db->join('people', 'people.person_id = employees.person_id');
		
		$this->db->where('start_day >=', $this->params['start_date']);
		$this->db->where('start_day <=', $this->params['end_date']);
		
		if (!isset($this->params['is_view_only_self']))
		{
			$this->db->where('employees_time_off.deleted',0);
			if (isset($this->params['show_approved']) && $this->params['show_approved'])
			{
				$this->db->where('approved',1);
			}
			else
			{
				$this->db->where('approved',0);
			}	
		}
		if ($this->params['employee_id'])
		{
			$this->db->where('employee_requested_person_id', $this->params['employee_id']);
		}
		if (!isset($this->params['is_view_only_self']) || $this->params['is_view_only_self'] == false)
		{
			$this->db->order_by('employees_time_off.id');			
		}
		else
		{
			$this->db->order_by('employees_time_off.id DESC');
		}
		
		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			
			if (isset($this->params['offset']))
			{
				$this->db->offset($this->params['offset']);
			}
		}
		
		return $this->db->get()->result_array();

	}
	
	function getTotalRows()
	{
		$current_location=$this->Employee->get_logged_in_employee_current_location_id();
		
		$this->db->from('employees_time_off');
		if ($this->params['employee_id'])
		{
			$this->db->where('employee_requested_person_id', $this->params['employee_id']);
		}
		
		if (isset($this->params['show_approved']) && $this->params['show_approved'])
		{
			$this->db->where('approved',1);
		}
		else
		{
			$this->db->where('approved',0);
		}
		
		$this->db->where('employee_requested_location_id', $current_location);
		
		$this->db->order_by('id');
		
		return $this->db->count_all_results();
		
	}
	
	public function getSummaryData()
	{
		$current_location=$this->Employee->get_logged_in_employee_current_location_id();
		$this->db->select('SUM(hours_requested) as hours', false);
		$this->db->from('employees_time_off');
		$this->db->where('employee_requested_location_id', $current_location);
		
		$this->db->where('start_day >=', $this->params['start_date']);
		$this->db->where('start_day <=', $this->params['end_date']);
		
		if (!isset($this->params['is_view_only_self']))
		{
			$this->db->where('employees_time_off.deleted',0);
			
			if (isset($this->params['show_approved']) && $this->params['show_approved'])
			{
				$this->db->where('approved',1);
			}
			else
			{
				$this->db->where('approved',0);
			}
			if ($this->params['employee_id'])
			{
				$this->db->where('employee_requested_person_id', $this->params['employee_id']);
			}
		}		
		return $this->db->get()->row_array();
	}
}
?>