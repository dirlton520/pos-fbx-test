<?php
require_once ("Report.php");
class Voided_transactions extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		$columns = array();
		
		$columns[] = array('data'=>lang('common_date'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_employee'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_sale_id'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_transaction_voided'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_transaction_voided_transacion_id'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_amount_returned'), 'align'=> 'left');
		
		return $columns;
	}
	
	public function getInputData()
	{
		
		$input_params = array();

		if ($this->settings['display'] == 'tabular')
		{
			$input_data = Report::get_common_report_input_data(TRUE);
			
			$specific_entity_data = array();
			$specific_entity_data['view']  = 'specific_entity';
			$specific_entity_data['specific_input_name'] = 'employee_id';
			$specific_entity_data['specific_input_label'] = lang('reports_employee');
			$employees = array();
			$employees['all'] = lang('reports_all');

			foreach($this->Employee->get_all()->result() as $employee)
			{
				$employees[$employee->person_id] = $employee->first_name .' '.$employee->last_name;
			}
			$specific_entity_data['specific_input_data'] = $employees;
			
			$input_params = array(
				array('view' => 'date_range', 'with_time' => TRUE,'date_range_label' => ''),
				$specific_entity_data,
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
				$tabular_data[] = array(
					array('data'=>date(get_date_format().' '.get_time_format(),strtotime($row['return_time'])), 'align'=>'left'),
					array('data'=>$row['employee'], 'align'=>'left'),
					array('data'=>$row['sale_id'] ? anchor('sales/receipt/'.$row['sale_id'],$this->config->item('sale_prefix').' '.$row['sale_id'],array('target' => '_blank')) : lang('common_none'), 'align'=>'left'),
					array('data'=>$row['orig_voided_processor_transaction_id'], 'align'=>'left'),
					array('data'=>$row['voided_processor_transaction_id'], 'align'=>'left'),
					array('data'=>to_currency($row['amount']), 'align'=>'left'),
				);
			}

			$data = array(
				"view" => 'tabular',
				"title" => lang('reports_voided_returned_transactions'),
				"subtitle" => date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date'])),
				"headers" => $this->getDataColumns(),
				"data" => $tabular_data,
				"summary_data" => $summary_data,
				"export_excel" => $this->params['export_excel'],
				"pagination" => $this->pagination->create_links(),
			);
		}
		
		return $data;
	}
	
	private function _base_query()
	{
		$location_ids = self::get_selected_location_ids();
		
		$this->db->select('processing_return_logs.return_time,CONCAT(phppos_people.first_name, " ",phppos_people.last_name) as employee, processing_return_logs.sale_id, orig_voided_processor_transaction_id,voided_processor_transaction_id,processing_return_logs.amount', FALSE);
		$this->db->from('processing_return_logs');
		$this->db->join('sales','sales.sale_id=processing_return_logs.sale_id','left');
		$this->db->join('employees','employees.person_id=processing_return_logs.employee_id');
		$this->db->join('people','employees.person_id=people.person_id');
		$this->db->where('processing_return_logs.return_time BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']));
		$this->db->group_start();
		$this->db->group_start();
		$this->db->where_in('sales.location_id',$location_ids);
		$this->db->where('processing_return_logs.sale_id IS NOT NULL');
		$this->db->group_end();
		$this->db->or_where('processing_return_logs.sale_id IS NULL');
		$this->db->group_end();
		
		if (isset($this->params['employee_id']) && $this->params['employee_id'] != 'all')
		{
			$this->db->where('processing_return_logs.employee_id',$this->params['employee_id']);
		}
		
		$this->db->order_by('processing_return_logs.return_time', ($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');
	}
	public function getData()
	{
		
		$return = array();
		$this->_base_query();
		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
		
			if (isset($this->params['offset']))
			{
				$this->db->offset($this->params['offset']);
			}
		}
		
		$return = $this->db->get()->result_array();
		
		return $return;
	}
	
	function getTotalRows()
	{	
		$this->_base_query();
		return $this->db->count_all_results();
	}
	
	function getSummaryData()
	{
		return array();
	}
	
}
?>