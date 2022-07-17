<?php
require_once ("Report.php");
class Summary_tips extends Report
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Tier');
		
	}
	
	public function getInputData()
	{
		
		$input_params = array();
		
	
		if ($this->settings['display'] == 'tabular')
		{
			$input_data = Report::get_common_report_input_data(TRUE);
			
			$input_params = array(
				array('view' => 'date_range', 'with_time' => TRUE),
				array('view' => 'dropdown','dropdown_label' =>lang('reports_employee_type'),'dropdown_name' => 'employee_type','dropdown_options' =>array( 'sale_person' => lang('reports_sale_person'), 'logged_in_employee' => lang('common_logged_in_employee')),'dropdown_selected_value' => 'sale_person'),				
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
			
				$data_row[] = array('data'=>$row['employee'], 'align'=> 'left');
				$data_row[] = array('data'=>to_currency($row['subtotal']), 'align'=> 'right');
				$data_row[] = array('data'=>to_currency($row['total']), 'align'=> 'right');
				$data_row[] = array('data'=>to_currency($row['tax']), 'align'=> 'right');
			
				if($this->has_profit_permission)
				{
					$data_row[] = array('data'=>to_currency($row['profit']), 'align'=> 'right');
				}
				$data_row[] = array('data'=>to_currency($row['tip']), 'align'=> 'right');
				
				$tabular_data[] = $data_row;
			}
		
			
	 		$data = array(
				'view' => 'tabular',
				"title" => lang('common_tips'),
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
		
		$columns[] = array('data'=>lang('common_employee'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
		$columns[] = array('data'=>lang('reports_total'), 'align'=> 'right');
		$columns[] = array('data'=>lang('common_tax'), 'align'=> 'right');

		if($this->has_profit_permission)
		{
			$columns[] = array('data'=>lang('common_profit'), 'align'=> 'right');
		}
		$columns[] = array('data'=>lang('common_tip'), 'align'=> 'right');
		
		return $columns;		
	}
	
	public function getData()
	{		
		
		$employee_column = isset($this->params['employee_type']) && $this->params['employee_type'] == 'logged_in_employee' ? 'employee_id' : 'sold_by_employee_id';
		
		$location_ids = self::get_selected_location_ids();
		
		$this->db->select("people.full_name as employee,sum(tip) as tip,sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit", false);
		$this->db->from('sales');
		$this->db->join('people',"people.person_id = sales.$employee_column",'left');
		$this->sale_time_where();
		$this->db->where('sales.deleted', 0);
		$this->db->where_in('sales.location_id', $location_ids);
		
		$this->db->group_by($employee_column, TRUE);
		$this->db->order_by('sale_time', ($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');
		
				
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
		
		$this->db->select('date(sale_time) as sale_date', false);
		$this->db->from('sales');
		$this->db->join('locations', 'sales.location_id = locations.location_id');
		
		if (isset($this->params['tier_id']) && $this->params['tier_id'])
		{
			if ($this->params['tier_id'] == 'none')
			{
				$this->db->where('sales.tier_id is NULL');				
			}
			elseif($this->params['tier_id'] == 'all')
			{
				$this->db->where('sales.tier_id is NOT NULL');				
			}
			else
			{
				$this->db->where('sales.tier_id',$this->params['tier_id']);
			}
		}
		
		
		$this->sale_time_where();
		$this->db->where('sales.deleted', 0);
		$this->db->where_in('sales.location_id', $location_ids);
		
		return $this->db->count_all_results();
	}
	
	public function getSummaryData()
	{
		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit,sum(tip) as tips', false);
		$this->db->from('sales');
		
		$this->sale_time_where();
		$this->db->where('deleted', 0);
		
		$return = array(
			'subtotal' => 0,
			'total' => 0,
			'tax' => 0,
			'profit' => 0,
			'tips' => 0,
		);
		
		foreach($this->db->get()->result_array() as $row)
		{
			$return['subtotal'] += to_currency_no_money($row['subtotal'],2);
			$return['total'] += to_currency_no_money($row['total'],2);
			$return['tax'] += to_currency_no_money($row['tax'],2);
			$return['profit'] += to_currency_no_money($row['profit'],2);
			$return['tips'] += to_currency_no_money($row['tips'],2);
		}
		
		if(!$this->has_profit_permission)
		{
			unset($return['profit']);
			unset($return['cogs']);
		}
		return $return;
	}

}
?>