<?php
require_once ("Report.php");
class Summary_items_top_sellers extends Report
{
	function __construct()
	{
		$this->load->model('Item_variations');
		
		parent::__construct();
	}
	
	public function getDataColumns()
	{		
		$columns = array();
		
		$columns[] = array('data'=>lang('common_item'), 'align'=> 'left');
		if (isset($this->params['group_by_variation']) && $this->params['group_by_variation'])
		{
			$columns[] = array('data'=>lang('common_variation'), 'align'=> 'left');
		}
		$columns[] = array('data'=>lang('common_item_number'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_product_id'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_supplier'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_category'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_current_cost_price'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_current_selling_price'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_quantity'), 'align'=> 'left');		
		$columns[] = array('data'=>lang('reports_quantity_purchased'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
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
		$this->load->model('Category');
		$input_params = array();
		$data = Report::get_common_report_input_data(TRUE);
		$data['supplier_search_suggestion_url'] = site_url('reports/supplier_search');
		$data['hide_excel_export_and_compare'] = FALSE;
		
		
		$category_entity_data = array();
		$category_entity_data['specific_input_name'] = 'category_id';
		$category_entity_data['specific_input_label'] = lang('reports_category');
		$category_entity_data['view'] = 'specific_entity';
		
		$categories = array();
		$categories[''] =lang('common_all');
		
		$categories_phppos= $this->Category->sort_categories_and_sub_categories($this->Category->get_all_categories_and_sub_categories());
		
		foreach($categories_phppos as $key=>$value)
		{
			$name = $this->config->item('show_full_category_path') ? str_repeat('&nbsp;&nbsp;', $value['depth']).$this->Category->get_full_path($key) : str_repeat('&nbsp;&nbsp;', $value['depth']).$value['name'];
			$categories[$key] = $name;
		}
		
		$category_entity_data['specific_input_data'] = $categories;
		
		
		$input_data = Report::get_common_report_input_data(TRUE);
		$specific_entity_data['specific_input_name'] = 'supplier_id';
		$specific_entity_data['specific_input_label'] = lang('reports_supplier');
		$specific_entity_data['search_suggestion_url'] = site_url('reports/supplier_search');
		$specific_entity_data['view'] = 'specific_entity';
		
		
		$specific_customer_entity_data['specific_input_name'] = 'customer_id';
		$specific_customer_entity_data['specific_input_label'] = lang('reports_customer');
		$specific_customer_entity_data['search_suggestion_url'] = site_url('reports/customer_search/0');
		$specific_customer_entity_data['view'] = 'specific_entity';
		
		
		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array();
			
			$input_params[] = array('view' => 'date_range', 'with_time' => TRUE);
			$input_params[] = array('view' => 'date_range', 'with_time' => TRUE, 'compare_to' => TRUE);
			$input_params[] = $specific_entity_data;
			$input_params[] = $specific_customer_entity_data;
			$input_params[] = $category_entity_data;
			$input_params[] = array('view' => 'dropdown','dropdown_label' =>lang('reports_sale_type'),'dropdown_name' => 'sale_type','dropdown_options' =>array('all' => lang('reports_all'), 'sales' => lang('reports_sales'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all');
			
			$this->load->model('Item_attribute');
			$attribute_count = $this->Item_attribute->count_all();
			
			if ($attribute_count > 0)
			{
				$input_params[] = array('view' => 'checkbox','checkbox_label' => lang('reports_group_by_variation'), 'checkbox_name' => 'group_by_variation');
			}
			$input_params[] = array('view' => 'dropdown','dropdown_label' =>lang('reports_order_by'),'dropdown_name' => 'order_by','dropdown_options' =>array('quantity_purchased' => lang('reports_quantity_purchased'), 'total' => lang('reports_total'), 'profit' => lang('reports_profit')),'dropdown_selected_value' => 'quantity_purchased');
			$input_params[] = array('view' => 'text','name' => 'limit', 'label' => lang('reports_limit'));
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
		$this->load->model('Category');
				
		$tabular_data = array();
		$report_data = $this->getData();
		$summary_data = $this->getSummaryData();
		
		if ($this->settings['display'] == 'tabular')
		{				
		
			$do_compare = isset($this->params['compare_to']) && $this->params['compare_to'];		

			if ($do_compare)
			{
				$compare_to_items = array();
			
				for($k=0;$k<count($report_data);$k++)
				{
					$compare_to_items[] = $report_data[$k]['item_id'];
				}
			
				$report_data_compare_model = new Summary_items_top_sellers();
				$report_data_compare_model->report_key = $this->report_key;
				$report_data_compare_model->setSettings($this->settings);
				$report_data_compare_model->setParams(array_merge($this->params,array('start_date'=>$this->params['start_date_compare'], 'end_date'=>$this->params['end_date_compare'])));

				$report_data_compare = $report_data_compare_model->getData();
				$report_data_summary_compare = $report_data_compare_model->getSummaryData();
			}


			foreach($report_data as $row)
			{
				if ($do_compare)
				{
					$index_compare = -1;
					$item_id_to_compare_to = $row['item_id'];
				
					for($k=0;$k<count($report_data_compare);$k++)
					{
						if ($report_data_compare[$k]['item_id'] == $item_id_to_compare_to)
						{
							$index_compare = $k;
							break;
						}
					}
				
					if (isset($report_data_compare[$index_compare]))
					{
						$row_compare = $report_data_compare[$index_compare];
					}
					else
					{
						$row_compare = FALSE;
					}
				}
			
				$data_row = array();
				$data_row[] = array('data'=>$row['name'], 'align' => 'left');
				if (isset($this->params['group_by_variation']) && $this->params['group_by_variation'])
				{
					$data_row[] = array('data'=>$this->Item_variations->get_variation_name($row['item_variation_id']), 'align' => 'left');
				}
				
				$data_row[] = array('data'=>$row['item_number'], 'align' => 'left');
				$data_row[] = array('data'=>$row['product_id'], 'align' => 'left');
				$data_row[] = array('data'=>$row['supplier'], 'align' => 'left');				
				$data_row[] = array('data'=>$this->Category->get_full_path($row['category_id']), 'align' => 'left');
				$data_row[] = array('data'=>to_currency($row['current_cost_price']), 'align' => 'right');
				$data_row[] = array('data'=>to_currency($row['current_selling_price']), 'align' => 'right');
				$data_row[] = array('data'=>to_quantity($row['quantity']), 'align' => 'left');				
				$data_row[] = array('data'=>to_quantity($row['quantity_purchased']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['quantity_purchased'] >= $row['quantity_purchased'] ? ($row['quantity_purchased'] == $row_compare['quantity_purchased'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_quantity($row_compare['quantity_purchased']) .'</span>':''), 'align' => 'left');
				$data_row[] = array('data'=>to_currency($row['subtotal']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['subtotal'] >= $row['subtotal'] ? ($row['subtotal'] == $row_compare['subtotal'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['subtotal']) .'</span>':''), 'align' => 'right');
				$data_row[] = array('data'=>to_currency($row['total']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['total'] >= $row['total'] ? ($row['total'] == $row_compare['total'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['total']) .'</span>':''), 'align' => 'right');
				$data_row[] = array('data'=>to_currency($row['tax']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['tax'] >= $row['tax'] ? ($row['tax'] == $row_compare['tax'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['tax']) .'</span>':''), 'align' => 'right');
				if($this->has_profit_permission)
				{
					$data_row[] = array('data'=>to_currency($row['profit']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['profit'] >= $row['profit'] ? ($row['profit'] == $row_compare['profit'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['profit']) .'</span>':''), 'align' => 'right');
				}
				
				$tabular_data[] = $data_row;
		
			}

			if ($do_compare)
			{
				foreach($summary_data as $key=>$value)
				{
					$summary_data[$key] = to_currency($value) . ' / <span class="compare '.($report_data_summary_compare[$key] >= $value ? ($value == $report_data_summary_compare[$key] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($report_data_summary_compare[$key]).'</span>';
				}
			
			}

			$data = array(
				"view" => 'tabular',
				"title" => lang('reports_items_top_sellers_report'),
				"subtitle" => date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date'])).($do_compare  ? ' '. lang('reports_compare_to'). ' '. date(get_date_format(), strtotime($this->params['start_date_compare'])) .'-'.date(get_date_format(), strtotime($this->params['end_date_compare'])) : ''),
				"headers" => $this->getDataColumns(),
				"data" => $tabular_data,
				"summary_data" => $summary_data,
				"export_excel" => $this->params['export_excel'],
				"pagination" => ''
			);
		}
		return $data;
	}
	
	private function get_items_with_sales_query($paginate,$only_item_id = FALSE)
	{
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		
		if ($this->params['category_id'])
		{
			if ($this->config->item('include_child_categories_when_searching_or_reporting'))
			{	
				$category_ids = $this->Category->get_category_id_and_children_category_ids_for_category_id($this->params['category_id']);			
			}
			else
			{
				$category_ids = array($this->params['category_id']);
			}
		}		
		
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		
		if ($only_item_id)
		{
			$this->db->select('sales_items.item_id');
		}
		else
		{
			if (isset($this->params['group_by_variation']) && $this->params['group_by_variation'])
			{
				$this->db->select('sales_items.item_variation_id as item_variation_id,items.item_id,suppliers.company_name as supplier,sales.location_id,items.cost_price as current_cost_price, items.unit_price as current_selling_price, items.name, items.item_number, items.product_id, categories.name as category , items.category_id, sum('.$this->db->dbprefix('sales_items').'.quantity_purchased) as quantity_purchased, sum('.$this->db->dbprefix('sales_items').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_items').'.total) as total, sum('.$this->db->dbprefix('sales_items').'.tax) as tax, sum('.$this->db->dbprefix('sales_items').'.profit) as profit', false);
			}
			else
			{
				$this->db->select('sales_items.item_variation_id as item_variation_id,items.item_id,suppliers.company_name as supplier,sales.location_id,items.cost_price as current_cost_price, items.unit_price as current_selling_price, items.name, items.item_number, items.product_id, categories.name as category , items.category_id, sum('.$this->db->dbprefix('sales_items').'.quantity_purchased) as quantity_purchased, sum('.$this->db->dbprefix('sales_items').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_items').'.total) as total, sum('.$this->db->dbprefix('sales_items').'.tax) as tax, sum('.$this->db->dbprefix('sales_items').'.profit) as profit', false);
			}
		}
		
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales_items.sale_id = sales.sale_id');
		$this->db->join('items', 'sales_items.item_id = items.item_id', 'left');
		$this->db->join('categories', 'categories.id = items.category_id', 'left');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		
		$this->db->where('sales.deleted', 0);
		$this->sale_time_where();
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('quantity_purchased > 0');
			
		}
		
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}
		
		if (isset($this->params['category_id']) && $this->params['category_id'])
		{			
			$this->db->where_in('items.category_id', $category_ids);
		}
		
		if (isset($this->params['supplier_id']) && $this->params['supplier_id'])
		{
			$this->db->where('items.supplier_id', $this->params['supplier_id']);
		}	
		
		if (isset($this->params['customer_id']) && $this->params['customer_id'])
		{
			$this->db->where('sales.customer_id', $this->params['customer_id']);
		}	
		

		if (isset($this->params['compare_to_items']) && count($this->params['compare_to_items']) > 0)
		{
			$this->db->where_in('items.item_id', $this->params['compare_to_items']);
		}	
				
		if (isset($this->params['group_by_variation']) && $this->params['group_by_variation'])
		{
			$this->db->group_by('sales_items.item_variation_id,items.item_id');
		}
		else
		{
			$this->db->group_by('sales_items.item_id');
		}
		
		if ($paginate)
		{
			//If we are exporting NOT exporting to excel make sure to use offset and limit
			if (isset($this->params['export_excel']) && !$this->params['export_excel'])
			{
				$this->db->limit($this->report_limit);
			
				if (isset($this->params['offset']))
				{
					$this->db->offset($this->params['offset']);
				}
			}
		}	
		$this->db->order_by((isset($this->params['order_by']) && $this->params['order_by'] ? $this->params['order_by'] : 'quantity_purchased').' DESC');
		$this->db->limit(isset($this->params['limit']) && $this->params['limit'] ? $this->params['limit'] : 50);
			
		return $this->db;
		
	}
	
	public function getData()
	{		
		$this->load->model('Category');
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		$primary_key_column = isset($this->params['group_by_variation']) && $this->params['group_by_variation'] ? 'item_variation_id' : 'item_id';
		
		$items_sales_data = $this->get_items_with_sales_query(TRUE)->get()->result_array();	
		
		$this->db->from('items');
		
		if (isset($this->params['group_by_variation']) && $this->params['group_by_variation'])
		{			
			$this->db->select('item_variations.id as item_variation_id,SUM(quantity) as quantity', FALSE);
			$this->db->join('item_variations','item_variations.item_id = items.item_id', 'left');
			$this->db->join('location_item_variations', 'location_item_variations.item_variation_id = item_variations.id and location_id IN('.$location_ids_string.')', 'left');
			$this->db->group_by('item_variations.id');
		}
		else
		{
			$this->db->select('items.item_id,SUM(quantity) as quantity', FALSE);
			$this->db->join('location_items', 'location_items.item_id = items.item_id and location_id IN('.$location_ids_string.')', 'left');
			$this->db->group_by('items.item_id');
		}		
		$quantity_result = $this->db->get()->result_array();
		$quantities_indexed_by_id = array();
		
		foreach($quantity_result as $quan_row)
		{
			$quantities_indexed_by_id[$quan_row[$primary_key_column]] = $quan_row['quantity'];
		}
		
		for($k=0;$k<count($items_sales_data);$k++)
		{
			$items_sales_data[$k]['quantity'] = $quantities_indexed_by_id[$items_sales_data[$k][$primary_key_column]];
		}
			
		$return = $items_sales_data;
		return $return;

	}
	
	
	public function getSummaryData()
	{
		return array();
	}
}
?>