<?php
require_once ("Report.php");
class Sales_generator extends Report
{
	function __construct()
	{
		parent::__construct();
		
	}
	
	function setCats()
	{
		$this->all_cats = $this->get_all_cats();
	}
	
	public function getInputData()
	{
		return FALSE;
	}
	
	public function getOutputData()
	{
		return FALSE;		
	}
	
	public function getDataColumns()
	{
		$return = array();
		
		$return['summary'] = array();
		$location_count = count(Report::get_selected_location_ids());			
			
		$return['summary'][] = array('data'=>lang('reports_sale_id'), 'align'=> 'left');
	
		if ($location_count > 1)
		{
			$return['summary'][] = array('data'=>lang('common_location'), 'align'=> 'left');
		}
	
		$return['summary'][] = array('data'=>lang('reports_date'), 'align'=> 'left');
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
		}
		$return['summary'][] = array('data'=>lang('reports_payment_type'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('reports_comments'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('common_discount_reason'), 'align'=> 'right');
		
	  for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
		{
			$this->load->model('Sale');
			
			$custom_field = $this->Sale->get_custom_field($k);
			if($custom_field !== FALSE)
			{
				$return['summary'][] = array('data'=>$custom_field, 'align'=> 'right');
			}
		}
		
		$return['details'] = array();
		$return['details'][] = array('data'=>lang('common_item_number'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('common_product_id'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('reports_name'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('reports_category'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('common_size'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('reports_serial_number'), 'align'=> 'left');
		if (!$this->config->item('hide_item_descriptions_in_reports') || (isset($this->params['export_excel']) && $this->params['export_excel']))
		{
			$return['details'][] = array('data'=>lang('reports_description'), 'align'=> 'left');
		}
		$return['details'][] = array('data'=>lang('reports_current_selling_price'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('reports_quantity_purchased'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
		$return['details'][] = array('data'=>lang('reports_total'), 'align'=> 'right');
		$return['details'][] = array('data'=>lang('common_tax'), 'align'=> 'right');
		if($this->has_profit_permission)
		{
			$return['details'][] = array('data'=>lang('common_profit'), 'align'=> 'right');			
		}
		
		if($this->has_cost_price_permission)
		{
			$return['details'][] = array('data'=>lang('common_cost_price'), 'align'=> 'right');			
		}
		
		$return['details'][] = array('data'=>lang('common_discount'), 'align'=> 'right');
		
		return $return;
	}
	
	private function get_all_cats()
	{
		$all_cats = array();
		foreach(array_values($this->params['values']) as $d)
		{
			//Get categories first outside of all other queries or we get nested query issues
			if ($d['f'] == 4)
			{
				foreach($d['i'] as $category)
				{
					if ($this->config->item('include_child_categories_when_searching_or_reporting'))
					{	
						$category_ids = $this->Category->get_category_id_and_children_category_ids_for_category_id($category);			
					}
					else
					{
						$category_ids = array($category);
					}
					
					foreach($category_ids as $cat_id)
					{
						$all_cats[]=$cat_id;
					}
				}
			}
		}
		
		return array_unique($all_cats);
	}
	
	public function getData()
	{		
		if($this->input->get('report_type')=='complex')
		{
			$x=$this->input->get();			
			$start_date=$d2[0]=$this->input->get('start_date');
			$end_date=$d2[1]=$this->input->get('end_date').' 23:59:59';
		}
		else 
		{	
			$report_input_dates = simple_date_range_to_date($this->input->get('report_date_range_simple'), $this->input->get('with_time'),$this->input->get('end_date_end_of_day')); 
			
			$start_date=$report_input_dates['start_date'];
			$end_date=$report_input_dates['end_date'].' 23:59:59';
		}
		
		if ($this->params['matched_items_only'])
		{
			$location_ids = self::get_selected_location_ids();
			$location_ids_string = implode(',',$location_ids);
			
			$this->db->select('sales.custom_field_1_value,sales.custom_field_2_value,sales.custom_field_3_value,sales.custom_field_4_value,sales.custom_field_5_value,sales.custom_field_6_value,sales.custom_field_7_value,sales.custom_field_8_value,sales.custom_field_9_value,sales.custom_field_10_value,customer_data.account_number as account_number, locations.name as location_name, sales.sale_id, sale_time, registers.name as register_name, date(sale_time) as sale_date, SUM(IFNULL('.$this->db->dbprefix('sales_items').'.quantity_purchased,0)) + SUM(IFNULL('.$this->db->dbprefix('sales_item_kits').'.quantity_purchased,0)) as items_purchased, CONCAT(sold_by_employee.first_name," ",sold_by_employee.last_name) as sold_by_employee, CONCAT(employee.first_name," ",employee.last_name) as employee_name, CONCAT(customer.first_name," ",customer.last_name) as customer_name, SUM(IFNULL('.$this->db->dbprefix('sales_items').'.subtotal,0)) + SUM(IFNULL('.$this->db->dbprefix('sales_item_kits').'.subtotal,0)) as subtotal, SUM(IFNULL('.$this->db->dbprefix('sales_items').'.total,0)) + SUM(IFNULL('.$this->db->dbprefix('sales_item_kits').'.total,0)) as total, SUM(IFNULL('.$this->db->dbprefix('sales_items').'.tax,0)) + SUM(IFNULL('.$this->db->dbprefix('sales_item_kits').'.tax,0)) as tax, SUM(IFNULL('.$this->db->dbprefix('sales_items').'.profit,0)) + SUM(IFNULL('.$this->db->dbprefix('sales_item_kits').'.profit,0)) as profit, sales.payment_type, sales.comment, sales.discount_reason', false);
			$this->db->from('sales');
			$this->db->join('sales_items', 'sales_items.sale_id = sales.sale_id','left');
			$this->db->join('sales_item_kits', 'sales_item_kits.sale_id = sales.sale_id','left');
			$this->db->join('locations', 'sales.location_id = locations.location_id','left');
			$this->db->join('registers', 'sales.register_id = registers.register_id','left');
			$this->db->join('people as employee', 'sales.employee_id = employee.person_id','left');
			$this->db->join('people as sold_by_employee', 'sales.sold_by_employee_id = sold_by_employee.person_id', 'left');
			$this->db->join('people as customer', 'sales.customer_id = customer.person_id', 'left');
			$this->db->join('customers as customer_data', 'sales.customer_id = customer_data.person_id', 'left');	
			$this->db->join('items', 'sales_items.item_id = items.item_id','left');
			$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id','left');
			$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id','left');
			$this->db->join('categories', 'categories.id = items.category_id','left');
			$this->db->where_in('sales.location_id', $location_ids);
			$this->db->where('sale_time BETWEEN '. $this->db->escape($start_date). ' and '. $this->db->escape($end_date));
			$this->_searchSalesQueryParams();
			
			$this->db->where('sales.deleted', 0);
					
			if ($this->config->item('hide_layaways_sales_in_reports'))
			{
				$this->db->where('sales.suspended = 0');
			}
			else
			{
				$this->db->where('sales.suspended < 2');					
			}
			if ($this->config->item('hide_store_account_payments_in_reports'))
			{
				$this->db->where('sales.store_account_payment', 0);
			}

			if ($this->params['tax_exempt'])
			{
				$this->db->where('sales.tax',0);
			}
			$this->db->group_by('sales.sale_id');
			$this->db->order_by('sale_time', ($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');
			
			//If we are exporting NOT exporting to excel make sure to use offset and limit
			if (isset($this->params['export_excel']) && !$this->params['export_excel'])
			{
				$this->db->limit($this->report_limit);
				$this->db->offset($this->params['offset']);
				
				
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
		
		else
		{
			$location_ids = self::get_selected_location_ids();
			$location_ids_string = implode(',',$location_ids);
			if($this->input->get('report_type')=='complex')
			{
				$x=$this->input->get();
				$start_date=$d2[0]=$this->input->get('start_date');
				$end_date=$d2[1]=$this->input->get('end_date').' 23:59:59';
			}
			else 
			{	
			
				$report_input_dates = simple_date_range_to_date($this->input->get('report_date_range_simple'), $this->input->get('with_time'),$this->input->get('end_date_end_of_day')); 
			
				$start_date=$report_input_dates['start_date'];
				$end_date=$report_input_dates['end_date'].' 23:59:59';
			}
			
			$sale_ids = $this->_getMatchingSaleIds();
			$this->db->select('sales.custom_field_1_value,sales.custom_field_2_value,sales.custom_field_3_value,sales.custom_field_4_value,sales.custom_field_5_value,sales.custom_field_6_value,sales.custom_field_7_value,sales.custom_field_8_value,sales.custom_field_9_value,sales.custom_field_10_value,customer_data.account_number as account_number,locations.name as location_name,sale_id, sale_time, date(sale_time) as sale_date, registers.name as register_name, sum(total_quantity_purchased) as items_purchased, CONCAT(sold_by_employee.first_name," ",sold_by_employee.last_name) as sold_by_employee, CONCAT(employee.first_name," ",employee.last_name) as employee_name, CONCAT(customer.first_name," ",customer.last_name) as customer_name, sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit, payment_type, comment, discount_reason', false);
			$this->db->from('sales');
			$this->db->join('locations', 'sales.location_id = locations.location_id');
			$this->db->join('people as employee', 'sales.employee_id = employee.person_id');
			$this->db->join('registers', 'sales.register_id = registers.register_id','left');
			$this->db->join('people as sold_by_employee', 'sales.sold_by_employee_id = sold_by_employee.person_id', 'left');
			$this->db->join('people as customer', 'sales.customer_id = customer.person_id', 'left');
			$this->db->join('customers as customer_data', 'sales.customer_id = customer_data.person_id', 'left');
			$this->db->where('sale_time BETWEEN '. $this->db->escape($start_date). ' and '. $this->db->escape($end_date));
			$this->db->where('sales.deleted', 0);
			if ($this->config->item('hide_layaways_sales_in_reports'))
			{
				$this->db->where('sales.suspended = 0');
			}
			else
			{
				$this->db->where('sales.suspended < 2');					
			}
			 
			if ($this->params['tax_exempt'])
			{
				$this->db->where('sales.tax',0);
			}
			if (!empty($sale_ids))
			{
				$this->db->group_start();
				$sale_ids_chunk = array_chunk($sale_ids,25);
				foreach($sale_ids_chunk as $sale_ids)
				{
					$this->db->or_where_in('sale_id', $sale_ids);
				}
				$this->db->group_end();
			}
			else
			{
				$this->db->where('sale_id', -1);
			}
			$this->db->group_by('sale_id');
			$this->db->order_by('sale_time', ($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');
			
			//If we are exporting NOT exporting to excel make sure to use offset and limit
			if (isset($this->params['export_excel']) && !$this->params['export_excel'])
			{
				$this->db->limit($this->report_limit);
				$this->db->offset($this->params['offset']);
				return $this->db->get()->result_array();
				exit;
			}		
		
			if(isset($this->params['export_excel']) && $this->params['export_excel'] == 1)
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
	}
	
	function getTotalRows()
	{		
		$sale_ids = $this->_getMatchingSaleIds();
		return count($sale_ids);
	}
	
	public function getSummaryData()
	{		
		if ($this->params['matched_items_only'])
		{
			$location_ids = self::get_selected_location_ids();
			if($this->input->get('report_type')=='complex')
			{
				$x=$this->input->get();			
				$start_date=$d2[0]=$this->input->get('start_date');
				$end_date=$d2[1]=$this->input->get('end_date').' 23:59:59';
			}
			else 
			{	
				$report_input_dates = simple_date_range_to_date($this->input->get('report_date_range_simple'), $this->input->get('with_time'),$this->input->get('end_date_end_of_day')); 
			
				$start_date=$report_input_dates['start_date'];
				$end_date=$report_input_dates['end_date'].' 23:59:59';
			}

						
				
			$this->db->select('sales.sale_id, SUM(IFNULL('.$this->db->dbprefix('sales_items').'.subtotal,0)) + SUM(IFNULL('.$this->db->dbprefix('sales_item_kits').'.subtotal,0)) as subtotal, SUM(IFNULL('.$this->db->dbprefix('sales_items').'.total,0)) + SUM(IFNULL('.$this->db->dbprefix('sales_item_kits').'.total,0)) as total,SUM(IFNULL('.$this->db->dbprefix('sales_items').'.tax,0)) + SUM(IFNULL('.$this->db->dbprefix('sales_item_kits').'.tax,0)) as tax,SUM(IFNULL('.$this->db->dbprefix('sales_items').'.profit,0)) + SUM(IFNULL('.$this->db->dbprefix('sales_item_kits').'.profit,0)) as profit,SUM(IFNULL('.$this->db->dbprefix('sales_items').'.quantity_purchased,0)) + SUM(IFNULL('.$this->db->dbprefix('sales_item_kits').'.quantity_purchased,0)) as items_purchased', false);
			$this->db->from('sales');
			$this->db->join('sales_items', 'sales_items.sale_id = sales.sale_id','left');
			$this->db->join('sales_item_kits', 'sales_item_kits.sale_id = sales.sale_id','left');
			$this->db->join('items', 'sales_items.item_id = items.item_id','left');
			$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id','left');
			$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id','left');
			$this->db->join('categories', 'categories.id = items.category_id','left');
			$this->db->where_in('sales.location_id', $location_ids);
			$this->db->where('sale_time BETWEEN '. $this->db->escape($start_date). ' and '. $this->db->escape($end_date));			
			$this->db->where('sales.deleted', 0);
					
			if ($this->config->item('hide_layaways_sales_in_reports'))
			{
				$this->db->where('sales.suspended = 0');
			}
			else
			{
				$this->db->where('sales.suspended < 2');					
			}
			if ($this->config->item('hide_store_account_payments_from_report_totals'))
			{
				$this->db->where('sales.store_account_payment', 0);
			}

			if ($this->params['tax_exempt'])
			{
				$this->db->where('sales.tax',0);
			}
			$this->_searchSalesQueryParams();
			$this->db->group_by('sales.sale_id');
			
			$result = $this->db->get()->result_array();
			$return = array('subtotal' => 0, 'total' => 0,'tax' => 0, 'profit' => 0);
			foreach($result as $row)
			{
				$return['subtotal']+=to_currency_no_money($row['subtotal'],2);
				$return['total']+=to_currency_no_money($row['total'],2);
				$return['tax']+=to_currency_no_money($row['tax'],2);
				$return['profit']+=to_currency_no_money($row['profit'],2);
			}
			
			if(!$this->has_profit_permission)
			{
				unset($return['profit']);
			}
			
			return $return;
		}
		else
		{
			$location_ids = self::get_selected_location_ids();
			if($this->input->get('report_type')=='complex')
			{
				$x=$this->input->get();
				$start_date=$d2[0]=$this->input->get('start_date');
				$end_date=$d2[1]=$this->input->get('end_date').' 23:59:59';
			}
			else 
			{	
				$report_input_dates = simple_date_range_to_date($this->input->get('report_date_range_simple'), $this->input->get('with_time'),$this->input->get('end_date_end_of_day')); 
			
				$start_date=$report_input_dates['start_date'];
				$end_date=$report_input_dates['end_date'].' 23:59:59';
			}
			$sale_ids = $this->_getMatchingSaleIds();
			$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit', false);
			$this->db->from('sales');
			$this->db->where_in('sales.location_id', $location_ids);
			$this->db->where('sale_time BETWEEN '. $this->db->escape($start_date). ' and '. $this->db->escape($end_date)); 
			$this->db->where('sales.deleted', 0);
			if ($this->config->item('hide_layaways_sales_in_reports'))
			{
				$this->db->where('sales.suspended = 0');
			}
			else
			{
				$this->db->where('sales.suspended < 2');					
			}
			
			if ($this->params['tax_exempt'])
			{
				$this->db->where('sales.tax',0);
			}
			$this->db->group_by('sale_id');
			
			if (!empty($sale_ids))
			{
				$this->db->group_start();
				$sale_ids_chunk = array_chunk($sale_ids,25);
				foreach($sale_ids_chunk as $sale_ids)
				{
					$this->db->or_where_in('sale_id', $sale_ids);
				}
				$this->db->group_end();
			}
			else
			{
				$this->db->where('sale_id', -1);
			}
			
			$return = array('subtotal' => 0, 'total' => 0,'tax' => 0, 'profit' => 0);
			$result = $this->db->get()->result_array();
			foreach($result as $row)
			{
				$return['subtotal']+=to_currency_no_money($row['subtotal'],2);
				$return['total']+=to_currency_no_money($row['total'],2);
				$return['tax']+=to_currency_no_money($row['tax'],2);
				$return['profit']+=to_currency_no_money($row['profit'],2);
			}
			
			if(!$this->has_profit_permission)
			{
				unset($return['profit']);
			}
			
			return $return;
		}
	}
	
	private function _getMatchingSaleIds()
	{		
		$location_ids = self::get_selected_location_ids();
		if($this->input->get('report_type')=='complex')
		{
		$x=$this->input->get();
		$start_date=$d2[0]=$this->input->get('start_date');
		$end_date=$d2[1]=$this->input->get('end_date').' 23:59:59';				
		}
		else 
		{	
			$report_input_dates = simple_date_range_to_date($this->input->get('report_date_range_simple'), $this->input->get('with_time'),$this->input->get('end_date_end_of_day')); 
			
			$start_date=$report_input_dates['start_date'];
			$end_date=$report_input_dates['end_date'].' 23:59:59';
		}
		$this->db->select('sales.sale_id, total_quantity_purchased as items_purchased, '.$this->db->dbprefix('sales').'.total as total', false);
		$this->db->from('sales');
		$this->db->join('sales_items', 'sales_items.sale_id = sales.sale_id','left');
		$this->db->join('sales_item_kits', 'sales_item_kits.sale_id = sales.sale_id','left');
		$this->db->join('items', 'sales_items.item_id = items.item_id','left');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id','left');
		$this->db->join('suppliers', 'suppliers.person_id = items.supplier_id','left');
		$this->db->join('categories', ' items.category_id=categories.id','left');
		
		$this->db->where_in('sales.location_id', $location_ids);
		$this->db->where('sale_time BETWEEN '. $this->db->escape($start_date). ' and '. $this->db->escape($end_date));
		$this->_searchSalesQueryParams();
		
		$this->db->where('sales.deleted', 0);
		if ($this->config->item('hide_layaways_sales_in_reports'))
		{
			$this->db->where('sales.suspended = 0');
		}
		else
		{
			$this->db->where('sales.suspended < 2');					
		}
		if ($this->config->item('hide_store_account_payments_in_reports'))
		{
			$this->db->where('sales.store_account_payment', 0);
		}

		$this->db->group_by('sales.sale_id');
		$this->db->order_by('sale_time', ($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');
		
		if ($this->params['tax_exempt'])
		{
			$this->db->where('sales.tax',0);
		}
				
		$sales_matches = $this->db->get()->result_array();
		$sale_ids = array();
		foreach($sales_matches as $sale_match)
		{
			$sale_ids[] = $sale_match['sale_id'];
		}
				return $sale_ids;
	}
	
	private function _searchSalesQueryParams($only_search = NULL)//items or item_kits
	{
		$decimals = $this->config->item('number_of_decimals') !== NULL && $this->config->item('number_of_decimals') != '' ? (int)$this->config->item('number_of_decimals') : 2;
		
		$matchType = 'where';
		$matchTypeGroup = 'group_start';		
		
		if ($this->params['matchType'] == 'matchType_Or') 
		{
			$matchType = 'or_where';
			$matchTypeGroup = 'or_group_start';		
		}
		if ($this->params['values'][0]['f'] != 0) 
		{
			$this->db->group_start();
			
			foreach ($this->params['values'] as $w => $d) 
			{
				$ops = $this->params['ops'][$d['o']]; // Condition Operator
				if (count($d['i']) > 1) 
				{
					if ($d['o'] == 1) { $ops = $this->params['ops'][5]; }
					if ($d['o'] == 2) { $ops = $this->params['ops'][6]; }
				}

				if  ($d['f'] == 6 && $d['o'] == 10) 
				{ 
					// Sale Type
					$this->db->or_where('sales.total_quantity_purchased > 0');
				} 
				elseif ($d['f'] == 6 && $d['o'] == 11) 
				{ 
					// Returns
					$this->db->or_where('sales.total_quantity_purchased < 0');
				} 
				elseif ($d['f'] == 7) 
				{ 
					for($k = 0;$k<count($d['i']);$k++)
					{
						$d['i'][$k] = $this->db->escape_str($d['i'][$k]);
					}
					
					// Sale Amount
					if ($this->params['matchType'] == 'matchType_All')
					{
						$this->db->where('ROUND('.$this->db->dbprefix('sales').'.total,'.$decimals.') '.str_replace("xx", join(", ", $d['i']), $ops),null,false);				
					}
					elseif($this->params['matchType'] == 'matchType_Or')
					{
						$this->db->or_where('ROUND('.$this->db->dbprefix('sales').'.total,'.$decimals.') '.str_replace("xx", join(", ", $d['i']), $ops),null,false);				
					}
				}
				elseif($d['f'] == 11)
				{
					//Payment type
					$payment_field = $this->db->dbprefix($this->params['tables'][$d['f']]);
				
					$payment_like = '(';
				
					$store_account_payment_types = get_all_language_values_for_key('common_store_account');
					$points_payment_types = get_all_language_values_for_key('common_points');
					$giftcard_payment_types = get_all_language_values_for_key('common_giftcard');
					$check_payment_types = get_all_language_values_for_key('common_check');
					$credit_payment_types = get_all_language_values_for_key('common_credit');
					$partial_credit_payment_types = get_all_language_values_for_key('sales_partial_credit');
					$cash_payment_types = get_all_language_values_for_key('common_cash');
					$debit_payment_types = get_all_language_values_for_key('common_debit');
				
					foreach($d['i'] as $payment_type)
					{
						$payment_type = $this->db->escape_like_str($payment_type);
						
						if ($payment_type == lang('common_store_account'))
						{
							$payment_types = $store_account_payment_types;
						}
						elseif($payment_type == lang('common_points'))
						{
							$payment_types = $points_payment_types;
						}
						elseif($payment_type == lang('common_giftcard'))
						{
							$payment_types = $giftcard_payment_types;
						}
						elseif($payment_type == lang('common_check'))
						{
							$payment_types = $check_payment_types;
						}
						elseif($payment_type == lang('common_credit'))
						{
							$payment_types = $credit_payment_types;
						}
						elseif($payment_type == lang('sales_partial_credit'))
						{
							$payment_types = $partial_credit_payment_types;	
						}
						elseif($payment_type == lang('common_cash'))
						{							
							$payment_types = $cash_payment_types;
						}
						elseif($payment_type == lang('common_debit'))
						{
							$payment_types = $debit_payment_types;
						}
						else
						{
							$payment_types[] = $payment_type;
						}
						
						foreach($payment_types as $payment_type_l)
						{
							$payment_like.= $payment_field." LIKE '".(!$this->config->item('speed_up_search_queries') ? '%' : '').$payment_type."%' OR ";						
						}			
					}
				 	$payment_like = rtrim($payment_like, ' OR ');
				
					$payment_like.= ')';
					$this->db->{$matchType}($payment_like, null, false);
				
				}
				elseif($d['f'] == 15)
				{					
					$this->db->$matchTypeGroup();
					if ($only_search === NULL || $only_search == 'items')
					{
						$this->db->or_where('items.manufacturer_id'.' '.str_replace("xx", join("', '", $d['i']), $ops));
					}
					
					if ($only_search === NULL || $only_search == 'item_kits')
					{
						$this->db->or_where('item_kits.manufacturer_id'.' '.str_replace("xx", join("', '", $d['i']), $ops));
					}
					
					$this->db->group_end();
				}
				
				elseif($d['f'] == 4)
				{					
					$this->db->$matchTypeGroup();
					if ($ops == " != 'xx'" || $ops == " NOT IN ('xx')")
					{
						if ($only_search === NULL || $only_search == 'items')
						{	
							$this->db->or_where_not_in('items.category_id',$this->all_cats);
						}
				
						if ($only_search === NULL || $only_search == 'item_kits')
						{
							$this->db->or_where_not_in('item_kits.category_id',$this->all_cats);
						}
						
					}
					else
					{
						if ($only_search === NULL || $only_search == 'items')
						{				
							$this->db->or_where_in('items.category_id',$this->all_cats);
						}
				
						if ($only_search === NULL || $only_search == 'item_kits')
						{
							$this->db->or_where_in('item_kits.category_id',$this->all_cats);
						}
					
					}
						$this->db->group_end();
				}
				
				else 
				{
					for($k = 0;$k<count($d['i']);$k++)
					{
						$d['i'][$k] = $this->db->escape_str($d['i'][$k]);
					}
					
					if($only_search===NULL || strpos($this->params['tables'][$d['f']], $only_search) !== FALSE)
					{
						$this->db->{$matchType}($this->params['tables'][$d['f']].' '.str_replace("xx", join("', '", $d['i']), $ops));
					}
					else
					{
						$this->db->{$matchType}('1=2');
					}
				}
			}
			$this->db->group_end();
		
		}
		
	}
	
	function get_report_details($ids, $export_excel=0)
	{
		$this->db->select('sales_items.sale_id, items.category_id, items.item_number, items.product_id as item_product_id, items.name as item_name, categories.name as category, quantity_purchased, serialnumber, sales_items.description, sales_items.subtotal, sales_items.total, sales_items.tax, sales_items.profit, discount_percent, items.size as size, items.unit_price as current_selling_price, suppliers.company_name as supplier_name, suppliers.person_id as supplier_id,sales_items.item_cost_price as cost_prices,', false);
		$this->db->from('sales_items');
		$this->db->join('items', 'sales_items.item_id = items.item_id', 'left');
		$this->db->join('categories', 'categories.id = items.category_id', 'left');
		$this->db->join('suppliers', 'items.supplier_id = suppliers.person_id', 'left');
							
		if ($this->params['matched_items_only'])
		{
			$this->db->join('sales', 'sales.sale_id = sales_items.sale_id','left');
			$this->db->join('locations', 'sales.location_id = locations.location_id','left');
			$this->db->join('registers', 'sales.register_id = registers.register_id','left');
			$this->db->join('people as employee', 'sales.employee_id = employee.person_id','left');
			$this->db->join('people as sold_by_employee', 'sales.sold_by_employee_id = sold_by_employee.person_id', 'left');
			$this->db->join('people as customer', 'sales.customer_id = customer.person_id', 'left');
			$this->db->join('customers as customer_data', 'sales.customer_id = customer_data.person_id', 'left');	
	
			$this->_searchSalesQueryParams('items');
		}
						
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
		
		$this->db->select('sales_item_kits.sale_id,item_kits.category_id, item_kits.item_kit_number as item_number, item_kits.product_id as item_product_id, item_kits.name as item_name, categories.name as category, quantity_purchased, NULL as serialnumber, sales_item_kits.description, sales_item_kits.subtotal, sales_item_kits.total, sales_item_kits.tax, sales_item_kits.profit, discount_percent, NULL as size, item_kits.unit_price as current_selling_price, NULL as supplier_name, NULL as supplier_id, null as cost_prices', false);
		$this->db->from('sales_item_kits');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id', 'left');
		$this->db->join('categories', 'categories.id = item_kits.category_id', 'left');		
		
		if ($this->params['matched_items_only'])
		{
			$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id','left');
			$this->db->join('locations', 'sales.location_id = locations.location_id','left');
			$this->db->join('registers', 'sales.register_id = registers.register_id','left');
			$this->db->join('people as employee', 'sales.employee_id = employee.person_id','left');
			$this->db->join('people as sold_by_employee', 'sales.sold_by_employee_id = sold_by_employee.person_id', 'left');
			$this->db->join('people as customer', 'sales.customer_id = customer.person_id', 'left');
			$this->db->join('customers as customer_data', 'sales.customer_id = customer_data.person_id', 'left');	
		
			$this->_searchSalesQueryParams('item_kits');		
		}
		
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
		$res=$query->result_array();
		
		if($export_excel == 1)
		{
			return $res;
			exit;
		}
		$this->load->model('Category');
		$details_data = array();
		foreach($res as $key=>$drow)
			{	
			
			
				$details_data_row = array();
				$details_data_row[] = array('data'=>$drow['item_number'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['item_product_id'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['item_name'], 'align'=>'left');
				
				$details_data_row[] = array('data'=>$this->Category->get_full_path($drow['category_id']), 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['size'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['serialnumber'], 'align'=>'left');
				if (!$this->config->item('hide_item_descriptions_in_reports') || (isset($this->params['export_excel']) && $this->params['export_excel']))
				{
					$details_data_row[] = array('data'=>character_limiter($drow['description'],150), 'align'=>'left');
				}
				$details_data_row[] = array('data'=>to_currency($drow['current_selling_price']), 'align'=>'left');
				$details_data_row[] = array('data'=>to_quantity($drow['quantity_purchased']), 'align'=>'left');
				$details_data_row[] = array('data'=>to_currency($drow['subtotal']), 'align'=>'right');
				$details_data_row[] = array('data'=>to_currency($drow['total']), 'align'=>'right');
				$details_data_row[] = array('data'=>to_currency($drow['tax']), 'align'=>'right');
				
				if($this->has_profit_permission)
				{
					$details_data_row[] = array('data'=>to_currency($drow['profit']), 'align'=>'right');					
				}
				$details_data_row[] = array('data'=>to_currency($drow['cost_prices']), 'align'=>'right');
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