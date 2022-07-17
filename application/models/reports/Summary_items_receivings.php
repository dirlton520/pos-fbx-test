<?php
require_once ("Report.php");
class Summary_items_receivings extends Report
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
		$columns[] = array('data'=>lang('common_supplier'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_category'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_current_cost_price'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_current_selling_price'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_quantity'), 'align'=> 'left');
		
		if ($this->params['items_to_show'] == 'items_with_receivings')
		{
			$columns[] = array('data'=>lang('common_qty_received'), 'align'=> 'left');
			$columns[] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
			$columns[] = array('data'=>lang('reports_total'), 'align'=> 'right');
			$columns[] = array('data'=>lang('common_tax'), 'align'=> 'right');
		
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
		
		
		
		$manufactor_entity_data = array();
		$manufactor_entity_data['specific_input_name'] = 'manufacturer_id';
		$manufactor_entity_data['specific_input_label'] = lang('common_manufacturer');
		$manufactor_entity_data['view'] = 'specific_entity';
		
		$manufactors = array();
		$manufactors[''] =lang('common_all');
				
		foreach($this->Manufacturer->get_all() as $key=>$manu)
		{
			$manufactors[$key] = $manu['name'];
		}
		
		$manufactor_entity_data['specific_input_data'] = $manufactors;
		
		
		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array();
			
			$input_params[] = array('view' => 'date_range', 'with_time' => TRUE);
			$input_params[] = array('view' => 'date_range', 'with_time' => TRUE, 'compare_to' => TRUE);
			$input_params[] = $specific_entity_data;
			$input_params[] = $manufactor_entity_data;
			
			$input_params[] = $category_entity_data;
			$input_params[] = array('view' => 'dropdown','dropdown_label' =>lang('reports_receiving_type'),'dropdown_name' => 'receiving_type','dropdown_options' =>array('all' => lang('reports_all'), 'receivings' => lang('reports_receiving'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all');
			$input_params[] = array('view' => 'dropdown','dropdown_label' => lang('reports_items_to_show'), 'dropdown_name' => 'items_to_show', 'dropdown_options' =>array('items_with_receivings' => lang('reports_items_with_recv'), 'items_without_receivings' => lang('reports_items_without_recv')));
			$input_params[] = array('view' => 'excel_export');
			$input_params[] = array('view' => 'locations');
			$input_params[] = array('view' => 'submit');
		}
		elseif ($this->settings['display'] == 'graphical')
		{
			$input_params[] = array('view' => 'date_range', 'with_time' => TRUE);
			$input_params[] = $specific_entity_data;
			$input_params[] = $manufactor_entity_data;
			$input_params[] = $category_entity_data;
			$input_params[] = array('view' => 'dropdown','dropdown_label' =>lang('reports_receiving_type'),'dropdown_name' => 'receiving_type','dropdown_options' =>array('all' => lang('reports_all'), 'receivings' => lang('reports_receiving'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all');
			$input_params[] = array('view' => 'dropdown','dropdown_label' => lang('reports_items_to_show'), 'dropdown_name' => 'items_to_show', 'dropdown_options' =>array('items_with_receivings' => lang('reports_items_with_recv'), 'items_without_receivings' => lang('reports_items_without_recv')));
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
				
		$this->setupDefaultPagination();
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
			
				$report_data_compare_model = new Summary_items_receivings();
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
				$data_row[] = array('data'=>$row['item_number'], 'align' => 'left');
				$data_row[] = array('data'=>$row['product_id'], 'align' => 'left');
				$data_row[] = array('data'=>$row['supplier'], 'align' => 'left');				
				$data_row[] = array('data'=>$this->Category->get_full_path($row['category_id']), 'align' => 'left');
				$data_row[] = array('data'=>to_currency($row['current_cost_price']), 'align' => 'right');
				$data_row[] = array('data'=>to_currency($row['current_selling_price']), 'align' => 'right');
				$data_row[] = array('data'=>to_quantity($row['quantity']), 'align' => 'left');
				
				if ($this->params['items_to_show'] == 'items_with_receivings')
				{
					$data_row[] = array('data'=>to_quantity($row['quantity_purchased']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['quantity_purchased'] >= $row['quantity_purchased'] ? ($row['quantity_purchased'] == $row_compare['quantity_purchased'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_quantity($row_compare['quantity_purchased']) .'</span>':''), 'align' => 'left');
					$data_row[] = array('data'=>to_currency($row['subtotal']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['subtotal'] >= $row['subtotal'] ? ($row['subtotal'] == $row_compare['subtotal'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['subtotal']) .'</span>':''), 'align' => 'right');
					$data_row[] = array('data'=>to_currency($row['total']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['total'] >= $row['total'] ? ($row['total'] == $row_compare['total'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['total']) .'</span>':''), 'align' => 'right');
					$data_row[] = array('data'=>to_currency($row['tax']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['tax'] >= $row['tax'] ? ($row['tax'] == $row_compare['tax'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['tax']) .'</span>':''), 'align' => 'right');
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
				"title" => lang('reports_items_summary_report'),
				"subtitle" => date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date'])).($do_compare  ? ' '. lang('reports_compare_to'). ' '. date(get_date_format(), strtotime($this->params['start_date_compare'])) .'-'.date(get_date_format(), strtotime($this->params['end_date_compare'])) : ''),
				"headers" => $this->getDataColumns(),
				"data" => $tabular_data,
				"summary_data" => $summary_data,
				"export_excel" => $this->params['export_excel'],
				"pagination" => $this->pagination->create_links()
			);
		}
		elseif($this->settings['display'] == 'graphical')
		{
			$graph_data = array();
			foreach($report_data as $row)
			{
				$graph_data[$row['name']] = to_currency_no_money($row['total']);
			}

			$currency_symbol = $this->config->item('currency_symbol') ? $this->config->item('currency_symbol') : '$';

			$data = array(
				'view' => 'graphical',
				'graph' => 'pie',
				"summary_data" => $summary_data,
				"title" => lang('reports_items_summary_report'),
				"data" => $graph_data,
				"tooltip_template" => "<%=label %>: ".((!$this->config->item('currency_symbol_location') || $this->config->item('currency_symbol_location') =='before') ? $currency_symbol : '')."<%= parseFloat(Math.round(value * 100) / 100).toFixed(".$this->decimals.") %>".($this->config->item('currency_symbol_location') =='after' ? $currency_symbol: ''),
			   "legend_template" => "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%> (".((!$this->config->item('currency_symbol_location') || $this->config->item('currency_symbol_location') =='before') ? $currency_symbol : '')."<%=parseFloat(Math.round(segments[i].value * 100) / 100).toFixed(".$this->decimals.")%>".($this->config->item('currency_symbol_location') =='after' ?  $currency_symbol : '').")<%}%></li><%}%></ul>"			
			);
			
		}
		return $data;
	}
	
	private function get_items_with_receivings_query($paginate,$only_item_id = FALSE)
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
			$this->db->select('receivings_items.item_id');
		}
		else
		{
			$this->db->select('items.item_id,suppliers.company_name as supplier,receivings.location_id,items.cost_price as current_cost_price, items.unit_price as current_selling_price, items.name, items.item_number, items.product_id, categories.name as category , items.category_id, sum('.$this->db->dbprefix('receivings_items').'.quantity_purchased) as quantity_purchased, sum('.$this->db->dbprefix('receivings_items').'.subtotal) as subtotal, sum('.$this->db->dbprefix('receivings_items').'.total) as total, sum('.$this->db->dbprefix('receivings_items').'.tax) as tax', false);
		}
		
		$this->db->from('receivings_items');
		$this->db->join('receivings', 'receivings_items.receiving_id = receivings.receiving_id');
		$this->db->join('items', 'receivings_items.item_id = items.item_id', 'left');
		$this->db->join('categories', 'categories.id = items.category_id', 'left');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$this->db->join('manufacturers', 'manufacturers.id = items.manufacturer_id', 'left');
		
		$this->db->where('receivings.deleted', 0);
		$this->receiving_time_where();
		
		if ($this->params['receiving_type'] == 'receivings')
		{
			$this->db->where('quantity_purchased > 0');
			
		}
		
		elseif ($this->params['receiving_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}
		
		if ($this->params['category_id'])
		{			
			$this->db->where_in('items.category_id', $category_ids);
		}
		
		if ($this->params['supplier_id'])
		{
			$this->db->where('items.supplier_id', $this->params['supplier_id']);
		}	
		
		if ($this->params['manufacturer_id'])
		{
			$this->db->where('items.manufacturer_id', $this->params['manufacturer_id']);			
		}
		
		if (isset($this->params['compare_to_items']) && count($this->params['compare_to_items']) > 0)
		{
			$this->db->where_in('items.item_id', $this->params['compare_to_items']);
		}	
				
		$this->db->group_by('receivings_items.item_id');
		$this->db->order_by('items.name');
		
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
		return $this->db;
		
	}
	
	private function get_items_without_receivings($paginate = TRUE)
	{
		$items_with_receivings_query = $this->get_items_with_receivings_query(FALSE,TRUE)->get_compiled_select();
		
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		
		$this->db->select('items.item_id,suppliers.company_name as supplier,items.cost_price as current_cost_price,items.unit_price as current_selling_price, items.name, items.item_number, items.product_id, categories.name as category , items.category_id, 0 as quantity_purchased, 0 as subtotal, 0 as total, 0 as tax', false);
		$this->db->from('items');			
		$this->db->join('categories','categories.id = items.category_id', 'left');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$this->db->join('manufacturers', 'manufacturers.id = items.manufacturer_id', 'left');
		
		if ($this->params['category_id'])
		{			
			$this->db->where_in('items.category_id', $category_ids);
		}
	
		if ($this->params['supplier_id'])
		{
			$this->db->where('items.supplier_id', $this->params['supplier_id']);
		}	
				
		if ($this->params['manufacturer_id'])
		{
			$this->db->where('items.manufacturer_id', $this->params['manufacturer_id']);			
		}
				
		$this->db->where($this->db->dbprefix('items').'.item_id NOT IN('.$items_with_receivings_query.')',null, FALSE);
		
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
		
		return $this->db;
		
	}
	
	public function getData()
	{		
		$this->load->model('Category');
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);

		$items_receivings_data = $this->get_items_with_receivings_query(TRUE)->get()->result_array();	
		
		if (isset($this->params['items_to_show']) && ($this->params['items_to_show'] == 'items_without_receivings'))
		{
			$items_in_report_without_receivings = $this->get_items_without_receivings()->get()->result_array();	
		}
		
		$this->db->select('items.item_id,SUM(quantity) as quantity', FALSE);
		$this->db->from('items');
		
		$this->db->join('location_items', 'location_items.item_id = items.item_id and location_id IN('.$location_ids_string.')', 'left');
		$this->db->group_by('items.item_id');
				
		$quantity_result = $this->db->get()->result_array();
		$quantities_indexed_by_id = array();
		
		foreach($quantity_result as $quan_row)
		{
			$quantities_indexed_by_id[$quan_row['item_id']] = $quan_row['quantity'];
		}
		
		for($k=0;$k<count($items_receivings_data);$k++)
		{
			$items_receivings_data[$k]['quantity'] = $quantities_indexed_by_id[$items_receivings_data[$k]['item_id']];
		}
		
		if (isset($this->params['items_to_show']) && ($this->params['items_to_show'] == 'items_without_receivings'))
		{
			for($k=0;$k<count($items_in_report_without_receivings);$k++)
			{
				$items_in_report_without_receivings[$k]['quantity'] = $quantities_indexed_by_id[$items_in_report_without_receivings[$k]['item_id']];
			}			
		}
		
		$return = array();
		
		if($this->params['items_to_show'] == 'items_without_receivings')
		{
			$return = $items_in_report_without_receivings;
		}
		elseif($this->params['items_to_show'] == 'items_with_receivings')
		{
			$return = $items_receivings_data;
		}
		return $return;

	}
	
	function getTotalRows()
	{
		if($this->params['items_to_show'] && $this->params['items_to_show'] == 'items_with_receivings')
		{
			return $this->get_items_with_receivings_query(FALSE)->count_all_results();
		} 
		elseif($this->params['items_to_show'] && $this->params['items_to_show'] == 'items_without_receivings') 
		{
			return  $this->get_items_without_receivings(FALSE)->count_all_results();
		}
	}
	
	public function getSummaryData()
	{
		if($this->params['items_to_show'] == 'items_with_receivings')
		{
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
		
			$this->db->select('sum('.$this->db->dbprefix('receivings_items').'.subtotal) as subtotal, sum('.$this->db->dbprefix('receivings_items').'.total) as total, sum('.$this->db->dbprefix('receivings_items').'.tax) as tax', false);
			$this->db->from('receivings');	
			$this->db->join('receivings_items', 'receivings_items.receiving_id = receivings.receiving_id');
			$this->db->join('items', 'receivings_items.item_id = items.item_id');

			$this->db->where('receivings.deleted', 0);
			$this->receiving_time_where();
		
			if ($this->params['receiving_type'] == 'receivings')
			{
				$this->db->where('quantity_purchased > 0');
			}
			elseif ($this->params['receiving_type'] == 'returns')
			{
				$this->db->where('quantity_purchased < 0');
			}
			$this->db->where($this->db->dbprefix('items').'.deleted', 0);
		
			if ($this->params['category_id'])
			{
				$this->db->where_in('items.category_id', $category_ids);
			}
		
			if ($this->params['supplier_id'])
			{
				$this->db->where('items.supplier_id', $this->params['supplier_id']);
			}
			
			if ($this->params['manufacturer_id'])
			{
				$this->db->where('items.manufacturer_id', $this->params['manufacturer_id']);			
			}
			
		
		
			$this->db->group_by('receivings_items.receiving_id');
			$return = array(
				'subtotal' => 0,
				'total' => 0,
				'tax' => 0,
			);
			
			foreach($this->db->get()->result_array() as $row)
			{
				$return['subtotal'] += to_currency_no_money($row['subtotal'],2);
				$return['total'] += to_currency_no_money($row['total'],2);
				$return['tax'] += to_currency_no_money($row['tax'],2);
			}
			
		}
		else
		{
			$return = array();
		}
		
		
		return $return;
	}
}
?>