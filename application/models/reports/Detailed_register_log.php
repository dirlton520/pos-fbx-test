<?php
require_once ("Report.php");
class Detailed_register_log extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		$return = array();
		
		$return['summary'] = array();
		if($this->Employee->has_module_action_permission('reports', 'delete_register_log', $this->Employee->get_logged_in_employee_info()->person_id))
		{
			$return['summary'][] = array('data'=>lang('common_delete'), 'align'=> 'left');
		}
		$return['summary'][] = array('data'=>lang('common_det'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_register'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_employee_open'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_close_employee'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_shift_start'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_shift_end'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_open_amount'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_close_amount'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_sales'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_total_additions'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_total_subtractions'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_difference'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_notes'), 'align'=> 'left');
				

		$return['details'] = array();
		$return['details'][] = array('data'=>lang('reports_payment_type'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('common_open_amount'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('reports_close_amount'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('common_sales'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('common_total_additions'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('common_total_subtractions'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('reports_difference'), 'align'=> 'left');
		
		return $return;
	}
	
	public function getInputData()
	{
		$input_data = Report::get_common_report_input_data(FALSE);
		
		$input_params = array();

		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array(
				array('view' => 'date_range', 'with_time' => FALSE, 'end_date_end_of_day' => FALSE),
				array('view' => 'excel_export'),
				array('view' => 'submit'),
			);
		}
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}
	
	public function getOutputData()
	{
		$this->setupDefaultPagination();
		$start_date = $this->params['start_date'];
		$end_date = $this->params['end_date'];
		$export_excel = $this->params['export_excel'];
		$headers = $this->getDataColumns();
		$report_data = $this->getData();
		
		$summary_data = array();
			
		foreach($report_data['summary'] as $row)
		{
			$details ='';
			$delete = '';
			
			if($row['shift_end']=='0000-00-00 00:00:00')
			{
				$shift_end='<span class="text-danger">'.lang('reports_register_log_open').'</span>';
				if($this->Employee->has_module_action_permission('reports', 'delete_register_log', $this->Employee->get_logged_in_employee_info()->person_id))
				{
					$delete=anchor('reports/delete_register_log/'.$row['register_log_id'], lang('common_delete'), 
					"onclick='return do_link_confirm(".json_encode(lang('reports_confirm_register_log_delete')).", this)'");
				}
			}
			else
			{
				$shift_end=date(get_date_format(), strtotime($row['shift_end'])) .' '.date(get_time_format(), strtotime($row['shift_end']));
				
				if($this->Employee->has_module_action_permission('reports', 'delete_register_log', $this->Employee->get_logged_in_employee_info()->person_id))
				{
					$delete=anchor('reports/delete_register_log/'.$row['register_log_id'], lang('common_delete'), 
					"onclick='return do_link_confirm(".json_encode(lang('reports_confirm_register_log_delete')).", this)'");
				}
			}
			
			if($this->Employee->has_module_action_permission('reports', 'edit_register_log', $this->Employee->get_logged_in_employee_info()->person_id))
			{
				$details = anchor('reports/edit_register_log/'.$row['register_log_id'], lang('common_edit')).', ';				
			}
		
			$details .= anchor('reports/register_log_details/'.$row['register_log_id'], lang('common_det')); 
			
			$summary_data[$row['register_log_id']] = array(
				array('data'=>$delete, 'align'=>'left'), 
				array('data'=>$details, 'align'=>'left'), 
				array('data'=>$row['register_name'], 'align'=>'left'), 
				array('data'=>$row['open_first_name'] . ' ' . $row['open_last_name'], 'align'=>'left'), 
				array('data'=>$row['close_first_name'] . ' ' . $row['close_last_name'], 'align'=>'left'), 
				array('data'=>date(get_date_format(), strtotime($row['shift_start'])) .' '.date(get_time_format(), strtotime($row['shift_start'])), 'align'=>'left'), 
				array('data'=>$shift_end, 'align'=>'left'), 
				array('data'=>to_currency($row['open_amount']), 'align'=>'right'), 
				array('data'=>to_currency($row['close_amount']), 'align'=>'right'), 
				array('data'=>to_currency($row['payment_sales_amount']), 'align'=>'right'),
				array('data'=>to_currency($row['total_payment_additions']), 'align'=>'right'),
				array('data'=>to_currency($row['total_payment_subtractions']), 'align'=>'right'),
				array('data'=>to_currency($row['difference']), 'align'=>'right'),
				array('data'=>$row['notes'], 'align'=>'left')
			);
		}

		foreach($report_data['details'] as $register_log_id => $register_logs)
		{
			foreach($register_logs as $row)
			{
				$details_data[$register_log_id][] =  array(
					array('data'=>strpos($row['payment_type'],'common_') !== FALSE ? lang($row['payment_type']) : $row['payment_type'], 'align'=>'left'), 
					array('data'=>to_currency($row['open_amount']), 'align'=>'right'), 
					array('data'=>to_currency($row['close_amount']), 'align'=>'right'), 
					array('data'=>to_currency($row['payment_sales_amount']), 'align'=>'right'),
					array('data'=>to_currency($row['total_payment_additions']), 'align'=>'right'),
					array('data'=>to_currency($row['total_payment_subtractions']), 'align'=>'right'),
					array('data'=>to_currency($row['difference']), 'align'=>'right'),
				);
			}
		}
		$data = array(
			"view" => 'tabular_details',
			"title" =>lang('reports_register_log_title'),
			"subtitle" => date(get_date_format(), strtotime($start_date)) .'-'.date(get_date_format(), strtotime($end_date)),
			"headers" => $this->getDataColumns(),
			"summary_data" => $summary_data,
			"overall_summary_data" => $this->getSummaryData(),
			"export_excel" => $export_excel,
			"pagination" => $this->pagination->create_links(),
		);
		
		isset($details_data) && !empty($details_data) ? $data["details_data"]=$details_data: '' ;
		
		return $data;
	}
	
	public function getData()
	{
		$location_id=isset($this->params['override_location_id']) ? $this->params['override_location_id'] : $this->Employee->get_logged_in_employee_current_location_id();
		
		$between = 'between ' . $this->db->escape($this->params['start_date'] . ' 00:00:00').' and ' . $this->db->escape($this->params['end_date'] . ' 23:59:59');
		$this->db->select("SUM(open_amount) as open_amount, SUM(close_amount) as close_amount,SUM(payment_sales_amount) as payment_sales_amount,SUM(total_payment_additions) as total_payment_additions,SUM(total_payment_subtractions) as total_payment_subtractions,SUM(close_amount - open_amount - payment_sales_amount - total_payment_additions + total_payment_subtractions) as difference,registers.name as register_name, open_person.first_name as open_first_name, open_person.last_name as open_last_name, close_person.first_name as close_first_name, close_person.last_name as close_last_name, register_log.*");
		$this->db->from('register_log as register_log');
		$this->db->join('register_log_payments','register_log.register_log_id = register_log_payments.register_log_id');
		$this->db->join('people as open_person', 'register_log.employee_id_open=open_person.person_id');
		$this->db->join('people as close_person', 'register_log.employee_id_close=close_person.person_id', 'left');
		$this->db->join('registers', 'registers.register_id = register_log.register_id');
		$this->db->where('register_log.shift_start ' . $between);
		$this->db->where('register_log.deleted ', 0);
		$this->db->where('registers.location_id', $location_id);
		$this->db->group_by('register_log.register_log_id');
		
		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			
			if (isset($this->params['offset']))
			{
				$this->db->offset($this->params['offset']);
			}
		}
		
		$summary = $this->db->get()->result_array();
		
		$register_log_ids = array();
		foreach($summary as $row)
		{
			$register_log_ids[] = $row['register_log_id'];
		}
		
		$this->db->select("payment_type,SUM(open_amount) as open_amount, SUM(close_amount) as close_amount,SUM(payment_sales_amount) as payment_sales_amount,SUM(total_payment_additions) as total_payment_additions,SUM(total_payment_subtractions) as total_payment_subtractions,SUM(close_amount - open_amount - payment_sales_amount - total_payment_additions + total_payment_subtractions) as difference,registers.name as register_name, open_person.first_name as open_first_name, open_person.last_name as open_last_name, close_person.first_name as close_first_name, close_person.last_name as close_last_name, register_log.*");
		$this->db->from('register_log as register_log');
		$this->db->join('register_log_payments','register_log.register_log_id = register_log_payments.register_log_id');
		$this->db->join('people as open_person', 'register_log.employee_id_open=open_person.person_id');
		$this->db->join('people as close_person', 'register_log.employee_id_close=close_person.person_id', 'left');
		$this->db->join('registers', 'registers.register_id = register_log.register_id');
		$this->db->where('register_log.deleted ', 0);
		$this->db->where('registers.location_id', $location_id);
		$this->db->group_by('register_log_payments.id');
		
		if (!empty($register_log_ids))
		{
			$this->db->group_start();
			$regster_log_chunk = array_chunk($register_log_ids,25);
			foreach($regster_log_chunk as $register_log_ids)
			{
				$this->db->or_where_in('register_log.register_log_id',$register_log_ids);
			}	
			$this->db->group_end();
		}
		$details = $this->db->get()->result_array();
		
		$register_log_details = array();
		foreach($details as $row)
		{
			$register_log_details[$row['register_log_id']][] = $row;
		}
		
		return array('summary' => $summary, 'details' => $register_log_details);
	}
	
	public function getTotalRows()
	{
		$location_id=isset($this->params['override_location_id']) ? $this->params['override_location_id'] : $this->Employee->get_logged_in_employee_current_location_id();
		
		$between = 'between ' . $this->db->escape($this->params['start_date'] . ' 00:00:00').' and ' . $this->db->escape($this->params['end_date'] . ' 23:59:59');
		$this->db->from('register_log as register_log');
		$this->db->join('people as open_person', 'register_log.employee_id_open=open_person.person_id');
		$this->db->join('people as close_person', 'register_log.employee_id_close=close_person.person_id', 'left');
		$this->db->join('registers', 'registers.register_id = register_log.register_id');
		$this->db->where('register_log.shift_start ' . $between);
		$this->db->where('register_log.deleted ', 0);
		$this->db->where('registers.location_id', $location_id);
		
		return $this->db->count_all_results();
	}
	
	
	public function getSummaryData() 
	{
		$location_id=isset($this->params['override_location_id']) ? $this->params['override_location_id'] : $this->Employee->get_logged_in_employee_current_location_id();
		$between = 'between ' . $this->db->escape($this->params['start_date'] . ' 00:00:00').' and ' . $this->db->escape($this->params['end_date'] . ' 23:59:59');
		$this->db->select("SUM(payment_sales_amount) as payment_sales_amount,SUM(close_amount - open_amount - payment_sales_amount - total_payment_additions + total_payment_subtractions) as difference");
		$this->db->from('register_log as register_log');
		$this->db->join('register_log_payments','register_log.register_log_id = register_log_payments.register_log_id');
		$this->db->join('people as open_person', 'register_log.employee_id_open=open_person.person_id');
		$this->db->join('people as close_person', 'register_log.employee_id_close=close_person.person_id', 'left');
		$this->db->join('registers', 'registers.register_id = register_log.register_id');
		$this->db->where('register_log.shift_start ' . $between);
		$this->db->where('register_log.deleted ', 0);
		$this->db->where('registers.location_id', $location_id);
		$this->db->group_by('register_log.register_log_id');
		
		$data = $this->db->get()->result_array();
		
		$overallSummaryData = array(
			'total_sales'=>0,
			'total_shortages'=>0,
			'total_overages'=>0,
			'total_difference'=>0
		);
		
		foreach($data as $row)
		{
			$overallSummaryData['total_sales'] += $row['payment_sales_amount'];
			if ($row['difference'] > 0) {
				$overallSummaryData['total_overages'] += $row['difference'];
			} else {
				$overallSummaryData['total_shortages'] += $row['difference'];
			}
		
			$overallSummaryData['total_difference'] += $row['difference'];
		}
		
		return $overallSummaryData;
	}
		
	public function delete_register_log($register_log_id)
	{	
		$this->db->where('register_log_id', $register_log_id);
		$this->db->update('register_log', array('deleted' => 1));
		
		$this->db->where('register_log_id', $register_log_id);
		$this->db->where('shift_end','0000-00-00 00:00:00');
		return $this->db->update('register_log', array('shift_end' => date('Y-m-d H:i:s')));
		
	}
}
?>