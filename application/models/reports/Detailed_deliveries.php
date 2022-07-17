<?php
require_once ("Report.php");
class Detailed_deliveries extends Report
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Tier');
		$this->load->model('Delivery');
	}
	
	public function getInputData()
	{
		$input_data = Report::get_common_report_input_data(TRUE);
		
		$input_params = array();

		if ($this->settings['display'] == 'tabular')
		{
			$specific_entity_data = array();
			$specific_entity_data['view']  = 'specific_entity';
			$specific_entity_data['specific_input_name'] = 'employee_id';
			$specific_entity_data['specific_input_label'] = lang('deliveries_delivery_employee');
			$employees = array('' => lang('common_all'));

			foreach($this->Employee->get_all()->result() as $employee)
			{
				$employees[$employee->person_id] = $employee->first_name .' '.$employee->last_name;
			}
			$specific_entity_data['specific_input_data'] = $employees;
			
			$dropdown_options = array('all'=>lang('common_all'));

			foreach($this->Delivery->get_all_statuses() as $id => $row)
			{
				$dropdown_options[$id] = $row['name'];
			}
			
			$input_params = array(
				array('view' => 'date_range', 'with_time' => TRUE),
				$specific_entity_data,
				array('view' => 'dropdown_status', 'dropdown_options' => $dropdown_options),
				array('view' => 'excel_export'),
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
		$this->load->helper('order');
		$this->load->model('Sale');			
		$this->load->model('Category');
		
		$this->setupDefaultPagination();
		
		$headers = $this->getDataColumns();
		
		$report_data = $this->getData();
		$tier_count = $this->Tier->count_all();
		
		$location_count = $this->Location->count_all();
		$summary_data = array();
		foreach($this->params['export_excel'] == 1 && isset($report_data['summary']) ? $report_data['summary']:$report_data as $key=>$row)
		{
			$summary_data_row = array();

			$link = site_url('reports/generate/specific_customer?report_type=complex&start_date='.$this->params['start_date'].'&start_date_formatted='.date(get_date_format().' '.get_time_format(), strtotime($this->params['start_date'])).'&end_date='.$this->params['end_date'].'&end_date_formatted='.date(get_date_format().' '.get_time_format(), strtotime($this->params['end_date'])).'&customer_id='.$row['customer_id'].'&sale_type=all&export_excel=0');
			
			$summary_data_row[] = array('data'=>anchor('sales/receipt/'.$row['sale_id'], '<i class="ion-printer"></i>', 
			array('target' => '_blank', 'class'=>'hidden-print')).'<span class="visible-print">'.$row['sale_id'].'</span>'.anchor('deliveries/view/'.$row['delivery_id'], '<i class="ion-document-text"></i>', 
			array('target' => '_blank')).' '.anchor('deliveries/view/'.$row['delivery_id'], lang('common_edit').' '.$row['sale_id'], 
			array('target' => '_blank','class'=>'hidden-print')), 'align'=>'left', 'detail_id' => $row['sale_id']);
			
			if ($location_count > 1)
			{
				$summary_data_row[] = array('data'=>$row['location_name'], 'align' => 'left');
			}
			
			$summary_data_row[] = array('data'=>date(get_date_format().'-'.get_time_format(), strtotime($row['sale_time'])), 'align'=>'left');
			
			$summary_data_row[] = array('data'=>delivery_status($row['status']), 'align'=>'left');
			$summary_data_row[] = array('data'=>$row['delivery_employee'], 'align'=>'left');
			$summary_data_row[] = array('data'=>$row['register_name'], 'align'=>'left');
			$summary_data_row[] = array('data'=>to_quantity($row['items_purchased']), 'align'=>'left');
			$summary_data_row[] = array('data'=>$row['employee_name'].($row['sold_by_employee'] && $row['sold_by_employee'] != $row['employee_name'] ? '/'. $row['sold_by_employee']: ''), 'align'=>'left');
			$summary_data_row[] = array('data'=>'<a href="'.$link.'" target="_blank">'.$row['customer_name'].(isset($row['account_number']) && $row['account_number'] ? ' ('.$row['account_number'].')' : '').'</a>', 'align'=>'left');
			$summary_data_row[] = array('data'=>to_currency($row['subtotal']), 'align'=>'right');
			$summary_data_row[] = array('data'=>to_currency($row['total']), 'align'=>'right');
			$summary_data_row[] = array('data'=>to_currency($row['tax']), 'align'=>'right');
			
			if($this->has_profit_permission)
			{
				$summary_data_row[] = array('data'=>to_currency($row['profit']), 'align'=>'right');
				$summary_data_row[] = array('data'=>to_currency($row['subtotal'] - $row['profit']), 'align'=>'right');
			}
			
			$summary_data_row[] = array('data'=>$row['payment_type'], 'align'=>'right');
			$summary_data_row[] = array('data'=>$row['comment'], 'align'=>'right');
			
			if ($tier_count)
			{
				$summary_data_row[] = array('data'=>$row['tier_name'], 'align'=>'right');
			}
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
				
		$data = array(
			'view' => 'tabular_details_lazy_load',
			"title" =>lang('reports_detailed_sales_report'),
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
		$return['summary'][] = array('data'=>lang('common_status'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('deliveries_delivery_employee'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_register'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_items_purchased'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_sold_by'), 'align'=> 'left');
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
		
		$tier_count = $this->Tier->count_all();
		if ($tier_count)
		{
			$return['summary'][] = array('data'=>lang('common_tier_name'), 'align'=> 'right');
		}
		$return['details'] = $this->get_details_data_columns_sales();			
		
		return $return;
	}
	
	public function getData()
	{		
		$this->db->select('sales_deliveries.id as delivery_id, employee_person.full_name as delivery_employee,status,price_tiers.name as tier_name, locations.name as location_name, sales.sale_id, sale_time, date(sale_time) as sale_date, registers.name as register_name, total_quantity_purchased as items_purchased, CONCAT(sold_by_employee.first_name," ",sold_by_employee.last_name) as sold_by_employee, CONCAT(sold_by_employee.first_name," ",sold_by_employee.last_name) as sold_by_employee, CONCAT(employee.first_name," ",employee.last_name) as employee_name, customer.person_id as customer_id, CONCAT(customer.first_name," ",customer.last_name) as customer_name, customer_data.account_number as account_number,subtotal as subtotal, total as total, tax as tax, profit as profit, payment_type, sales_deliveries.comment', false);
		$this->db->from('sales_deliveries');
		$this->db->join('sales', 'sales.sale_id = sales_deliveries.sale_id', 'left');
		$this->db->join('locations', 'sales_deliveries.location_id = locations.location_id','left');
		$this->db->join('registers', 'sales.register_id = registers.register_id', 'left');
		$this->db->join('price_tiers', 'sales.tier_id = price_tiers.id', 'left');
		$this->db->join('people as employee', 'sales.employee_id = employee.person_id','left');
		$this->db->join('people as sold_by_employee', 'sales.sold_by_employee_id = sold_by_employee.person_id', 'left');
		$this->db->join('people as customer', 'sales.customer_id = customer.person_id', 'left');
		$this->db->join('people as employee_person', 'sales_deliveries.delivery_employee_person_id = employee_person.person_id', 'left');
		$this->db->join('customers as customer_data', 'sales.customer_id = customer_data.person_id', 'left');
		
		if ($this->params['employee_id'])
		{
			$this->db->where('delivery_employee_person_id ', $this->params['employee_id']);
		}
		if ($this->params['deliveries_status'] != 'all')
		{
			$this->db->where('sales_deliveries.status ',$this->params['deliveries_status']);
		}
		$this->db->group_start();
		$this->delivery_time_where();
		$this->db->group_end();

		$this->db->group_start();
			$this->db->where('sales.deleted', 0);
			$this->db->or_where('sales_deliveries.sale_id', NULL);
		$this->db->group_end();


		$this->db->where('sales_deliveries.deleted', 0);
		
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
		$this->db->from('sales_deliveries');
		$this->db->join('sales', 'sales.sale_id = sales_deliveries.sale_id','left');
		
		$this->db->group_start();
		$this->delivery_time_where();
		$this->db->group_end();

		$this->db->group_start();
		$this->db->where('sales.deleted', 0);
		$this->db->or_where('sales_deliveries.sale_id', NULL);
		$this->db->group_end();
		
		$this->db->where('sales_deliveries.deleted', 0);
		if ($this->params['employee_id'])
		{
			$this->db->where('delivery_employee_person_id ',$this->params['employee_id']);
		}
		
		return $this->db->count_all_results();
	}
	public function getSummaryData()
	{
		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit', false);
		$this->db->from('sales');
		$this->db->join('sales_deliveries', 'sales.sale_id = sales_deliveries.sale_id');
		
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('sales.store_account_payment', 0);
		}
		
		if ($this->params['employee_id'])
		{
			$this->db->where('delivery_employee_person_id ',$this->params['employee_id']);
		}
		if ($this->params['deliveries_status'] != 'all')
		{
			$this->db->where('sales_deliveries.status ',$this->params['deliveries_status']);
		}
		$this->db->group_start();
		$this->delivery_time_where();
		$this->db->group_end();
		$this->db->where('sales.deleted', 0);
		$this->db->where('sales_deliveries.deleted', 0);
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