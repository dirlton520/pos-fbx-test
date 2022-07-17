<?php
require_once ("Report.php");
class Summary_items extends Report
{
	function __construct()
	{
		$this->load->model('Item_variations');
		parent::__construct();
	}
	
	public function getDataColumns()
	{		
		$columns = array();
		
		$columns[] = array('data'=>lang('common_item_id'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_item'), 'align'=> 'left');
		if (isset($this->params['group_by_variation']) && $this->params['group_by_variation'])
		{
			$columns[] = array('data'=>lang('common_variation'), 'align'=> 'left');
		}
		$columns[] = array('data'=>lang('common_item_number'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_product_id'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_supplier'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_category'), 'align'=> 'left');
		if ($this->has_cost_price_permission)
		{
			$columns[] = array('data'=>lang('reports_current_cost_price'), 'align'=> 'left');
		}
		$columns[] = array('data'=>lang('reports_current_selling_price'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_quantity'), 'align'=> 'left');
		
		if ($this->params['items_to_show'] == 'items_with_sales')
		{
			$columns[] = array('data'=>lang('reports_quantity_purchased'), 'align'=> 'left');
			$columns[] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
			$columns[] = array('data'=>lang('reports_total'), 'align'=> 'right');
			$columns[] = array('data'=>lang('common_tax'), 'align'=> 'right');
		
			if($this->has_profit_permission)
			{
				$columns[] = array('data'=>lang('common_profit'), 'align'=> 'right');
				$columns[] = array('data'=>lang('reports_cogs'), 'align'=> 'right');
			}
		}
		
		return $columns;		
	}
	
	public function getInputData()
	{
		$this->load->model('Category');
		$this->load->model('Tier');
		$input_params = array();
		$data = Report::get_common_report_input_data(TRUE);
		
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
		
		
		$register_input_data_entry = array();
		$register_input_data_entry['view']  = 'specific_entity';
		$register_input_data_entry['specific_input_name'] = 'register_id';
		$register_input_data_entry['specific_input_label'] = lang('reports_register');
		$registers = array();
		$registers[''] = lang('common_all');
		foreach($this->Register->get_all()->result() as $register)
		{
			$location_info = $this->Location->get_info($register->location_id);
			$registers[$register->register_id] = $location_info->name.' - '.$register->name;
		}
		$register_input_data_entry['specific_input_data'] = $registers;
		
		
		$tier_entity_data = array();
		$tier_entity_data['specific_input_name'] = 'tier_id';
		$tier_entity_data['specific_input_label'] = lang('common_tier_name');
		$tier_entity_data['view'] = 'specific_entity';
	
		$tiers = array();
		$tiers[''] =lang('common_no_tier_or_tier');
		$tiers['none'] = lang('common_none');
		$tiers['all'] = lang('common_all');
		$tiers_phppos= $this->Tier->get_all()->result_array();
		foreach($tiers_phppos as $value)
		{
			$tiers[$value['id']] = $value['name'];
		}
		
		$tier_entity_data['specific_input_data'] = $tiers;
		
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
			$input_params[] = $manufactor_entity_data;
			
			$input_params[] = $specific_customer_entity_data;
			$input_params[] = $category_entity_data;
			$input_params[] = $register_input_data_entry;
			$input_params[] = array('view' => 'dropdown','dropdown_label' =>lang('reports_sale_type'),'dropdown_name' => 'sale_type','dropdown_options' =>array('all' => lang('reports_all'), 'sales' => lang('reports_sales'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all');
			$input_params[] = array('view' => 'dropdown','dropdown_label' => lang('reports_items_to_show'), 'dropdown_name' => 'items_to_show', 'dropdown_options' =>array('items_with_sales' => lang('reports_items_with_sales'), 'items_without_sales' => lang('reports_items_without_sales')));
			
			$this->load->model('Item_attribute');
			$attribute_count = $this->Item_attribute->count_all();
			
			if ($attribute_count > 0)
			{
				$input_params[] = array('view' => 'checkbox','checkbox_label' => lang('reports_group_by_variation'), 'checkbox_name' => 'group_by_variation');
			}
			$input_params[] = array('view' => 'checkbox','checkbox_label' => lang('reports_include_item_kits'), 'checkbox_name' => 'include_item_kits');
			
			$input_params[] = array('view' => 'excel_export');
			$input_params[] = array('view' => 'locations');
			$input_params[] = array('view' => 'submit');
		}
		elseif ($this->settings['display'] == 'graphical')
		{
			$input_params[] = array('view' => 'date_range', 'with_time' => TRUE);
			$input_params[] = $specific_entity_data;
			$input_params[] = $manufactor_entity_data;
			$input_params[] = $specific_customer_entity_data;
			$input_params[] = $category_entity_data;
			$input_params[] = $register_input_data_entry;
			$input_params[] = array('view' => 'dropdown','dropdown_label' =>lang('reports_sale_type'),'dropdown_name' => 'sale_type','dropdown_options' =>array('all' => lang('reports_all'), 'sales' => lang('reports_sales'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all');
			$input_params[] = array('view' => 'dropdown','dropdown_label' => lang('reports_items_to_show'), 'dropdown_name' => 'items_to_show', 'dropdown_options' =>array('items_with_sales' => lang('reports_items_with_sales'), 'items_without_sales' => lang('reports_items_without_sales')));
			$input_params[] = array('view' => 'checkbox','checkbox_label' => lang('reports_group_by_variation'), 'checkbox_name' => 'group_by_variation');
			$input_params[] = array('view' => 'locations');
			$input_params[] = array('view' => 'submit');
			
		}
		
		if (count($tiers_phppos))
		{
			array_unshift($input_params,$tier_entity_data);
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
			
				$report_data_compare_model = new Summary_items();
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
				$data_row[] = array('data'=>$row['item_id'], 'align' => 'left');
				$data_row[] = array('data'=>$row['name'], 'align' => 'left');
				if (isset($this->params['group_by_variation']) && $this->params['group_by_variation'])
				{
					$data_row[] = array('data'=>$this->Item_variations->get_variation_name($row['item_variation_id']), 'align' => 'left');
				}
				
				$data_row[] = array('data'=>$row['item_number'], 'align' => 'left');
				$data_row[] = array('data'=>$row['product_id'], 'align' => 'left');
				$data_row[] = array('data'=>$row['supplier'], 'align' => 'left');				
				$data_row[] = array('data'=>$this->Category->get_full_path($row['category_id']), 'align' => 'left');
				
				if ($this->has_cost_price_permission)
				{
					$data_row[] = array('data'=>to_currency($row['current_cost_price']), 'align' => 'right');
				}
				$data_row[] = array('data'=>to_currency($row['current_selling_price']), 'align' => 'right');
				$data_row[] = array('data'=>to_quantity($row['quantity']), 'align' => 'left');
				
				if ($this->params['items_to_show'] == 'items_with_sales')
				{
					$data_row[] = array('data'=>to_quantity($row['quantity_purchased']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['quantity_purchased'] >= $row['quantity_purchased'] ? ($row['quantity_purchased'] == $row_compare['quantity_purchased'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_quantity($row_compare['quantity_purchased']) .'</span>':''), 'align' => 'left');
					$data_row[] = array('data'=>to_currency($row['subtotal']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['subtotal'] >= $row['subtotal'] ? ($row['subtotal'] == $row_compare['subtotal'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['subtotal']) .'</span>':''), 'align' => 'right');
					$data_row[] = array('data'=>to_currency($row['total']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['total'] >= $row['total'] ? ($row['total'] == $row_compare['total'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['total']) .'</span>':''), 'align' => 'right');
					$data_row[] = array('data'=>to_currency($row['tax']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['tax'] >= $row['tax'] ? ($row['tax'] == $row_compare['tax'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['tax']) .'</span>':''), 'align' => 'right');
					if($this->has_profit_permission)
					{
						$data_row[] = array('data'=>to_currency($row['profit']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['profit'] >= $row['profit'] ? ($row['profit'] == $row_compare['profit'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['profit']) .'</span>':''), 'align' => 'right');
						$data_row[] = array('data'=>to_currency($row['subtotal'] - $row['profit']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['subtotal'] - $row_compare['profit'] >= $row['subtotal'] - $row['profit'] ? ($row['subtotal'] - $row['profit'] == $row_compare['subtotal'] - $row_compare['profit'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['subtotal'] - $row_compare['profit']) .'</span>':''), 'align' => 'right');
					}
				}
				
				$tabular_data[] = $data_row;
		
			}

			if ($do_compare)
			{
				foreach($summary_data as $key=>$value)
				{
					if ($key == 'damaged_qty')
					{
						$summary_data[$key] = to_quantity($value) . ' / <span class="compare '.($report_data_summary_compare[$key] >= $value ? ($value == $report_data_summary_compare[$key] ?  '' : 'compare_better') : 'compare_worse').'">'.to_quantity($report_data_summary_compare[$key]).'</span>';
					}
					else
					{
						$summary_data[$key] = to_currency($value) . ' / <span class="compare '.($report_data_summary_compare[$key] >= $value ? ($value == $report_data_summary_compare[$key] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($report_data_summary_compare[$key]).'</span>';
					}
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
		$this->db->join('price_tiers', 'sales.tier_id = price_tiers.id', 'left');
		$this->db->join('items', 'sales_items.item_id = items.item_id', 'left');
		$this->db->join('categories', 'categories.id = items.category_id', 'left');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		$this->db->join('manufacturers', 'manufacturers.id = items.manufacturer_id', 'left');
		
		$this->db->where('sales.deleted', 0);
		$this->sale_time_where();
		
		if (isset($this->params['register_id']) && $this->params['register_id'])
		{
			$this->db->where('sales.register_id',$this->params['register_id']);
		}
		
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
		
		if (isset($this->params['manufacturer_id']) && $this->params['manufacturer_id'])
		{
			$this->db->where('items.manufacturer_id', $this->params['manufacturer_id']);			
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
		if (!isset($this->params['include_item_kits']))
		{
			$this->db->order_by('items.name');
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
		return $this->db;
		
	}
	
	private function get_item_kits_with_sales_query($paginate,$only_item_kit_id = FALSE)
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
		
		if ($only_item_kit_id)
		{
			$this->db->select('sales_item_kits.item_kit_id');
		}
		else
		{
			$this->db->select('"" as item_kit_variation_id,item_kits.item_kit_id,"" as supplier,sales.location_id,item_kits.cost_price as current_cost_price, item_kits.unit_price as current_selling_price, item_kits.name, item_kits.item_kit_number, item_kits.product_id, categories.name as category , item_kits.category_id, sum('.$this->db->dbprefix('sales_item_kits').'.quantity_purchased) as quantity_purchased, sum('.$this->db->dbprefix('sales_item_kits').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_item_kits').'.total) as total, sum('.$this->db->dbprefix('sales_item_kits').'.tax) as tax, sum('.$this->db->dbprefix('sales_item_kits').'.profit) as profit', false);
		}
		
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales_item_kits.sale_id = sales.sale_id');
		$this->db->join('price_tiers', 'sales.tier_id = price_tiers.id', 'left');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id', 'left');
		$this->db->join('categories', 'categories.id = item_kits.category_id', 'left');
		
		$this->db->where('sales.deleted', 0);
		$this->sale_time_where();
		
		if (isset($this->params['register_id']) && $this->params['register_id'])
		{
			$this->db->where('sales.register_id',$this->params['register_id']);
		}
		
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
			$this->db->where_in('item_kits.category_id', $category_ids);
		}
		
		
		if (isset($this->params['customer_id']) && $this->params['customer_id'])
		{
			$this->db->where('sales.customer_id', $this->params['customer_id']);
		}	

				
		$this->db->group_by('sales_item_kits.item_kit_id');
		
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
	
		
	private function get_items_without_sales($paginate = TRUE)
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
		
		$items_with_sales_query = $this->get_items_with_sales_query(FALSE,TRUE)->get_compiled_select();
		
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		
		$this->db->from('items');
		
		if (isset($this->params['group_by_variation']) && $this->params['group_by_variation'])
		{
			$this->db->join('item_variations','item_variations.item_id = items.item_id');
			$this->db->select('item_variations.id as item_variation_id,items.item_id,suppliers.company_name as supplier,items.cost_price as current_cost_price,items.unit_price as current_selling_price, items.name, items.item_number, items.product_id, categories.name as category , items.category_id, 0 as quantity_purchased, 0 as subtotal, 0 as total, 0 as tax, 0 as profit', false);
		}
		else
		{
			$this->db->select('items.item_id,suppliers.company_name as supplier,items.cost_price as current_cost_price,items.unit_price as current_selling_price, items.name, items.item_number, items.product_id, categories.name as category , items.category_id, 0 as quantity_purchased, 0 as subtotal, 0 as total, 0 as tax, 0 as profit', false);
		}
		
			
		$this->db->join('categories','categories.id = items.category_id', 'left');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id', 'left');
		
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
		
		
		if (isset($this->params['group_by_variation']) && $this->params['group_by_variation'])
		{
			$this->db->group_by('item_variations.id,items.item_id');
		}
				
		$this->db->where($this->db->dbprefix('items').'.item_id NOT IN('.$items_with_sales_query.') and '.$this->db->dbprefix('items').'.deleted=0',null, FALSE);
		
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

	private function get_item_kits_without_sales($paginate = TRUE)
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
		
		$item_kits_with_sales_query = $this->get_item_kits_with_sales_query(FALSE,TRUE)->get_compiled_select();
		
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		
		$this->db->from('item_kits');
		if (isset($this->params['group_by_variation']) && $this->params['group_by_variation'])
		{
			$this->db->select('"" as item_variation_id,item_kits.item_kit_id,"" as supplier,item_kits.cost_price as current_cost_price,item_kits.unit_price as current_selling_price, item_kits.name, item_kits.item_kit_number as item_number, item_kits.product_id, categories.name as category , item_kits.category_id, 0 as quantity_purchased, 0 as subtotal, 0 as total, 0 as tax, 0 as profit', false);
		}
		else
		{
			$this->db->select('item_kits.item_kit_id,"" as supplier,item_kits.cost_price as current_cost_price,item_kits.unit_price as current_selling_price, item_kits.name, item_kits.item_kit_number as item_number, item_kits.product_id, categories.name as category , item_kits.category_id, 0 as quantity_purchased, 0 as subtotal, 0 as total, 0 as tax, 0 as profit', false);
		}
		
		$this->db->join('categories','categories.id = item_kits.category_id', 'left');
		
		if ($this->params['category_id'])
		{			
			$this->db->where_in('item_kits.category_id', $category_ids);
		}
					
		$this->db->where($this->db->dbprefix('item_kits').'.item_kit_id NOT IN('.$item_kits_with_sales_query.') and '.$this->db->dbprefix('item_kits').'.deleted=0',null, FALSE);
		
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
		$primary_key_column = isset($this->params['group_by_variation']) && $this->params['group_by_variation'] ? 'item_variation_id' : 'item_id';
		
		if (isset($this->params['include_item_kits']) && $this->params['include_item_kits'])
		{
			$items_sales_query = $this->get_items_with_sales_query(FALSE)->get_compiled_select();	
			$items_kits_sales_query = $this->get_item_kits_with_sales_query(FALSE)->get_compiled_select();
			
			//If we are exporting NOT exporting to excel make sure to use offset and limit
			if (isset($this->params['export_excel']) && !$this->params['export_excel'])
			{
				$limit = $this->report_limit;
				$offset = 0;
				
				if (isset($this->params['offset']))
				{
					$offset = $this->params['offset'];
				}
				
				$items_sales_data = $this->db->query($items_sales_query." UNION ALL ".$items_kits_sales_query." order by name limit ".$offset.", ".$limit)->result_array();
				
			}
			else
			{
				$items_sales_data = $this->db->query($items_sales_query." UNION ALL ".$items_kits_sales_query.' order by name')->result_array();
			}
		}
		else
		{
			$items_sales_data = $this->get_items_with_sales_query(TRUE)->get()->result_array();	
		}
		
		if (isset($this->params['items_to_show']) && ($this->params['items_to_show'] == 'items_without_sales'))
		{
			if (isset($this->params['include_item_kits']) && $this->params['include_item_kits'])
			{
				$items_sales_query = $this->get_items_without_sales(FALSE)->get_compiled_select();	
				$items_kits_sales_query = $this->get_item_kits_without_sales(FALSE)->get_compiled_select();
				
				if (isset($this->params['export_excel']) && !$this->params['export_excel'])
				{
					$limit = $this->report_limit;
					$offset = 0;
				
					if (isset($this->params['offset']))
					{
						$offset = $this->params['offset'];
					}	
					$items_in_report_without_sales = $this->db->query($items_sales_query." UNION ALL ".$items_kits_sales_query." order by name limit ".$offset.", ".$limit)->result_array();
				}
				else
				{
					$items_in_report_without_sales = $this->db->query($items_sales_query." UNION ALL ".$items_kits_sales_query.' order by name')->result_array();
				}
			}
			else
			{
				$items_in_report_without_sales = $this->get_items_without_sales()->get()->result_array();	
			}
		}
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
		
		if (isset($this->params['items_to_show']) && ($this->params['items_to_show'] == 'items_without_sales'))
		{
			for($k=0;$k<count($items_in_report_without_sales);$k++)
			{
				$items_in_report_without_sales[$k]['quantity'] = $quantities_indexed_by_id[$items_in_report_without_sales[$k][$primary_key_column]];
			}			
		}
		
		$return = array();
		
		if($this->params['items_to_show'] == 'items_without_sales')
		{
			$return = $items_in_report_without_sales;
		}
		elseif($this->params['items_to_show'] == 'items_with_sales')
		{
			$return = $items_sales_data;
		}
		return $return;

	}
	
	function getTotalRows()
	{
		if($this->params['items_to_show'] && $this->params['items_to_show'] == 'items_with_sales')
		{
			return $this->get_items_with_sales_query(FALSE)->count_all_results();
		} 
		elseif($this->params['items_to_show'] && $this->params['items_to_show'] == 'items_without_sales') 
		{
			return  $this->get_items_without_sales(FALSE)->count_all_results();
		}
	}
	
	public function getSummaryData()
	{
		if($this->params['items_to_show'] == 'items_with_sales')
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
		
			$this->db->select('sum('.$this->db->dbprefix('sales_items').'.quantity_purchased) as total_number_of_items_sold, sum('.$this->db->dbprefix('sales_items').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_items').'.total) as total, sum('.$this->db->dbprefix('sales_items').'.tax) as tax, sum('.$this->db->dbprefix('sales_items').'.profit) as profit', false);
			$this->db->from('sales');	
			$this->db->join('sales_items', 'sales_items.sale_id = sales.sale_id');
			$this->db->join('items', 'sales_items.item_id = items.item_id');
			$this->db->join('manufacturers', 'manufacturers.id = items.manufacturer_id', 'left');
			$this->db->where('sales.deleted', 0);
			$this->sale_time_where();
		
			if (isset($this->params['register_id']) && $this->params['register_id'])
			{
				$this->db->where('sales.register_id',$this->params['register_id']);
			}
		
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
			
			if (isset($this->params['manufacturer_id']) && $this->params['manufacturer_id'])
			{
				$this->db->where('items.manufacturer_id', $this->params['manufacturer_id']);			
			}
			
		
			if (isset($this->params['customer_id']) && $this->params['customer_id'])
			{
				$this->db->where('sales.customer_id', $this->params['customer_id']);
			}	
		
		
			
			$return = array(
				'subtotal' => 0,
				'total' => 0,
				'tax' => 0,
				'total_number_of_items_sold' => 0,
				'profit' => 0,
				'cogs' => 0,
				'damaged_qty' => 0,
			);
			
			foreach($this->db->get()->result_array() as $row)
			{
				$return['subtotal'] += to_currency_no_money($row['subtotal'],2);
				$return['total'] += to_currency_no_money($row['total'],2);
				$return['tax'] += to_currency_no_money($row['tax'],2);
				$return['total_number_of_items_sold'] +=  $row['total_number_of_items_sold'];
				$return['profit'] += to_currency_no_money($row['profit'],2);
				$return['cogs'] += to_currency_no_money($row['subtotal'] - $row['profit'],2);
			}
			
			if (isset($this->params['include_item_kits']) && $this->params['include_item_kits'])
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
		
				$this->db->select('sum('.$this->db->dbprefix('sales_item_kits').'.quantity_purchased) as total_number_of_items_sold,sum('.$this->db->dbprefix('sales_item_kits').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_item_kits').'.total) as total, sum('.$this->db->dbprefix('sales_item_kits').'.tax) as tax, sum('.$this->db->dbprefix('sales_item_kits').'.profit) as profit', false);
				$this->db->from('sales');	
				$this->db->join('sales_item_kits', 'sales_item_kits.sale_id = sales.sale_id');
				$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id');

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
					$this->db->where_in('item_kits.category_id', $category_ids);
				}
		
		
				if (isset($this->params['customer_id']) && $this->params['customer_id'])
				{
					$this->db->where('sales.customer_id', $this->params['customer_id']);
				}	
		
		
				
				
				foreach($this->db->get()->result_array() as $row)
				{
					$return['subtotal'] += to_currency_no_money($row['subtotal'],2);
					$return['total'] += to_currency_no_money($row['total'],2);
					$return['tax'] += to_currency_no_money($row['tax'],2);
					$return['total_number_of_items_sold'] +=$row['total_number_of_items_sold'];
					$return['profit'] += to_currency_no_money($row['profit'],2);
					$return['cogs'] += to_currency_no_money($row['subtotal'] - $row['profit'],2);
				}
				
			}
		}
		else
		{
			$return = array();
		}
		
		if(!$this->has_profit_permission)
		{
			unset($return['profit']);
			unset($return['cogs']);
		}
		
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		
		$damaged_query = $this->db->query('SELECT SUM(phppos_damaged_items_log.damaged_qty) as damaged_qty FROM phppos_damaged_items_log WHERE location_id in ('.$location_ids_string.') and damaged_date BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']))->row();
		
		if ($damaged_query->damaged_qty)
		{
			$return['damaged_qty'] = $damaged_query->damaged_qty;
		}
		return $return;
	}
}
?>
