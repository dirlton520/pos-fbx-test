<?php
require_once ("Report.php");
class Store_account_statements_supplier extends Report
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
		$specific_entity_data['specific_input_name'] = 'supplier_id';
		$specific_entity_data['specific_input_label'] = lang('reports_supplier');
		$specific_entity_data['search_suggestion_url'] = site_url('reports/supplier_search');
		$specific_entity_data['view'] = 'specific_entity';
		
		
		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array();
			
			$input_params[] = $specific_entity_data;
			$input_params[] = array('view' => 'date_range', 'with_time' => FALSE,'end_date_end_of_day' => FALSE);
			$input_params[] = array('view' => 'checkbox','checkbox_label' => lang('reports_hide_items'), 'checkbox_name' => 'hide_items');
			$input_params[] = array('view' => 'checkbox','checkbox_label' => lang('reports_hide_paid'), 'checkbox_name' => 'hide_paid');
			$input_params[] = array('view' => 'dropdown','dropdown_label' =>lang('reports_pull_payments_by'),'dropdown_name' => 'pull_payments_by','dropdown_options' =>array('payment_date' => lang('reports_payment_date'), 'receiving_date' => lang('reports_receiving_date')), 'dropdown_selected_value' => '');
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
		$this->load->model('Supplier');			
		$this->load->model('Category');
		
		$this->setupDefaultPagination();
		$report_data = $this->getData();
		
		$location_count = $this->Location->count_all();
		
		$data = array(
			'total_amount_due' => $this->getSummaryData(),
			"view" => 'supplier_store_account_statements',
			"title" => lang('reports_store_account_statements'),
			"location_count" => $location_count,
			"subtitle" => date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date'])),
			'report_data' => $report_data,
			'hide_items' => isset($this->params['hide_items']) ? TRUE: FALSE,
			"pagination" => $this->pagination->create_links(),
			'date_column' => $this->params['pull_payments_by'] == 'payment_date' ? 'date' : 'receiving_time',
		);
		
		return $data;
	}
	
	
	
	public function getData()
	{
		$return = array();
		
		$supplier_ids_for_report = array();
		$supplier_id = $this->params['supplier_id'];
		
		if (!$supplier_id)
		{
			$this->db->select('person_id');
			$this->db->from('suppliers');
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
				$supplier_ids_for_report[] = $row['person_id'];
			}
		}
		else
		{
			$this->db->select('person_id');
			$this->db->from('suppliers');
			$this->db->where('person_id', $supplier_id);
			$this->db->where('deleted',0);
			
			$result = $this->db->get()->row_array();
			
			if (!empty($result))
			{
				$supplier_ids_for_report[] = $result['person_id'];
			}
		}
				
		foreach($supplier_ids_for_report as $supplier_id)
		{
			$this->db->select("supplier_store_accounts_paid_receivings.partial_payment_amount,supplier_store_accounts.*,receivings.receiving_time,locations.name as location");
			$this->db->from('supplier_store_accounts');
			$this->db->where('supplier_store_accounts.supplier_id', $supplier_id);
			$this->db->join('receivings', 'receivings.receiving_id = supplier_store_accounts.receiving_id', 'left');
			$this->db->join('supplier_store_accounts_paid_receivings','receivings.receiving_id=supplier_store_accounts_paid_receivings.receiving_id','left');
			
			$this->db->join('locations', 'receivings.location_id = locations.location_id', 'left');
			$location_ids = self::get_selected_location_ids();
			$this->db->where_in('receivings.location_id',$location_ids);
			
			if ($this->params['pull_payments_by'] == 'payment_date')
			{
				$this->db->where('date >=', $this->params['start_date']);
				$this->db->where('date <=', $this->params['end_date']. '23:59:59');				
				$this->db->order_by('date');
			}
			else
			{
				$this->db->where('receiving_time >=', $this->params['start_date']);
				$this->db->where('receiving_time <=', $this->params['end_date']. '23:59:59');
				$this->db->order_by('receiving_time', ($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');
			}
			
			
			$result = $this->db->get()->result_array();
			
			//If we don't have results from this month, pull the last store account entry we have
			if (count($result) == 0)
			{
				$this->db->select("supplier_store_accounts.*,receivings.receiving_time,locations.name as location");
				$this->db->from('supplier_store_accounts');
				$this->db->where('supplier_store_accounts.supplier_id', $supplier_id);
				$this->db->where_in('receivings.location_id',$location_ids);
				$this->db->join('receivings', 'receivings.receiving_id = supplier_store_accounts.receiving_id', 'left');
				$this->db->join('locations', 'receivings.location_id = locations.location_id', 'left');
				$this->db->limit(1);
				if ($this->params['pull_payments_by'] == 'payment_date')
				{
					$this->db->order_by('date', 'DESC');
				}
				else
				{
					$this->db->order_by('receiving_time', 'DESC');
				}
			
				$this->db->limit(1); 	
				$result = $this->db->get()->result_array();
				
			}
			
			for ($k=0;$k<count($result);$k++)
			{
				$item_names = array();
				$receiving_id = $result[$k]['receiving_id'];
				
				$this->db->select('name, receivings_items.description');
				$this->db->from('items');
				$this->db->join('receivings_items', 'receivings_items.item_id = items.item_id');
				$this->db->where('receiving_id', $receiving_id);
				
				foreach($this->db->get()->result_array() as $row)
				{
					$item_name_and_desc = $row['name'];
				
					if ($row['description'])
					{
						$item_name_and_desc .= ' - '.$row['description'];
					}
					
					$item_names[] = $item_name_and_desc;
				}
								
				$result[$k]['items'] = implode(', ', $item_names);
			}
			$return[]= array('supplier_info' => $this->Supplier->get_info($supplier_id),'store_account_transactions' => $result);
		}
		
		return $return;
	}
	
	public function getTotalRows()
	{
		$supplier_id = $this->params['supplier_id'];
		
		if (!$supplier_id)
		{
			$this->db->distinct();
			$this->db->select('supplier_store_accounts.supplier_id');
			$this->db->from('supplier_store_accounts');
			$this->db->join('receivings', 'receivings.receiving_id = supplier_store_accounts.receiving_id');
			$this->db->where('balance !=', 0);
		}
		else
		{
			$this->db->distinct();
			$this->db->select('supplier_store_accounts.supplier_id');
			$this->db->from('supplier_store_accounts');
			$this->db->join('receivings', 'receivings.receiving_id = supplier_store_accounts.receiving_id');
			$this->db->where('supplier_store_accounts.supplier_id', $supplier_id);
		}
		
		return $this->db->get()->num_rows();
	}
	
	
	public function getSummaryData()
	{
		$return = array();
		
		$supplier_ids_for_report = array();
		$supplier_id = $this->params['supplier_id'];
		
		if (!$supplier_id)
		{
			$this->db->select('person_id');
			$this->db->from('suppliers');
			$this->db->where('balance !=', 0);
			$this->db->where('deleted',0);
			
			$result = $this->db->get()->result_array();
			
			foreach($result as $row)
			{
				$supplier_ids_for_report[] = $row['person_id'];
			}
		}
		else
		{
			$this->db->select('person_id');
			$this->db->from('suppliers');
			$this->db->where('person_id', $supplier_id);
			$this->db->where('deleted',0);
			
			$result = $this->db->get()->row_array();
			
			if (!empty($result))
			{
				$supplier_ids_for_report[] = $result['person_id'];
			}
		}
				
		foreach($supplier_ids_for_report as $supplier_id)
		{
			$this->db->select("supplier_store_accounts.*,receivings.receiving_time,locations.name as location");
			$this->db->from('supplier_store_accounts');
			$this->db->where('supplier_store_accounts.supplier_id', $supplier_id);
			$this->db->join('receivings', 'receivings.receiving_id = supplier_store_accounts.receiving_id', 'left');
			$this->db->join('locations', 'receivings.location_id = locations.location_id', 'left');
			$location_ids = self::get_selected_location_ids();
			$this->db->where_in('receivings.location_id',$location_ids);
			
			if ($this->params['pull_payments_by'] == 'payment_date')
			{
				$this->db->where('date >=', $this->params['start_date']);
				$this->db->where('date <=', $this->params['end_date']. '23:59:59');				
				$this->db->order_by('date');
			}
			else
			{
				$this->db->where('receiving_time >=', $this->params['start_date']);
				$this->db->where('receiving_time <=', $this->params['end_date']. '23:59:59');
				$this->db->order_by('receiving_time', ($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');
			}
			
			
			$result = $this->db->get()->result_array();
			
			//If we don't have results from this month, pull the last store account entry we have
			if (count($result) == 0)
			{
				$this->db->select("supplier_store_accounts.*,receivings.receiving_time,locations.name as location");
				$this->db->from('supplier_store_accounts');
				$this->db->where('supplier_store_accounts.supplier_id', $supplier_id);
				$this->db->where_in('receivings.location_id',$location_ids);
				$this->db->join('receivings', 'receivings.receiving_id = supplier_store_accounts.receiving_id', 'left');
				$this->db->join('locations', 'receivings.location_id = locations.location_id', 'left');
				$this->db->limit(1);
				if ($this->params['pull_payments_by'] == 'payment_date')
				{
					$this->db->order_by('date', 'DESC');
				}
				else
				{
					$this->db->order_by('receiving_time', 'DESC');
				}
			
				$this->db->limit(1); 	
				$result = $this->db->get()->result_array();
				
			}
			
			for ($k=0;$k<count($result);$k++)
			{
				$item_names = array();
				$receiving_id = $result[$k]['receiving_id'];
				
				$this->db->select('name, receivings_items.description');
				$this->db->from('items');
				$this->db->join('receivings_items', 'receivings_items.item_id = items.item_id');
				$this->db->where('receiving_id', $receiving_id);
				
				foreach($this->db->get()->result_array() as $row)
				{
					$item_name_and_desc = $row['name'];
				
					if ($row['description'])
					{
						$item_name_and_desc .= ' - '.$row['description'];
					}
					
					$item_names[] = $item_name_and_desc;
				}
								
				$result[$k]['items'] = implode(', ', $item_names);
			}
			$return[]= array('supplier_info' => $this->Supplier->get_info($supplier_id),'store_account_transactions' => $result);
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