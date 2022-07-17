<?php
require_once ("Report.php");
class Summary_item_kits extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		$columns = array();
		
		$columns[] = array('data'=>lang('common_item'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_quantity_purchased'), 'align'=> 'right');
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
		
		$input_params = array();

		if ($this->settings['display'] == 'tabular')
		{
			$input_data = Report::get_common_report_input_data(TRUE);
			
			$input_params = array(
				array('view' => 'date_range', 'with_time' => TRUE),
				array('view' => 'date_range', 'with_time' => TRUE, 'compare_to' => TRUE),
				array('view' => 'dropdown','dropdown_label' =>lang('reports_sale_type'),'dropdown_name' => 'sale_type','dropdown_options' =>array('all' => lang('reports_all'), 'sales' => lang('reports_sales'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all'),
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
		$tabular_data = array();
		$report_data = $this->getData();
		$summary_data = $this->getSummaryData();
		
		if ($this->settings['display'] == 'tabular')
		{				
		
			$do_compare = isset($this->params['compare_to']) && $this->params['compare_to'];		
			if ($do_compare)
			{
				$compare_to_item_kits = array();
			
				for($k=0;$k<count($report_data);$k++)
				{
					$compare_to_item_kits[] = $report_data[$k]['item_kit_id'];
				}
			
				$report_data_compare_model = new Summary_item_kits();
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
					$item_kit_id_to_compare_to = $row['item_kit_id'];
				
					for($k=0;$k<count($report_data_compare);$k++)
					{
						if ($report_data_compare[$k]['item_kit_id'] == $item_kit_id_to_compare_to)
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
				"view" => "tabular",
				"title" => lang('reports_item_kits_summary_report'),
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
					"title" => lang('reports_item_kits_summary_report'),
					"data" => $graph_data,
					"tooltip_template" => "<%=label %>: ".((!$this->config->item('currency_symbol_location') || $this->config->item('currency_symbol_location') =='before') ? $currency_symbol : '')."<%= parseFloat(Math.round(value * 100) / 100).toFixed(".$this->decimals.") %>".($this->config->item('currency_symbol_location') =='after' ? $currency_symbol: ''),
				   "legend_template" => "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%> (".((!$this->config->item('currency_symbol_location') || $this->config->item('currency_symbol_location') =='before') ? $currency_symbol : '')."<%=parseFloat(Math.round(segments[i].value * 100) / 100).toFixed(".$this->decimals.")%>".($this->config->item('currency_symbol_location') =='after' ?  $currency_symbol : '').")<%}%></li><%}%></ul>"
				);
				
			}
			return $data;
		
	}
	
	public function getData()
	{
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		
		$this->db->select('item_kits.item_kit_id,name, sum('.$this->db->dbprefix('sales_item_kits').'.quantity_purchased) as quantity_purchased, sum('.$this->db->dbprefix('sales_item_kits').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_item_kits').'.total) as total, sum('.$this->db->dbprefix('sales_item_kits').'.tax) as tax, sum('.$this->db->dbprefix('sales_item_kits').'.profit) as profit', false);
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales_item_kits.sale_id = sales.sale_id');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id');
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
		
		if (isset($this->params['compare_to_item_kits']) && count($this->params['compare_to_item_kits']) > 0)
		{
			$this->db->where_in('item_kits.item_kit_id', $this->params['compare_to_item_kits']);
		}	
		
		$this->db->group_by('item_kits.item_kit_id');
		$this->db->order_by('name');
		
		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			
			if(isset($this->params['offset']))
			{
				$this->db->offset($this->params['offset']);
			}
		}

		return $this->db->get()->result_array();		
	}
	
	function getTotalRows()
	{
		$location_ids = self::get_selected_location_ids();
		$this->db->select('COUNT(DISTINCT('.$this->db->dbprefix('sales_item_kits').'.item_kit_id)) as item_kit_count');
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales_item_kits.sale_id = sales.sale_id');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id');
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
		
		if (isset($this->params['compare_to_item_kits']) && count($this->params['compare_to_item_kits']) > 0)
		{
			$this->db->where_in('item_kits.item_kit_id', $this->params['compare_to_item_kits']);
		}	
		
		$ret = $this->db->get()->row_array();
		return $ret['item_kit_count'];
	}
	
	
	public function getSummaryData()
	{
		$location_ids = self::get_selected_location_ids();
		$this->db->select('sum('.$this->db->dbprefix('sales_item_kits').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_item_kits').'.total) as total, sum('.$this->db->dbprefix('sales_item_kits').'.tax) as tax, sum('.$this->db->dbprefix('sales_item_kits').'.profit) as profit', false);
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales_item_kits.sale_id = sales.sale_id');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id');
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
		
		$this->db->group_by('sales_item_kits.sale_id');
		
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
}
?>