<?php
require_once ("Report.php");
class Summary_categories extends Report
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Category');
	}
	
	
	public function getInputData()
	{
		$this->load->model('Tier');
		
		$input_params = array();

		$specific_entity_data = array();
		$specific_entity_data['view']  = 'specific_entity';
		$specific_entity_data['specific_input_name'] = 'employee_id';
		$specific_entity_data['specific_input_label'] = lang('common_employee');
		$employees = array('' => lang('common_all'));

		foreach($this->Employee->get_all()->result() as $employee)
		{
			$employees[$employee->person_id] = $employee->first_name .' '.$employee->last_name;
		}
		$specific_entity_data['specific_input_data'] = $employees;
		
		
		if ($this->settings['display'] == 'tabular')
		{
			$input_data = Report::get_common_report_input_data(TRUE);
			
			$input_params = array(
				array('view' => 'date_range', 'with_time' => TRUE),
				array('view' => 'date_range', 'with_time' => TRUE, 'compare_to' => TRUE),
				$specific_entity_data,
				array('view' => 'dropdown','dropdown_label' =>lang('reports_sale_type'),'dropdown_name' => 'sale_type','dropdown_options' =>array('all' => lang('reports_all'), 'sales' => lang('reports_sales'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all'),
				array('view' => 'checkbox','checkbox_label' => lang('reports_show_top_level_category_summary'), 'checkbox_name' => 'top_level_cat_summary'),
				array('view' => 'excel_export'),
				array('view' => 'locations'),
				array('view' => 'submit'),
			);
		}
		elseif ($this->settings['display'] == 'graphical')
		{
			$input_data = Report::get_common_report_input_data(FALSE);
			
			$input_params = array(
				array('view' => 'date_range', 'with_time' => TRUE),
				$specific_entity_data,
				array('view' => 'dropdown','dropdown_label' =>lang('reports_sale_type'),'dropdown_name' => 'sale_type','dropdown_options' =>array('all' => lang('reports_all'), 'sales' => lang('reports_sales'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all'),
				array('view' => 'checkbox','checkbox_label' => lang('reports_show_top_level_category_summary'), 'checkbox_name' => 'top_level_cat_summary'),
				array('view' => 'locations'),
				array('view' => 'submit'),
			);
		
		}
		
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
		
		if (count($tiers_phppos))
		{
			array_unshift($input_params,$tier_entity_data);
		}
		
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}
	
	function getOutputData()
	{
		
		$do_compare = isset($this->params['compare_to']) && $this->params['compare_to'];		
		$subtitle = date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date'])).($do_compare  ? ' '. lang('reports_compare_to'). ' '. date(get_date_format(), strtotime($this->params['start_date_compare'])) .'-'.date(get_date_format(), strtotime($this->params['end_date_compare'])) : '');

		$report_data = $this->getData();
		
		if (isset($this->params['top_level_cat_summary']) && $this->params['top_level_cat_summary'])
		{
			$new_report_data = array();
			
			foreach($report_data as $row)
			{
				$row['category_id'] = $this->Category->get_root_parent_category_id($row['category_id']);
				$new_report_data[] = $row;
			}
			
			$report_data = $this->merge_category_data($new_report_data);
		}
		
		$summary_data = $this->getSummaryData();
		
		$inventory_totals_by_category = $this->getInventoryTotalsCategories();
		
		if ($this->settings['display'] == 'tabular')
		{				
			$this->setupDefaultPagination();
			$tabular_data = array();
			
			if ($do_compare)
			{
				$report_data_compare_model = new Summary_categories();
				$report_data_compare_model->report_key = $this->report_key;
				$report_data_compare_model->setSettings($this->settings);
				$report_data_compare_model->setParams(array_merge($this->params,array('start_date'=>$this->params['start_date_compare'], 'end_date'=>$this->params['end_date_compare'])));

				$report_data_compare = $report_data_compare_model->getData();
				$report_data_summary_compare = $report_data_compare_model->getSummaryData();
				
				if (isset($this->params['top_level_cat_summary']) && $this->params['top_level_cat_summary'])
				{
					$new_report_data_compare = array();
				
					foreach($report_data_compare as $row)
					{
						$row['category_id'] = $this->Category->get_root_parent_category_id($row['category_id']);
						$new_report_data_compare[] = $row;
					}
				
					$report_data_compare = $this->merge_category_data($new_report_data_compare);
				}
			}

			$index = 0;
			
			foreach($report_data as $row)
			{
				$data_row = array();
				if ($do_compare)
				{
					if (isset($report_data_compare[$row['category_id']]))
					{
						$row_compare = $report_data_compare[$row['category_id']];
					}
					else
					{
						$row_compare = FALSE;
					}
				}
			
			
				$data_row[] = array('data'=>$this->Category->get_full_path($row['category_id']), 'align' => 'left');
				$data_row[] = array('data'=>to_currency($row['subtotal']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['subtotal'] >= $row['subtotal'] ? ($row['subtotal'] == $row_compare['subtotal'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['subtotal']) .'</span>':''), 'align' => 'right');
				$data_row[] = array('data'=>to_currency($row['total']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['total'] >= $row['total'] ? ($row['total'] == $row_compare['total'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['total']) .'</span>':''), 'align' => 'right');
				$data_row[] = array('data'=>to_currency($row['discount_total']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['discount_total'] >= $row['discount_total'] ? ($row['discount_total'] == $row_compare['discount_total'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['discount_total']) .'</span>':''), 'align' => 'right');
				$data_row[] = array('data'=>to_quantity($inventory_totals_by_category[$row['category_id']]['total_items_in_inventory']), 'align' => 'right');
				$data_row[] = array('data'=>to_currency($inventory_totals_by_category[$row['category_id']]['inventory_total']), 'align' => 'right');
				$data_row[] = array('data'=>to_currency($inventory_totals_by_category[$row['category_id']]['inventory_sale_total']), 'align' => 'right');
				
				//Unset so at the end we can display categories that we didn't show
				unset($inventory_totals_by_category[$row['category_id']]);				
								
				$data_row[] = array('data'=>to_currency($row['tax']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['tax'] >= $row['tax'] ? ($row['tax'] == $row_compare['tax'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['tax']) .'</span>':''), 'align' => 'right');
				if($this->has_profit_permission)
				{
					$data_row[] = array('data'=>to_currency($row['profit']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['profit'] >= $row['profit'] ? ($row['profit'] == $row_compare['profit'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['profit']) .'</span>':''), 'align' => 'right');
				}
				$data_row[] = array('data'=>floatval($row['item_sold']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['item_sold'] >= $row['item_sold'] ? ($row['item_sold'] == $row_compare['item_sold'] ?  '' : 'compare_better') : 'compare_worse').'">'.floatval($row_compare['item_sold']) .'</span>':''), 'align' => 'right');
				
				$tabular_data[] = $data_row;				
			}
			
			foreach($inventory_totals_by_category as $category_id => $total_inventory)
			{
				$data_row = array();
				
				$data_row[] = array('data'=>$this->Category->get_full_path($category_id), 'align' => 'left');
				$data_row[] = array('data'=>to_currency(0), 'align' => 'left');
				$data_row[] = array('data'=>to_currency(0), 'align' => 'left');
				$data_row[] = array('data'=>to_currency(0), 'align' => 'left');

				$data_row[] = array('data'=>to_quantity($inventory_totals_by_category[$category_id]['total_items_in_inventory']), 'align' => 'right');
				$data_row[] = array('data'=>to_currency($inventory_totals_by_category[$category_id]['inventory_total']), 'align' => 'right');
				$data_row[] = array('data'=>to_currency($inventory_totals_by_category[$category_id]['inventory_sale_total']), 'align' => 'right');
				
											
								
				$data_row[] = array('data'=>to_currency(0), 'align' => 'left');
				if($this->has_profit_permission)
				{
					$data_row[] = array('data'=>to_currency(0), 'align' => 'left');
				}
				$data_row[] = array('data'=>to_quantity(0), 'align' => 'left');
				
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
				'view' => 'tabular',
				"title" => lang('reports_categories_summary_report'),
				"subtitle" => $subtitle,
				"headers" => $this->getDataColumns(),
				"data" => $tabular_data,
				"summary_data" => $summary_data,
				"export_excel" => $this->params['export_excel'],
				"pagination" => $this->pagination->create_links(),
			);
			
		}
		elseif($this->settings['display'] == 'graphical')
		{
			$graph_data = array();
			foreach($report_data as $row)
			{
				$graph_data[$this->Category->get_full_path($row['category_id'])] = to_currency_no_money($row['total']);
			}

			$currency_symbol = $this->config->item('currency_symbol') ? $this->config->item('currency_symbol') : '$';

			$data = array(
				'view' => 'graphical',
				'graph' => 'pie',
				"summary_data" => $summary_data,
				"title" => lang('reports_categories_summary_report'),
				"data" => $graph_data,
				"subtitle" => $subtitle,
				"tooltip_template" => "<%=label %>: ".((!$this->config->item('currency_symbol_location') || $this->config->item('currency_symbol_location') =='before') ? $currency_symbol : '')."<%= parseFloat(Math.round(value * 100) / 100).toFixed(".$this->decimals.") %>".($this->config->item('currency_symbol_location') =='after' ? $currency_symbol: ''),
			  "legend_template" => "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%> (".((!$this->config->item('currency_symbol_location') || $this->config->item('currency_symbol_location') =='before') ? $currency_symbol : '')."<%=parseFloat(Math.round(segments[i].value * 100) / 100).toFixed(".$this->decimals.")%>".($this->config->item('currency_symbol_location') =='after' ?  $currency_symbol : '').")<%}%></li><%}%></ul>"
			);
		}
		
		return $data;
	}
	
	public function getDataColumns()
	{
		$columns = array();
		
		$columns[] = array('data'=>lang('reports_category'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
		$columns[] = array('data'=>lang('reports_total'), 'align'=> 'right');
		$columns[] = array('data'=>lang('reports_total_discounts'), 'align'=> 'right');
		$columns[] = array('data'=>lang('reports_quantity'), 'align'=> 'right');
		$columns[] = array('data'=>lang('reports_inventory_total'), 'align'=> 'right');
		$columns[] = array('data'=>lang('reports_inventory_sale_total'), 'align'=> 'right');
		
		$columns[] = array('data'=>lang('common_tax'), 'align'=> 'right');

		if($this->has_profit_permission)
		{
			$columns[] = array('data'=>lang('common_profit'), 'align'=> 'right');
		}
		$columns[] = array('data'=>lang('common_items_sold'), 'align'=> 'right');
		
		return $columns;		
	}
	
	public function getData()
	{	
		$this->db->select('SUM(item_unit_price * quantity_purchased*(discount_percent/100)) as discount_total,items.category_id, categories.name as category , sum('.$this->db->dbprefix('sales_items').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_items').'.total) as total, sum('.$this->db->dbprefix('sales_items').'.tax) as tax, sum('.$this->db->dbprefix('sales_items').'.profit) as profit, sum('.$this->db->dbprefix('sales_items').'.quantity_purchased) as item_sold', false);
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		$this->db->join('price_tiers', 'sales.tier_id = price_tiers.id', 'left');
		$this->db->join('items', 'sales_items.item_id = items.item_id');
		$this->db->join('categories', 'categories.id = items.category_id');
		$this->sale_time_where();
		$this->db->where('sales.deleted', 0);
		
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
		
		
		if (isset($this->params['employee_id']) && $this->params['employee_id'])
		{
			$this->db->where('sales.employee_id', $this->params['employee_id']);
		}
		
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('sales.store_account_payment', 0);
		}

		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}
		
		if (isset($this->params['compare_to_categories']) && count($this->params['compare_to_categories']) > 0)
		{
			$this->db->where_in('items.category_id', $this->params['compare_to_categories']);
		}	
		
		$this->db->group_by('items.category_id');
		
		
		$items= $this->db->get()->result_array();	

		$this->db->select('SUM(item_kit_unit_price * quantity_purchased*(discount_percent/100)) as discount_total,item_kits.category_id, categories.name as category , sum('.$this->db->dbprefix('sales_item_kits').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_item_kits').'.total) as total, sum('.$this->db->dbprefix('sales_item_kits').'.tax) as tax, sum('.$this->db->dbprefix('sales_item_kits').'.profit) as profit, sum('.$this->db->dbprefix('sales_item_kits').'.quantity_purchased) as item_sold', false);
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id');
		$this->db->join('price_tiers', 'sales.tier_id = price_tiers.id', 'left');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id');
		$this->db->join('categories', 'categories.id = item_kits.category_id');		
		$this->sale_time_where();
		$this->db->where('sales.deleted', 0);
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('sales.store_account_payment', 0);
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
		
		if (isset($this->params['compare_to_categories']) && count($this->params['compare_to_categories']) > 0)
		{
			$this->db->where_in('item_kits.category_id', $this->params['compare_to_categories']);
		}	
		
			
		$this->db->group_by('item_kits.category_id');		
		$item_kits = $this->db->get()->result_array();
		$items_and_kits = $this->merge_item_and_item_kits($items, $item_kits);
		
		return $items_and_kits;
	}
	
	public function getSummaryData()
	{
		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit', false);
		$this->db->from('sales');
		$this->db->join('price_tiers', 'sales.tier_id = price_tiers.id', 'left');
		
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
			$this->db->where('sales.total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('sales.total_quantity_purchased < 0');
		}
		
		if (isset($this->params['employee_id']) && $this->params['employee_id'])
		{
			$this->db->where('sales.employee_id', $this->params['employee_id']);
		}
		
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('sales.store_account_payment', 0);
		}
		
		
		$this->sale_time_where();
		$this->db->where('deleted', 0);
		
		$return = array(
			'subtotal' => 0,
			'total' => 0,
			'tax' => 0,
			'profit' => 0,
		);
		
		foreach($this->db->get()->result_array() as $row)
		{
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
	
	function getTotalRows()
	{
		$this->db->from('categories');
		$this->db->where('deleted',0);
		return $this->db->count_all_results();
	}
	
	private function merge_category_data($category_data)
	{
		$new_cats = array();
				
		$merged = array();
		
		foreach($category_data as $row)
		{
			$category = $row['category_id'];
			
			if (!isset($merged[$category]))
			{
				$merged[$category] = $row;
			}
			else
			{
				$merged[$category]['subtotal']+= $row['subtotal'];
				$merged[$category]['total']+= $row['total'];
				$merged[$category]['tax']+= $row['tax'];
				$merged[$category]['profit']+= $row['profit'];
				$merged[$category]['item_sold']+= $row['item_sold'];
				$merged[$category]['discount_total']+= $row['discount_total'];
			}
		}
		
		return $merged;
		
		
	}
		
	private function merge_item_and_item_kits($items, $item_kits)
	{
		$location_ids = self::get_selected_location_ids();
		$new_items = array();
		$new_item_kits = array();
		
		foreach($items as $item)
		{
			$new_items[$item['category_id']] = $item;
		}
		
		foreach($item_kits as $item_kit)
		{
			$new_item_kits[$item_kit['category_id']] = $item_kit;
		}
		
		$merged = array();
		
		foreach($new_items as $category=>$row)
		{
			if (!isset($merged[$category]))
			{
				$merged[$category] = $row;
			}
			else
			{
				$merged[$category]['subtotal']+= $row['subtotal'];
				$merged[$category]['total']+= $row['total'];
				$merged[$category]['tax']+= $row['tax'];
				$merged[$category]['profit']+= $row['profit'];
				$merged[$category]['item_sold']+= $row['item_sold'];
				$merged[$category]['discount_total']+= $row['discount_total'];
			}
		}
		
		foreach($new_item_kits as $category=>$row)
		{
			if (!isset($merged[$category]))
			{
				$merged[$category] = $row;
			}
			else
			{
				$merged[$category]['subtotal']+= $row['subtotal'];
				$merged[$category]['total']+= $row['total'];
				$merged[$category]['tax']+= $row['tax'];
				$merged[$category]['profit']+= $row['profit'];
				$merged[$category]['item_sold']+= $row['item_sold'];
				$merged[$category]['discount_total']+= $row['discount_total'];
			}
		}
		
		
		return $merged;
	}
	
	function getInventoryTotalsCategories()
	{

		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		$location_ids_string = implode(',',$location_ids);
		
		$location_item_variations_quantity_col = $this->db->dbprefix('location_item_variations').'.quantity';
		$location_items_quantity_col = $this->db->dbprefix('location_items').'.quantity';
		
		$full_sum_query = 'COALESCE(SUM('.$location_item_variations_quantity_col.'),SUM('.$location_items_quantity_col.'),0)';
		$quantity_query = 'COALESCE('.$location_item_variations_quantity_col.','.$location_items_quantity_col.',0)';			


    	$query = 'select
			category_id,
            sum(total_items_in_inventory) total_items_in_inventory,
            sum(inventory_total) inventory_total,
            sum(inventory_sale_total) inventory_sale_total,
            sum(inventory_total) / sum(total_items_in_inventory) as weighted_cost
            from (
              select
                phppos_items.item_id,phppos_items.category_id, coalesce(q.location_id, v.location_id, null) location_id,
                ifnull(q.quantity,0) + ifnull(v.variation_quantity,0) as total_items_in_inventory,
                      coalesce(q.q_cost_price, v.v_cost_price, phppos_items.cost_price, 0) as cost_price,
                (ifnull(q.quantity,0) + ifnull(v.variation_quantity,0)) * coalesce(v.v_cost_price, q.q_cost_price, phppos_items.cost_price, 0) as inventory_total,
                (ifnull(q.quantity,0) + ifnull(v.variation_quantity,0)) * coalesce(v.v_unit, q.q_unit, phppos_items.unit_price, 0) as inventory_sale_total,
                q_unit,
                v_unit,
                q_cost_price,
                v_cost_price

              FROM phppos_items

              left join (
                select phppos_location_items.item_id, phppos_location_items.location_id, sum(quantity) quantity
                  ,coalesce(phppos_location_items.cost_price, phppos_items.cost_price) q_cost_price 
                  ,coalesce(phppos_location_items.unit_price, phppos_items.unit_price) q_unit
                from phppos_location_items
                        join phppos_items on phppos_items.item_id = phppos_location_items.item_id
                where phppos_location_items.item_id not in (select item_id from phppos_item_variations)
                  and phppos_location_items.`location_id` IN ('.$location_ids_string.')
                group by item_id, location_id
              ) q on phppos_items.item_id=q.item_id

              left JOIN (
                SELECT item_id, item_variation_id, location_id
                  ,sum(quantity) variation_quantity
                  ,coalesce(phppos_location_item_variations.cost_price, phppos_item_variations.cost_price) v_cost_price
                  ,coalesce(phppos_location_item_variations.unit_price, phppos_item_variations.unit_price) v_unit
                FROM phppos_location_item_variations
                join phppos_item_variations on phppos_item_variations.id=phppos_location_item_variations.item_variation_id
                WHERE 1 = 1
                        and phppos_item_variations.deleted = 0
                        and `phppos_location_item_variations`.`location_id` IN ('.$location_ids_string.')
                        GROUP BY item_id, item_variation_id, location_id
               ) v on phppos_items.item_id=v.item_id
               where
                 phppos_items.deleted = 0
					       and is_service != 1
             ) x GROUP BY category_id';
                    
			 
			 $return = array();
			 
	 		foreach($this->db->query($query)->result_array() as $row)
			{
				$category_id = $row['category_id'];
				unset($row['category_id']);
				$return[$category_id] = $row;
			}
			
			return $return;
	}
}
?>