<?php
require_once ("Report.php");
class Giftcard_audit extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		
		$columns = array();		
		$columns[] = array('data'=>lang('reports_date'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_giftcard_number'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_description'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_comment'), 'align'=> 'left');

		return $columns;		
	}
	
	public function getInputData()
	{
		if ($this->settings['display'] == 'tabular')
		{
			$input_data = Report::get_common_report_input_data(TRUE);
			
			$input_params = array();
			
			$input_params[] = array('view' => 'date_range', 'with_time' => TRUE);
			$input_params[] = array('view' => 'giftcard_number');
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
		$start_date = $this->params['start_date'];
		$end_date = $this->params['end_date'];
		$headers = $this->getDataColumns();
		$report_data = $this->getData();
		$tabular_data = array();
		foreach($report_data as $row)
		{
			$row['log_message'] = strip_tags($row['log_message']);
			$row['log_message'] = preg_replace('/'.$this->config->item('sale_prefix').' ([0-9]+)/', anchor('sales/receipt/$1', $this->config->item('sale_prefix').' $1'), $row['log_message']);
			
			$tabular_data[] = array(
				array('data'=>date(get_date_format().' '.get_time_format(), strtotime($row['log_date'])), 'align'=> 'left'), 
				array('data'=>$row['giftcard_number'], 'align'=> 'left'), 
				array('data'=>$row['description'], 'align'=> 'left'), 
				array('data'=>$row['log_message'], 'align'=> 'left'), 
			);
		}
		
		
		$data = array(
			"view" => 'tabular',
			"title" => lang('reports_giftcard'). ' '.lang('reports_audit_report'),
			"subtitle" => date(get_date_format(), strtotime($start_date)) .'-'.date(get_date_format(), strtotime($end_date)),
			"headers" => $headers,
			"data" => $tabular_data,
			"summary_data" => $this->getSummaryData(),
			"export_excel" => $this->params['export_excel'],
			"pagination" => $this->pagination->create_links(),
		);
		
		return $data;
	}
	
	
	public function getData()
	{
		$data = array();
		
		$this->db->from('giftcards_log');
		$this->db->join('giftcards', 'giftcards.giftcard_id = giftcards_log.giftcard_id');
		
		if ($this->params['giftcard_number'] != '')
		{
			$this->db->where('giftcards.giftcard_number', $this->params['giftcard_number']);
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
		
		$this->db->where('log_date >=',$this->params['start_date']);
		$this->db->where('log_date <=',$this->params['end_date']);
		$this->db->order_by('log_date', ($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');

		return $this->db->get()->result_array();
	}
	
	public function getTotalRows()
	{
		$this->db->from('giftcards_log');
		$this->db->join('giftcards', 'giftcards.giftcard_id = giftcards_log.giftcard_id');
		
		if ($this->params['giftcard_number'] != '')
		{
			$this->db->where('giftcards.giftcard_number', $this->params['giftcard_number']);
		}
		
		$this->db->where('log_date >=',$this->params['start_date']);
		$this->db->where('log_date <=',$this->params['end_date']);

		return $this->db->count_all_results();
	}
	
	public function getSummaryData()
	{
		return array();
	}
}
?>