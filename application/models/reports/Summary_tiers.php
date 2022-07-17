<?php
require_once ("Report.php");
class Summary_tiers extends Report
{
	function __construct()
	{
		parent::__construct();
	}

	public function getDataColumns()
	{
		$columns = array();
		
		$columns[] = array('data'=>lang('common_tier_name'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_count'), 'align'=> 'right');
		$columns[] = array('data'=>lang('common_sub_total'), 'align'=> 'right');
		$columns[] = array('data'=>lang('common_total'), 'align'=> 'right');
		$columns[] = array('data'=>lang('common_tax'), 'align'=> 'right');
		$columns[] = array('data'=>lang('common_profit'), 'align'=> 'right');

		return $columns;		
	}
	
	public function getInputData()
	{
		$input_data = Report::get_common_report_input_data(TRUE);

		$input_params = array();

		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array(
				array('view' => 'date_range', 'with_time' => TRUE),
				array('view' => 'dropdown','dropdown_label' =>lang('reports_sale_type'),'dropdown_name' => 'sale_type','dropdown_options' =>array('all' => lang('reports_all'), 'sales' => lang('reports_sales'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all'),
				array('view' => 'excel_export'),
				array('view' => 'locations'),
				array('view' => 'submit'),
			);
		}

		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}
	
	public function getOutputData()
	{
		$this->load->model('Sale');
		$this->setupDefaultPagination();
		$report_data = $this->getData();
		$start_date = $this->params['start_date'];
		$end_date = $this->params['end_date'];
		$tabular_data = array();
		foreach($report_data as $row)
		{
			$data_row = array();
			
			$data_row[] = array('data'=>$row['tier_name'], 'align'=>'left');
			$data_row[] = array('data'=>$row['count'], 'align'=>'right');
			$data_row[] = array('data'=>to_currency($row['subtotal']), 'align'=>'right');
			$data_row[] = array('data'=>to_currency($row['total']), 'align'=>'right');
			$data_row[] = array('data'=>to_currency($row['tax']), 'align'=>'right');
			$data_row[] = array('data'=>to_currency($row['profit']), 'align'=>'right');
			
			$tabular_data[] = $data_row;
		}
		$data = array(
			"view" => 'tabular',
			"title" => lang('reports_tiers_summary_report'),
			"subtitle" => date(get_date_format(), strtotime($start_date)) .'-'.date(get_date_format(), strtotime($end_date)),
			"headers" => $this->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $this->getSummaryData(),
			"export_excel" => $this->params['export_excel'],
			"pagination" => $this->pagination->create_links()
		);

		return $data;
	}
	
	
	public function getData()
	{		
		$this->db->select('COUNT(tier_id) as count, SUM(subtotal) as subtotal, SUM(total) as total, SUM(tax) as tax, SUM(profit) as profit, price_tiers.name as tier_name');
		$this->db->from('sales'); 
		$this->db->join('price_tiers','sales.tier_id=price_tiers.id');
		$this->db->group_by('sales.tier_id');
		$this->db->where('sales.deleted', 0);
		$this->sale_time_where();
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
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
		$this->db->select('COUNT(id) as count');
		$this->db->from('price_tiers');
		
		$ret = $this->db->get()->row_array();
		return $ret['count'];
	}
	
	
	public function getSummaryData()
	{
		return array();
	}

}
?>