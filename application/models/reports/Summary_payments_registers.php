<?php
require_once ("Report.php");
class Summary_payments_registers extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	public function getInputData()
	{
		$input_params = array();
		$specific_entity_data = array();
		$specific_entity_data['view']  = 'specific_entity';
		$specific_entity_data['specific_input_name'] = 'employee_id';
		$specific_entity_data['specific_input_label'] = lang('reports_employee');
		$employees = array();
		$employees[''] = lang('common_all');
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
				$specific_entity_data,
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
		$report_data = $this->getData();
		$summary_data = $this->getSummaryData();
		
		if ($this->settings['display'] == 'tabular')
		{
			$start_date = $this->params['start_date'];
			$end_date = $this->params['end_date'];
			$compare_start_date = $this->params['start_date_compare'];
			$compare_end_date = $this->params['end_date_compare'];
			
			$export_excel = $this->params['export_excel'];
			$do_compare = isset($this->params['compare_to']) && $this->params['compare_to'];		
			$tabular_data = array();
		
			if ($do_compare)
			{
				$report_data_compare_model = new Summary_payments();
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
					if (isset($report_data_compare[$row['payment_type']]))
					{
						$row_compare = $report_data_compare[$row['payment_type']];
					}
					else
					{
						$row_compare = FALSE;
					}
				}
			
				$tabular_data[] = array(array('data'=>$row['payment_type'], 'align'=>'left'),array('data'=>to_currency($row['payment_amount']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['payment_amount'] >= $row['payment_amount'] ? ($row['payment_amount'] == $row_compare['payment_amount'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_currency($row_compare['payment_amount']) .'</span>':''), 'align'=>'right'));
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
				"title" => lang('reports_payments_summary_report'),
				"subtitle" => date(get_date_format(), strtotime($start_date)) .'-'.date(get_date_format(), strtotime($end_date)).($do_compare  ? ' '. lang('reports_compare_to'). ' '. date(get_date_format(), strtotime($compare_start_date)) .'-'.date(get_date_format(), strtotime($compare_end_date)) : ''),
				"headers" => $this->getDataColumns(),
				"data" => $tabular_data,
				"summary_data" => $summary_data,
				"export_excel" => $export_excel,
				"pagination" => $this->pagination->create_links(),
			);
		}
		elseif($this->settings['display'] == 'graphical')
		{
			$graph_data = array();
			foreach($report_data as $row)
			{
				$graph_data[$row['payment_type']] = to_currency_no_money($row['payment_amount']);
			}
			$currency_symbol = $this->config->item('currency_symbol') ? $this->config->item('currency_symbol') : '$';
				
			
			$data = array(
				'view' => 'graphical',
				'graph' => 'bar',
				"summary_data" => $summary_data,
				"title" => lang('reports_payments_summary_report'),
				"data" => $graph_data,
				"tooltip_template" => "<%=label %>: ".((!$this->config->item('currency_symbol_location') || $this->config->item('currency_symbol_location') =='before') ? $currency_symbol : '')."<%= parseFloat(Math.round(value * 100) / 100).toFixed(".$this->decimals.") %>".($this->config->item('currency_symbol_location') =='after' ? $currency_symbol: ''),
			);
			
		}
		
		return $data;
	}
	
	public function getDataColumns()
	{
		return array(array('data'=>lang('reports_payment_type'), 'align'=> 'left'), array('data'=>lang('reports_total'), 'align'=> 'right'));
	}
	
	public function getData()
	{
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		$sale_ids_for_payments = $this->get_sale_ids_for_payments();
		
		$sales_totals = array();
		
		$this->db->select('sale_id, SUM(total) as total', false);
		$this->db->from('sales');
		
		if ($this->params['employee_id'])
		{
			$this->db->where('employee_id', $this->params['employee_id']);
		}
		if (count($sale_ids_for_payments))
		{
			$this->db->group_start();
			$sale_ids_chunk = array_chunk($sale_ids_for_payments,25);
			foreach($sale_ids_chunk as $sale_ids)
			{
				$this->db->or_where_in('sale_id',$sale_ids);
			}
			$this->db->group_end();
		}
		
			
		$this->db->where('deleted', 0);
		$this->db->group_by('sale_id');
		foreach($this->db->get()->result_array() as $sale_total_row)
		{
			$sales_totals[$sale_total_row['sale_id']] = to_currency_no_money($sale_total_row['total'], 2);
		}
		$this->db->select('sales_payments.sale_id, sales_payments.payment_type, payment_amount, payment_id', false);
		$this->db->from('sales_payments');
		$this->db->join('sales', 'sales.sale_id=sales_payments.sale_id');
		$this->db->where('payment_date BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and location_id IN('.$location_ids_string.')');
		
		if ($this->config->item('hide_store_account_payments_in_reports'))
		{
			$this->db->where('store_account_payment',0);
		}
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('payment_amount > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('payment_amount < 0');
		}
		
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		
		$this->db->order_by('sale_id, payment_date, payment_type');
				
		$sales_payments = $this->db->get()->result_array();
		
		$payments_by_sale = array();
		foreach($sales_payments as $row)
		{
        	$payments_by_sale[$row['sale_id']][] = $row;
		}
		
		$this->load->model('Sale');
		$payment_data = $this->Sale->get_payment_data_by_register($payments_by_sale,$sales_totals);
		
		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$payment_data = array_slice($payment_data, isset($this->params['offset']) ? $this->params['offset'] : 0, $this->report_limit);
		}
		
		return $payment_data;
	}
	
	function getTotalRows()
	{
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		
		$this->db->select('COUNT(DISTINCT('.$this->db->dbprefix('sales_payments').'.payment_type)) as payment_count');
		$this->db->from('sales_payments');
		$this->db->join('sales', 'sales.sale_id=sales_payments.sale_id');
		$this->db->where('payment_date BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and location_id IN('.$location_ids_string.')');
		if ($this->params['employee_id'])
		{
			$this->db->where('employee_id', $this->params['employee_id']);
		}
		if ($this->config->item('hide_store_account_payments_in_reports'))
		{
			$this->db->where('store_account_payment',0);
		}
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('payment_amount > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('payment_amount < 0');
		}
		
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		
		$ret = $this->db->get()->row_array();
		return $ret['payment_count'];
	}
	
	public function getSummaryData()
	{
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		$sale_ids_for_payments = $this->get_sale_ids_for_payments();
		
		$sales_totals = array();
		
		$this->db->select('sale_id, SUM(total) as total', false);
		$this->db->from('sales');
		if ($this->params['employee_id'])
		{
			$this->db->where('employee_id', $this->params['employee_id']);
		}
		if (count($sale_ids_for_payments))
		{
			$this->db->group_start();
			$sale_ids_chunk = array_chunk($sale_ids_for_payments,25);
			foreach($sale_ids_chunk as $sale_ids)
			{
				$this->db->or_where_in('sale_id',$sale_ids);
			}
			$this->db->group_end();
		}
		$this->db->where('deleted', 0);
		$this->db->group_by('sale_id');
		
		foreach($this->db->get()->result_array() as $sale_total_row)
		{
			$sales_totals[$sale_total_row['sale_id']] = to_currency_no_money($sale_total_row['total'],2);
		}
		$this->db->select('sales_payments.sale_id, sales_payments.payment_type, payment_amount, payment_id', false);
		$this->db->from('sales_payments');
		$this->db->join('sales', 'sales.sale_id=sales_payments.sale_id');
		$this->db->where('payment_date BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and location_id IN('.$location_ids_string.')');
		
		if ($this->config->item('hide_store_account_payments_in_reports'))
		{
			$this->db->where('store_account_payment',0);
		}
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('payment_amount > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('payment_amount < 0');
		}
		
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->order_by('sale_id, payment_date, payment_type');
				
		$sales_payments = $this->db->get()->result_array();
		
		$payments_by_sale = array();
		foreach($sales_payments as $row)
		{
        	$payments_by_sale[$row['sale_id']][] = $row;
		}
		
		$payment_data = $this->Sale->get_payment_data($payments_by_sale,$sales_totals);		
		
		$return = array('total' => 0);
		foreach($payment_data as $payment)
		{
			$return['total']+=$payment['payment_amount'];
		}
				
		return $return;
	}
	
	function get_sale_ids_for_payments()
	{
		$sale_ids = array();
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		
		$this->db->select('sales_payments.sale_id');
		$this->db->distinct();
		$this->db->from('sales_payments');
		$this->db->join('sales', 'sales.sale_id=sales_payments.sale_id');
		$this->db->where('payment_date BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and location_id IN('.$location_ids_string.')');
	
		foreach($this->db->get()->result_array() as $sale_row)
		{
			 $sale_ids[] = $sale_row['sale_id'];
		}
		
		return $sale_ids;
	}
}
?>