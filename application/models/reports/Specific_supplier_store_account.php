<?php
require_once ("Report.php");
class Specific_supplier_store_account extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		$return = array(array('data'=>lang('reports_id'), 'align'=>'left'),
		array('data'=>lang('reports_time'), 'align'=> 'left'),
		array('data'=>lang('reports_receiving_id'), 'align'=> 'left'),
		array('data'=>lang('reports_debit'), 'align'=> 'left'),
		array('data'=>lang('reports_credit'), 'align'=> 'left'),
		array('data'=>lang('reports_balance'), 'align'=> 'left'),
		array('data'=>lang('reports_items'), 'align'=> 'left'),		
		array('data'=>lang('reports_comment'), 'align'=> 'left'));
		
		$location_count = $this->Location->count_all();
		
		if ($location_count > 1)
		{
			array_unshift($return,array('data'=>lang('common_location'), 'align'=> 'left'));
		}
		
		return $return;
		
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
			
			$input_params[] = array('view' => 'date_range', 'with_time' => TRUE);
			$input_params[] = $specific_entity_data;
			$input_params[] = array('view' => 'dropdown','dropdown_label' =>lang('reports_sale_type'),'dropdown_name' => 'sale_type','dropdown_options' =>array('all' => lang('reports_all'), 'sales' => lang('reports_sales'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all');
			$input_params[] = array('view' => 'excel_export');
			$input_params[] = array('view' => 'locations');
			$input_params[] = array('view' => 'submit');
		}
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}
	
	public function getOutputData()
	{
		$this->setupDefaultPagination();
		$this->load->model('Sale');
		$this->load->model('Supplier');
		
		$headers = $this->getDataColumns();
		$report_data = $this->getData();

		$tabular_data = array();
		$location_count = $this->Location->count_all();
		
		foreach($report_data as $row)
		{
									
			$tab_row = array(array('data'=>$row['sno'], 'align'=> 'left'),
			array('data'=>date(get_date_format().'-'.get_time_format(), strtotime($row['date'])), 'align'=> 'left'),
			array('data'=>$row['receiving_id'] ? anchor('receivings/receipt/'.$row['receiving_id'], 'RECV '.$row['receiving_id'], array('target' => '_blank')) : '-', 'align'=> 'center'),
			array('data'=> $row['transaction_amount'] > 0 ? to_currency($row['transaction_amount']) : to_currency(0), 'align'=> 'right'),
			array('data'=>$row['transaction_amount'] < 0 ? to_currency($row['transaction_amount'] * -1)  : to_currency(0), 'align'=> 'right'),
			array('data'=>to_currency($row['balance']), 'align'=> 'right'),
			array('data'=>$row['items'], 'align'=> 'left'),
			array('data'=>$row['comment'], 'align'=> 'left'));

			if ($location_count > 1)
			{
				array_unshift($tab_row,array('data'=>$row['location'], 'align'=> 'left'));
			}
			$tabular_data[] = $tab_row;
									
		}


		if ($this->params['supplier_id'])
		{
			$this->load->model('Supplier');
			$supplier_info = $this->Supplier->get_info($this->params['supplier_id']);
		
			if ($supplier_info->company_name)
			{
				$supplier_title = $supplier_info->company_name.' ('.$supplier_info->first_name .' '. $supplier_info->last_name.')';
			}
			else
			{
				$supplier_title = $supplier_info->first_name .' '. $supplier_info->last_name;		
			}
		}
		else
		{
			$supplier_title = lang('common_all');
		}
		$data = array(
			"view" => 'tabular',
			"title" => lang('reports_detailed_store_account_report').$supplier_title,
			"subtitle" => date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date'])),
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
		$location_ids = self::get_selected_location_ids();
		$this->db->select('supplier_store_accounts.*,receivings.*,locations.name as location');
		$this->db->from('supplier_store_accounts');
		$this->db->join('receivings', 'receivings.receiving_id = supplier_store_accounts.receiving_id', 'left');
		$this->db->join('locations', 'receivings.location_id = locations.location_id', 'left');
		$this->db->where_in('receivings.location_id',$location_ids);
		if ($this->params['supplier_id'])
		{
			$this->db->where('receivings.supplier_id',$this->params['supplier_id']);
		}
		
		$this->db->where('date BETWEEN "'.$this->params['start_date'].'" and "'.$this->params['end_date'].'"');
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
		return $result;
	}
	
	public function getTotalRows()
	{
		$location_ids = self::get_selected_location_ids();
		
		$this->db->from('supplier_store_accounts');
		$this->db->join('receivings', 'receivings.receiving_id = supplier_store_accounts.receiving_id', 'left');
		$this->db->where_in('receivings.location_id',$location_ids);
		if ($this->params['supplier_id'])
		{
			$this->db->where('receivings.supplier_id',$this->params['supplier_id']);
		}
		$this->db->where('date BETWEEN "'.$this->params['start_date'].'" and "'.$this->params['end_date'].'"');
		return $this->db->count_all_results();
	}
	
	
	public function getSummaryData()
	{

		if ($this->params['supplier_id'])
		{
			$summary_data=array('balance'=>$this->Supplier->get_info($this->params['supplier_id'])->balance);
			return $summary_data;
		}
		return array();
	}
}
?>