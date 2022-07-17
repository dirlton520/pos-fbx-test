<?php
require_once ("Report.php");
class Summary_journal extends Report
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Category');
		$this->load->model('Sale');
	}
	
	
	public function getInputData()
	{	
		$input_data = Report::get_common_report_input_data(TRUE);
			
		$input_params = array(
			array('view' => 'date_range', 'with_time' => TRUE),
			array('view' => 'dropdown','dropdown_label' =>lang('reports_sale_type'),'dropdown_name' => 'sale_type','dropdown_options' =>array('all' => lang('reports_all'), 'sales' => lang('reports_sales'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all'),
			array('view' => 'locations'),
			array('view' => 'excel_export'),
			array('view' => 'submit'),
		);
				
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}
	
	function getOutputData()
	{
		
		$subtitle = date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date']));

		$report_data = $this->getData();
				
		$summary_data = $this->getSummaryData();
		
		$this->setupDefaultPagination();
		$tabular_data = array();

		$index = 0;
		
		foreach($report_data as $row)
		{
			$data_row = array();
			foreach($row as $cell)
			{
				$data_row[] = array('data'=>$cell, 'align' => 'left');
			}
			$tabular_data[] = $data_row;				
		}
		
			
	 		$data = array(
				'view' => 'tabular',
				"title" => lang('reports_summary_journal'),
				"subtitle" => $subtitle,
				"headers" => $this->getDataColumns(),
				"data" => $tabular_data,
				"summary_data" => $summary_data,
				"export_excel" => $this->params['export_excel'],
				"pagination" => $this->pagination->create_links(),
			);
			
		
		return $data;
	}
	
	public function getDataColumns()
	{
		$payment_options = array_keys($this->Sale->get_payment_options_with_language_keys());
		$columns = array();
		
		$columns[] = array('data'=>lang('reports_date'), 'align'=> 'left');
		
		foreach($payment_options as $payment_option)
		{
			$columns[] = array('data'=>$payment_option, 'align'=> 'left');
		}
		
		foreach($this->Category->get_all_categories_including_children() as $cat_row)
		{
			$columns[] = array('data'=>$cat_row['name'], 'align'=> 'left');
		}
		
		$columns[] = array('data'=>lang('common_tax'), 'align'=> 'left');
		
		return $columns;		
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
	
	public function getCategoryData()
	{
		$this->db->select('date(phppos_sales.sale_time) as sale_date,items.category_id, categories.name as category , sum('.$this->db->dbprefix('sales_items').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_items').'.total) as total, sum('.$this->db->dbprefix('sales_items').'.tax) as tax, sum('.$this->db->dbprefix('sales_items').'.profit) as profit, sum('.$this->db->dbprefix('sales_items').'.quantity_purchased) as item_sold', false);
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		$this->db->join('price_tiers', 'sales.tier_id = price_tiers.id', 'left');
		$this->db->join('items', 'sales_items.item_id = items.item_id');
		$this->db->join('categories', 'categories.id = items.category_id');
		$this->sale_time_where();
		$this->db->where('sales.deleted', 0);
		
		
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('sales.store_account_payment', 0);
		}

		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}
				
		$this->db->group_by('DATE(phppos_sales.sale_time),items.category_id');
		
		
		$items= $this->db->get()->result_array();	

		$this->db->select('date(phppos_sales.sale_time) as sale_date,item_kits.category_id, categories.name as category , sum('.$this->db->dbprefix('sales_item_kits').'.subtotal) as subtotal, sum('.$this->db->dbprefix('sales_item_kits').'.total) as total, sum('.$this->db->dbprefix('sales_item_kits').'.tax) as tax, sum('.$this->db->dbprefix('sales_item_kits').'.profit) as profit, sum('.$this->db->dbprefix('sales_item_kits').'.quantity_purchased) as item_sold', false);
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id');
		$this->db->join('price_tiers', 'sales.tier_id = price_tiers.id', 'left');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id');
		$this->db->join('categories', 'categories.id = item_kits.category_id');		
		$this->sale_time_where();
		$this->db->where('sales.deleted', 0);
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('sales.store_account_payment', 0);
		}		
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('quantity_purchased < 0');
		}
					
		$this->db->group_by('DATE(phppos_sales.sale_time),item_kits.category_id');		
		$item_kits = $this->db->get()->result_array();
		$items_and_kits = $this->merge_item_and_item_kits($items, $item_kits);
		
		return $items_and_kits;
		
	}
	
	private function merge_item_and_item_kits($items, $item_kits)
	{
		$location_ids = self::get_selected_location_ids();
		$new_items = array();
		$new_item_kits = array();
		
		foreach($items as $item)
		{
			$new_items[$item['sale_date']][$item['category_id']] = $item;
		}
				
		foreach($item_kits as $item_kit)
		{
			$new_item_kits[$item_kit['sale_date']][$item_kit['category_id']] = $item_kit;
		}
		
		$merged = array();
		
		foreach($new_items as $sale_date=>$category_row)
		{
			foreach($category_row as $category_id => $row)
			{
				if (!isset($merged[$sale_date][$category_id]))
				{
					$merged[$sale_date][$category_id] = $row;
				}
				else
				{
					$merged[$sale_date][$category_id]['subtotal']+= $row['subtotal'];
					$merged[$sale_date][$category_id]['total']+= $row['total'];
					$merged[$sale_date][$category_id]['tax']+= $row['tax'];
					$merged[$sale_date][$category_id]['profit']+= $row['profit'];
					$merged[$sale_date][$category_id]['item_sold']+= $row['item_sold'];
				}
			}
		}
		
		foreach($new_item_kits as $sale_date=>$category_row)
		{
			foreach($category_row as $category_id => $row)
			{
				if (!isset($merged[$sale_date][$category_id]))
				{
					$merged[$sale_date][$category_id] = $row;
				}
				else
				{
					$merged[$sale_date][$category_id]['subtotal']+= $row['subtotal'];
					$merged[$sale_date][$category_id]['total']+= $row['total'];
					$merged[$sale_date][$category_id]['tax']+= $row['tax'];
					$merged[$sale_date][$category_id]['profit']+= $row['profit'];
					$merged[$sale_date][$category_id]['item_sold']+= $row['item_sold'];
				}
			}
		}
		
		
		return $merged;
	}
	
	
	public function getPaymentsData()
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
		$this->db->order_by('sale_id, payment_date, payment_type');
				
		$sales_payments = $this->db->get()->result_array();
		
		$payments_by_sale = array();
		foreach($sales_payments as $row)
		{
        	$payments_by_sale[$row['sale_id']][] = $row;
		}
		
		$this->load->model('Sale');
		$payment_data = $this->Sale->get_payment_data_grouped_by_day($payments_by_sale,$sales_totals);
		
		
		return $payment_data;
	}
	
	
	public function getData()
	{	
		$payment_options = array_keys($this->Sale->get_payment_options_with_language_keys());
		
		$payment_data = $this->getPaymentsData();
		$category_data = $this->getCategoryData();
		$tax_data = $this->getTaxData();
		
		$return = array();
		
		foreach(array_keys($category_data) as $date)
		{
			$row = array();
			$row[] = date(get_date_format(), strtotime($date));
					
			foreach($payment_options as $payment_option)
			{
				$row[] = to_currency(isset($payment_data[$date][$payment_option]) ? $payment_data[$date][$payment_option] : 0);
			}
		
			foreach($this->Category->get_all_categories_including_children() as $cat_row)
			{
				$row[] = to_currency(isset($category_data[$date][$cat_row['id']]['subtotal']) ? $category_data[$date][$cat_row['id']]['subtotal'] : 0);
			}
		
			$row[] = to_currency(isset($tax_data[$date]) ? $tax_data[$date] : 0);
			
			$return[] = $row;
			
		}
		
		return $return;
	}
	
	public function getSummaryData()
	{
		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit', false);
		$this->db->from('sales');
				
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('sales.total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('sales.total_quantity_purchased < 0');
		}
				
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('sales.store_account_payment', 0);
		}

		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			if (isset($this->params['offset']))
			{
				$this->db->offset($this->params['offset']);
			}
		}
		
		
		$this->sale_time_where();
		$this->db->where('deleted', 0);
		
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
	
	function getTotalRows()
	{
		return 1;
	}
	
	function getTaxData()
	{
		$this->db->select('date(sale_time) as sale_date,sum(tax) as tax',false);
		$this->db->from('sales');
				
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('sales.total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('sales.total_quantity_purchased < 0');
		}
				
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('sales.store_account_payment', 0);
		}
		
		
		$this->sale_time_where();
		$this->db->where('deleted', 0);
		$this->db->group_by('date(sale_time)');		
		
		$return = array();
		
		foreach($this->db->get()->result_array() as $row)
		{
			$return[$row['sale_date']] = $row['tax'];
		}
		
		return $return;
	}
}
?>