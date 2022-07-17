<?php
require_once ("Report.php");
class Detailed_commissions extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getInputData()
	{
		$input_data = Report::get_common_report_input_data(TRUE);
		
		$input_params = array();
		$specific_entity_data = array();
		$specific_entity_data['view']  = 'specific_entity';
		$specific_entity_data['specific_input_name'] = 'employee_id';
		$specific_entity_data['specific_input_label'] = lang('reports_employee');
		$employees = array();

		$can_view_all_employee_commissions = false;
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;

		if ($this->Employee->has_module_action_permission('reports','view_all_employee_commissions', $employee_id))
		{
			$can_view_all_employee_commissions = true;
		}	
		
		if($can_view_all_employee_commissions == false)
		{
			$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
			$employee = $this->Employee->get_info($employee_id);			
			$employees[$employee->person_id] = $employee->first_name .' '.$employee->last_name;
		}
		else
		{
			$employees['-1'] = lang('common_all');
			foreach($this->Employee->get_all()->result() as $employee)
			{
				$employees[$employee->person_id] = $employee->first_name .' '.$employee->last_name;
			}
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
		$this->setupDefaultPagination();
		$this->load->model('Category');
		
		$logged_in_employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		
		$can_view_all_employee_commissions = false;
		if (!$this->Employee->has_module_action_permission('reports','view_all_employee_commissions', $logged_in_employee_id))
		{
			$employee_id = $logged_in_employee_id;
		}
		else
		{
			$employee_id = $this->params['employee_id'];
		}
		
		$headers = $this->getDataColumns();
		$report_data = $this->getData();
		$export_excel = $this->params['export_excel'];
		$start_date = $this->params['start_date'];
		$end_date = $this->params['end_date'];
		$summary_data = array();
		$details_data = array();
		$location_count = $this->Location->count_all();			

		foreach(isset($export_excel) == 1 && isset($report_data['summary']) ? $report_data['summary']:$report_data as $key=>$row)
		{
			$summary_data_row = array();	
			
			$summary_data_row[] = array('data'=>anchor('sales/receipt/'.$row['sale_id'], '<i class="ion-printer"></i>', array('target' => '_blank')).' '.anchor('sales/edit/'.$row['sale_id'], '<i class="ion-document-text"></i>', array('target' => '_blank')).' '.anchor('sales/edit/'.$row['sale_id'], lang('common_edit').' '.$row['sale_id'], array('target' => '_blank')), 'align'=>'left', 'detail_id' => $row['sale_id']);
			
			if ($location_count > 1)
			{
				$summary_data_row[] = array('data'=>$row['location_name'], 'align'=>'left');
			}
			
			$summary_data_row[] = array('data'=>date(get_date_format().'-'.get_time_format(), strtotime($row['sale_time'])), 'align'=> 'left');
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
			$summary_data_row[] = array('data'=>to_currency($row['commission']), 'align'=> 'right');
			$summary_data_row[] = array('data'=>$row['payment_type'], 'align'=>'right');
			$summary_data_row[] = array('data'=>$row['comment'], 'align'=>'right');
			$summary_data[$key] = $summary_data_row;
			
			
			if($export_excel == 1)
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
					$details_data_row[] = array('data'=>to_currency($drow['commission']), 'align'=>'right');
					$details_data_row[] = array('data'=>$drow['discount_percent'].'%', 'align'=>'left');
					$details_data[$key][] = $details_data_row;
				}
			}
		}
		$employee_info = $this->Employee->get_info($employee_id);
		$data = array(
			"view" => 'tabular_details_lazy_load',
			"title" => $employee_info->first_name .' '. $employee_info->last_name.' '.lang('reports_report'),
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
		$return['summary'][] = array('data'=>lang('reports_commission'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('reports_payment_type'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('reports_comments'), 'align'=> 'right');

		$return['details'] = $this->get_details_data_columns_sales();			
		
		return $return;	
	}
	
	public function getData()
	{
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$can_view_all_employee_commissions = false;
		if ($this->Employee->has_module_action_permission('reports','view_all_employee_commissions', $employee_id))
		{
			$can_view_all_employee_commissions = true;
		}
		
		$data = array();
		$data['summary'] = array();
		$data['details'] = array();
		
		$this->db->select('customer_data.account_number as account_number, locations.name as location_name, sales_items.sale_id, sale_time, date(sale_time) as sale_date, sum(quantity_purchased) as items_purchased, CONCAT(first_name," ",last_name) as customer_name, sum('.$this->db->dbprefix('sales_items').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_items').'.total) as total, sum('.$this->db->dbprefix('sales_items').'.tax) as tax, sum('.$this->db->dbprefix('sales_items').'.profit) as profit, sum('.$this->db->dbprefix('sales_items').'.commission) as commission, payment_type, comment', false);
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		$this->db->join('locations', 'sales.location_id = locations.location_id');
		$this->db->join('people', 'sales.customer_id = people.person_id', 'left');
		$this->db->join('customers as customer_data', 'sales.customer_id = customer_data.person_id', 'left');
		
		$this->sale_time_where();
		
		if ($this->params['employee_type'] == 'logged_in_employee')
		{
			if ($can_view_all_employee_commissions)
			{
				if ($this->params['employee_id'] != -1)
				{
					$this->db->where('sales.employee_id', $this->params['employee_id']);
				}
			}
			else
			{
				$this->db->where('sales.employee_id', $employee_id);
			}
		}
		else
		{
			if ($can_view_all_employee_commissions)
			{
				if ($this->params['employee_id'] != -1)
				{
					$this->db->where('sales.sold_by_employee_id', $this->params['employee_id']);	
				}
			}
			else
			{
				$this->db->where('sales.sold_by_employee_id', $employee_id);			
			}		
		}

		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('sales.total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('sales.total_quantity_purchased < 0');
		}
		
		$this->db->where('sales.deleted', 0);
		
		$this->db->group_by('sales_items.sale_id');
		
		$qry1=$this->db->get_compiled_select();
		
		$this->db->select('customer_data.account_number as account_number, locations.name as location_name, sales_item_kits.sale_id, sale_time, date(sale_time) as sale_date, sum(quantity_purchased) as items_purchased, CONCAT(first_name," ",last_name) as customer_name, sum('.$this->db->dbprefix('sales_item_kits').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_item_kits').'.total) as total, sum('.$this->db->dbprefix('sales_item_kits').'.tax) as tax, sum('.$this->db->dbprefix('sales_item_kits').'.profit) as profit, sum('.$this->db->dbprefix('sales_item_kits').'.commission) as commission, payment_type, comment', false);
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id');
		$this->db->join('locations', 'sales.location_id = locations.location_id');
		$this->db->join('people', 'sales.customer_id = people.person_id', 'left');
		$this->db->join('customers as customer_data', 'sales.customer_id = customer_data.person_id', 'left');

		$this->sale_time_where();
		
		if ($this->params['employee_type'] == 'logged_in_employee')
		{
			if ($can_view_all_employee_commissions)
			{
				if ($this->params['employee_id'] != -1)
				{
					$this->db->where('sales.employee_id', $this->params['employee_id']);
				}
			}
			else
			{
				$this->db->where('sales.employee_id', $employee_id);
			}
		}
		else
		{
			if ($can_view_all_employee_commissions)
			{
				if ($this->params['employee_id'] != -1)
				{
					$this->db->where('sold_by_employee_id', $this->params['employee_id']);	
				}
			}
			else
			{
				$this->db->where('sold_by_employee_id', $employee_id);			
			}		
		}

		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}
		$this->db->where('sales.deleted', 0);
		
		$this->db->group_by('sales_item_kits.sale_id');
			
		$qry2=$this->db->get_compiled_select();		
		
		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{	
			$limit=$this->report_limit;
			$offset=isset($this->params['offset']) ? $this->params['offset'] : 0;
			
			$query = $this->db->query(" select account_number, location_name, sale_id, sale_time,  sale_date, sum(items_purchased) as items_purchased,  customer_name, sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit, sum(commission) as commission, payment_type,comment from (".$qry1." UNION ".$qry2. ") as alias where sale_id is not null group by sale_id limit ".$offset.",".$limit);
	
			$res = $query->result_array();
			return $res;				
				
				
			exit;
		}		
		
		if (isset($this->params['export_excel']) && $this->params['export_excel'] == 1)
		{
			
			
			$query = $this->db->query(" select account_number, location_name, sale_id, sale_time,  sale_date, sum(items_purchased) as items_purchased,  customer_name, sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit, sum(commission) as commission, payment_type,comment from (".$qry1." UNION ".$qry2. ") as alias where sale_id is not null group by sale_id");
	
			$res = $query->result_array();
			
			$data=array();
			$data['summary']=array();
			$data['details']=array();
		
		foreach($res as $sale_summary_row)
		{
			$data['summary'][$sale_summary_row['sale_id']] = $sale_summary_row; 
		}
		
		$sale_ids = array();
		
		foreach($data['summary'] as $sale_row)
		{
			$sale_ids[] = $sale_row['sale_id'];
		}
		
		$result= $this->get_report_details($sale_ids,1);
		
		
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
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$can_view_all_employee_commissions = false;
		if ($this->Employee->has_module_action_permission('reports','view_all_employee_commissions', $employee_id))
		{
			$can_view_all_employee_commissions = true;
		}
		
		$this->db->from('sales');
		
		if ($this->params['employee_type'] == 'logged_in_employee')
		{
			if ($can_view_all_employee_commissions)
			{
				if ($this->params['employee_id'] != -1)
				{
					$this->db->where('sales.employee_id', $this->params['employee_id']);
				}
			}
			else
			{
				$this->db->where('sales.employee_id', $employee_id);
			}
		}
		else
		{
			if ($can_view_all_employee_commissions)
			{
				if ($this->params['employee_id'] != -1)
				{
					$this->db->where('sales.sold_by_employee_id', $this->params['employee_id']);	
				}
			}
			else
			{
				$this->db->where('sales.sold_by_employee_id', $employee_id);			
			}		
		}
		$this->sale_time_where();
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('sales.total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('sales.total_quantity_purchased < 0');
		}
		
		$this->db->where('sales.deleted', 0);
		
		return $this->db->count_all_results();

	}
	
	public function getSummaryData()
	{
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$can_view_all_employee_commissions = false;
		if ($this->Employee->has_module_action_permission('reports','view_all_employee_commissions', $employee_id))
		{
			$can_view_all_employee_commissions = true;
		}
		
		$employee_column = $this->params['employee_type'] == 'logged_in_employee' ? 'employee_id' : 'sold_by_employee_id';
		
		$this->db->select('CONCAT(first_name, " ",last_name) as employee, sum('.$this->db->dbprefix('sales_items').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_items').'.total) as total, sum('.$this->db->dbprefix('sales_items').'.tax) as tax, sum('.$this->db->dbprefix('sales_items').'.profit) as profit, sum('.$this->db->dbprefix('sales_items').'.commission) as commission', false);
		
		$this->db->from('sales');
		$this->db->join('sales_items', 'sales_items.sale_id = sales.sale_id');
		$this->db->join('employees', 'employees.person_id = sales.'.$employee_column);
		$this->db->join('people', 'employees.person_id = people.person_id');
		
		$this->sale_time_where();
		if ($this->params['employee_type'] == 'logged_in_employee')
		{
			if ($can_view_all_employee_commissions)
			{
				if ($this->params['employee_id'] != -1)
				{
					$this->db->where('sales.employee_id', $this->params['employee_id']);
				}
			}
			else
			{
				$this->db->where('sales.employee_id', $employee_id);
			}
		}
		else
		{
			if ($can_view_all_employee_commissions)
			{
				if ($this->params['employee_id'] != -1)
				{
					$this->db->where('sales.sold_by_employee_id', $this->params['employee_id']);	
				}
			}
			else
			{
				$this->db->where('sales.sold_by_employee_id', $employee_id);			
			}		
		}
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('sales.total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('sales.total_quantity_purchased < 0');
		}
		
		$this->db->where('sales.deleted', 0);
		
		if (!$can_view_all_employee_commissions)
		{
			if ($this->params['employee_id'] != -1)
			{
				$this->db->where('employees.person_id',$employee_id);
			}
		}
		
		$this->db->group_by($employee_column);
		
		$qry1=$this->db->get_compiled_select();
		
		$this->db->select('CONCAT(first_name, " ",last_name) as employee, sum('.$this->db->dbprefix('sales_item_kits').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_item_kits').'.total) as total, sum('.$this->db->dbprefix('sales_item_kits').'.tax) as tax, sum('.$this->db->dbprefix('sales_item_kits').'.profit) as profit, sum('.$this->db->dbprefix('sales_item_kits').'.commission) as commission', false);
		
		$this->db->from('sales');
		$this->db->join('sales_item_kits', 'sales_item_kits.sale_id = sales.sale_id');
		$this->db->join('employees', 'employees.person_id = sales.'.$employee_column);
		$this->db->join('people', 'employees.person_id = people.person_id');
		
		$this->sale_time_where();
		if ($this->params['employee_type'] == 'logged_in_employee')
		{
			if ($can_view_all_employee_commissions)
			{
				if ($this->params['employee_id'] != -1)
				{
					$this->db->where('sales.employee_id', $this->params['employee_id']);
				}
			}
			else
			{
				$this->db->where('sales.employee_id', $employee_id);
			}
		}
		else
		{
			if ($can_view_all_employee_commissions)
			{
				if ($this->params['employee_id'] != -1)
				{
					$this->db->where('sales.sold_by_employee_id', $this->params['employee_id']);	
				}
			}
			else
			{
				$this->db->where('sales.sold_by_employee_id', $employee_id);			
			}		
		}
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('sales.total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('sales.total_quantity_purchased < 0');
		}
		
		$this->db->where('sales.deleted', 0);
		
		if (!$can_view_all_employee_commissions)
		{
			if ($this->params['employee_id'] != -1)
			{
				$this->db->where('employees.person_id',$employee_id);
			}
		}
		
		$this->db->group_by($employee_column);

		//If we are exporting NOT exporting to excel make sure to use offset and limit

		$qry2=$this->db->get_compiled_select();
		
		
		$query = $this->db->query($qry1." UNION ".$qry2);
		
		$res=$query->result_array();
		

		$return = array(
			'subtotal' => 0,
			'total' => 0,
			'tax' => 0,
			'profit' => 0,
			'commission' => 0,
			'cogs' => 0,
		);
		
		foreach($res as $row)
		{ 
			$return['subtotal'] += to_currency_no_money($row['subtotal'],2);
			$return['total'] += to_currency_no_money($row['total'],2);
			$return['tax'] += to_currency_no_money($row['tax'],2);
			$return['profit'] += to_currency_no_money($row['profit'],2);
			$return['commission'] += to_currency_no_money($row['commission'],2);
			$return['cogs'] += to_currency_no_money($row['subtotal']-$row['profit'],2);
			
			
		}
		if(!$this->has_profit_permission)
		{
			unset($return['profit']);
			unset($return['cogs']);
		}
		return $return;
	}

	private function merge_item_and_item_kits($items, $item_kits)
	{
		$new_items = array();
		$new_item_kits = array();
		
		foreach($items as $item)
		{
			$new_items[$item['commission']] = $item;
		}
		
		foreach($item_kits as $item_kit)
		{
			$new_item_kits[$item_kit['commission']] = $item_kit;
		}
		
		$merged = array();
		
		foreach($new_items as $commission=>$row)
		{
			if (!isset($merged[$commission]))
			{
				$merged[$commission] = $row;
			}
			else
			{
				$merged[$commission]['subtotal']+= $row['subtotal'];
				$merged[$commission]['total']+= $row['total'];
				$merged[$commission]['tax']+= $row['tax'];
				$merged[$commission]['profit']+= $row['profit'];
				
				
			}
		}
		
		foreach($new_item_kits as $commission=>$row)
		{
			if (!isset($merged[$commission]))
			{
				$merged[$commission] = $row;
			}
			else
			{
				$merged[$commission]['subtotal']+= $row['subtotal'];
				$merged[$commission]['total']+= $row['total'];
				$merged[$commission]['tax']+= $row['tax'];
				$merged[$commission]['profit']+= $row['profit'];
				
				
			}
		}
		
		return $merged;
	}
}
?>