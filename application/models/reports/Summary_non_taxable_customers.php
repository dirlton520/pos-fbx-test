<?php
require_once ("Report.php");
class Summary_non_taxable_customers extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getInputData()
	{
		
		$input_params = array();

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

		$specific_entity_data['specific_input_name'] = 'item_id';
		$specific_entity_data['specific_input_label'] = lang('common_item');
		$specific_entity_data['search_suggestion_url'] = site_url('reports/item_search');
		$specific_entity_data['view'] = 'specific_entity';		

		if ($this->settings['display'] == 'tabular')
		{
			$input_data = Report::get_common_report_input_data(TRUE);
			
			$total_number_of_sales_dropdown = array('' => lang('common_any'));
			
			for($k=1;$k<=250;$k++)
			{
				$total_number_of_sales_dropdown[$k] = $k.': '.lang('common_greater_or_equal');
			}
			
			$months_dropdown = array();
			$months_dropdown = array('' => lang('common_any'));
			
			for($k=1;$k<=24;$k++)
			{
				$months_dropdown[$k] = $k.' '.lang('reports_months');
			}
			$input_params = array(
				array('view' => 'date_range', 'with_time' => TRUE),
				array('view' => 'dropdown','dropdown_label' =>lang('reports_sale_type'),'dropdown_name' => 'sale_type','dropdown_options' =>array('all' => lang('reports_all'), 'sales' => lang('reports_sales'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all'),
				array('view' => 'total_spent'),
				array('view' => 'dropdown','dropdown_label' =>lang('reports_total_number_of_sales'),'dropdown_name' => 'total_number_of_sales','dropdown_options' => $total_number_of_sales_dropdown,'dropdown_selected_value' => ''),
				array('view' => 'dropdown','dropdown_label' =>lang('reports_purchased_in_last'),'dropdown_name' => 'purchased_in_last_months','dropdown_options' => $months_dropdown,'dropdown_selected_value' => ''),
				$specific_entity_data,
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
				array('view' => 'dropdown','dropdown_label' =>lang('reports_sale_type'),'dropdown_name' => 'sale_type','dropdown_options' =>array('all' => lang('reports_all'), 'sales' => lang('reports_sales'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all'),
				array('view' => 'dropdown','dropdown_label' =>lang('reports_purchased_in_last'),'dropdown_name' => 'purchased_in_last_months','dropdown_options' => $months_dropdown,'dropdown_selected_value' => ''),
				array('view' => 'total_spent'),
				$specific_entity_data,
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
	
	public function getOutputData()
	{
		$report_data = $this->getData();
		$summary_data = $this->getSummaryData();
		$subtitle = date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date']));
		if ($this->settings['display'] == 'tabular')
		{
			$this->setupDefaultPagination();
		
			$tabular_data = array();
			$report_data = $this->getData();
			$no_customer = $this->getNoCustomerData();
			$report_data = array_merge($no_customer,$report_data);
		
			foreach($report_data as $row)
			{
				$data_row = array();
			
				$data_row[] = array('data'=>$row['person_id'], 'align' => 'left');
				$data_row[] = array('data'=>$row['customer'], 'align' => 'left');
				$data_row[] = array('data'=>$row['tax_certificate'], 'align' => 'left');
				$data_row[] = array('data'=>$row['phone_number'], 'align' => 'left');
				$data_row[] = array('data'=>$row['email'], 'align' => 'left');
				$data_row[] = array('data'=>$row['address_1'], 'align' => 'left');
				$data_row[] = array('data'=>$row['address_2'], 'align' => 'left');
				$data_row[] = array('data'=>$row['city'], 'align' => 'left');
				$data_row[] = array('data'=>$row['state'], 'align' => 'left');
				$data_row[] = array('data'=>$row['zip'], 'align' => 'left');
				$data_row[] =  array('data'=>to_currency($row['total']), 'align' => 'right');
				if($this->has_profit_permission)
				{
					$data_row[] = array('data'=>to_currency($row['profit']), 'align' => 'right');
				}
			
				if ($this->config->item('enable_customer_loyalty_system') && $this->config->item('loyalty_option') == 'advanced')
				{
					$data_row[] = array('data'=>to_currency_no_money($row['points_used']), 'align' => 'right');
					$data_row[] = array('data'=>to_currency_no_money($row['points_gained']), 'align' => 'right');
				}
				elseif ($this->config->item('enable_customer_loyalty_system') && $this->config->item('loyalty_option') == 'simple')
				{
				   $sales_until_discount = $this->config->item('number_of_sales_for_discount') - $row['current_sales_for_discount'];
					$data_row[] = array('data'=>to_quantity($sales_until_discount), 'align' => 'right');
				}
			
				$data_row[] = array('data'=>$row['count'], 'align' => 'left');
				$data_row[] = array('data'=>to_currency($row['total']/$row['count']), 'align' => 'left');
				$data_row[] = array('data'=>to_quantity($row['total_quantity_purchased']/$row['count']), 'align' => 'left');
				
				$tabular_data[] = $data_row;				
			}

			$data = array(
				"view" => 'tabular',
				"title" => lang('reports_customers_summary_report'),
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
					$graph_data[$row['customer']] = to_currency_no_money($row['total']);
				}
		
				$currency_symbol = $this->config->item('currency_symbol') ? $this->config->item('currency_symbol') : '$';

				$data = array(
					'view' => 'graphical',
					'graph' => 'pie',
					"title" => lang('reports_customers_summary_report'),
					"data" => $graph_data,
					"summary_data" => $summary_data,
					"tooltip_template" => "<%=label %>: ".((!$this->config->item('currency_symbol_location') || $this->config->item('currency_symbol_location') =='before') ? $currency_symbol : '')."<%= parseFloat(Math.round(value * 100) / 100).toFixed(".$this->decimals.") %>".($this->config->item('currency_symbol_location') =='after' ? $currency_symbol: ''),
				   "legend_template" => "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%> (".((!$this->config->item('currency_symbol_location') || $this->config->item('currency_symbol_location') =='before') ? $currency_symbol : '')."<%=parseFloat(Math.round(segments[i].value * 100) / 100).toFixed(".$this->decimals.")%>".($this->config->item('currency_symbol_location') =='after' ?  $currency_symbol : '').")<%}%></li><%}%></ul>"
				);	
			}
			return $data;
		
		
	}
	
	public function getDataColumns()
	{
		$this->lang->load('customers');
		$columns = array();
		
		$columns[] = array('data'=>lang('common_person_id'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_customer'), 'align'=> 'left');
		$columns[] = array('data'=>lang('customers_tax_certificate'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_phone_number'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_email'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_address_1'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_address_2'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_city'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_state'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_zip'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_total'), 'align'=> 'right');

		if($this->has_profit_permission)
		{
			$columns[] = array('data'=>lang('common_profit'), 'align'=> 'right');
		}
		
		if ($this->config->item('enable_customer_loyalty_system') && $this->config->item('loyalty_option') == 'advanced')
		{
			$columns[] = array('data'=>lang('reports_points_used'), 'align'=> 'left');
			$columns[] = array('data'=>lang('reports_points_earned'), 'align'=> 'left');
		}
		elseif ($this->config->item('enable_customer_loyalty_system') && $this->config->item('loyalty_option') == 'simple')
		{
			$columns[] = array('data'=>lang('common_sales_until_discount'), 'align'=> 'left');
		}
		$columns[] = array('data'=>lang('reports_number_of_transactions'), 'align'=> 'right');
		$columns[] = array('data'=>lang('reports_average_ticket_size'), 'align'=> 'right');
		$columns[] = array('data'=>lang('reports_average_items_sold_per_transaction'), 'align'=> 'right');
		
		return $columns;		
	}
	
	public function getData()
	{
		$this->db->select('COUNT(*) as count,customers.tax_certificate,customers.current_sales_for_discount, customer_id, CONCAT(first_name, " ",last_name) as customer, customers.person_id as person_id, people.phone_number, people.email, people.zip,people.address_1,people.address_2,people.state,people.city, sum(total) as total,sum(profit) as profit, sum(total_quantity_purchased) as total_quantity_purchased', false);
		$this->db->from('sales');
		$this->db->join('customers', 'customers.person_id = sales.customer_id');
		$this->db->join('people', 'customers.person_id = people.person_id');
		$this->db->where('sales.tax',0);
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
		}
		
		if(isset($this->params['item_id']) && $this->params['item_id'])
		{
			$this->db->where('sale_id IN(SELECT sale_id FROM phppos_sales_items WHERE item_id = '.$this->db->escape($this->params['item_id']).')');
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
		
		
		
		$this->sale_time_where();
		$this->db->where('sales.deleted', 0);
	
		$this->db->group_by('customer_id');
		
		if (isset($this->params['total_number_of_sales']) && $this->params['total_number_of_sales'])
		{
			$this->db->having('count >=',$this->params['total_number_of_sales']);
		}
		
		if ($this->params['total_spent_condition'] != 'any' && is_numeric($this->params['total_spent_amount']))
		{
			$condition = '=';
			switch($this->params['total_spent_condition'])
			{
				case $this->params['total_spent_condition'] == 'greater_than':
					$condition = '>';
				break;

				case $this->params['total_spent_condition'] == 'less_than':
					$condition = '<';
				break;

				case $this->params['total_spent_condition'] == 'equal_to':
					$condition = '=';
				break;
				
			}
					
			$decimals = $this->config->item('number_of_decimals') !== NULL && $this->config->item('number_of_decimals') != '' ? (int)$this->config->item('number_of_decimals') : 2;
				
			$this->db->having('ROUND(sum(total),'.$decimals.') '.$condition.' '.$this->params['total_spent_amount']);
		}
		
		if (isset($this->params['purchased_in_last_months']) && $this->params['purchased_in_last_months'])
		{
			$after_date = date('Y-m-d',strtotime('-'.$this->params['purchased_in_last_months'].' months'));
			$this->db->where('sale_time >=',$after_date);
		}
		
		$this->db->order_by('last_name');
		
		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			if (isset($this->params['offset']))
			{
				$this->db->offset($this->params['offset']);
			}
		}
		
		$ret = $this->db->get()->result_array();
		
		if ($this->config->item('enable_customer_loyalty_system') && $this->config->item('loyalty_option') == 'advanced')
		{
			$customer_ids = array(-1);
			
			for($k=0;$k<count($ret);$k++)
			{
				$customer_ids[] = $ret[$k]['customer_id'];
			}
			
			$this->db->select('customer_id, points_used, points_gained');
			$this->db->from('sales');
			$this->sale_time_where();
			
			$this->db->where_in('customer_id', $customer_ids);
			$this->db->group_by('sale_id');
			$cust_ret = $this->db->get()->result_array();
			
			$customers_points = array();
			
			for($j=0;$j<count($cust_ret);$j++)
			{
				$cust_row = $cust_ret[$j];
				
				if (!isset($customers_points[$cust_row['customer_id']]))
				{
					$customers_points[$cust_row['customer_id']] = array(
						'points_used' => $cust_row['points_used'],
						'points_gained' => $cust_row['points_gained'],
					);
				}
				else
				{
					$customers_points[$cust_row['customer_id']]['points_used']+=$cust_row['points_used'];
					$customers_points[$cust_row['customer_id']]['points_gained']+=$cust_row['points_gained'];
				}
			}
				
			for($p=0;$p<count($ret);$p++)
			{
				$ret[$p]['points_used'] = isset($customers_points[$ret[$p]['customer_id']]['points_used']) ? $customers_points[$ret[$p]['customer_id']]['points_used'] : 0;
				$ret[$p]['points_gained'] = isset($customers_points[$ret[$p]['customer_id']]['points_gained']) ? $customers_points[$ret[$p]['customer_id']]['points_gained'] : 0;
			}
		}
		
		return $ret;
	}
	
	public function getNoCustomerData()
	{
		$this->db->select('"" as tax_certificate,"" as points_used,"" as points_gained,"" as zip,"" as city,"" as state,"" as address_1,"" as address_2,"" as zip,COUNT(*) as count,'.$this->db->escape(lang('reports_no_customer')).' as customer, "-" as person_id, "-" as phone_number,"-" as email, sum(total) as total,sum(profit) as profit,sum(total_quantity_purchased) as total_quantity_purchased', false);
		$this->db->from('sales');
		$this->db->where('sales.tax',0);
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
		}
		if(isset($this->params['item_id']) && $this->params['item_id'])
		{
			$this->db->where('sale_id IN(SELECT sale_id FROM phppos_sales_items WHERE item_id = '.$this->db->escape($this->params['item_id']).')');
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
		
		
		$this->sale_time_where();
		$this->db->where('sales.deleted', 0);
		
		$this->db->where('customer_id',NULL);
		$this->db->group_by('customer_id');
		if ($this->params['total_number_of_sales'])
		{
			$this->db->having('count >=',$this->params['total_number_of_sales']);
		}
		
		if ($this->params['total_spent_condition'] != 'any' && is_numeric($this->params['total_spent_amount']))
		{
			$condition = '=';
			switch($this->params['total_spent_condition'])
			{
				case $this->params['total_spent_condition'] == 'greater_than':
					$condition = '>';
				break;

				case $this->params['total_spent_condition'] == 'less_than':
					$condition = '<';
				break;

				case $this->params['total_spent_condition'] == 'equal_to':
					$condition = '=';
				break;
				
			}
					
			$decimals = $this->config->item('number_of_decimals') !== NULL && $this->config->item('number_of_decimals') != '' ? (int)$this->config->item('number_of_decimals') : 2;
				
			$this->db->having('ROUND(sum(total),'.$decimals.') '.$condition.' '.$this->params['total_spent_amount']);
		}

		return $this->db->get()->result_array();		
	}
	
	public function getSummaryData()
	{
		$this->db->select('COUNT(*) as count, sum(total) as total, sum(profit) as profit', false);
		$this->db->from('sales');
		$this->db->where('sales.tax',0);
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
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
		
		
		if(isset($this->params['item_id']) && $this->params['item_id'])
		{
			$this->db->where('sale_id IN(SELECT sale_id FROM phppos_sales_items WHERE item_id = '.$this->db->escape($this->params['item_id']).')');
		}
		
		$this->sale_time_where();
		$this->db->where('sales.deleted', 0);
		
		
		$this->db->group_by('customer_id');
		if (isset($this->params['total_number_of_sales']) && $this->params['total_number_of_sales'])
		{
			$this->db->having('count >=',$this->params['total_number_of_sales']);
		}
		
		if ($this->params['total_spent_condition'] != 'any' && is_numeric($this->params['total_spent_amount']))
		{
			$condition = '=';
			switch($this->params['total_spent_condition'])
			{
				case $this->params['total_spent_condition'] == 'greater_than':
					$condition = '>';
				break;

				case $this->params['total_spent_condition'] == 'less_than':
					$condition = '<';
				break;

				case $this->params['total_spent_condition'] == 'equal_to':
					$condition = '=';
				break;
				
			}
					
			$decimals = $this->config->item('number_of_decimals') !== NULL && $this->config->item('number_of_decimals') != '' ? (int)$this->config->item('number_of_decimals') : 2;
				
			$this->db->having('ROUND(sum(total),'.$decimals.') '.$condition.' '.$this->params['total_spent_amount']);
		}		
		
		if (isset($this->params['purchased_in_last_months']) && $this->params['purchased_in_last_months'])
		{
			$after_date = date('Y-m-d',strtotime('-'.$this->params['purchased_in_last_months'].' months'));
			$this->db->where('sale_time >=',$after_date);
		}
		
		$return = array(
			'total' => 0,
			'profit' => 0,
		);
		
		foreach($this->db->get()->result_array() as $row)
		{
			$return['total'] += to_currency_no_money($row['total'],2);
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
		$this->db->select('sum(total) as total,COUNT(*) as count', false);
		$this->db->from('sales');
		$this->db->join('customers', 'customers.person_id = sales.customer_id');
		$this->db->join('people', 'customers.person_id = people.person_id');
		$this->sale_time_where();
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
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
		
		
		$this->db->where('sales.deleted', 0);
		
		$this->db->group_by('customer_id');
		if ($this->params['total_number_of_sales'])
		{
			$this->db->having('count >=',$this->params['total_number_of_sales']);
		}
		
		if ($this->params['total_spent_condition'] != 'any' && is_numeric($this->params['total_spent_amount']))
		{
			$condition = '=';
			switch($this->params['total_spent_condition'])
			{
				case $this->params['total_spent_condition'] == 'greater_than':
					$condition = '>';
				break;

				case $this->params['total_spent_condition'] == 'less_than':
					$condition = '<';
				break;

				case $this->params['total_spent_condition'] == 'equal_to':
					$condition = '=';
				break;
				
			}
					
			$decimals = $this->config->item('number_of_decimals') !== NULL && $this->config->item('number_of_decimals') != '' ? (int)$this->config->item('number_of_decimals') : 2;
				
			$this->db->having('ROUND(sum(total),'.$decimals.') '.$condition.' '.$this->params['total_spent_amount']);
		}
		
		$num_customers = $this->db->get()->num_rows();
		
		$this->db->select($this->db->escape(lang('reports_no_customer')).' as customer, "-" as person_id, "-" as phone_number, sum(total) as total,sum(profit) as profit,COUNT(*) as count', false);
		$this->db->from('sales');
		$this->sale_time_where();
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
		}
		
		$this->db->where('sales.deleted', 0);
		
		$this->db->where('customer_id',NULL);
		$this->db->group_by('customer_id');
		if ($this->params['total_number_of_sales'])
		{
			$this->db->having('count >=',$this->params['total_number_of_sales']);
		}
		
		
		if ($this->params['total_spent_condition'] != 'any' && is_numeric($this->params['total_spent_amount']))
		{
			$condition = '=';
			switch($this->params['total_spent_condition'])
			{
				case $this->params['total_spent_condition'] == 'greater_than':
					$condition = '>';
				break;

				case $this->params['total_spent_condition'] == 'less_than':
					$condition = '<';
				break;

				case $this->params['total_spent_condition'] == 'equal_to':
					$condition = '=';
				break;
				
			}
					
			$decimals = $this->config->item('number_of_decimals') !== NULL && $this->config->item('number_of_decimals') != '' ? (int)$this->config->item('number_of_decimals') : 2;
				
			$this->db->having('ROUND(sum(total),'.$decimals.') '.$condition.' '.$this->params['total_spent_amount']);
		}
		
		
		$num_no_customers = $this->db->get()->num_rows();
		
		return $num_customers + $num_no_customers;
	}
	
}
?>