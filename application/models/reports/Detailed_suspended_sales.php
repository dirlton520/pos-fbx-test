<?php
require_once ("Report.php");
class Detailed_suspended_sales extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		$return = array();
		
		$location_count = $this->Location->count_all();		
		
		$return['summary'] = array();
		$return['summary'][] = array('data'=>lang('reports_sale_id'), 'align'=> 'left');
		if ($location_count > 1)
		{
			$return['summary'][] = array('data'=>lang('common_location'), 'align'=> 'left');
		}
		
		$return['summary'][] = array('data'=>lang('reports_date'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_register'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_items_purchased'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_qty_picked_up'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_sold_by'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_sold_to'), 'align'=> 'left');		
		$return['summary'][] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('reports_total'), 'align'=> 'right');
		
		$return['summary'][] = array('data'=>lang('common_amount_due'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('common_amount_paid'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('common_last_payment_date'), 'align'=> 'right');

		$return['summary'][] = array('data'=>lang('common_tax'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('reports_tax_based_on_payments'), 'align'=> 'right');
				
		if($this->has_profit_permission)
		{
			$return['summary'][] = array('data'=>lang('common_profit'), 'align'=> 'right');
			$return['summary'][] = array('data'=>lang('common_cogs'), 'align'=> 'right');
		}
		$return['summary'][] = array('data'=>lang('reports_payment_type'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('reports_comments'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('common_type'), 'align'=> 'right');

		$return['details'] = $this->get_details_data_columns_sales();			
		
		return $return;
	}


	
	public function getInputData()
	{		
		$input_data = Report::get_common_report_input_data(TRUE);
		$specific_entity_data['specific_input_name'] = 'customer_id';
		$specific_entity_data['specific_input_label'] = lang('reports_customer');
		$specific_entity_data['search_suggestion_url'] = site_url('reports/customer_search/1');
		$specific_entity_data['view'] = 'specific_entity';
	
	
		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array();
		
			$input_params[] = array('view' => 'date_range', 'with_time' => TRUE);
			$input_params[] = $specific_entity_data;
			
			$dropdown_options = array('all' => lang('reports_all_open_layaways_and_estimates'), 'layaway' => ($this->config->item('user_configured_layaway_name') ? $this->config->item('user_configured_layaway_name') : lang('common_layaway')), 'completed_layaway'  => lang('reports_completed_layaway'), 'estimate' => lang('common_estimate'),'completed_estimate'  => lang('reports_completed_estimate'));
			$this->load->model('Sale_types');
			foreach($this->Sale_types->get_all()->result_array() as $sale_type)
			{
				$dropdown_options[$sale_type['id']] = $sale_type['name'];
			}
			
			$input_params[] = array('view' => 'dropdown','dropdown_label' =>lang('reports_sale_type'),'dropdown_name' => 'sale_type','dropdown_options' =>$dropdown_options,'dropdown_selected_value' => 'all');
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
		$this->setupDefaultPagination();
		$this->load->model('Sale');	
		$this->load->model('Category');
		
		$headers = $this->getDataColumns();
		$report_data = $this->getData();

		$summary_data = array();
		$details_data = array();

		$location_count = $this->Location->count_all();
		$export_excel = $this->params['export_excel'];
		$start_date = $this->params['start_date'];
		$end_date = $this->params['end_date'];
		$customer_id = $this->params['customer_id'];
			
		foreach(isset($export_excel) == 1 && isset($report_data['summary']) ? $report_data['summary']:$report_data as $key=>$row)
		{
			$summary_data_row = array();

			$link = site_url('reports/generate/specific_customer?report_type=complex&start_date='.$this->params['start_date'].'&start_date_formatted='.date(get_date_format().' '.get_time_format(), strtotime($this->params['start_date'])).'&end_date='.$this->params['end_date'].'&end_date_formatted='.date(get_date_format().' '.get_time_format(), strtotime($this->params['end_date'])).'&customer_id='.$row['customer_id'].'&sale_type=all&export_excel=0');
			
			$summary_data_row[] = array('data'=>anchor('sales/receipt/'.$row['sale_id'], '<i class="ion-printer"></i>', 
			array('target' => '_blank', 'class'=>'hidden-print')).'<span class="visible-print">'.$row['sale_id'].'</span>'.anchor('sales/edit/'.$row['sale_id'], '<i class="ion-document-text"></i>', 
			array('target' => '_blank')).' '.anchor('sales/edit/'.$row['sale_id'], lang('common_edit').' '.$row['sale_id'], 
			array('target' => '_blank','class'=>'hidden-print')).'<br />'.anchor('sales/clone_sale/'.$row['sale_id'], lang('common_clone'), 
			array('target' => '_blank','class'=>'hidden-print')), 'align'=>'left', 'detail_id' => $row['sale_id']);
			
			if ($location_count > 1)
			{
				$summary_data_row[] = array('data'=>$row['location_name'], 'align' => 'left');
			}
			
			$summary_data_row[] = array('data'=>date(get_date_format().'-'.get_time_format(), strtotime($row['sale_time'])), 'align'=>'left');
			$summary_data_row[] = array('data'=>$row['register_name'], 'align'=>'left');
			$summary_data_row[] = array('data'=>to_quantity($row['items_purchased']), 'align'=>'left');
			$summary_data_row[] = array('data'=>to_quantity($row['total_quantity_received']), 'align'=>'left');
			$summary_data_row[] = array('data'=>$row['employee_name'].($row['sold_by_employee'] && $row['sold_by_employee'] != $row['employee_name'] ? '/'. $row['sold_by_employee']: ''), 'align'=>'left');
			$summary_data_row[] = array('data'=>'<a href="'.$link.'" target="_blank">'.$row['customer_name'].(isset($row['account_number']) && $row['account_number'] ? ' ('.$row['account_number'].')' : '').'</a>', 'align'=>'left');
			$summary_data_row[] = array('data'=>to_currency($row['subtotal']), 'align'=>'right');
			$summary_data_row[] = array('data'=>to_currency($row['total']), 'align'=>'right');
			$summary_data_row[] = array('data'=>to_currency($row['amount_due']), 'align'=>'right');
			$summary_data_row[] = array('data'=>to_currency($row['amount_paid']), 'align'=>'right');
			$summary_data_row[] = array('data'=>$row['last_payment_date'], 'align'=>'right');
			$summary_data_row[] = array('data'=>to_currency($row['tax']), 'align'=>'right');
			$summary_data_row[] = array('data'=>to_currency($row['tax'] * (1-($row['amount_due']/$row['total']))), 'align'=>'right');
			
			if($this->has_profit_permission)
			{
				$summary_data_row[] = array('data'=>to_currency($row['profit']), 'align'=>'right');
				$summary_data_row[] = array('data'=>to_currency($row['subtotal'] - $row['profit']), 'align'=>'right');
			}
			
			$summary_data_row[] = array('data'=>$row['payment_type'], 'align'=>'right');
			$summary_data_row[] = array('data'=>$row['comment'], 'align'=>'right');
			
			
			if ($row['suspended'] == 1)
			{
				$summary_data_row[] = array('data'=> ($this->config->item('user_configured_layaway_name') ? $this->config->item('user_configured_layaway_name') : lang('common_layaway')), 'align'=>'right');
			}
			elseif ($row['suspended'] == 2)
			{
				$summary_data_row[] = array('data'=> lang('common_estimate'), 'align'=>'right');
			}
			elseif ($row['was_layaway'] == 1)
			{
				$summary_data_row[] = array('data'=> lang('reports_completed_layaway'), 'align'=>'right');
			}
			elseif ($row['was_estimate'] == 1)
			{
				$summary_data_row[] = array('data'=> lang('reports_completed_estimate'), 'align'=>'right');
			}
			else
			{
				$summary_data_row[] = array('data'=> $row['sale_type_name'], 'align'=>'right');
			}
			
			$summary_data[$key] = $summary_data_row;
			
			if($export_excel == 1)
			{

				foreach($report_data['details'][$key] as $drow)
				{
					$details_data_row = array();
					
					$details_data_row[] = array('data'=>$drow['item_number'], 'align'=>'left');
					$details_data_row[] = array('data'=>$drow['item_product_id'], 'align'=>'left');
					$details_data_row[] = array('data'=>$drow['item_id'], 'align'=>'left');
					$details_data_row[] = array('data'=>$drow['item_name'], 'align'=>'left');
					$details_data_row[] = array('data'=>$this->Category->get_full_path($drow['category_id']), 'align'=>'left');
					$details_data_row[] = array('data'=>$drow['size'], 'align'=>'left');
					$details_data_row[] = array('data'=>$drow['supplier_name']. ' ('.$drow['supplier_id'].')', 'align'=>'left');
					$details_data_row[] = array('data'=>$drow['serialnumber'], 'align'=>'left');
					$details_data_row[] = array('data'=>character_limiter($drow['description'],150), 'align'=>'left');
					$details_data_row[] = array('data'=>to_currency($drow['current_selling_price']), 'align'=>'left');
					$details_data_row[] = array('data'=>to_quantity($drow['quantity_purchased']), 'align'=>'left');
					$details_data_row[] = array('data'=>to_quantity($drow['quantity_received']), 'align'=>'left');
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
		
		if ($customer_id)
		{
			$this->load->model('Customer');
			$customer_info = $this->Customer->get_info($customer_id);
		}
		
		$data = array(
			"view" => 'tabular_details_lazy_load',
			"title" =>lang('reports_detailed_suspended_sales_report'),
			"subtitle" => ($customer_id ? ($customer_info->first_name .' '. $customer_info->last_name) : lang('common_all')).' '.date(get_date_format(), strtotime($start_date)) .'-'.date(get_date_format(), strtotime($end_date)),
			"headers" => $this->getDataColumns(),
			"summary_data" => $summary_data,
			"overall_summary_data" => $this->getSummaryData(),
			"export_excel" => $export_excel,
			"pagination" => $this->pagination->create_links(),
			"report_model" => 'Detailed_suspended_sales'
		);
		isset($details_data) && !empty($details_data) ? $data["details_data"]=$details_data: '' ;
		
		return $data;
	}
	
	
	public function getData()
	{
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
				
		$this->db->select('sales.total_quantity_received,sale_types.name as sale_type_name,locations.name as location_name, sale_id, sale_time, date(sale_time) as sale_date, registers.name as register_name, total_quantity_purchased as items_purchased, CONCAT(sold_by_employee.first_name," ",sold_by_employee.last_name) as sold_by_employee, CONCAT(sold_by_employee.first_name," ",sold_by_employee.last_name) as sold_by_employee, CONCAT(employee.first_name," ",employee.last_name) as employee_name, customer.person_id as customer_id, CONCAT(customer.first_name," ",customer.last_name) as customer_name, customer_data.account_number as account_number,subtotal, total, tax, profit, payment_type, comment, discount_reason,suspended, was_layaway, was_estimate', false);
		$this->db->from('sales');
		$this->db->join('sale_types', 'sale_types.id = sales.suspended', 'left');
		$this->db->join('locations', 'sales.location_id = locations.location_id');
		$this->db->join('registers', 'sales.register_id = registers.register_id', 'left');
		$this->db->join('people as employee', 'sales.employee_id = employee.person_id');
		$this->db->join('people as sold_by_employee', 'sales.sold_by_employee_id = sold_by_employee.person_id', 'left');
		$this->db->join('people as customer', 'sales.customer_id = customer.person_id', 'left');
		$this->db->join('customers as customer_data', 'sales.customer_id = customer_data.person_id', 'left');
		$this->db->where('sale_time BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and sales.location_id IN('.$location_ids_string.')');
		if ($this->params['sale_type'] == 'layaway')
		{
			$this->db->where('sales.suspended', 1);
		}
		elseif ($this->params['sale_type'] == 'completed_layaway')
		{
			$this->db->where('sales.suspended', 0);
			$this->db->where('sales.was_layaway', 1);		
		}
		elseif ($this->params['sale_type'] == 'estimate')
		{
			$this->db->where('sales.suspended', 2);
		}
		elseif ($this->params['sale_type'] == 'completed_estimate')
		{
			$this->db->where('sales.suspended', 0);
			$this->db->where('sales.was_estimate', 1);		
		}
		elseif ($this->params['sale_type'] == 'all')
		{
			$this->db->where('sales.suspended !=', 0);
		}
		else //Custom type
		{
			$this->db->where('sales.suspended',$this->params['sale_type']);
		}
		
		if ($this->params['customer_id'])
		{
			$this->db->where('sales.customer_id', $this->params['customer_id']);
		}
		
		$this->db->where('sales.deleted', 0);
		$this->db->group_by('sale_id');
		$this->db->order_by('sale_time', ($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');
		
		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$sale_ids =array();
			$this->db->limit($this->report_limit);
			
			if (isset($this->params['offset']))
			{
				$this->db->offset($this->params['offset']);
			}
			$sales = $this->db->get()->result_array();
			
			foreach($sales as $sale_row)
			{
				$sale_ids[] = $sale_row['sale_id'];
			}
			
			$all_payments_for_sales = $this->Sale->_get_all_sale_payments($sale_ids);	
				
				
			$this->load->model('Sale');
			for($k=0;$k<count($sales);$k++)
			{
				$sales[$k]['last_payment_date'] = lang('common_none');			
				$sale_total = $this->Sale->get_sale_total($sales[$k]['sale_id']);		
				$amount_paid = 0;
				$sale_id = $sales[$k]['sale_id'];
						
				$payment_data = array();
			
				if (isset($all_payments_for_sales[$sale_id]))
				{
					$total_sale_balance = $sale_total;		
				
					foreach($all_payments_for_sales[$sale_id] as $payment_row)
					{
						//Postive sale total, positive payment
						if ($sale_total >= 0 && $payment_row['payment_amount'] >=0)
						{
							$payment_amount = $payment_row['payment_amount'] <= $total_sale_balance ? $payment_row['payment_amount'] : $total_sale_balance;
						}//Negative sale total negative payment
						elseif ($sale_total < 0 && $payment_row['payment_amount']  < 0)
						{
							$payment_amount = $payment_row['payment_amount'] >= $total_sale_balance ? $payment_row['payment_amount'] : $total_sale_balance;
						}//Positive Sale total negative payment
						elseif($sale_total >= 0 && $payment_row['payment_amount']  < 0)
						{
							$payment_amount = $total_sale_balance != 0 ? $payment_row['payment_amount'] : 0;
						}//Negtive sale total postive payment
						elseif($sale_total < 0 && $payment_row['payment_amount']  >= 0)
						{
							$payment_amount = $total_sale_balance != 0 ? $payment_row['payment_amount'] : 0;
						}				
			
						$total_sale_balance-=$payment_amount;	
						$amount_paid+=	$payment_amount;	
					
					
						$sales[$k]['last_payment_date'] = date(get_date_format().' '.get_time_format(), strtotime($payment_row['payment_date']));		
					}
				}
			
				$sales[$k]['sale_total'] = $sale_total;
				$sales[$k]['amount_due'] = $sale_total - $amount_paid;
				$sales[$k]['amount_paid'] = $amount_paid;
			}
			
			
			return $sales;
			exit;
		}		
		
		if (isset($this->params['export_excel']) && $this->params['export_excel'] == 1)
		{
			
			$data=array();
			$data['summary']=array();
			$data['details']=array();
			$sales = $this->db->get()->result_array();
			foreach($sales as $sale_summary_row)
			{
				$data['summary'][$sale_summary_row['sale_id']] = $sale_summary_row; 
			}

			$sale_ids = array();
			
			foreach($data['summary'] as $sale_row)
			{
				$sale_ids[] = $sale_row['sale_id'];
			}
			
			
			$all_payments_for_sales = $this->Sale->_get_all_sale_payments($sale_ids);	
				
				
			$this->load->model('Sale');
			for($k=0;$k<count($sales);$k++)
			{
				$sale_id = $sales[$k]['sale_id'];
				$data['summary'][$sale_id]['last_payment_date'] = lang('common_none');			
				$sale_total = $this->Sale->get_sale_total($sales[$k]['sale_id']);		
				$amount_paid = 0;
						
				$payment_data = array();
			
				if (isset($all_payments_for_sales[$sale_id]))
				{
					$total_sale_balance = $sale_total;		
				
					foreach($all_payments_for_sales[$sale_id] as $payment_row)
					{
						//Postive sale total, positive payment
						if ($sale_total >= 0 && $payment_row['payment_amount'] >=0)
						{
							$payment_amount = $payment_row['payment_amount'] <= $total_sale_balance ? $payment_row['payment_amount'] : $total_sale_balance;
						}//Negative sale total negative payment
						elseif ($sale_total < 0 && $payment_row['payment_amount']  < 0)
						{
							$payment_amount = $payment_row['payment_amount'] >= $total_sale_balance ? $payment_row['payment_amount'] : $total_sale_balance;
						}//Positive Sale total negative payment
						elseif($sale_total >= 0 && $payment_row['payment_amount']  < 0)
						{
							$payment_amount = $total_sale_balance != 0 ? $payment_row['payment_amount'] : 0;
						}//Negtive sale total postive payment
						elseif($sale_total < 0 && $payment_row['payment_amount']  >= 0)
						{
							$payment_amount = $total_sale_balance != 0 ? $payment_row['payment_amount'] : 0;
						}				
			
						$total_sale_balance-=$payment_amount;	
						$amount_paid+=	$payment_amount;	
					
					
						$data['summary'][$sale_id]['last_payment_date'] = date(get_date_format().' '.get_time_format(), strtotime($payment_row['payment_date']));		
					}
				}
			
				$data['summary'][$sale_id]['sale_total'] = $sale_total;
				$data['summary'][$sale_id]['amount_due'] = $sale_total - $amount_paid;
				$data['summary'][$sale_id]['amount_paid'] = $amount_paid;
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
		$location_ids_string = implode(',',$location_ids);
		
		$this->db->select('COUNT(sale_id) as sale_count');
		$this->db->from('sales');
		$this->db->where('sale_time BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and location_id IN('.$location_ids_string.')');
		if ($this->params['sale_type'] == 'layaway')
		{
			$this->db->where('suspended', 1);
		}
		elseif ($this->params['sale_type'] == 'completed_layaway')
		{
			$this->db->where('suspended', 0);
			$this->db->where('was_layaway', 1);		
		}
		elseif ($this->params['sale_type'] == 'estimate')
		{
			$this->db->where('suspended', 2);
		}
		elseif ($this->params['sale_type'] == 'completed_estimate')
		{
			$this->db->where('suspended', 0);
			$this->db->where('was_estimate', 1);		
		}
		elseif ($this->params['sale_type'] == 'all')
		{
			$this->db->where('suspended !=', 0);
		}
		else //Custom type
		{
			$this->db->where('sales.suspended',$this->params['sale_type']);
		}
		
		if ($this->params['customer_id'])
		{
			$this->db->where('sales.customer_id', $this->params['customer_id']);
		}
		
		$this->db->where('sales.deleted', 0);

		$ret = $this->db->get()->row_array();
		return $ret['sale_count'];
	}
	
	public function getSummaryData()
	{
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		
		$this->db->select('sale_id,subtotal, total, tax, profit', false);
		$this->db->from('sales');
		$this->db->where('sale_time BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and location_id IN('.$location_ids_string.')');

		if ($this->params['sale_type'] == 'layaway')
		{
			$this->db->where('suspended', 1);
		}
		elseif ($this->params['sale_type'] == 'completed_layaway')
		{
			$this->db->where('suspended', 0);
			$this->db->where('was_layaway', 1);		
		}
		elseif ($this->params['sale_type'] == 'estimate')
		{
			$this->db->where('suspended', 2);
		}
		elseif ($this->params['sale_type'] == 'completed_estimate')
		{
			$this->db->where('suspended', 0);
			$this->db->where('was_estimate', 1);		
		}
		elseif ($this->params['sale_type'] == 'all')
		{
			$this->db->where('suspended !=', 0);
		}
		else //Custom type
		{
			$this->db->where('sales.suspended',$this->params['sale_type']);
		}
		
		if ($this->params['customer_id'])
		{
			$this->db->where('sales.customer_id', $this->params['customer_id']);
		}
		
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('sales.store_account_payment', 0);
		}
		
		$this->db->where('sales.deleted', 0);
		$this->db->group_by('sales.sale_id');
		
		$return = array(
			'subtotal' => 0,
			'total' => 0,
			'tax' => 0,
			'tax_based_on_payments' => 0,
			'profit' => 0,
			'total_paid' => 0,
			'total_due' => 0,
			'cogs' => 0,
		);

		$sale_ids = array();

		foreach($this->db->get()->result_array() as $row)
		{
			$return['subtotal'] += to_currency_no_money($row['subtotal'],2);
			$return['total'] += to_currency_no_money($row['total'],2);
			$return['tax'] += to_currency_no_money($row['tax'],2);
			$return['profit'] += to_currency_no_money($row['profit'],2);
			$return['cogs'] += to_currency_no_money($row['subtotal']-$row['profit'],2);

			$sale_ids[] = $row['sale_id'];
		}
		
		
		$all_payments_for_sales = $this->Sale->_get_all_sale_payments($sale_ids);	
		
		for($k=0;$k<count($sale_ids);$k++)
		{
			$sale_total = $this->Sale->get_sale_total($sale_ids[$k]);		
			$amount_paid = 0;
			$sale_id = $sale_ids[$k];
					
			$payment_data = array();
		
			if (isset($all_payments_for_sales[$sale_id]))
			{
				$total_sale_balance = $sale_total;		
			
				foreach($all_payments_for_sales[$sale_id] as $payment_row)
				{
					//Postive sale total, positive payment
					if ($sale_total >= 0 && $payment_row['payment_amount'] >=0)
					{
						$payment_amount = $payment_row['payment_amount'] <= $total_sale_balance ? $payment_row['payment_amount'] : $total_sale_balance;
					}//Negative sale total negative payment
					elseif ($sale_total < 0 && $payment_row['payment_amount']  < 0)
					{
						$payment_amount = $payment_row['payment_amount'] >= $total_sale_balance ? $payment_row['payment_amount'] : $total_sale_balance;
					}//Positive Sale total negative payment
					elseif($sale_total >= 0 && $payment_row['payment_amount']  < 0)
					{
						$payment_amount = $total_sale_balance != 0 ? $payment_row['payment_amount'] : 0;
					}//Negtive sale total postive payment
					elseif($sale_total < 0 && $payment_row['payment_amount']  >= 0)
					{
						$payment_amount = $total_sale_balance != 0 ? $payment_row['payment_amount'] : 0;
					}				
		
					$total_sale_balance-=$payment_amount;	
					$amount_paid+=	$payment_amount;	
				
				}
			}
		
			$return['total_due']+= $sale_total - $amount_paid;
			$return['tax_based_on_payments'] = $return['tax'] * (1-($return['total_due']/$sale_total));
			$return['total_paid']+= $amount_paid;
		}
		
		
		if(!$this->has_profit_permission)
		{
			unset($return['profit']);
			unset($return['cogs']);
		}
		return $return;
	}
	
	
	
	
	
	
	function get_details_data_columns_sales()
	{
		$details = array();
		$details[] = array('data'=>lang('common_item_number'), 'align'=> 'left');
		$details[] = array('data'=>lang('common_product_id'), 'align'=> 'left');
		$details[] = array('data'=>lang('common_item_id'), 'align'=> 'left');
		$details[] = array('data'=>lang('reports_name'), 'align'=> 'left');
		$details[] = array('data'=>lang('reports_category'), 'align'=> 'left');
		$details[] = array('data'=>lang('common_size'), 'align'=> 'left');
		$details[] = array('data'=>lang('common_supplier'), 'align'=> 'left');
		$details[] = array('data'=>lang('reports_serial_number'), 'align'=> 'left');
		if (!$this->config->item('hide_item_descriptions_in_reports') || (isset($this->params['export_excel']) && $this->params['export_excel']))
		{
			$details[] = array('data'=>lang('reports_description'), 'align'=> 'left');
		}
		
		$details[] = array('data'=>lang('common_unit_price'), 'align'=> 'left');
		
		$details[] = array('data'=>lang('reports_quantity_purchased'), 'align'=> 'left');
		$details[] = array('data'=>lang('common_qty_picked_up'), 'align'=> 'left');
		$details[] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
		$details[] = array('data'=>lang('reports_total'), 'align'=> 'right');
		$details[] = array('data'=>lang('common_tax'), 'align'=> 'right');
		if($this->has_profit_permission)
		{
			$details[] = array('data'=>lang('common_profit'), 'align'=> 'right');			
			$details[] = array('data'=>lang('common_cogs'), 'align'=> 'right');			
		}
		
		
		if (strpos($this->report_key, 'commission') !== false)
		{
			$details[] = array('data'=>lang('reports_commission'), 'align'=> 'right');			
		}
		
		$details[] = array('data'=>lang('common_discount'), 'align'=> 'right');
		return $details;
	}
	
	function get_report_details($ids, $export_excel=0)
	{
		$this->db->select('sales_items.quantity_received,sales_items.item_unit_price as unit_price,sales_items.item_variation_id, items.item_id, sales_items.sale_id, items.category_id, items.item_number, items.product_id as item_product_id, items.name as item_name, categories.name as category, quantity_purchased, serialnumber, sales_items.description, subtotal, total, tax, profit, commission, discount_percent, items.size as size, sales_items.item_unit_price as current_selling_price, suppliers.company_name as supplier_name, suppliers.person_id as supplier_id', false);
		$this->db->from('sales_items');
		$this->db->join('items', 'sales_items.item_id = items.item_id', 'left');
		$this->db->join('categories', 'categories.id = items.category_id', 'left');
		$this->db->join('suppliers', 'items.supplier_id = suppliers.person_id', 'left');
		if (!empty($ids))
		{
			$sale_ids_chunk = array_chunk($ids,25);
			$this->db->group_start();
			foreach($sale_ids_chunk as $sale_ids)
			{
				$this->db->or_where_in('sales_items.sale_id', $sale_ids);
			}
			$this->db->group_end();
		}
		else
		{
			$this->db->where('1', '2', FALSE);		
		}		
		$qry1=$this->db->get_compiled_select();
		
		$this->db->select('sales_item_kits.quantity_received,sales_item_kits.item_kit_unit_price as unit_price, 0 as item_variation_id, item_kits.item_kit_id, sales_item_kits.sale_id,item_kits.category_id, item_kits.item_kit_number as item_number, item_kits.product_id as item_product_id, item_kits.name as item_name, categories.name as category, quantity_purchased, NULL as serialnumber, sales_item_kits.description, subtotal, total, tax, profit, commission, discount_percent, NULL as size, sales_item_kits.item_kit_unit_price as current_selling_price, NULL as supplier_name, NULL as supplier_id', false);
		$this->db->from('sales_item_kits');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id', 'left');
		$this->db->join('categories', 'categories.id = item_kits.category_id', 'left');
		if (!empty($ids))
		{
			$sale_ids_chunk = array_chunk($ids,25);
			$this->db->group_start();
			foreach($sale_ids_chunk as $sale_ids)
			{
				$this->db->or_where_in('sales_item_kits.sale_id', $sale_ids);
			}
			$this->db->group_end();
		}
		else
		{
			$this->db->where('1', '2', FALSE);		
		}
		
		$qry2=$this->db->get_compiled_select();
		
		$query = $this->db->query($qry1." UNION ALL ".$qry2);
		//echo $this->db->last_query();exit;
		$res=$query->result_array();
		
		if($export_excel == 1)
		{
			return $res;
			exit;
		}
		$this->load->model('Category');
		
		$variation_ids = array();
		foreach($res as $key=>$drow)
		{			
			if (isset($row['variation_id']) && $row['variation_id'])
			{
				$variation_ids[] = $row['variation_id'];
			}
		}
		
		
		$this->load->model('Item_variations');
		$variation_attrs = $this->Item_variations->get_attributes($variation_ids);

		$variation_labels = array();
		
		foreach($variation_attrs as $variation_id => $attrs)
		{
			 $variation_labels[$variation_id] = implode(', ', array_column($attrs,'label'));
		}
		
		$details_data = array();
		foreach($res as $key=>$drow)
			{	
				$details_data_row = array();
				$details_data_row[] = array('data'=>$drow['item_number'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['item_product_id'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['item_id'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['item_name'].(isset($variation_labels[$drow['item_variation_id']]) ? ': '.$variation_labels[$drow['item_variation_id']] : ''), 'align'=>'left');
				$details_data_row[] = array('data'=>$this->Category->get_full_path($drow['category_id']), 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['size'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['supplier_name'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['serialnumber'], 'align'=>'left');
				if (!$this->config->item('hide_item_descriptions_in_reports') || (isset($this->params['export_excel']) && $this->params['export_excel']))
				{
					$details_data_row[] = array('data'=>character_limiter($drow['description'],150), 'align'=>'left');
				}
				$details_data_row[] = array('data'=>to_currency($drow['unit_price']), 'align'=>'left');
				$details_data_row[] = array('data'=>to_quantity($drow['quantity_purchased']), 'align'=>'left');
				$details_data_row[] = array('data'=>to_quantity($drow['quantity_received']), 'align'=>'left');
				
				$details_data_row[] = array('data'=>to_currency($drow['subtotal']), 'align'=>'right');
				$details_data_row[] = array('data'=>to_currency($drow['total']), 'align'=>'right');
				$details_data_row[] = array('data'=>to_currency($drow['tax']), 'align'=>'right');
				
				if($this->has_profit_permission)
				{
					$details_data_row[] = array('data'=>to_currency($drow['profit']), 'align'=>'right');					
					$details_data_row[] = array('data'=>to_currency($drow['subtotal']-$drow['profit']), 'align'=>'right');					
				}
				
				if (strpos($this->report_key, 'commission') !== false)
				{
					$details_data_row[] = array('data'=>to_currency($drow['commission']), 'align'=>'right');					
				}
				$details_data_row[] = array('data'=>$drow['discount_percent'].'%', 'align'=> 'left');
				
				$details_data[$key][$drow['sale_id']] = $details_data_row;
			}
		
		$data=array(
		"headers" => $this->getDataColumns(),
		"details_data" => $details_data
		);
		
		return $data;
	}
	
}
?>