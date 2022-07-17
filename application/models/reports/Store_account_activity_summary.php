<?php
require_once ("Report.php");
class Store_account_activity_summary extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		$return = array(
		array('data'=>lang('reports_customer'), 'align'=> 'left'),
		array('data'=>lang('reports_debits'), 'align'=> 'left'),
		array('data'=>lang('reports_credits'), 'align'=> 'left'));		
		
		return $return;
		
	}
	
	public function getInputData()
	{
		$input_data = Report::get_common_report_input_data(TRUE);
		
		$input_params = array();

		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array(
				array('view' => 'date_range', 'with_time' => TRUE),
				array('view' => 'locations'),
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
		$report_data = $this->getData();
		$location_count = $this->Location->count_all();

		foreach($report_data as $row)
		{
			$tab_row = array(array('data'=>$row['first_name'].' '.$row['last_name'], 'align'=> 'left'),
									array('data'=> to_currency($row['debits']), 'align'=> 'right'),
									array('data'=> to_currency($row['credits']), 'align'=> 'right'));
									
				if ($location_count > 1)
				{
					array_unshift($tab_row,array('data'=>$row['location'], 'align'=> 'left'));
				}

				$tabular_data[] = $tab_row;					
									
		}

		$data = array(
			"view" => 'tabular',
			"title" => lang('reports_store_account_activity_summary_report'),
			"subtitle" => date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date'])),
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
		
		$this->db->select('SUM(IF(transaction_amount > 0, `transaction_amount`, 0)) as debits, SUM(IF(transaction_amount < 0, `transaction_amount`, 0)) as credits,store_accounts.*, people.first_name, people.last_name,locations.name as location');
		$this->db->from('store_accounts');
		$this->db->join('sales', 'sales.sale_id = store_accounts.sale_id', 'left');
		$this->db->join('locations', 'sales.location_id = locations.location_id', 'left');
		$this->db->join('customers', 'customers.person_id = store_accounts.customer_id');
		$this->db->join('people', 'customers.person_id = people.person_id');
		$this->db->where('date BETWEEN "'.$this->params['start_date'].'" and "'.$this->params['end_date'].'"');
		$this->db->where_in('sales.location_id',$location_ids);
		$this->db->group_by('customers.person_id');
		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			
			if (isset($this->params['offset']))
			{
				$this->db->offset($this->params['offset']);
			}
		}
		
		$result = $this->db->get()->result_array();
		
		return $result;
	}
	
	public function getTotalRows()
	{
		$location_ids = self::get_selected_location_ids();
		
		$this->db->from('store_accounts');
		$this->db->join('sales', 'sales.sale_id = store_accounts.sale_id', 'left');
		$this->db->where('date BETWEEN "'.$this->params['start_date'].'" and "'.$this->params['end_date'].'"');
		$this->db->where_in('sales.location_id',$location_ids);
		
		return $this->db->count_all_results();
	}
	
	
	public function getSummaryData()
	{
		$location_ids = self::get_selected_location_ids();
		
		$this->db->select("SUM(IF(transaction_amount > 0, `transaction_amount`, 0)) as debits, SUM(IF(transaction_amount < 0, `transaction_amount`, 0)) as credits", false);
		$this->db->from('store_accounts');
		$this->db->join('sales', 'sales.sale_id = store_accounts.sale_id', 'left');
		$this->db->join('customers', 'customers.person_id = store_accounts.customer_id');
		$this->db->join('people', 'customers.person_id = people.person_id');
		$this->db->where('date BETWEEN "'.$this->params['start_date'].'" and "'.$this->params['end_date'].'"');
		$this->db->where_in('sales.location_id',$location_ids);
		
		$return = $this->db->get()->row_array();
		
		$this->db->select('SUM(balance) as total_balance_of_all_store_accounts', false);
		$this->db->from('customers');		
		$result = $this->db->get()->row_array();
		
		$return = array_merge($return, $result);
		return $return;
		
	}
}
?>