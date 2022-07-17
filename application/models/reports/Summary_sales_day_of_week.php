<?php
require_once ("Report.php");

class Summary_sales_day_of_week extends Report
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Tier');
		
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
			$tabular_data = array();
			
			if ($do_compare)
			{
				$report_data_compare_model = new Summary_sales_day_of_week();
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
					$day_of_week_compare_to = $row['day_of_week'];
				
					for($k=0;$k<count($report_data_compare);$k++)
					{
						if ($report_data_compare[$k]['day_of_week'] == $day_of_week_compare_to)
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
			
				$data_row[] = array('data'=>$this->_week_day_to_day_name($row['day_of_week']), 'align'=>'left');
				$data_row[] = array('data'=>$row['number_of_transactions'].($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['number_of_transactions'] >= $row['number_of_transactions'] ? ($row['number_of_transactions'] == $row_compare['number_of_transactions'] ?  '' : 'compare_better') : 'compare_worse').'">'.$row_compare['number_of_transactions'] .'</span>':''), 'align' => 'right');
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
				'view' => 'tabular',
				"title" => lang('reports_sales_summary_by_time_report'),
				"subtitle" => $subtitle,
				"headers" => $this->getDataColumns(),
				"data" => $tabular_data,
				"summary_data" => $summary_data,
				"export_excel" => $this->params['export_excel'],
				"pagination" => ''
			);
			
		}
		elseif($this->settings['display'] == 'graphical')
		{
			$graph_data = array();
			foreach($report_data as $row)
			{
				$graph_data[$row['time_range']] = to_quantity($row['number_of_transactions']);
			}

			$currency_symbol = $this->config->item('currency_symbol') ? $this->config->item('currency_symbol') : '$';

			$data = array(
				'view' => 'graphical',
				'graph' => 'bar',
				"summary_data" => $summary_data,
				"title" => lang('reports_sales_summary_by_time_report'),
				"data" => $graph_data,
				"subtitle" => $subtitle,
				"tooltip_template" => "<%=label %>:  <%=value %>",
			);
		}
		
		return $data;
	}
	

	public function getDataColumns()
	{
		$columns = array();
		
		$columns[] = array('data'=>lang('reports_time_range'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_number_of_transactions'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
		$columns[] = array('data'=>lang('reports_total'), 'align'=> 'right');
		$columns[] = array('data'=>lang('common_tax'), 'align'=> 'right');

		if($this->has_profit_permission)
		{
			$columns[] = array('data'=>lang('common_profit'), 'align'=> 'right');
		}
		
		return $columns;		
	}
	
	private function _week_day_to_day_name($index)
	{
		if ($index == 0)
		{
			return lang('common_monday');
		}
		elseif($index == 1)
		{
			return lang('common_tuesday');
			
		}
		elseif($index == 2)
		{
			return lang('common_wednesday');
			
		}
		elseif($index == 3)
		{
			return lang('common_thursday');
			
		}
		elseif($index == 4)
		{
			return lang('common_friday');
			
		}
		elseif($index == 5)
		{
			return lang('common_saturday');
			
		}
		elseif($index == 6)
		{
			return lang('common_sunday');			
		}
		
	}
	
	public function getData()
	{		
		$this->db->select('WEEKDAY(sale_time) as day_of_week, count(*) as number_of_transactions,sale_time, SUM(subtotal) as subtotal, SUM(total) as total, SUM(tax) as tax, SUM(profit) as profit');
		$this->db->from('sales');
		
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
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
		}
		$this->sale_time_where();
		$this->db->group_by('WEEKDAY(sale_time)');
		$this->db->where('deleted', 0);
				
		return $this->db->get()->result_array();
	}
	
	
	public function getSummaryData()
	{
		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax,sum(profit) as profit', false);
		$this->db->from('sales');
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
		
		$this->sale_time_where();
		$this->db->where('deleted', 0);
		
		
		$this->db->group_by('sale_id');
		
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