<?php
require_once ("Report.php");
class Summary_taxes_receivings extends Report
{
	function __construct()
	{
		parent::__construct();
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
				array('view' => 'dropdown','dropdown_label' =>lang('reports_receiving_type'),'dropdown_name' => 'receiving_type','dropdown_options' =>array('all' => lang('reports_all'), 'receiving' => lang('common_receiving'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all'),
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
				array('view' => 'dropdown','dropdown_label' =>lang('reports_receiving_type'),'dropdown_name' => 'receiving_type','dropdown_options' =>array('all' => lang('reports_all'), 'receiving' => lang('common_receiving'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all'),
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
		$this->load->model('Receiving');

		$start_date=$this->params['start_date'];
		$end_date=$this->params['end_date'];
		$receiving_type = $this->params['receiving_type'];
		$export_excel = isset($this->params['export_excel']) ? $this->params['export_excel'] : false ;
		$do_compare = isset($this->params['compare_to']) && $this->params['compare_to'];
		$offset = isset($this->params['offset'])? $this->params['offset'] : 0;
				

		$tabular_data = array();
		$report_data = $this->getData();
		$summary_data = $this->getSummaryData();
		
		if($this->settings['display'] == 'tabular')
		{
			$compare_start_date=$this->params['start_date_compare'];
			$compare_end_date=$this->params['end_date_compare'];
			
			if ($do_compare)
			{
				$report_data_compare_model = new Summary_taxes_receivings();
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
					if (isset($report_data_compare[$row['name']]))
					{
						$row_compare = $report_data_compare[$row['name']];
					}
					else
					{
						$row_compare = FALSE;
					}
				}
			
			
				$tabular_data[] = array(array('data'=>$row['name'], 'align'=>'left'),array('data'=>to_currency($row['subtotal']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['subtotal'] >= $row['subtotal'] ? ($row['subtotal'] == $row_compare['subtotal'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['subtotal']) : ''), 'align'=>'left'),array('data'=>to_currency($row['tax']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['tax'] >= $row['tax'] ? ($row['tax'] == $row_compare['tax'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['tax']).'</span>' : ''), 'align'=>'left'), array('data'=>to_currency($row['total']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['total'] >= $row['total'] ? ($row['total'] == $row_compare['total'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['total']) .'</span>':''), 'align'=>'left'));
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
				"title" => lang('reports_taxes_summary_report'),
				"subtitle" => date(get_date_format(), strtotime($start_date)) .'-'.date(get_date_format(), strtotime($end_date)).($do_compare  ? ' '. lang('reports_compare_to'). ' '. date(get_date_format(), strtotime($compare_start_date)) .'-'.date(get_date_format(), strtotime($compare_end_date)) : ''),
				"headers" => $this->getDataColumns(),
				"data" => $tabular_data,
				"summary_data" => $summary_data,
				"export_excel" => $export_excel,
				"pagination" => $this->pagination->create_links()
			);
		
		}
		elseif($this->settings['display'] == 'graphical')
		{
			$graph_data = array();
			foreach($report_data as $row)
			{
				$graph_data[$row['name']] = to_currency_no_money($row['tax']);
			}

			$currency_symbol = $this->config->item('currency_symbol') ? $this->config->item('currency_symbol') : '$';
		
			$data = array(
				'view' => 'graphical',
				'graph' => 'bar',
				"title" => lang('reports_taxes_summary_report'),
				"data" => $graph_data,
				"summary_data" => $summary_data,
				"tooltip_template" => "<%=label %>: ".((!$this->config->item('currency_symbol_location') || $this->config->item('currency_symbol_location') =='before') ? $currency_symbol : '')."<%= parseFloat(Math.round(value * 100) / 100).toFixed(".$this->decimals.") %>".($this->config->item('currency_symbol_location') =='after' ? $currency_symbol: ''),
			);
		}
		
		return $data;
	}
		
	public function getDataColumns()
	{
		return array(array('data'=>lang('reports_tax_percent'), 'align'=>'left'),array('data'=>lang('reports_subtotal'), 'align'=>'left'), array('data'=>lang('common_tax'), 'align'=>'left'),array('data'=>lang('reports_total'), 'align'=>'left'));
	}
	
	public function getData()
	{
		$location_ids = self::get_selected_location_ids();
		$this->taxes_data = array();
	
		//Just one tax
		if ($this->getTotalRows() == 2)
		{
			$location_ids = self::get_selected_location_ids();
			$this->db->select('receivings_items.subtotal, receivings_items.total, receivings_items.tax, receivings_items.profit');
			$this->db->from('receivings_items', 'receivings.receiving_id = receivings_items.receiving_id');
			$this->db->join('receivings', 'receivings.receiving_id=receivings_items.receiving_id');
			$this->receiving_time_where();
			$this->db->where_in('receivings.location_id', $location_ids);
			$this->db->where('receivings.deleted', 0);
			$this->db->where('receivings_items.tax != 0');
		
			if ($this->params['receiving_type'] == 'receiving')
			{
				$this->db->where('receivings_items.quantity_purchased > 0');
			}
			elseif ($this->params['receiving_type'] == 'returns')
			{
				$this->db->where('receivings_items.quantity_purchased < 0');
			}
		
			$this->db->where('receivings.store_account_payment', 0);
				
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
			
			
			$name = lang('reports_taxable_recv');
			$this->taxes_data[$name]['name'] = $name;
			$this->taxes_data[$name]['subtotal'] = $return['subtotal'];
			$this->taxes_data[$name]['tax'] = $return['tax'];
			$this->taxes_data[$name]['profit'] = $return['profit'];
			$this->taxes_data[$name]['total'] = ($return['subtotal'] + $return['tax']);		
		}
		else //Many Taxes
		{
			$this->db->select('receivings.receiving_id, item_id,  line');
			$this->db->from('receivings');
			$this->db->join('receivings_items', 'receivings.receiving_id = receivings_items.receiving_id');
			
			$this->receiving_time_where();
			$this->db->where_in('receivings.location_id', $location_ids);
			$this->db->where('receivings.deleted', 0);
			$this->db->where('receivings.store_account_payment', 0);
			

			if ($this->params['receiving_type'] == 'receiving')
			{
				$this->db->where('receivings_items.quantity_purchased > 0');
			}
			elseif ($this->params['receiving_type'] == 'returns')
			{
				$this->db->where('receivings_items.quantity_purchased < 0');
			}
		
			
			$counter = 0;
			foreach($this->db->get()->result_array() as $row)
			{
				if ($row['item_id'])
				{
					$reset_cache = $counter == 0 ? TRUE : FALSE;
					$this->getTaxesForItems($row['receiving_id'], $row['item_id'], $row['line'], $this->taxes_data,$reset_cache);
				}
				
				$counter++;
				
			}			
		}
		
		$this->getNonTaxableTotalForItems($this->taxes_data);		
		ksort($this->taxes_data);
		return $this->taxes_data;
	}
	
	function getTotalRows()
	{
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		
		$this->db->select('COUNT(DISTINCT(CONCAT('.$this->db->dbprefix('receivings_items_taxes').'.name,'.$this->db->dbprefix('receivings_items_taxes').'.percent))) as tax_count', false);
		$this->db->from('receivings_items_taxes');
		$this->db->join('receivings', 'receivings.receiving_id=receivings_items_taxes.receiving_id');
		$this->db->where('receiving_time BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and location_id IN('.$location_ids_string.')');
		$this->db->where('receivings.store_account_payment',0);
		$this->db->where_in('receivings.location_id', $location_ids);
		$this->db->where('receivings.deleted', 0);
		$this->receiving_time_where();
		
		
		$ret = $this->db->get()->row_array();
		
		//add 1 for non taxable
		return $ret['tax_count'] + 1;
	}
	
	function getTaxesForItems($receiving_id, $item_id, $line, &$taxes_data,$reset_cache = FALSE)
	{
		static $all_tax_data;
		
		if ($reset_cache)
		{
			$all_tax_data = FALSE;
		}
		
		
		if ($all_tax_data === FALSE)
		{
			$this->db->select('receivings_items_taxes.receiving_id,receivings_items_taxes.item_id,receivings_items_taxes.line,name, percent, cumulative, item_unit_price, item_cost_price, quantity_purchased, discount_percent');
			$this->db->from('receivings_items_taxes');
			$this->db->join('receivings', 'receivings.receiving_id = receivings_items_taxes.receiving_id');
			$this->db->join('receivings_items', 'receivings_items_taxes.receiving_id = '.$this->db->dbprefix('receivings_items').'.receiving_id and receivings_items_taxes.item_id = '.$this->db->dbprefix('receivings_items').'.item_id and receivings_items_taxes.line='.$this->db->dbprefix('receivings_items').'.line');
			$this->db->where($this->db->dbprefix('receivings').'.receiving_time >=', $this->params['start_date']);
			$this->db->where($this->db->dbprefix('receivings').'.receiving_time <=', $this->params['end_date']);
			$this->db->where('receivings.deleted', 0);
			$this->db->where('receivings_items.tax != 0');
			$this->db->order_by('receivings_items_taxes.receiving_id, receivings_items_taxes.item_id, receivings_items_taxes.cumulative');
			$all_tax_data_result = $this->db->get()->result_array();
			$all_tax_data = array();
			foreach($all_tax_data_result as $row)
			{
				$all_tax_data[$row['receiving_id'].'|'.$row['item_id'].'|'.$row['line']][] = $row;
			}
						
		}
		
		if (isset($all_tax_data["$receiving_id|$item_id|$line"]))
		{
			$tax_result = $all_tax_data["$receiving_id|$item_id|$line"];
		
			for($k=0;$k<count($tax_result);$k++)
			{
				$row = $tax_result[$k];
				if ($row['cumulative'])
				{
					$previous_tax = $tax;
					$subtotal = ($row['item_unit_price']*$row['quantity_purchased']-$row['item_unit_price']*$row['quantity_purchased']*$row['discount_percent']/100);
					$tax = ($subtotal + $tax) * ($row['percent'] / 100);
				}
				else
				{
					$subtotal = ($row['item_unit_price']*$row['quantity_purchased']-$row['item_unit_price']*$row['quantity_purchased']*$row['discount_percent']/100);
					$tax = $subtotal * ($row['percent'] / 100);
				}
			
				if (empty($taxes_data[$row['name'].' ('.$row['percent'] . '%)']))
				{
					$taxes_data[$row['name'].' ('.$row['percent'] . '%)'] = array('name' => $row['name'].' ('.$row['percent'] . '%)', 'tax' => 0, 'subtotal' => 0, 'total' => 0, 'profit' => 0);
				}
						
			  $profit = $subtotal - ($row['item_cost_price']*$row['quantity_purchased']);
				
				$taxes_data[$row['name'].' ('.$row['percent'] . '%)']['subtotal'] += to_currency_no_money($subtotal);
				$taxes_data[$row['name'].' ('.$row['percent'] . '%)']['tax'] += ($tax);
				$taxes_data[$row['name'].' ('.$row['percent'] . '%)']['total'] += ($subtotal+ $tax);
				$taxes_data[$row['name'].' ('.$row['percent'] . '%)']['profit'] += to_currency_no_money($profit);
			
			}
		}
	}
	
	function getNonTaxableTotalForItems(&$taxes_data)
	{
		$location_ids = self::get_selected_location_ids();
		$this->db->select('sum('.$this->db->dbprefix('receivings_items').'.subtotal) as subtotal,sum('.$this->db->dbprefix('receivings_items').'.profit) as profit', false);
		$this->db->from('receivings_items');
		$this->db->join('receivings', 'receivings.receiving_id = receivings_items.receiving_id');
		$this->receiving_time_where();
		$this->db->where_in('receivings.location_id', $location_ids);
		$this->db->where('receivings.deleted', 0);
		$this->db->where('receivings.store_account_payment',0);
		
		
		if ($this->params['receiving_type'] == 'receiving')
		{
			$this->db->where('receivings_items.quantity_purchased > 0');
		}
		elseif ($this->params['receiving_type'] == 'returns')
		{
			$this->db->where('receivings_items.quantity_purchased < 0');
		}
		
		$this->db->where('receivings_items.tax',0);
			
		
		$non_tax = $this->db->get()->result_array();
		$subtotal = 0;
		$profit = 0;
		
		foreach($non_tax as $non_tax_row)
		{
			$subtotal+=$non_tax_row['subtotal'];
			$profit+=$non_tax_row['profit'];
		}
		
		$taxes_data[lang('reports_non_taxable')] = array(
			'name' => lang('reports_non_taxable'),
			'subtotal' => $subtotal,
			'total' => $subtotal,
			'tax' => 0,
			'profit' => $profit,
		);
			
	}
	public function getSummaryData()
	{
		$return = array('subtotal' => 0, 'total' => 0, 'tax' =>0);

		$this->db->select('sum(total) as total,sum(subtotal) as subtotal', false);
		$this->db->from('receivings');
		if ($this->params['receiving_type'] == 'receiving')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['receiving_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
		}
		
		
		$this->receiving_time_where();
		$this->db->where('deleted', 0);
		$this->db->where('receivings.store_account_payment', 0);
		
		foreach($this->db->get()->result_array() as $row)
		{
			$return['subtotal'] += to_currency_no_money($row['subtotal'],2);
			$return['total'] += to_currency_no_money($row['total'],2);
		}
		
		foreach(array_values($this->taxes_data) as $row)
		{
			$return['tax'] += to_currency_no_money($row['tax'],2);
		}
		if(!$this->has_profit_permission)
		{
			unset($return['profit']);
		}
		return $return;
	}
}
?>