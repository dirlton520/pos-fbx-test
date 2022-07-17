<?php
require_once ("Report.php");
class Store_account_statements extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array();	
	}
	
	
	public function getInputData()
	{		
		$input_data = Report::get_common_report_input_data(TRUE);
		$specific_entity_data['specific_input_name'] = 'customer_id';
		$specific_entity_data['specific_input_label'] = lang('reports_customer');
		$specific_entity_data['search_suggestion_url'] = site_url('reports/customer_search/1');
		$specific_entity_data['view'] = 'specific_entity';
		
		
		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array();
			
			$input_params[] = $specific_entity_data;
			$input_params[] = array('view' => 'date_range', 'with_time' => FALSE,'end_date_end_of_day' => FALSE);
			$input_params[] = array('view' => 'checkbox','checkbox_label' => lang('reports_hide_items'), 'checkbox_name' => 'hide_items');
			$input_params[] = array('view' => 'checkbox','checkbox_label' => lang('reports_hide_paid'), 'checkbox_name' => 'hide_paid');
			$input_params[] = array('view' => 'dropdown','dropdown_label' =>lang('reports_pull_payments_by'),'dropdown_name' => 'pull_payments_by','dropdown_options' =>array('payment_date' => lang('reports_payment_date'), 'sale_date' => lang('common_sale_date')), 'dropdown_selected_value' => '');
			$input_params[] = array('view' => 'locations');
			$input_params[] = array('view' => 'submit');
		}
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}
	
	public function getOutputData()
	{
		$this->load->model('Sale');			
		$this->load->model('Customer');			
		$this->load->model('Category');
		
		$this->setupDefaultPagination();
		$report_data = $this->getData();
		$location_count = $this->Location->count_all();
		
		$total_amount_due = 0;
		
		$data = array(
			'total_amount_due' => $this->getSummaryData(),
			"view" => 'store_account_statements',
			"title" => lang('reports_store_account_statements'),
			"subtitle" => date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date'])),
			'location_count' => $location_count,
			'report_data' => $report_data,
			'hide_items' => isset($this->params['hide_items']) ? TRUE: FALSE,
			"pagination" => $this->pagination->create_links(),
			'date_column' => $this->params['pull_payments_by'] == 'payment_date' ? 'date' : 'sale_time',
		);
		
		return $data;
	}
	public function getData()
	{
		$this->load->model('Customer');
		$return = array();
		
		$customer_ids_for_report = array();
		$customer_id = $this->params['customer_id'];
		
		if (!$customer_id)
		{
			$this->db->select('person_id');
			$this->db->from('customers');
			$this->db->where('balance !=', 0);
			$this->db->where('deleted',0);
			$this->db->limit($this->report_limit);
			
			if (isset($this->params['offset']))
			{
				$this->db->offset($this->params['offset']);
			}
			$result = $this->db->get()->result_array();
			
			foreach($result as $row)
			{
				$customer_ids_for_report[] = $row['person_id'];
			}
		}
		else
		{
			$this->db->select('person_id');
			$this->db->from('customers');
			$this->db->where('person_id', $customer_id);
			$this->db->where('deleted',0);
			
			$result = $this->db->get()->row_array();
			
			if (!empty($result))
			{
				$customer_ids_for_report[] = $result['person_id'];
			}
		}
				
		foreach($customer_ids_for_report as $customer_id)
		{
			$this->db->select("store_accounts_paid_sales.partial_payment_amount,store_accounts.*,sales.sale_time,locations.name as location");
			$this->db->from('store_accounts');
			$this->db->where('store_accounts.customer_id', $customer_id);
			$this->db->join('sales', 'sales.sale_id = store_accounts.sale_id', 'left');
			$this->db->join('store_accounts_paid_sales','sales.sale_id=store_accounts_paid_sales.sale_id','left');
			$this->db->join('locations', 'sales.location_id = locations.location_id', 'left');
			$location_ids = self::get_selected_location_ids();
			$this->db->where_in('sales.location_id',$location_ids);
			
			if ($this->params['pull_payments_by'] == 'payment_date')
			{
				$this->db->where('date >=', $this->params['start_date']);
				$this->db->where('date <=', $this->params['end_date']. ' 23:59:59');				
				$this->db->order_by('date');
			}
			else
			{
				$this->db->where('sale_time >=', $this->params['start_date']);
				$this->db->where('sale_time <=', $this->params['end_date']. ' 23:59:59');
				$this->db->order_by('sale_time');
			}
						
			$result = $this->db->get()->result_array();
			
			//If we don't have results from this month, pull the last store account entry we have
			if (count($result) == 0)
			{
				$this->db->select("store_accounts.*,sales.sale_time,locations.name as location");
				$this->db->from('store_accounts');
				$this->db->where('store_accounts.customer_id', $customer_id);
				$this->db->join('sales', 'sales.sale_id = store_accounts.sale_id', 'left');
				$this->db->join('locations', 'sales.location_id = locations.location_id', 'left');
				$this->db->order_by('sno', 'DESC');		
				
				$location_ids = self::get_selected_location_ids();
				$this->db->where_in('sales.location_id',$location_ids);
				if ($this->params['pull_payments_by'] == 'payment_date')
				{
					$this->db->where('store_accounts.date <=', $this->params['end_date']. ' 23:59:59');				
				}
				else
				{
					$this->db->where('sale_time <=', $this->params['end_date']. ' 23:59:59');
				}
					
				$this->db->limit(1); 	
				$result = $this->db->get()->result_array();
			}
			
			for ($k=0;$k<count($result);$k++)
			{
				$item_names = array();
				$sale_id = $result[$k]['sale_id'];
				
				$this->db->select('name, sales_items.description');
				$this->db->from('items');
				$this->db->join('sales_items', 'sales_items.item_id = items.item_id');
				
				$this->db->where('sale_id', $sale_id);
				
				foreach($this->db->get()->result_array() as $row)
				{
					$item_name_and_desc = $row['name'];
				
					if ($row['description'])
					{
						$item_name_and_desc .= ' - '.$row['description'];
					}
					
					$item_names[] = $item_name_and_desc;
				}
				
				$this->db->select('name');
				$this->db->from('item_kits');
				$this->db->join('sales_item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id');
				$this->db->where('sale_id', $sale_id);
				
				foreach($this->db->get()->result_array() as $row)
				{
					$item_names[] = $row['name'];
				}
				
				$result[$k]['items'] = implode(', ', $item_names);
			}
			
			if (!empty($result))
			{
				$return[]= array('customer_info' => $this->Customer->get_info($customer_id),'store_account_transactions' => $result);
			}
		}
		
		return $return;
	}
	
	public function getTotalRows()
	{
		$customer_id = $this->params['customer_id'];
		
		if (!$customer_id)
		{
			$this->db->distinct();
			$this->db->select('store_accounts.customer_id');
			$this->db->from('store_accounts');
			$this->db->join('sales', 'sales.sale_id = store_accounts.sale_id');
			$this->db->where('balance !=', 0);
		}
		else
		{
			$this->db->distinct();
			$this->db->select('store_accounts.customer_id');
			$this->db->from('store_accounts');
			$this->db->join('sales', 'sales.sale_id = store_accounts.sale_id');
			$this->db->where('store_accounts.customer_id', $customer_id);
		}
		
		return $this->db->get()->num_rows();
	}
	
	//This gets total amount due
	public function getSummaryData()
	{
		
		$this->load->model('Customer');
		$return = array();
		
		$customer_ids_for_report = array();
		$customer_id = $this->params['customer_id'];
		
		if (!$customer_id)
		{
			$this->db->select('person_id');
			$this->db->from('customers');
			$this->db->where('balance !=', 0);
			$this->db->where('deleted',0);
			
			$result = $this->db->get()->result_array();
			
			foreach($result as $row)
			{
				$customer_ids_for_report[] = $row['person_id'];
			}
		}
		else
		{
			$this->db->select('person_id');
			$this->db->from('customers');
			$this->db->where('person_id', $customer_id);
			$this->db->where('deleted',0);
			
			$result = $this->db->get()->row_array();
			
			if (!empty($result))
			{
				$customer_ids_for_report[] = $result['person_id'];
			}
		}
				
		foreach($customer_ids_for_report as $customer_id)
		{
			$this->db->select("store_accounts.*,sales.sale_time,locations.name as location");
			$this->db->from('store_accounts');
			$this->db->where('store_accounts.customer_id', $customer_id);
			$this->db->join('sales', 'sales.sale_id = store_accounts.sale_id', 'left');
			$this->db->join('locations', 'sales.location_id = locations.location_id', 'left');
			
			$location_ids = self::get_selected_location_ids();
			$this->db->where_in('sales.location_id',$location_ids);
			
			if ($this->params['pull_payments_by'] == 'payment_date')
			{
				$this->db->where('date >=', $this->params['start_date']);
				$this->db->where('date <=', $this->params['end_date']. ' 23:59:59');				
				$this->db->order_by('date');
			}
			else
			{
				$this->db->where('sale_time >=', $this->params['start_date']);
				$this->db->where('sale_time <=', $this->params['end_date']. ' 23:59:59');
				$this->db->order_by('sale_time');
			}
			
			
			$result = $this->db->get()->result_array();
			
			//If we don't have results from this month, pull the last store account entry we have
			if (count($result) == 0)
			{
				$this->db->select("store_accounts.*,sales.sale_time,locations.name as location");
				$this->db->from('store_accounts');
				$this->db->where('store_accounts.customer_id', $customer_id);
				$this->db->join('sales', 'sales.sale_id = store_accounts.sale_id', 'left');
				$this->db->join('locations', 'sales.location_id = locations.location_id', 'left');
				$this->db->order_by('sno', 'DESC');		
				
				$location_ids = self::get_selected_location_ids();
				$this->db->where_in('sales.location_id',$location_ids);
				if ($this->params['pull_payments_by'] == 'payment_date')
				{
					$this->db->where('store_accounts.date <=', $this->params['end_date']. ' 23:59:59');				
				}
				else
				{
					$this->db->where('sale_time <=', $this->params['end_date']. ' 23:59:59');
				}
					
				$this->db->limit(1); 	
				$result = $this->db->get()->result_array();
			}
			
			for ($k=0;$k<count($result);$k++)
			{
				$item_names = array();
				$sale_id = $result[$k]['sale_id'];
				
				$this->db->select('name, sales_items.description');
				$this->db->from('items');
				$this->db->join('sales_items', 'sales_items.item_id = items.item_id');
				
				$this->db->where('sale_id', $sale_id);
				
				foreach($this->db->get()->result_array() as $row)
				{
					$item_name_and_desc = $row['name'];
				
					if ($row['description'])
					{
						$item_name_and_desc .= ' - '.$row['description'];
					}
					
					$item_names[] = $item_name_and_desc;
				}
				
				$this->db->select('name');
				$this->db->from('item_kits');
				$this->db->join('sales_item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id');
				$this->db->where('sale_id', $sale_id);
				
				foreach($this->db->get()->result_array() as $row)
				{
					$item_names[] = $row['name'];
				}
				
				$result[$k]['items'] = implode(', ', $item_names);
			}
			
			if (!empty($result))
			{
				$return[]= array('customer_info' => $this->Customer->get_info($customer_id),'store_account_transactions' => $result);
			}
		}
				
		
		$total_amount_due = 0;

		foreach($return as $data) 
		{
			$amount_due = 0;
			foreach($data['store_account_transactions'] as $transaction)
			{
		
				$amount_due = $transaction['balance'];
			}
			$total_amount_due+=$amount_due;
		}
		
		return $total_amount_due;
	}
}
?>