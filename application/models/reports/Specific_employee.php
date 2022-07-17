<?php
require_once ("Report.php");
class Specific_employee extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getInputData()
	{		
		$input_data = Report::get_common_report_input_data(TRUE);
		$specific_entity_data = array();
		$specific_entity_data['view']  = 'specific_entity';
		$specific_entity_data['specific_input_name'] = 'employee_id';
		$specific_entity_data['specific_input_label'] = lang('reports_employee');
		$employees = array();

		foreach($this->Employee->get_all()->result() as $employee)
		{
			$employees[$employee->person_id] = $employee->first_name .' '.$employee->last_name;
		}
		$specific_entity_data['specific_input_data'] = $employees;
		
		
		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array();
			
			$input_params[] = array('view' => 'date_range', 'with_time' => TRUE);
			$input_params[] = $specific_entity_data;
			$input_params[] = array('view' => 'dropdown','dropdown_label' =>lang('reports_sale_type'),'dropdown_name' => 'sale_type','dropdown_options' =>array('all' => lang('reports_all'), 'sales' => lang('reports_sales'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all');
			$input_params[] = array('view' => 'dropdown','dropdown_label' =>lang('reports_employee_type'),'dropdown_name' => 'employee_type','dropdown_options' =>array( 'sale_person' => lang('reports_sale_person'), 'logged_in_employee' => lang('common_logged_in_employee')),'dropdown_selected_value' => 'sale_person');
			$input_params[] = array('view' => 'excel_export');
			$input_params[] = array('view' => 'locations');
			$input_params[] = array('view' => 'submit');
		}
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}
	
	public function getOutputData()
	{
		$this->load->model('Sale');			
		$this->load->model('Category');
		
		$this->setupDefaultPagination();
		
		$headers = $this->getDataColumns();
		$report_data = $this->getData();

		$summary_data = array();
		$details_data = array();
		$location_count = $this->Location->count_all();
		
		foreach(isset($this->params['export_excel']) == 1 && isset($report_data['summary']) ? $report_data['summary']:$report_data as $key=>$row)
		{
			$summary_data_row = array();
		
			$summary_data_row[] = array('data'=>anchor('sales/receipt/'.$row['sale_id'], '<i class="ion-printer"></i>', array('target' => '_blank')).' '.anchor('sales/edit/'.$row['sale_id'], '<i class="ion-document-text"></i>', array('target' => '_blank')).' '.anchor('sales/edit/'.$row['sale_id'], lang('common_edit').' '.$row['sale_id'], array('target' => '_blank')), 'align'=>'left', 'detail_id' => $row['sale_id']);
			
			if ($location_count > 1)
			{
				$summary_data_row[] = array('data'=>$row['location_name'], 'align'=>'left');
			}
			
			$summary_data_row[] = array('data'=>date(get_date_format().'-'.get_time_format(), strtotime($row['sale_time'])), 'align'=> 'left');
			$summary_data_row[] = array('data'=>$row['register_name'], 'align'=> 'left');
			$summary_data_row[] = array('data'=>to_quantity($row['items_purchased']), 'align'=> 'left');
			$summary_data_row[] = array('data'=>$row['customer_name'].(isset($row['account_number']) && $row['account_number'] ? ' ('.$row['account_number'].')' : ''), 'align'=> 'left');
			$summary_data_row[] = array('data'=>to_currency($row['subtotal']), 'align'=> 'right');
			$summary_data_row[] = array('data'=>to_currency($row['total']), 'align'=> 'right');
			$summary_data_row[] = array('data'=>to_currency($row['tax']), 'align'=> 'right');
			if($this->has_profit_permission)
			{
				$summary_data_row[] = array('data'=>to_currency($row['profit']), 'align'=>'right');
				$summary_data_row[] = array('data'=>to_currency($row['subtotal'] - $row['profit']), 'align'=>'right');
			}
		
			$summary_data_row[] = array('data'=>$row['payment_type'], 'align'=>'right');
			$summary_data_row[] = array('data'=>$row['comment'], 'align'=>'right');
			$summary_data_row[] = array('data'=>$row['discount_reason'], 'align'=>'right');
			$summary_data[$key] = $summary_data_row;
			
			if($this->params['export_excel'] == 1)
			{
				foreach($report_data['details'][$key] as $drow)
				{
					$details_data_row = array();
					
					$details_data_row[] = array('data'=>$drow['item_number'], 'align'=>'left');
					$details_data_row[] = array('data'=>$drow['item_product_id'], 'align'=>'left');
					$details_data_row[] = array('data'=>$drow['item_name'], 'align'=>'left');
					$details_data_row[] = array('data'=>$this->Category->get_full_path($drow['category_id']), 'align'=>'left');
					$details_data_row[] = array('data'=>$drow['size'], 'align'=>'left');
					$details_data_row[] = array('data'=>$drow['supplier_name']. ' ('.$drow['supplier_id'].')', 'align'=>'left');
					$details_data_row[] = array('data'=>$drow['serialnumber'], 'align'=>'left');
					$details_data_row[] = array('data'=>character_limiter($drow['description'],150), 'align'=>'left');
					$details_data_row[] = array('data'=>to_currency($drow['current_selling_price']), 'align'=>'left');
					$details_data_row[] = array('data'=>to_quantity($drow['quantity_purchased']), 'align'=>'left');
					$details_data_row[] = array('data'=>to_currency($drow['subtotal']), 'align'=>'right');
					$details_data_row[] = array('data'=>to_currency($drow['total']), 'align'=>'right');
					$details_data_row[] = array('data'=>to_currency($drow['tax']), 'align'=>'right');
					
					if($this->has_profit_permission)
					{
						$details_data_row[] = array('data'=>to_currency($drow['profit']), 'align'=>'right');		
						$details_data_row[] = array('data'=>to_currency($drow['subtotal'] - $drow['profit']), 'align'=>'right');										
									
					}
					
					$details_data_row[] = array('data'=>$drow['discount_percent'].'%', 'align'=>'left');
					$details_data[$key][] = $details_data_row;
				}
			}
		}
		$employee_info = $this->Employee->get_info($this->params['employee_id']);
		$data = array(
			"view" => 'tabular_details_lazy_load',
			"title" => $employee_info->first_name .' '. $employee_info->last_name.' '.lang('reports_report'),
			"subtitle" => date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date'])),
			"headers" => $this->getDataColumns(),
			"summary_data" => $summary_data,
			"overall_summary_data" => $this->getSummaryData(),
			"export_excel" => $this->params['export_excel'],
			"pagination" => $this->pagination->create_links(),
			"report_model" => get_class($this),
		);

		isset($details_data) && !empty($details_data) ? $data["details_data"]=$details_data: '' ;
		
		return $data;
	}
	
	public function getDataColumns()
	{
		$return = array();
		
		$return['summary'] = array();
		$location_count = $this->Location->count_all();
		
		$return['summary'][] = array('data'=>lang('reports_sale_id'), 'align'=> 'left');
		if ($location_count > 1)
		{
			$return['summary'][] = array('data'=>lang('common_location'), 'align'=> 'left');
		}
		
		$return['summary'][] = array('data'=>lang('reports_date'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_register'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_items_purchased'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_sold_to'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('reports_total'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('common_tax'), 'align'=> 'right');
				
		if($this->has_profit_permission)
		{
			$return['summary'][] = array('data'=>lang('common_profit'), 'align'=> 'right');
			$return['summary'][] = array('data'=>lang('common_cogs'), 'align'=> 'right');
		}
		$return['summary'][] = array('data'=>lang('reports_payment_type'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('reports_comments'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('common_discount_reason'), 'align'=> 'right');

		$return['details'] = $this->get_details_data_columns_sales();			
		
		return $return;	
	}
		
	public function getData()
	{
		$location_ids = self::get_selected_location_ids();
		$data = array();
		$data['summary'] = array();
		$data['details'] = array();
		
		$this->db->select('customer_data.account_number as account_number, locations.name as location_name, sale_id, sale_time, registers.name as register_name, date(sale_time) as sale_date, sum(total_quantity_purchased) as items_purchased, CONCAT(first_name," ",last_name) as customer_name, sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit, payment_type, comment, discount_reason', false);
		$this->db->from('sales');
		$this->db->join('locations', 'sales.location_id = locations.location_id');
		$this->db->join('registers', 'sales.register_id = registers.register_id', 'left');
		$this->db->join('people', 'sales.customer_id = people.person_id', 'left');
		$this->db->join('customers as customer_data', 'sales.customer_id = customer_data.person_id', 'left');
		
		$this->db->where_in('sales.location_id', $location_ids);
		$this->sale_time_where();
		
		if ($this->params['employee_type'] == 'logged_in_employee')
		{
			$this->db->where('employee_id', $this->params['employee_id']);
		}
		else
		{
			$this->db->where('sold_by_employee_id', $this->params['employee_id']);			
		}

		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
		}
		
		$this->db->where('sales.deleted', 0);
		
		$this->db->group_by('sale_id');
		$this->db->order_by('sale_time', ($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');
		
		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			
			if (isset($this->params['offset']))
			{
				$this->db->offset($this->params['offset']);
			}
			return $this->db->get()->result_array();
			exit;
		}		
		if (isset($this->params['export_excel']) && $this->params['export_excel'] == 1)
		{
			
			$data=array();
			$data['summary']=array();
			$data['details']=array();
			foreach($this->db->get()->result_array() as $sale_summary_row)
			{
				$data['summary'][$sale_summary_row['sale_id']] = $sale_summary_row; 
			}

			$sale_ids = array();
			
			foreach($data['summary'] as $sale_row)
			{
				$sale_ids[] = $sale_row['sale_id'];
			}

			$result = $this->get_report_details($sale_ids,1);

			foreach($result as $sale_item_row)
			{
				$data['details'][$sale_item_row['sale_id']][] = $sale_item_row;
			}
			
			return $data;
			exit;
		}
		
	}
	

	public function getTotalRows()
	{
		$location_ids = self::get_selected_location_ids();
		$this->db->select("COUNT(sale_id) as sale_count");
		$this->db->from('sales');
		$this->db->where_in('sales.location_id', $location_ids);
		$this->sale_time_where();
		if ($this->params['employee_type'] == 'logged_in_employee')
		{
			$this->db->where('employee_id', $this->params['employee_id']);
		}
		else
		{
			$this->db->where('sold_by_employee_id', $this->params['employee_id']);			
		}
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
		}
		
		$this->db->where('sales.deleted', 0);
		
		$ret = $this->db->get()->row_array();
		
		return $ret['sale_count'];

	}
	
	public function getSummaryData()
	{
		$location_ids = self::get_selected_location_ids();
		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit', false);
		$this->db->from('sales');
		$this->db->where_in('sales.location_id', $location_ids);
		$this->sale_time_where();
		if ($this->params['employee_type'] == 'logged_in_employee')
		{
			$this->db->where('employee_id', $this->params['employee_id']);
		}
		else
		{
			$this->db->where('sold_by_employee_id', $this->params['employee_id']);			
		}
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
		}
		
		$this->db->where('sales.deleted', 0);
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('store_account_payment', 0);
		}
		
		$this->db->group_by('sales.sale_id');
		
		$return = array(
			'subtotal' => 0,
			'total' => 0,
			'tax' => 0,
			'profit' => 0,
			'cogs' => 0,
		);
		
		foreach($this->db->get()->result_array() as $row)
		{
			$return['subtotal'] += to_currency_no_money($row['subtotal'],2);
			$return['total'] += to_currency_no_money($row['total'],2);
			$return['tax'] += to_currency_no_money($row['tax'],2);
			$return['profit'] += to_currency_no_money($row['profit'],2);
			$return['cogs'] += to_currency_no_money($row['subtotal']-$row['profit'],2);
		}
		
		if(!$this->has_profit_permission)
		{
			unset($return['profit']);
			unset($return['cogs']);
		}
		return $return;
	}	
	
}
?>