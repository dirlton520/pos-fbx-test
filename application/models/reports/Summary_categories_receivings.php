<?php
require_once ("Report.php");
class Summary_categories_receivings extends Report
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Category');
	}
	
	
	public function getInputData()
	{		
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
				array('view' => 'dropdown','dropdown_label' =>lang('reports_receiving_type'),'dropdown_name' => 'receiving_type','dropdown_options' =>array('all' => lang('reports_all'), 'sales' => lang('reports_sales'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all'),
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
				array('view' => 'dropdown','dropdown_label' =>lang('reports_receiving_type'),'dropdown_name' => 'receiving_type','dropdown_options' =>array('all' => lang('reports_all'), 'receiving' => lang('common_receiving'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all'),
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
				$report_data_compare_model = new Summary_categories_receivings();
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
				$data_row[] = array('data'=>to_currency($row['tax']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['tax'] >= $row['tax'] ? ($row['tax'] == $row_compare['tax'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['tax']) .'</span>':''), 'align' => 'right');
				$data_row[] = array('data'=>floatval($row['item_sold']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['item_sold'] >= $row['item_sold'] ? ($row['item_sold'] == $row_compare['item_sold'] ?  '' : 'compare_better') : 'compare_worse').'">'.floatval($row_compare['item_sold']) .'</span>':''), 'align' => 'right');
				
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
		$columns[] = array('data'=>lang('common_tax'), 'align'=> 'right');

		$columns[] = array('data'=>lang('reports_items_recved'), 'align'=> 'right');
		
		return $columns;		
	}
	
	public function getData()
	{	
		$this->db->select('items.category_id, categories.name as category , sum('.$this->db->dbprefix('receivings_items').'.subtotal) as subtotal, sum('.$this->db->dbprefix('receivings_items').'.total) as total, sum('.$this->db->dbprefix('receivings_items').'.tax) as tax, sum('.$this->db->dbprefix('receivings_items').'.profit) as profit, sum('.$this->db->dbprefix('receivings_items').'.quantity_purchased) as item_sold', false);
		$this->db->from('receivings_items');
		$this->db->join('receivings', 'receivings.receiving_id = receivings_items.receiving_id');
		$this->db->join('items', 'receivings_items.item_id = items.item_id');
		$this->db->join('categories', 'categories.id = items.category_id');
		$this->receiving_time_where();
		$this->db->where('receivings.deleted', 0);
				
		
		if (isset($this->params['employee_id']) && $this->params['employee_id'])
		{
			$this->db->where('receivings.employee_id', $this->params['employee_id']);
		}
		
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('receivings.store_account_payment', 0);
		}

		if ($this->params['receiving_type'] == 'receiving')
		{
			$this->db->where('quantity_purchased > 0');
		}
		elseif ($this->params['receiving_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}
		
		if (isset($this->params['compare_to_categories']) && count($this->params['compare_to_categories']) > 0)
		{
			$this->db->where_in('items.category_id', $this->params['compare_to_categories']);
		}	
		
		$this->db->group_by('items.category_id');
		
	
		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			if (isset($this->params['offset']))
			{
				$this->db->offset($this->params['offset']);
			}
		}		
		
		$items= $this->db->get()->result_array();	
				
		return $items;
	}
	
	public function getSummaryData()
	{
		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit', false);
		$this->db->from('receivings');
				
		if ($this->params['receiving_type'] == 'receiving')
		{
			$this->db->where('receivings.total_quantity_purchased > 0');
		}
		elseif ($this->params['receiving_type'] == 'returns')
		{
			$this->db->where('receivings.total_quantity_purchased < 0');
		}
		
		if (isset($this->params['employee_id']) && $this->params['employee_id'])
		{
			$this->db->where('receivings.employee_id', $this->params['employee_id']);
		}
		
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('receivings.store_account_payment', 0);
		}
		
		
		$this->receiving_time_where();
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
}
?>