<?php
require_once ("Report.php");
class Summary_giftcards extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array(array('data'=>lang('common_giftcards_giftcard_number'), 'align'=>'left'),array('data'=>lang('common_description'), 'align'=> 'left'), array('data'=>lang('common_giftcards_card_value'), 'align'=> 'left'), array('data'=>lang('reports_sales_generator_selectField_1'), 'align'=> 'left'));
	}
	
	public function getInputData()
	{
		$input_params = array();

		if ($this->settings['display'] == 'tabular')
		{
			$input_data = Report::get_common_report_input_data(FALSE);
			
			$input_params = array(
				array('view' => 'excel_export'),
				array('view' => 'submit'),
			);
		}
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}
	
	
	function getOutputData()
	{
		$this->setupDefaultPagination();
		
		$tabular_data = array();
		$report_data = $this->getData();
		foreach($report_data as $row)
		{
			$tabular_data[] = array(array('data'=>$row['giftcard_number'], 'align'=> 'left'), array('data'=>$row['description'], 'align'=> 'left'),array('data'=>to_currency($row['value']), 'align'=> 'left'), array('data'=>$row['customer_name'].(isset($row['account_number']) && $row['account_number'] ? ' ('.$row['account_number'].')' : ''), 'align'=> 'left'));
		}

		$data = array(
			"view" => 'tabular',
			"title" => lang('reports_giftcard_summary_report'),
			"subtitle" => '',
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
		$this->db->select('customer_data.account_number as account_number, giftcard_number, description, value, CONCAT(first_name, " ",last_name) as customer_name', false);
		$this->db->from('giftcards');
		$this->db->where('giftcards.deleted', 0);
		$this->db->join('people', 'giftcards.customer_id = people.person_id', 'left');
		$this->db->join('customers as customer_data', 'people.person_id = customer_data.person_id', 'left');
		$this->db->order_by('giftcard_number');

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
		$this->db->select('SUM(value) as total_liabilities', false);
		$this->db->from('giftcards');
		$this->db->where('deleted', 0);
		return $this->db->get()->row_array();		
	}
	
	function getTotalRows()
	{
		$this->db->from('giftcards');
		$this->db->where('deleted', 0);
		$this->db->join('people', 'giftcards.customer_id = people.person_id', 'left');
		$this->db->order_by('giftcard_number');
		
		return $this->db->count_all_results();
	}
	
}
?>