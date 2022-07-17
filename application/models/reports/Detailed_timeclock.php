<?php
require_once ("Report.php");
class Detailed_timeclock extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		
		$columns = array();
		if (!isset($this->params['is_view_only_self']) || $this->params['is_view_only_self'] == false)
		{
			$columns[] = array('data'=>lang('common_edit'), 'align'=> 'left');
			$columns[] = array('data'=>lang('common_delete'), 'align'=> 'left');
			$columns[] = array('data'=>lang('reports_employee'), 'align'=> 'left');
		}
		$columns[] = array('data'=>lang('common_clock_in'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_clock_out'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_hours'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_hourly_pay_rate'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_total'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_clock_in_comment'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_clock_out_comment'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_ip_address_clock_in'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_ip_address_clock_out'), 'align'=> 'left');
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

			$edit=anchor('timeclocks/view/'.$row['id'].'/?'.$_SERVER['QUERY_STRING'], lang('common_edit'));
			
			$delete=anchor('timeclocks/delete/'.$row['id'].'?'.$_SERVER['QUERY_STRING'], lang('common_delete'), 
			"onclick='return do_link_confirm(".json_encode(lang('reports_confirm_timeclock_delete')).", this)'");

			$data_row[] = array('data'=>$edit, 'align' => 'left');
			$data_row[] = array('data'=>$delete, 'align' => 'left');
			$data_row[] = array('data'=>$row['first_name'].' '.$row['last_name'], 'align' => 'left');
			$data_row[] = array('data'=>date(get_date_format().' '.get_time_format(), strtotime($row['clock_in'])), 'align' => 'left');
			
			if ($row['clock_out'] != '0000-00-00 00:00:00')
			{
				$data_row[] = array('data'=>date(get_date_format().' '.get_time_format(), strtotime($row['clock_out'])), 'align' => 'left');
				$t1 = strtotime ($row['clock_out']);
				$t2 = strtotime ($row['clock_in']);
				$diff = $t1 - $t2;
				$hours = $diff / ( 60 * 60 );
				
				//Not really the purpose of this function; but it rounds to 2 decimals
				$hours = to_currency_no_money($hours,2);	
			}
			else
			{
				$data_row[] = array('data'=>lang('reports_not_clocked_out'), 'align' => 'left');
				$hours = lang('reports_not_clocked_out');				
			}
			
			$data_row[] = array('data'=>$hours, 'align' => 'left');			
			$data_row[] = array('data'=>to_currency($row['hourly_pay_rate']), 'align' => 'left');			
			$data_row[] = array('data'=>to_currency((float)$row['hourly_pay_rate'] * (float)$hours), 'align' => 'left');			
			$data_row[] = array('data'=>$row['clock_in_comment'], 'align' => 'left');			
			$data_row[] = array('data'=>$row['clock_out_comment'], 'align' => 'left');
			$data_row[] = array('data'=>$row['ip_address_clock_in'], 'align' => 'left');
			$data_row[] = array('data'=>$row['ip_address_clock_out'], 'align' => 'left');
					
			$tabular_data[] = $data_row;			
		}

		$employee_info = $this->Employee->get_info($employee_id);

		$data = array(
			"view" => 'tabular',
			"title" => ($employee_id ? $employee_info->first_name . ' '.$employee_info->last_name . ' - ' : ' ').lang('reports_detailed_timeclock_report'),
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
		if (!isset($this->params['is_view_only_self']) || $this->params['is_view_only_self'] == false)
		{
			$this->db->order_by('employees_time_clock.id');			
		}
		else
		{
			$this->db->order_by('employees_time_clock.id DESC');
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
		
		return $this->db->count_all_results();
		
	}
	
	public function getSummaryData()
	{
		$location_ids = self::get_selected_location_ids();
		
		$this->db->from('employees_time_clock');
		$this->db->where('clock_in >=', $this->params['start_date']);
		$this->db->where('clock_in <=', $this->params['end_date']);
		$this->db->where('clock_out !=','0000-00-00 00:00:00');
		$this->db->where_in('location_id', $location_ids);
		$this->db->join('employees', 'employees.person_id = employees_time_clock.employee_id');
		$this->db->join('people', 'people.person_id = employees.person_id');
		if ($this->params['employee_id'])
		{
			$this->db->where('employee_id', $this->params['employee_id']);
		}
		$result =  $this->db->get()->result_array();
		
		$return = array('hours' => 0, 'total' => 0);
		foreach($result as $row)
		{
			$t1 = strtotime ($row['clock_out']);
			$t2 = strtotime ($row['clock_in']);
			$diff = $t1 - $t2;
			$hours = $diff / ( 60 * 60 );
		
			//Not really the purpose of this function; but it rounds to 2 decimals
			$hours = to_currency_no_money($hours,2);
			$return['hours']+=$hours;
			$return['total']+=$hours * $row['hourly_pay_rate'];
		}
		
		return $return;
	}
}
?>