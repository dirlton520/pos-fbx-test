<?php
require_once ("Report.php");
class Summary_suppliers_receivings extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		$columns = array();
		
		$columns[] = array('data'=>lang('reports_supplier'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
		$columns[] = array('data'=>lang('reports_total'), 'align'=> 'right');		
		$columns[] = array('data'=>lang('common_tax'), 'align'=> 'right');		
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
		$tabular_data = array();
		$report_data = $this->getData();
		$summary_data = $this->getSummaryData();
		
		if ($this->settings['display'] == 'tabular')
		{				

			foreach($report_data as $row)
			{
				$data_row = array();
			
				$data_row[] = array('data'=>$row['supplier'], 'align' => 'left');
				$data_row[] = array('data'=>to_currency($row['subtotal']), 'align'=>'right');
				$data_row[] = array('data'=>to_currency($row['total']), 'align'=>'right');
				$data_row[] = array('data'=>to_currency($row['tax']), 'align'=> 'right');
				$tabular_data[] = $data_row;			
			}

			$data = array(
				"view" => 'tabular',
				"title" => lang('reports_suppliers_receivings_summary_report'),
				"subtitle" => date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date'])),
				"headers" => $this->getDataColumns(),
				"data" => $tabular_data,
				"summary_data" => $summary_data,
				"export_excel" => $this->params['export_excel'],
				"pagination" => $this->pagination->create_links(),
			);
		}
		else
		{
			$graph_data = array();
			foreach($report_data as $row)
			{
				$graph_data[$row['supplier']] = to_currency_no_money($row['total']);
			}

			$currency_symbol = $this->config->item('currency_symbol') ? $this->config->item('currency_symbol') : '$';

			$data = array(
				"view" => 'graphical',
				'graph' => 'pie',
				"summary_data" => $summary_data,
				"title" => lang('reports_suppliers_receivings_summary_report'),
				"data" => $graph_data,
				"tooltip_template" => "<%=label %>: ".((!$this->config->item('currency_symbol_location') || $this->config->item('currency_symbol_location') =='before') ? $currency_symbol : '')."<%= parseFloat(Math.round(value * 100) / 100).toFixed(".$this->decimals.") %>".($this->config->item('currency_symbol_location') =='after' ? $currency_symbol: ''),
			   "legend_template" => "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%> (".((!$this->config->item('currency_symbol_location') || $this->config->item('currency_symbol_location') =='before') ? $currency_symbol : '')."<%=parseFloat(Math.round(segments[i].value * 100) / 100).toFixed(".$this->decimals.")%>".($this->config->item('currency_symbol_location') =='after' ?  $currency_symbol : '').")<%}%></li><%}%></ul>"
			);
		
			
		}
		return $data;
	}
	
	
	public function getData()
	{
		$this->db->select('CONCAT(company_name, " (",first_name, " ",last_name, ")") as supplier, sum('.$this->db->dbprefix('receivings').'.subtotal) as subtotal, sum('.$this->db->dbprefix('receivings').'.total) as total, sum('.$this->db->dbprefix('receivings').'.tax) as tax, sum('.$this->db->dbprefix('receivings').'.profit) as profit', false);
		$this->db->from('receivings');
		$this->db->join('suppliers', 'suppliers.person_id = receivings.supplier_id','left');
		$this->db->join('people', 'suppliers.person_id = people.person_id', 'left');
		
		$this->receiving_time_where();
		$this->db->where('receivings.deleted', 0);
		//$this->db->where('receivings.suspended', 0);
		
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('receivings.store_account_payment', 0);

		}

		
		if ($this->params['receiving_type'] == 'receiving')
		{
			$this->db->where('receivings.total_quantity_purchased > 0');
		}
		elseif ($this->params['receiving_type'] == 'returns')
		{
			$this->db->where('receivings.total_quantity_purchased < 0');
		}
			
		$this->db->group_by('receivings.supplier_id');
		$this->db->order_by('people.last_name');
		
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
		$this->db->select('COUNT(DISTINCT('.$this->db->dbprefix('people').'.person_id)) as supplier_count');
		$this->db->from('receivings');
		$this->db->join('suppliers', 'suppliers.person_id = receivings.supplier_id');
		$this->db->join('people', 'suppliers.person_id = people.person_id');
		
		$this->receiving_time_where();
		$this->db->where('receivings.deleted', 0);
		//$this->db->where('receivings.suspended', 0);
		
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('receivings.store_account_payment', 0);

		}
		
		if ($this->params['receiving_type'] == 'receiving')
		{
			$this->db->where('receivings.total_quantity_purchased > 0');
		}
		elseif ($this->params['receiving_type'] == 'returns')
		{
			$this->db->where('receivings.total_quantity_purchased < 0');
		}
			
		$ret = $this->db->get()->row_array();
		return $ret['supplier_count'];
	}
	
	
	public function getSummaryData()
	{
		$this->db->select('receivings.subtotal, receivings.total, receivings.tax, receivings.profit', false);
		$this->db->from('receivings');
		$this->db->join('suppliers', 'suppliers.person_id = receivings.supplier_id');
		$this->db->join('people', 'suppliers.person_id = people.person_id');
		
		$this->receiving_time_where();
		$this->db->where('receivings.deleted', 0);
		//$this->db->where('receivings.suspended', 0);
		
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('receivings.store_account_payment', 0);

		}
		
		if ($this->params['receiving_type'] == 'receiving')
		{
			$this->db->where('receivings.total_quantity_purchased > 0');
		}
		elseif ($this->params['receiving_type'] == 'returns')
		{
			$this->db->where('receivings.total_quantity_purchased < 0');
		}		
				
		$this->db->group_by('receivings.receiving_id');
		
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
		return $return;
	}
}
?>