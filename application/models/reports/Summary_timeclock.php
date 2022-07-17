<?php
require_once ("Report.php");
class Summary_timeclock extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		
		$columns = array();
		
		$columns[] = array('data'=>lang('reports_employee'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_hours'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_total'), 'align'=> 'left');

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
			$input_params[] = array('view' => 'locations');
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
			
			$data_row[] = array('data'=>$row['first_name'].' '.$row['last_name'], 'align' => 'left');
			$data_row[] = array('data'=>$row['hours'], 'align' => 'left');			
			$data_row[] = array('data'=>to_currency($row['total']), 'align' => 'left');			
			$tabular_data[] = $data_row;			
		}

		$employee_info = $this->Employee->get_info($employee_id);

		$data = array(
			"view" => 'tabular',
			"title" => ($employee_id ? $employee_info->first_name . ' '.$employee_info->last_name . ' - ' : ' ').lang('reports_summary_timeclock_report'),
			"subtitle" => date(get_date_format(), strtotime($start_date)) .'-'.date(get_date_format(), strtotime($end_date)),
			"headers" => $this->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $this->getSummaryData(),
			"export_excel" => $this->params['export_excel'],
			"pagination" => false,
		);

		return $data;
	}
	
	public function getData()
	{
		$location_ids = self::get_selected_location_ids();
		$this->db->select('employees_time_clock.*, people.first_name, people.last_name');
		$this->db->from('employees_time_clock');
		$this->db->where('clock_in >=', $this->params['start_date']);
		$this->db->where('clock_in <=', $this->params['end_date']);
		$this->db->where_in('location_id', $location_ids);
		$this->db->join('employees', 'employees.person_id = employees_time_clock.employee_id');
		$this->db->join('people', 'people.person_id = employees.person_id');
		if ($this->params['employee_id'])
		{
			$this->db->where('employee_id', $this->params['employee_id']);
		}
		$this->db->order_by('people.first_name');
				
		$data = $this->db->get()->result_array();
		
		$return = array();
		foreach($data as $row)
		{
			if (!isset($return[$row['employee_id']]))
			{
				$return[$row['employee_id']] = array('first_name' => $row['first_name'], 'last_name' => $row['last_name'], 'hours' => 0, 'total' => 0);
			}
			
			if ($row['clock_out'] != '0000-00-00 00:00:00')
			{
				$data_row[] = array('data'=>date(get_date_format().' '.get_time_format(), strtotime($row['clock_out'])), 'align' => 'left');
				$t1 = strtotime ($row['clock_out']);
				$t2 = strtotime ($row['clock_in']);
				$diff = $t1 - $t2;
				$hours = $diff / ( 60 * 60 );
				
				//Not really the purpose of this function; but it rounds to 2 decimals
				$hours = to_currency_no_money($hours,2);
				$return[$row['employee_id']]['hours']+=$hours;
				$return[$row['employee_id']]['total']+=$hours * $row['hourly_pay_rate'];
			}			 
		}
		return array_values($return);
	}
	
	function getTotalRows()
	{
		$location_ids = self::get_selected_location_ids();
		
		$this->db->from('employees_time_clock');
		$this->db->where('clock_in >=', $this->params['start_date']);
		$this->db->where('clock_in <=', $this->params['end_date']);
		$this->db->where_in('location_id', $location_ids);
		
		if ($this->params['employee_id'])
		{
			$this->db->where('employee_id', $this->params['employee_id']);
		}
		
		$this->db->order_by('id');
		$this->db->group_by('employee_id');
		return($this->db->get()->num_rows());
	}
	
	public function getSummaryData()
	{
		return array();
	}
}
?>