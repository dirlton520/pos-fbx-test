<?php
require_once ("Report.php");
class Summary_sales extends Report
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Tier');
		
	}
	
	public function getInputData()
	{
    $CI =& get_instance();
		
		$payment_types = array();
		$payment_types['']  = lang('common_all');
		$payment_types = array_merge($payment_types,array_flip($CI->Sale->get_payment_options_with_language_keys()));
		
		
		$input_params = array();
		
		$specific_entity_data['specific_input_name'] = 'item_id';
		$specific_entity_data['specific_input_label'] = lang('common_item');
		$specific_entity_data['search_suggestion_url'] = site_url('reports/item_search');
		$specific_entity_data['view'] = 'specific_entity';
		
		
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
		
		
		$category_entity_data = array();
		$category_entity_data['specific_input_name'] = 'category_id';
		$category_entity_data['specific_input_label'] = lang('reports_category');
		$category_entity_data['view'] = 'specific_entity';
		
		$categories = array();
		$categories[] =lang('common_all');
		
		$categories_phppos= $this->Category->sort_categories_and_sub_categories($this->Category->get_all_categories_and_sub_categories());
		
		foreach($categories_phppos as $key=>$value)
		{
			$name = $this->config->item('show_full_category_path') ? str_repeat('&nbsp;&nbsp;', $value['depth']).$this->Category->get_full_path($key) : str_repeat('&nbsp;&nbsp;', $value['depth']).$value['name'];
			$categories[$key] = $name;
		}
		
		$category_entity_data['specific_input_data'] = $categories;
		
		
		if ($this->settings['display'] == 'tabular')
		{
			$input_data = Report::get_common_report_input_data(TRUE);
			
			$input_params = array(
				array('view' => 'date_range', 'with_time' => TRUE),
				array('view' => 'date_range', 'with_time' => TRUE, 'compare_to' => TRUE),
				$specific_entity_data,
				$category_entity_data,
				array('view' => 'dropdown','dropdown_label' =>lang('common_payment'),'dropdown_name' => 'payment_type','dropdown_options' => $payment_types,'dropdown_selected_value' => ''),
				array('view' => 'dropdown','dropdown_label' =>lang('reports_sale_type'),'dropdown_name' => 'sale_type','dropdown_options' =>array('all' => lang('reports_all'), 'sales' => lang('reports_sales'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all'),
				array('view' => 'dropdown','dropdown_label' => lang('reports_group_by'),'dropdown_name' => 'group_by','dropdown_options' =>array('' => lang('common_day'),'YEAR(sale_date), MONTH(sale_date), WEEK(sale_date)' => lang('common_week'), 'YEAR(sale_date), MONTH(sale_date)' => lang('common_month'), 'YEAR(sale_date)' => lang('common_year')),'dropdown_selected_value' => ''),
				array('view' => 'excel_export'),
				 array('view' => 'checkbox','checkbox_label' => lang('reports_list_each_location_separately'), 'checkbox_name' => 'list_each_location_separately'),				
				 array('view' => 'checkbox','checkbox_label' => lang('reports_ecommerce_only'), 'checkbox_name' => 'ecommerce_only'),				
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
				$category_entity_data,
				array('view' => 'dropdown','dropdown_label' =>lang('common_payment'),'dropdown_name' => 'payment_type','dropdown_options' => $payment_types,'dropdown_selected_value' => ''),
				array('view' => 'dropdown','dropdown_label' =>lang('reports_sale_type'),'dropdown_name' => 'sale_type','dropdown_options' =>array('all' => lang('reports_all'), 'sales' => lang('reports_sales'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all'),
				array('view' => 'dropdown','dropdown_label' => lang('reports_group_by'),'dropdown_name' => 'group_by','dropdown_options' =>array('' => lang('common_day'),'YEAR(sale_date), MONTH(sale_date), WEEK(sale_date)' => lang('common_week'), 'YEAR(sale_date), MONTH(sale_date)' => lang('common_month'), 'YEAR(sale_date)' => lang('common_year')),'dropdown_selected_value' => ''),
				 array('view' => 'checkbox','checkbox_label' => lang('reports_list_each_location_separately'), 'checkbox_name' => 'list_each_location_separately'),				
				 array('view' => 'checkbox','checkbox_label' => lang('reports_ecommerce_only'), 'checkbox_name' => 'ecommerce_only'),
				array('view' => 'locations'),
				array('view' => 'submit'),
			);
		
		}
		
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
		$summary_data = $this->getSummaryData();
		
		if ($this->settings['display'] == 'tabular')
		{				
			$this->setupDefaultPagination();
			$tabular_data = array();
			
			if ($do_compare)
			{
				$report_data_compare_model = new Summary_sales();
				$report_data_compare_model->report_key = $this->report_key;
				$report_data_compare_model->setSettings($this->settings);
				$report_data_compare_model->setParams(array_merge($this->params,array('start_date'=>$this->params['start_date_compare'], 'end_date'=>$this->params['end_date_compare'])));

				$report_data_compare = $report_data_compare_model->getData();
				$report_data_summary_compare = $report_data_compare_model->getSummaryData();
			}

			$index = 0;
			foreach($report_data as $row)
			{
				$data_row = array();
				if ($do_compare)
				{
					if (isset($report_data_compare[$index]))
					{
						$row_compare = $report_data_compare[$index];
					}
					else
					{
						$row_compare = FALSE;
					}
				}
				
				if (isset($this->params['list_each_location_separately']) && $this->params['list_each_location_separately'])
				{
					$data_row[] = array('data'=>$row['location'], 'align'=>'left');					
				}
				
				$data_row[] = array('data'=>date(get_date_format(), strtotime($row['sale_date'])).($do_compare && $row_compare ? ' / <span class="compare ">'.date(get_date_format(), strtotime($row_compare['sale_date'])).'</span>':''), 'align'=>'left');
				$data_row[] = array('data'=>lang('common_'.strtolower(date('l',strtotime($row['sale_date'])))), 'align'=>'left');
				$data_row[] = array('data'=>to_quantity($row['count']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['count'] >= $row['count'] ? ($row['count'] == $row_compare['count'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_quantity($row_compare['count']) .'</span>':''), 'align'=>'center');
				$data_row[] = array('data'=>to_currency($row['subtotal']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['subtotal'] >= $row['subtotal'] ? ($row['subtotal'] == $row_compare['subtotal'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['subtotal']) .'</span>':''), 'align'=>'right');
				$data_row[] = array('data'=>to_currency($row['total']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['total'] >= $row['total'] ? ($row['total'] == $row_compare['total'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['total']) .'</span>':''), 'align'=>'right');
				$data_row[] = array('data'=>to_currency($row['tax']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['tax'] >= $row['tax'] ? ($row['tax'] == $row_compare['tax'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['tax']) .'</span>':''), 'align'=>'right');
			
				if($this->has_profit_permission)
				{
					$data_row[] = array('data'=>to_currency($row['profit']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['profit'] >= $row['profit'] ? ($row['profit'] == $row_compare['profit'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['profit']) .'</span>':''), 'align'=>'right');
				}
				$tabular_data[] = $data_row;
			
				$index++;
			}
		
			if ($do_compare)
			{
				foreach($summary_data as $key=>$value)
				{
					if ($key == 'sales_per_time_period')
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
				'view' => 'tabular',
				"title" => lang('reports_sales_summary_report'),
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
				$graph_data[date(get_date_format(), strtotime($row['sale_date']))]= to_currency_no_money($row['total']);
			}

			$currency_symbol = $this->config->item('currency_symbol') ? $this->config->item('currency_symbol') : '$';

			$data = array(
				'view' => 'graphical',
				'graph' => 'line',
				"summary_data" => $summary_data,
				"title" => lang('reports_sales_summary_report'),
				"data" => $graph_data,
				"subtitle" => $subtitle,
				"tooltip_template" => "<%=label %>: ".((!$this->config->item('currency_symbol_location') || $this->config->item('currency_symbol_location') =='before') ? $currency_symbol : '')."<%= parseFloat(Math.round(value * 100) / 100).toFixed(".$this->decimals.") %>".($this->config->item('currency_symbol_location') =='after' ? $currency_symbol: ''),
			);
		}
		
		return $data;
	}
	
	public function getDataColumns()
	{
		$columns = array();
		
		if (isset($this->params['list_each_location_separately']) && $this->params['list_each_location_separately'])
		{
			$columns[] = array('data'=>lang('common_location'), 'align'=> 'left');			
		}
		$columns[] = array('data'=>lang('reports_date'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_day'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_sales_per_time_period'), 'align'=> 'center');
		$columns[] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
		$columns[] = array('data'=>lang('reports_total'), 'align'=> 'right');
		$columns[] = array('data'=>lang('common_tax'), 'align'=> 'right');

		if($this->has_profit_permission)
		{
			$columns[] = array('data'=>lang('common_profit'), 'align'=> 'right');
		}
		
		return $columns;		
	}
	
	public function getData()
	{		
		
		if (isset($this->params['category_id']) && $this->params['category_id'])
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
		
		$this->db->from('sales');
		
		if((isset($this->params['item_id']) && $this->params['item_id']) || isset($category_ids))
		{
			$this->db->join('sales_items', 'sales_items.sale_id = sales.sale_id','left');
			$this->db->join('items','items.item_id = sales_items.item_id','left');
			$this->db->join('sales_item_kits','sales_item_kits.sale_id = sales.sale_id','left');
			$this->db->join('item_kits','item_kits.item_kit_id = sales_item_kits.item_kit_id','left');
			
			$sales_items= $this->db->dbprefix('sales_items');
			$sales_item_kits= $this->db->dbprefix('sales_item_kits');
			
			$this->db->select("count(sale_time) as count, date(sale_time) as sale_date, sum(COALESCE($sales_items.subtotal,0)) + sum(COALESCE($sales_item_kits.subtotal,0)) as subtotal, sum(COALESCE($sales_items.total,0)) +  sum(COALESCE($sales_item_kits.total,0)) as total, sum(COALESCE($sales_items.tax,0)) + sum(COALESCE($sales_item_kits.tax,0)) as tax, sum(COALESCE($sales_items.profit,0)) + sum(COALESCE($sales_item_kits.profit,0)) as profit", false);
			
			if (isset($this->params['item_id']) && $this->params['item_id'])
			{
				$this->db->where('sales_items.item_id',$this->params['item_id']);
			}
			
			if (isset($category_ids) && !empty($category_ids))
			{				
				$this->db->group_start();
				$this->db->where_in('items.category_id',$category_ids);
				$this->db->or_where_in('item_kits.category_id',$category_ids);
				$this->db->group_end();
			}
		}
		else
		{
			$this->db->select('locations.name as location,count(sale_time) as count, date(sale_time) as sale_date, sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit', false);
		}
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
		
		
		if (isset($this->params['payment_type']) && $this->params['payment_type'])
		{
			if (strpos($this->params['payment_type'],'common_') !== FALSE)
			{
				$payment_types = get_all_language_values_for_key($this->params['payment_type']);
			}
			else
			{
				$payment_types = array($this->params['payment_type']);				
			}
			
			$this->db->group_start();
			foreach($payment_types as $payment_type)
			{
				$this->db->or_like('sales.payment_type',$payment_type,'both');
			}
			$this->db->group_end();
		}
		
		
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
		}

		if (isset($this->params['ecommerce_only']) && $this->params['ecommerce_only'] == 1){
			$this->db->where('is_ecommerce', 1);
		}
		
		$this->sale_time_where();
		$this->db->where('sales.deleted', 0);
		$this->db->where_in('sales.location_id', $location_ids);
		
		$location_group_by = '';
		if (isset($this->params['list_each_location_separately']) && $this->params['list_each_location_separately'])
		{
			$location_group_by = 'sales.location_id,';
		}
		
		if (isset($this->params['group_by']) && $this->params['group_by'])
		{
			$this->db->group_by($location_group_by.$this->params['group_by'], TRUE);
		}
		else
		{
			$this->db->group_by($location_group_by.'sale_date');
		}
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
		
		
		if (isset($this->params['payment_type']) && $this->params['payment_type'])
		{
			if (strpos($this->params['payment_type'],'common_') !== FALSE)
			{
				$payment_types = get_all_language_values_for_key($this->params['payment_type']);
			}
			else
			{
				$payment_types = array($this->params['payment_type']);				
			}
			
			$this->db->group_start();
			foreach($payment_types as $payment_type)
			{
				$this->db->or_like('sales.payment_type',$payment_type,'both');
			}
			$this->db->group_end();
		}
		
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
		}
		$this->sale_time_where();
		$this->db->where('sales.deleted', 0);
		$this->db->where_in('sales.location_id', $location_ids);
		$location_group_by = '';
		if (isset($this->params['list_each_location_separately']) && $this->params['list_each_location_separately'])
		{
			$location_group_by = 'sales.location_id,';
		}
		
		if (isset($this->params['group_by']) && $this->params['group_by'])
		{
			$this->db->group_by($location_group_by.$this->params['group_by'], TRUE);
		}
		else
		{
			$this->db->group_by($location_group_by.'sale_date');
		}
		
		return $this->db->count_all_results();
	}
	
	public function getSummaryData()
	{
		
		if (isset($this->params['category_id']) && $this->params['category_id'])
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
		
		$this->db->from('sales');
		
		if((isset($this->params['item_id']) && $this->params['item_id']) || isset($category_ids))
		{
			$this->db->join('sales_items', 'sales_items.sale_id = sales.sale_id','left');
			$this->db->join('items','items.item_id = sales_items.item_id','left');
			$this->db->join('sales_item_kits','sales_item_kits.sale_id = sales.sale_id','left');
			$this->db->join('item_kits','item_kits.item_kit_id = sales_item_kits.item_kit_id','left');
			
			$sales_items= $this->db->dbprefix('sales_items');
			$sales_item_kits= $this->db->dbprefix('sales_item_kits');
			$this->db->select("count(sale_time) as count, date(sale_time) as sale_date, sum(COALESCE($sales_items.subtotal,0)) + sum(COALESCE($sales_item_kits.subtotal,0)) as subtotal, sum(COALESCE($sales_items.total,0)) +  sum(COALESCE($sales_item_kits.total,0)) as total, sum(COALESCE($sales_items.tax,0)) + sum($sales_item_kits.tax) as tax, sum(COALESCE($sales_items.profit,0)) + sum(COALESCE($sales_item_kits.profit,0)) as profit", false);
			
			if (isset($this->params['item_id']) && $this->params['item_id'])
			{
				$this->db->where('sales_items.item_id',$this->params['item_id']);
			}
			
			if (isset($category_ids) && !empty($category_ids))
			{
				
				$this->db->group_start();
				$this->db->where_in('items.category_id',$category_ids);
				$this->db->or_where_in('item_kits.category_id',$category_ids);
				$this->db->group_end();
			}
		}
		else
		{
			$this->db->select('count(sale_time) as count,date(sale_time) as sale_date, sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit', false);
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
		
		if (isset($this->params['payment_type']) && $this->params['payment_type'])
		{
			if (strpos($this->params['payment_type'],'common_') !== FALSE)
			{
				$payment_types = get_all_language_values_for_key($this->params['payment_type']);
			}
			else
			{
				$payment_types = array($this->params['payment_type']);				
			}
			
			$this->db->group_start();
			foreach($payment_types as $payment_type)
			{
				$this->db->or_like('sales.payment_type',$payment_type,'both');
			}
			$this->db->group_end();
		}
		
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
		}
		
		if (isset($this->params['ecommerce_only']) && $this->params['ecommerce_only'] == 1){
			$this->db->where('is_ecommerce', 1);
		}
	
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('sales.store_account_payment', 0);
		}
		
		
		$this->sale_time_where();
		$this->db->where('sales.deleted', 0);
		
		$this->db->where_in('sales.location_id', $location_ids);
		
		$return = array(
			'subtotal' => 0,
			'total' => 0,
			'tax' => 0,
			'profit' => 0,
			'sales_per_time_period' => 0,
		);
		
		$rows = 0;
		
		$start_date = strtotime($this->params['start_date']);
		$end_date = strtotime($this->params['end_date']);
		$datediff = $end_date - $start_date;

		$number_of_days = round($datediff / (60 * 60 * 24));
		
		foreach($this->db->get()->result_array() as $row)
		{
			$return['subtotal'] += to_currency_no_money($row['subtotal'],2);
			$return['total'] += to_currency_no_money($row['total'],2);
			$return['tax'] += to_currency_no_money($row['tax'],2);
			$return['profit'] += to_currency_no_money($row['profit'],2);
			$return['sales_per_time_period'] += $row['count'];
			$rows++;
		}
		
		$return['sales_per_time_period'] = round($return['sales_per_time_period']/$rows,2);
		$return['average'] = $return['total']/$number_of_days;
		
		
		
			
		$this->db->select('SUM(phppos_sales.total) as total_non_tax');
		$this->db->from('sales');
		$this->db->where('sales.tax',0);
		
		
		if((isset($this->params['item_id']) && $this->params['item_id']) || isset($category_ids))
		{
			$this->db->join('sales_items', 'sales_items.sale_id = sales.sale_id','left');
			$this->db->join('items','items.item_id = sales_items.item_id','left');
			$this->db->join('sales_item_kits','sales_item_kits.sale_id = sales.sale_id','left');
			$this->db->join('item_kits','item_kits.item_kit_id = sales_item_kits.item_kit_id','left');
			
			$sales_items= $this->db->dbprefix('sales_items');
			$sales_item_kits= $this->db->dbprefix('sales_item_kits');
			
			if (isset($this->params['item_id']) && $this->params['item_id'])
			{
				$this->db->where('sales_items.item_id',$this->params['item_id']);
			}
			
			if (isset($category_ids) && !empty($category_ids))
			{
				
				$this->db->group_start();
				$this->db->where_in('items.category_id',$category_ids);
				$this->db->or_where_in('item_kits.category_id',$category_ids);
				$this->db->group_end();
			}
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
		
		if (isset($this->params['payment_type']) && $this->params['payment_type'])
		{
			if (strpos($this->params['payment_type'],'common_') !== FALSE)
			{
				$payment_types = get_all_language_values_for_key($this->params['payment_type']);
			}
			else
			{
				$payment_types = array($this->params['payment_type']);				
			}
			
			$this->db->group_start();
			foreach($payment_types as $payment_type)
			{
				$this->db->or_like('sales.payment_type',$payment_type,'both');
			}
			$this->db->group_end();
		}
		
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
		}
		
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('sales.store_account_payment', 0);
		}
		
		
		$this->sale_time_where();
		$this->db->where('sales.deleted', 0);
		
		
		$row = $this->db->get()->row_array();
		
		$non_taxable = $row['total_non_tax'];
		
		if(!isset($this->params['item_id']) || !$this->params['item_id'])
		{
			$return['non_taxable'] = $non_taxable;
		}
		
		if(!$this->has_profit_permission)
		{
			unset($return['profit']);
		}
		return $return;
	}


}
?>