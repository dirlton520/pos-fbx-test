<?php
require_once ("Report.php");
class Detailed_appointments extends Report
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Appointment');
		$this->lang->load('appointments');
	}
	
	
	public function getInputData()
	{		
		$input_params = array();

		$specific_entity_data = array();
		$specific_entity_data['view']  = 'specific_entity';
		$specific_entity_data['specific_input_name'] = 'employee_id';
		$specific_entity_data['specific_input_label'] = lang('common_employee');
		$employees = array('' => lang('common_all'));

		foreach($this->Employee->get_all()->result() as $employee)
		{
			$employees[$employee->person_id] = $employee->first_name .' '.$employee->last_name;
		}
		$specific_entity_data['specific_input_data'] = $employees;
		
		$specific_entity_data_person = array();
		$specific_entity_data_person['specific_input_name'] = 'person_id';
		$specific_entity_data_person['specific_input_label'] = lang('common_person_name');
		$specific_entity_data_person['search_suggestion_url'] = site_url('reports/person_search/0');
		$specific_entity_data_person['view'] = 'specific_entity';
		
		if ($this->settings['display'] == 'tabular')
		{
			$input_data = Report::get_common_report_input_data(TRUE);
			
			$input_params = array(
				array('view' => 'date_range', 'with_time' => TRUE),
				$specific_entity_data,
				$specific_entity_data_person,
				array('view' => 'excel_export'),
				array('view' => 'locations'),
				array('view' => 'submit'),
			);
		}
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}
	
	function getOutputData()
	{
		$subtitle = date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date']));

		$report_data = $this->getData();
		$summary_data = $this->getSummaryData();
		
		if ($this->settings['display'] == 'tabular')
		{				
			$this->setupDefaultPagination();
			$tabular_data = array();
			foreach($report_data as $row)
			{
				$data_row = array();
			
				$data_row[] = array('data'=>date(get_date_format().' '.get_time_format(), strtotime($row['start_time'])), 'align' => 'left');
				$data_row[] = array('data'=>date(get_date_format().' '.get_time_format(), strtotime($row['end_time'])), 'align' => 'left');
				$data_row[] = array('data'=>$row['person'], 'align' => 'left');
				$data_row[] = array('data'=>$row['employee'], 'align' => 'left');
				$data_row[] = array('data'=>$row['type'], 'align' => 'left');
				$data_row[] = array('data'=>$row['notes'], 'align' => 'left');
				
				$tabular_data[] = $data_row;				
			}
					
	 		$data = array(
				'view' => 'tabular',
				"title" => lang('reports_appointments_detailed_report'),
				"subtitle" => $subtitle,
				"headers" => $this->getDataColumns(),
				"data" => $tabular_data,
				"summary_data" => $summary_data,
				"export_excel" => $this->params['export_excel'],
				"pagination" => $this->pagination->create_links(),
			);
			
		}
		
		return $data;
	}
	
	public function getDataColumns()
	{
		$columns = array();
		
		$columns[] = array('data'=>lang('appointments_start_date'), 'align'=> 'left');
		$columns[] = array('data'=>lang('appointments_end_date'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_person_name'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_employee'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_category'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_notes'), 'align'=> 'left');
		
		return $columns;		
	}
	
	public function getData()
	{	
		
		$location_ids = Report::get_selected_location_ids();
		$location_ids = implode(',',$location_ids);
	
		$where = 'start_time BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']).' and '.$this->db->dbprefix('appointments').'.location_id IN ('.$location_ids.')';
		
		$this->db->select('appointment_types.name as type,appointments.*, person.*,CONCAT(employee.first_name, " ", employee.last_name) as employee, CONCAT(person.first_name, " ", person.last_name) as person, person.last_name as person_last_name', false);
		$this->db->from('appointments');
		$this->db->join('appointment_types', 'appointment_types.id = appointments.appointments_type_id','left');
		
		$this->db->join('people as person', 'person.person_id = appointments.person_id','left');
		$this->db->join('people as employee', 'employee.person_id = appointments.employee_id','left');

		$this->db->where('appointments.deleted', 0);
		
		$this->db->where($where);
		if (isset($this->params['employee_id']) && $this->params['employee_id'])
		{
			$this->db->where('appointments.employee_id', $this->params['employee_id']);
		}
		
		if (isset($this->params['person_id']) && $this->params['person_id'])
		{
			$this->db->where('appointments.person_id', $this->params['person_id']);
		}
		
		$this->db->order_by('start_time');
	
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
	
	public function getSummaryData()
	{
		return array();
	}
	
	function getTotalRows()
	{
		return false;
	}
}
?>