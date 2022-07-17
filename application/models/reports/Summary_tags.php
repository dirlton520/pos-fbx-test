<?php
require_once ("Report.php");
class Summary_tags extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		$columns = array();
		
		$columns[] = array('data'=>lang('common_tag'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
		$columns[] = array('data'=>lang('reports_total'), 'align'=> 'right');
		$columns[] = array('data'=>lang('common_tax'), 'align'=> 'right');

		if($this->has_profit_permission)
		{
			$columns[] = array('data'=>lang('common_profit'), 'align'=> 'right');
		}
		$columns[] = array('data'=>lang('common_items_sold'), 'align'=> 'right');
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
	
	function getOutputData()
	{
		$this->load->model('Sale');
		$this->load->model('Tag');
		
		$do_compare = isset($this->params['compare_to']) && $this->params['compare_to'];		
		$subtitle = date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date'])).($do_compare  ? ' '. lang('reports_compare_to'). ' '. date(get_date_format(), strtotime($this->params['start_date_compare'])) .'-'.date(get_date_format(), strtotime($this->params['end_date_compare'])) : '');

		$tabular_data = array();
		$report_data = $this->getData();
		$summary_data = $this->getSummaryData();
		$start_date = $this->params['start_date'];
		$end_date = $this->params['end_date'];
		$sale_type = $this->params['sale_type'];
		
		if ($this->settings['display'] == 'tabular')
		{
			$this->setupDefaultPagination();
			$export_excel = $this->params['export_excel'];
			
			if ($do_compare)
			{
				$compare_start_date = $this->params['start_date_compare'];
				$compare_end_date = $this->params['end_date_compare'];
				
				$compare_to_tags = array();
			
				foreach(array_keys($report_data) as $tag_name)
				{
					$compare_to_tags[] = $tag_name;
				}
			
				$report_data_compare_model = new Summary_tags();
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
					if (isset($report_data_compare[$row['tag']]))
					{
						$row_compare = $report_data_compare[$row['tag']];
					}
					else
					{
						$row_compare = FALSE;
					}
				}
			
				$data_row = array();
			
				$data_row[] = array('data'=>$row['tag'], 'align' => 'left');
				$data_row[] = array('data'=>to_currency($row['subtotal']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['subtotal'] >= $row['subtotal'] ? ($row['subtotal'] == $row_compare['subtotal'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['subtotal']) .'</span>':''), 'align' => 'right');
				$data_row[] = array('data'=>to_currency($row['total']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['total'] >= $row['total'] ? ($row['total'] == $row_compare['total'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['total']) .'</span>':''), 'align' => 'right');
				$data_row[] = array('data'=>to_currency($row['tax']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['tax'] >= $row['tax'] ? ($row['tax'] == $row_compare['tax'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['tax']) .'</span>':''), 'align' => 'right');
				if($this->has_profit_permission)
				{
					$data_row[] = array('data'=>to_currency($row['profit']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['profit'] >= $row['profit'] ? ($row['profit'] == $row_compare['profit'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['profit']) .'</span>':''), 'align' => 'right');
				}
				$data_row[] = array('data'=>floatval($row['item_sold']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['item_sold'] >= $row['item_sold'] ? ($row['item_sold'] == $row_compare['item_sold'] ?  '' : 'compare_better') : 'compare_worse').'">'.floatval($row_compare['item_sold']) .'</span>':''), 'align' => 'right');
				$tabular_data[] = $data_row;				
			}		

			$data = array(
				"view" => 'tabular',
				"title" => lang('reports_tags_summary_report'),
				"subtitle" => date(get_date_format(), strtotime($start_date)) .'-'.date(get_date_format(), strtotime($end_date)).($do_compare  ? ' '. lang('reports_compare_to'). ' '. date(get_date_format(), strtotime($compare_start_date)) .'-'.date(get_date_format(), strtotime($compare_end_date)) : ''),
				"headers" => $this->getDataColumns(),
				"data" => $tabular_data,
				"summary_data" => $summary_data,
				"export_excel" => $export_excel,
				"pagination" => $this->pagination->create_links(),
			);
		}
		elseif ($this->settings['display'] == 'graphical')
		{
			$graph_data = array();
			foreach($report_data as $row)
			{
				$graph_data[$row['tag']] = to_currency_no_money($row['total']);
			}
			$currency_symbol = $this->config->item('currency_symbol') ? $this->config->item('currency_symbol') : '$';


			$data = array(
				"view" => "graphical",
				"graph" => 'pie',
				"title" => lang('reports_tags_summary_report'),
				"data" => $graph_data,
				'summary_data' => array(),
				"tooltip_template" => "<%=label %>: ".((!$this->config->item('currency_symbol_location') || $this->config->item('currency_symbol_location') =='before') ? $currency_symbol : '')."<%= parseFloat(Math.round(value * 100) / 100).toFixed(".$this->decimals.") %>".($this->config->item('currency_symbol_location') =='after' ? $currency_symbol: ''),
			   "legend_template" => "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%> (".((!$this->config->item('currency_symbol_location') || $this->config->item('currency_symbol_location') =='before') ? $currency_symbol : '')."<%=parseFloat(Math.round(segments[i].value * 100) / 100).toFixed(".$this->decimals.")%>".($this->config->item('currency_symbol_location') =='after' ?  $currency_symbol : '').")<%}%></li><%}%></ul>"
			);
		}
			
		return $data;
		
	}
	
	public function getData()
	{
		$this->db->select('tags.name as tag, sum('.$this->db->dbprefix('sales_items').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_items').'.total) as total, sum('.$this->db->dbprefix('sales_items').'.tax) as tax, sum('.$this->db->dbprefix('sales_items').'.profit) as profit, sum('.$this->db->dbprefix('sales_items').'.quantity_purchased) as item_sold', false);
		$this->db->from('sales');
		$this->db->join('sales_items', 'sales_items.sale_id = sales.sale_id');
		$this->db->join('items', 'items.item_id = sales_items.item_id');
		$this->db->join('items_tags', 'items_tags.item_id = items.item_id');
		$this->db->join('tags', 'tags.id = items_tags.tag_id');
		
		$this->db->group_start();
		$this->db->where('items.name !=', lang('common_discount'));
		$this->db->where('sales.deleted', 0);
		$this->db->or_where('items.name IS NULL');
		$this->db->group_end();
		
		$this->sale_time_where();
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('sales_items.quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('sales_items.quantity_purchased < 0');
		}
		
		if (isset($this->params['compare_to_tags']) && count($this->params['compare_to_tags']) > 0)
		{
			$this->db->where_in('tags.name', $this->params['compare_to_tags']);
		}				

		$this->db->group_by('tags.name');
		$this->db->order_by('tags.name');
		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			
			if (isset($this->params['offset']))
			{
				$this->db->offset($this->params['offset']);
			}
		}

		$items = $this->db->get()->result_array();	
		
		$this->db->select('tags.name as tag, sum('.$this->db->dbprefix('sales_item_kits').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_item_kits').'.total) as total, sum('.$this->db->dbprefix('sales_item_kits').'.tax) as tax, sum('.$this->db->dbprefix('sales_item_kits').'.profit) as profit, sum('.$this->db->dbprefix('sales_item_kits').'.quantity_purchased) as item_sold', false);
		$this->db->from('sales');
		$this->db->join('sales_item_kits', 'sales_item_kits.sale_id = sales.sale_id');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id');
		$this->db->join('item_kits_tags', 'item_kits_tags.item_kit_id = item_kits.item_kit_id');
		$this->db->join('tags', 'tags.id = item_kits_tags.tag_id');
		$this->sale_time_where();
		
		$this->db->or_where('item_kits.name IS NULL');		
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('sales_item_kits.quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('sales_item_kits.quantity_purchased < 0');
		}
		
		if (isset($this->params['compare_to_tags']) && count($this->params['compare_to_tags']) > 0)
		{
			$this->db->where_in('tags.name', $this->params['compare_to_tags']);
		}	
		
		$this->db->where('sales.deleted', 0);

		$this->db->group_by('tags.name');
		$this->db->order_by('tags.name');
		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			if (isset($this->params['offset']))
			{
				$this->db->offset($this->params['offset']);
			}
		}
		$item_kits = $this->db->get()->result_array();
		return $this->merge_item_and_item_kits($items, $item_kits);
	}
	
	private function merge_item_and_item_kits($items, $item_kits)
	{
		$new_items = array();
		$new_item_kits = array();
		
		foreach($items as $item)
		{
			$new_items[$item['tag']] = $item;
		}
		
		foreach($item_kits as $item_kit)
		{
			$new_item_kits[$item_kit['tag']] = $item_kit;
		}
		
		$merged = array();
		
		foreach($new_items as $tag=>$row)
		{
			if (!isset($merged[$tag]))
			{
				$merged[$tag] = $row;
			}
			else
			{
				$merged[$tag]['subtotal']+= $row['subtotal'];
				$merged[$tag]['total']+= $row['total'];
				$merged[$tag]['tax']+= $row['tax'];
				$merged[$tag]['profit']+= $row['profit'];
				$merged[$tag]['item_sold']+= $row['item_sold'];
			}
		}
		
		foreach($new_item_kits as $tag=>$row)
		{
			if (!isset($merged[$tag]))
			{
				$merged[$tag] = $row;
			}
			else
			{
				$merged[$tag]['subtotal']+= $row['subtotal'];
				$merged[$tag]['total']+= $row['total'];
				$merged[$tag]['tax']+= $row['tax'];
				$merged[$tag]['profit']+= $row['profit'];
				$merged[$tag]['item_sold']+= $row['item_sold'];
			}
		}
		
		
		return $merged;
	}
	
	public function getSummaryData()
	{
		return array();
	}
	
	function getTotalRows()
	{
		return $this->Tag->count_all();
	}
}
?>