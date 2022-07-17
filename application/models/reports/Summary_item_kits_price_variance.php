<?php
require_once ("Report.php");
class Summary_item_kits_price_variance extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{		
		$columns = array();
		
		$columns[] = array('data'=>lang('common_item'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_item_number'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_product_id'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_category'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_quantity_purchased'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_current_selling_price'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_average_price_per_unit'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
		$columns[] = array('data'=>lang('reports_variance'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_total'), 'align'=> 'right');
		$columns[] = array('data'=>lang('common_tax'), 'align'=> 'right');

		if($this->has_profit_permission)
		{
			$columns[] = array('data'=>lang('common_profit'), 'align'=> 'right');
		}
		
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
		$this->setupDefaultPagination();
		$this->load->model('Category');
		$tabular_data = array();
		$report_data = $this->getData();

		foreach($report_data as $row)
		{
			$data_row = array();
			
			$data_row[] = array('data'=>$row['name'], 'align' => 'left');
			$data_row[] = array('data'=>$row['item_kit_number'], 'align' => 'left');
			$data_row[] = array('data'=>$row['product_id'], 'align' => 'left');
			$data_row[] = array('data'=>$this->Category->get_full_path($row['category_id']), 'align' => 'left');
			$data_row[] = array('data'=>to_quantity($row['quantity_purchased']), 'align' => 'left');
			$data_row[] = array('data'=>to_currency($row['current_selling_price']), 'align'=>'right');
			$data_row[] = array('data'=>to_currency($row['average_unit_cost']), 'align'=>'right');
			$data_row[] = array('data'=>to_currency($row['subtotal']), 'align'=>'right');
			
			$data_row[] = array('data'=>'<span class="compare '.($row['variance_from_sale_price'] >=0 ? ($row['variance_from_sale_price'] == 0 ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row['variance_from_sale_price']) .'</span>', 'align'=>'right');
			$data_row[] = array('data'=>to_currency($row['total']), 'align'=>'right');
			$data_row[] = array('data'=>to_currency($row['tax']), 'align'=> 'right');
			
			if($this->has_profit_permission)
			{
				$data_row[] = array('data'=>to_currency($row['profit']), 'align'=>'right');
			}
			$tabular_data[] = $data_row;			
		}

		$summary_data = $this->getSummaryData();

		foreach($summary_data as $key=>$value)
		{
			if ($key == 'variance')
			{
				$summary_data[$key] = '<span class="compare '.($summary_data[$key] >= 0 ? (0 == $summary_data[$key] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($value).'</span>';
			}
		}

		$data = array(
			"view" => 'tabular',
			"title" => lang('reports_price_variance_report'),
			"subtitle" => date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date'])),
			"headers" => $this->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $summary_data,
			"export_excel" => $this->params['export_excel'],
			"pagination" => $this->pagination->create_links(),
		);

		return $data;
		
	}
	
	public function getData()
	{
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
				
		$this->db->select('sum('.$this->db->dbprefix('sales_item_kits').'.subtotal)/sum('.$this->db->dbprefix('sales_item_kits').'.quantity_purchased) as average_unit_cost,item_kits.item_kit_id,item_kits.unit_price as current_selling_price, item_kits.name, item_kits.item_kit_number, item_kits.product_id, categories.name as category , item_kits.category_id, sum('.$this->db->dbprefix('sales_item_kits').'.quantity_purchased) as quantity_purchased, sum('.$this->db->dbprefix('sales_item_kits').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_item_kits').'.total) as total, sum('.$this->db->dbprefix('sales_item_kits').'.tax) as tax, sum('.$this->db->dbprefix('sales_item_kits').'.profit) as profit, sum('.$this->db->dbprefix('sales_item_kits').'.subtotal)-sum(ROUND('.$this->db->dbprefix('sales_item_kits').'.regular_item_kit_unit_price_at_time_of_sale*'.$this->db->dbprefix('sales_item_kits').'.quantity_purchased,CASE WHEN '.$this->db->dbprefix('item_kits').'.tax_included =1 THEN 10 ELSE 2 END)) + CASE WHEN '.$this->db->dbprefix('item_kits').'.tax_included =1 THEN SUM('.$this->db->dbprefix('sales_item_kits').'.tax) ELSE 0 END as variance_from_sale_price', false);
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales_item_kits.sale_id = sales.sale_id');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id');
		$this->db->join('categories', 'categories.id = item_kits.category_id', 'left');
		$this->sale_time_where();
		$this->db->where('sales.deleted', 0);

		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}
						

		$this->db->group_by('item_kits.item_kit_id');
		$this->db->order_by('name');

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
		$this->db->select('COUNT(DISTINCT('.$this->db->dbprefix('sales_item_kits').'.item_kit_id)) as item_count');
		$this->db->from('sales_item_kits');		
		$this->db->join('item_kits', 'item_kits.item_kit_id = sales_item_kits.item_kit_id');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id');
		$this->sale_time_where();
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}
		
		$this->db->where('sales.deleted', 0);
		
			
		$ret = $this->db->get()->row_array();
		return $ret['item_count'];
	}
	
	public function getSummaryData()
	{		
		$this->db->select('sum('.$this->db->dbprefix('sales_item_kits').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_item_kits').'.total) as total, sum('.$this->db->dbprefix('sales_item_kits').'.tax) as tax, sum('.$this->db->dbprefix('sales_item_kits').'.profit) as profit,sum('.$this->db->dbprefix('sales_item_kits').'.subtotal)-sum(ROUND('.$this->db->dbprefix('sales_item_kits').'.regular_item_kit_unit_price_at_time_of_sale*'.$this->db->dbprefix('sales_item_kits').'.quantity_purchased,CASE WHEN '.$this->db->dbprefix('item_kits').'.tax_included =1 THEN 10 ELSE 2 END)) + CASE WHEN '.$this->db->dbprefix('item_kits').'.tax_included =1 THEN '.$this->db->dbprefix('sales_item_kits').'.tax ELSE 0 END as variance_from_sale_price', false);
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales_item_kits.sale_id = sales.sale_id');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id');
		$this->db->join('categories', 'categories.id = item_kits.category_id', 'left');
				
		$this->sale_time_where();
		$this->db->where('sales.deleted', 0);

		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('sales_item_kits.quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('sales_item_kits.quantity_purchased < 0');
		}
				
		$this->db->group_by('sales_item_kits.sale_id');
		
		$return = array(
			'variance' => 0,
			'subtotal' => 0,
			'total' => 0,
			'tax' => 0,
			'profit' => 0,
		);
		
		foreach($this->db->get()->result_array() as $row)
		{
			$return['variance'] += to_currency_no_money($row['variance_from_sale_price'],2);
			$return['subtotal'] += to_currency_no_money($row['subtotal'],2);
			$return['total'] += to_currency_no_money($row['total'],2);
			$return['tax'] += to_currency_no_money($row['tax'],2);
			$return['profit'] += to_currency_no_money($row['profit'],2);
		}
		if(!$this->has_profit_permission)
		{
			unset($return['profit']);
		}
		return $return;
	}
}
?>