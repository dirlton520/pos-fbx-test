<?php
require_once ("Report.php");
class Detailed_payments extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		$return = array();
		
		$return['summary'] = array();
		$return['summary'][] = array('data'=>lang('reports_sale_id'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_location'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_sale_date'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_payment_date'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_payment_type'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_payment_amount'), 'align'=> 'left');
				

		$return['details'] = array();
		$return['details'][] = array('data'=>lang('reports_payment_date'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('reports_payment_type'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('common_payment_amount'), 'align'=> 'left');
		
		return $return;
	}
	
	public function getInputData()
	{
		
		$input_params = array();

		if ($this->settings['display'] == 'tabular')
		{
			$input_data = Report::get_common_report_input_data(TRUE);
			
			$input_params = array(
				array('view' => 'date_range', 'with_time' => TRUE),
				array('view' => 'dropdown','dropdown_label' =>lang('reports_sale_type'),'dropdown_name' => 'sale_type','dropdown_options' =>array('all' => lang('reports_all'), 'sales' => lang('reports_sales'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all'),
				array('view' => 'excel_export'),
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
		$this->load->model('Sale');
		$headers = $this->getDataColumns();
		$report_data = $this->getData();

		$summary_data = array();
		$details_data = array();
		$export_excel = $this->params['export_excel'];
		$start_date = $this->params['start_date'];
		$end_date = $this->params['end_date'];
		
			
		foreach($report_data['summary'] as $sale_id=>$row)
		{			
			foreach($row as $payment_type => $payment_data_row)
			{
				$summary_data_row = array();
				$summary_data_row[] = array('data'=>anchor('sales/receipt/'.$payment_data_row['sale_id'], '<i class="ion-printer"></i>', array('target' => '_blank', 'class'=>'hidden-print')).'<span class="visible-print">'.$payment_data_row['sale_id'].'</span>'.anchor('sales/edit/'.$payment_data_row['sale_id'], '<i class="ion-document-text"></i>', array('target' => '_blank')).' '.anchor('sales/edit/'.$payment_data_row['sale_id'], lang('common_edit').' '.$payment_data_row['sale_id'], array('target' => '_blank','class'=>'hidden-print')), 'align'=>'left');
				$summary_data_row[] = array('data'=>$payment_data_row['location_name'], 'align'=>'left');
				$summary_data_row[] = array('data'=>date(get_date_format().'-'.get_time_format(), strtotime($payment_data_row['sale_time'])), 'align'=>'left');
				$summary_data_row[] = array('data'=>date(get_date_format().'-'.get_time_format(), strtotime($payment_data_row['payment_date'])), 'align'=>'left');
				$summary_data_row[] = array('data'=>$payment_data_row['payment_type'], 'align'=>'left');
				$summary_data_row[] = array('data'=>to_currency($payment_data_row['payment_amount']), 'align'=>'right');

				$summary_data[$sale_id.'|'.$payment_type] = $summary_data_row;
			}
		}

		$temp_details_data = array();
		
		foreach($report_data['details']['sale_ids'] as $sale_id => $drows)
		{
			$payment_types = array();
			foreach ($drows as $drow)
			{
				$payment_types[$drow['payment_type']] = TRUE;
			}
			
			foreach(array_keys($payment_types) as $payment_type)
			{
				foreach ($drows as $drow)
				{
					$details_data_row = array();

					$details_data_row[] = array('data'=>date(get_date_format().'-'.get_time_format(), strtotime($drow['payment_date'])), 'align'=>'left');
					$details_data_row[] = array('data'=>$drow['payment_type'], 'align'=>'left');
					$details_data_row[] = array('data'=>to_currency($drow['payment_amount']), 'align'=>'right');

					$details_data[$sale_id.'|'.$payment_type][] = $details_data_row;
				}
			}
		}
			
		$data = array(
			"view" =>'tabular_details',
			"title" =>lang('reports_detailed_payments_report'),
			"subtitle" => date(get_date_format(), strtotime($start_date)) .'-'.date(get_date_format(), strtotime($end_date)),
			"headers" => $this->getDataColumns(),
			"summary_data" => $summary_data,
			"overall_summary_data" => $this->getSummaryData(),
			"export_excel" => $export_excel,
			"pagination" => $this->pagination->create_links(),
			"report_model" => get_class($this),
		);
		isset($details_data) && !empty($details_data) ? $data["details_data"]=$details_data: '' ;
		
		return $data;
		
	}
	
	
	public function getData()
	{
		$data = array();
		$data['summary'] = array();
		$data['details'] = array();
		$data['details']['sale_ids'] = array();
				
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		$sale_ids_for_payments = $this->get_sale_ids_for_payments();
		
		$sales_totals = array();
		
		$this->db->select('sale_id, SUM(total) as total', false);
		$this->db->from('sales');
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
		$this->db->select('sales.sale_time, sales_payments.sale_id, sales_payments.payment_type, payment_amount, payment_date, payment_id', false);
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
		$this->db->order_by('payment_date, sale_id, payment_type');
				
		$sales_payments = $this->db->get()->result_array();
		
		$payments_by_sale = array();
		foreach($sales_payments as $row)
		{
        	$payments_by_sale[$row['sale_id']][] = $row;
		}
		
		$payment_data = $this->Sale->get_payment_data_grouped_by_sale($payments_by_sale,$sales_totals);
		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$payment_data = array_slice($payment_data, isset($this->params['offset']) ? $this->params['offset'] : 0, $this->report_limit, TRUE);
		}
		
		$data['summary'] = $payment_data;
		$sale_ids_for_report = array();
		
		foreach(array_keys($data['summary']) as $sale_id)
		{
			$sale_ids_for_report[] = $sale_id;
		}
		
		
		$sales_totals = array();
		
		$this->db->select('sale_id, SUM(total) as total', false);
		$this->db->from('sales');
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
			$sales_totals[$sale_total_row['sale_id']] = $sale_total_row['total'];
		}
		
		$this->db->select('sales.sale_time, sales_payments.sale_id, sales_payments.payment_type, payment_amount, payment_date, payment_id', false);
		$this->db->from('sales_payments');
		$this->db->join('sales', 'sales.sale_id=sales_payments.sale_id');

		if (!empty($sale_ids_for_report))
		{
			$sale_ids_chunk = array_chunk($sale_ids_for_report,25);
			$this->db->group_start();
			foreach($sale_ids_chunk as $sale_ids)
			{
				$this->db->or_where_in('sales.sale_id', $sale_ids);
			}			
			$this->db->group_end();
		}
		else
		{
			$this->db->where('1', '2', FALSE);		
		}
		
		$this->db->order_by('payment_date, sale_id, payment_type');
		
		$sales_payments = $this->db->get()->result_array();
		
		$payments_by_sale = array();
		foreach($sales_payments as $row)
		{
        	$payments_by_sale[$row['sale_id']][] = $row;
		}
		
		$payment_data = $this->Sale->get_payment_data_grouped_by_sale($payments_by_sale,$sales_totals);
		foreach($payment_data as $sale_id => $payments_row)
		{
			foreach($payments_row as $payment_type => $sale_payment_row)
			{
				$data['details'][$sale_id.'|'.$payment_type][] = $sale_payment_row;
				$data['details']['sale_ids'][$sale_id][] = $sale_payment_row;
			}
		}
		return $data;
		
	}
	
	public function getTotalRows()
	{
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		
		$this->db->select("COUNT(payment_date) as payment_row_count", false);
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

		$ret = $this->db->get()->row_array();
		return $ret['payment_row_count'];
	}
	public function getSummaryData()
	{
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		$sale_ids_for_payments = $this->get_sale_ids_for_payments();

		$sales_totals = array();
		
		$this->db->select('sale_id, SUM(total) as total', false);
		$this->db->from('sales');
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
		$this->db->order_by('payment_date, sale_id, payment_type');
				
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