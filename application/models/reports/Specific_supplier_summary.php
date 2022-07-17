<?php
require_once ("Report.php");
class Specific_supplier_summary extends Report
{
	function __construct()
	{		
		parent::__construct();
	}
	
	public function getDataColumns()
	{

		$return = array();
		
		$return['summary'] = array();
		$location_count = $this->Location->count_all();
		
    $return['summary'][] = array('data'=>lang('reports_inventory_number'), 'align'=> 'left');
		if ($location_count > 1)
		{
                    $return['summary'][] = array('data'=>lang('common_location'), 'align'=> 'left');
		}
		
    $return['summary'][] = array('data'=>lang('common_item_name'), 'align'=> 'left');
    $return['summary'][] = array('data'=>lang('common_category'), 'align'=> 'left');
    $return['summary'][] = array('data'=>lang('reports_quantity_sold'), 'align'=> 'left');
    $return['summary'][] = array('data'=>lang('common_unit_price'), 'align'=> 'left');
    $return['summary'][] = array('data'=>lang('common_tax'), 'align'=> 'right');
    $return['summary'][] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
                
                
				
		if($this->Employee->has_module_action_permission('reports','show_profit',$this->Employee->get_logged_in_employee_info()->person_id))
		{
			$return['summary'][] = array('data'=>lang('common_profit'), 'align'=> 'right');
		}
		

		$return['details'] = array();
		
    $return['details'][] = array('data'=>lang('reports_date'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('reports_quantity_purchased'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
		$return['details'][] = array('data'=>lang('reports_total'), 'align'=> 'right');
		$return['details'][] = array('data'=>lang('common_tax'), 'align'=> 'right');
		if($this->Employee->has_module_action_permission('reports','show_profit',$this->Employee->get_logged_in_employee_info()->person_id))
		{
			$return['details'][] = array('data'=>lang('common_profit'), 'align'=> 'right');			
		}
		$return['details'][] = array('data'=>lang('common_discount'), 'align'=> 'right');
		
		return $return;		
	}
		
	public function getInputData()
	{
		$input_data = Report::get_common_report_input_data(TRUE);
		$specific_entity_data['specific_input_name'] = 'supplier_id';
		$specific_entity_data['specific_input_label'] = lang('reports_supplier');
		$specific_entity_data['search_suggestion_url'] = site_url('reports/supplier_search/1');
		$specific_entity_data['view'] = 'specific_entity';
		
		
		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array();
			
			$input_params[] = array('view' => 'date_range', 'with_time' => TRUE);
			$input_params[] = $specific_entity_data;
			$input_params[] = array('view' => 'dropdown','dropdown_label' =>lang('reports_sale_type'),'dropdown_name' => 'sale_type','dropdown_options' =>array('all' => lang('reports_all'), 'sales' => lang('reports_sales'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all');
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
		$this->load->model('Supplier');
		$this->load->model('Category');
		
		$this->setupDefaultPagination();
		$headers = $this->getDataColumns();
		$report_data = $this->getData();

		$summary_data = array();
		$details_data = array();
		$location_count = $this->Location->count_all();

		foreach($report_data['summary'] as $key=>$row)
		{			
			$summary_data_row = array();
					
			if ($location_count > 1)
			{
				$summary_data_row[] = array('data'=>$row['location_name'], 'align' => 'left');
			}
                        
                        
                        $summary_data_row[] = array('data'=>$row['item_number'], 'align'=> 'left');
                        $summary_data_row[] = array('data'=>$row['item_name'], 'align'=> 'left');
                        $summary_data_row[] = array('data'=>$this->Category->get_full_path($row['category']), 'align'=> 'left');
                        $summary_data_row[] = array('data'=>to_quantity($row['items_purchased']), 'align'=> 'left');
                        $summary_data_row[] = array('data'=>to_currency($row['item_price']), 'align'=> 'left');
                        $summary_data_row[] = array('data'=>to_currency($row['tax']), 'align'=> 'right');
                        $summary_data_row[] = array('data'=>to_currency($row['subtotal']), 'align'=> 'right');
                        
                        if($this->has_profit_permission)
                        {
                                $summary_data_row[] = array('data'=>to_currency($row['profit']), 'align'=>'right');
                        }
		
                                                
			$summary_data[$key] = $summary_data_row;
			foreach($report_data['details'][$key] as $drow)
			{                            
				$details_data_row = array();
                                $details_data_row[] = array('data'=>date(get_date_format(),strtotime($drow['sale_date'])), 'align'=> 'left');
				$details_data_row[] = array('data'=>to_quantity($drow['quantity_purchased']), 'align'=> 'left');
				$details_data_row[] = array('data'=>to_currency($drow['subtotal']), 'align'=> 'right');
				$details_data_row[] = array('data'=>to_currency($drow['total']), 'align'=> 'right');
				$details_data_row[] = array('data'=>to_currency($drow['tax']), 'align'=> 'right');
				
				if($this->has_profit_permission)
				{
					$details_data_row[] = array('data'=>to_currency($drow['profit']), 'align'=>'right');					
				}
				$details_data_row[] = array('data'=>$drow['discount_percent'].'%', 'align'=> 'left');
				
				$details_data[$key][] = $details_data_row;
			}	
		}
		
		$supplier_info = $this->Supplier->get_info($this->params['supplier_id']);
		if ($supplier_info->company_name)
		{
			$supplier_title = $supplier_info->company_name.' ('.$supplier_info->first_name .' '. $supplier_info->last_name.')';
		}
		else
		{
			$supplier_title = $supplier_info->first_name .' '. $supplier_info->last_name;		
		}
		
		$data = array(
					"view" => 'tabular_details',
					"title" => $supplier_title.' '.lang('reports_report'),
					"subtitle" => date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date'])),
					"headers" => $this->getDataColumns(),
					"summary_data" => $summary_data,
					"details_data" => $details_data,
					"overall_summary_data" => $this->getSummaryData(),
					"export_excel" => $this->params['export_excel'],
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
    
		$this->db->select('items.unit_price as item_price,items.name as item_name,items.item_number as item_number,sales.sale_id, sale_time, date(sale_time) as sale_date,items.item_id, items.category_id as category, sum(quantity_purchased) as items_purchased,  COUNT('.$this->db->dbprefix('sales').'.sale_id) as sold_quantity, sum('.$this->db->dbprefix('sales_items').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_items').'.total) as total, sum('.$this->db->dbprefix('sales_items').'.tax) as tax, sum('.$this->db->dbprefix('sales_items').'.profit) as profit, payment_type, comment', false);
		$this->db->from('sales');
		$this->db->join('sales_items','sales_items.sale_id = sales.sale_id');
		$this->db->join('items','sales_items.item_id = items.item_id');
		$this->db->join('people', 'sales.customer_id = people.person_id', 'left');
		$this->db->where('DATE(sale_time) BETWEEN "'. $this->params['start_date']. '" and "'. $this->params['end_date'].'" and supplier_id='.$this->params['supplier_id']);
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}
		
		$this->db->where('sales.deleted', 0);
		$this->db->group_by('item_id');
		$this->db->order_by('sale_date');

		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();
		
		foreach($data['summary'] as $key=>$value)
		{
                    
        $this->db->select('date(sale_time) as sale_date,items.supplier_id,items.category_id as category, sales_items.description,quantity_purchased, sales_items.subtotal,sales_items.total, sales_items.tax, sales_items.profit, discount_percent');
        $this->db->from('sales');
				$this->db->join('sales_items','sales_items.sale_id = sales.sale_id');
				$this->db->join('items','sales_items.item_id = items.item_id');
        $this->db->where('items.item_id = '.$value['item_id']);
        $this->db->where('items.supplier_id = '.$this->params['supplier_id']);
				$this->db->where('DATE(sale_time) BETWEEN "'. $this->params['start_date']. '" and "'. $this->params['end_date'].'" and supplier_id='.$this->params['supplier_id']);
        $data['details'][$key] = $this->db->get()->result_array();                    
                        
		}
		return $data;
	}
	
	public function getTotalRows()
	{		
		$location_ids = self::get_selected_location_ids();
		$this->db->select('COUNT('.$this->db->dbprefix('sales').'.sale_id) as sale_count');
		$this->db->from('sales');
		$this->db->join('sales_items', 'sales_items.sale_id = sales.sale_id');
		$this->db->join('items', 'items.item_id = sales_items.item_id', 'left');
		
		$this->db->where_in('sales.location_id', $location_ids);
		$this->db->where('sale_time BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and items.supplier_id='.$this->db->escape($this->params['supplier_id']));
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('sales.total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('sales.total_quantity_purchased < 0');
		}
		
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
		
		$ret = $this->db->get()->row_array();
		return $ret['sale_count'];
	}	
	
	public function getSummaryData()
	{
		$location_ids = self::get_selected_location_ids();
		$this->db->select('sum('.$this->db->dbprefix('sales_items').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_items').'.total) as total, sum('.$this->db->dbprefix('sales_items').'.tax) as tax, sum('.$this->db->dbprefix('sales_items').'.profit) as profit', false);
		$this->db->from('sales');
		$this->db->join('sales_items', 'sales.sale_id = sales_items.sale_id', 'left');
		$this->db->join('items', 'sales_items.item_id = items.item_id', 'left');

		$this->db->where_in('sales.location_id', $location_ids);
		$this->db->where('sale_time BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and items.supplier_id='.$this->db->escape($this->params['supplier_id']));
		
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('sales.total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('sales.total_quantity_purchased < 0');
		}
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
		
		$this->db->group_by('sales.sale_id');
		
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