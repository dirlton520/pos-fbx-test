<?php
require_once ("Report.php");
class Detailed_profit_and_loss extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array();		
	}
	
	
	public function getInputData()
	{
		$input_data = Report::get_common_report_input_data(TRUE);
		
		$input_params = array();

		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array(
				array('view' => 'date_range', 'with_time' => TRUE),
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
		$this->load->model('Sale');			
		$this->load->model('Category');
		
		
		$data = array(
			"view" => 'profit_and_loss_details',
			"subtitle" => date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date'])),
			"details_data" => $this->getData(),
			"overall_summary_data" => $this->getSummaryData(),
		);
		
		return $data;		
	}	
	
	
	public function getData()
	{
		$location_ids = self::get_selected_location_ids();
		
		
		$total = 0;
		
		$data = array();
		
		$sales_totals = array();
		$this->db->select('sale_id, sum(total) as total', false);
		$this->db->from('sales');
		$this->db->group_by('sale_id');
			
		foreach($this->db->get()->result_array() as $sale_total_row)
		{
			$sales_totals[$sale_total_row['sale_id']] = to_currency_no_money($sale_total_row['total']);
		}

		$this->db->select('sales_payments.sale_id, sales_payments.payment_type as payment_type, payment_amount', false);
		$this->db->from('sales_payments');
		$this->db->join('sales', 'sales.sale_id=sales_payments.sale_id');
		$this->db->where('date(payment_date) BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']));
		
		//We only want sales, we don't want negative transactions
		$this->db->where('payment_amount > 0');
		$this->db->where('sales.suspended < 2');
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->where_in('sales.location_id', $location_ids);
		//$this->sale_time_where();
		
		$this->db->order_by('sale_id, payment_date, payment_type');
		
		$sales_payments = $this->db->get()->result_array();
		
		$payments_by_sale = array();
		
		foreach($sales_payments as $row)
		{
        	$payments_by_sale[$row['sale_id']][] = $row;
		}
		
		$payment_data = array();
		
		foreach($payments_by_sale as $sale_id => $payment_rows)
		{
			if(isset($sales_totals[$sale_id])){
				$total_sale_balance = $sales_totals[$sale_id];
			}
			
			foreach($payment_rows as $row)
			{
				$payment_amount = $row['payment_amount'] <= $total_sale_balance ? $row['payment_amount'] : $total_sale_balance;
				
				if (!isset($payment_data[$row['payment_type']]))
				{
					$payment_data[$row['payment_type']] = array('payment_type' => $row['payment_type'], 'payment_amount' => 0 );
				}
				
				if ($total_sale_balance != 0)
				{
					$payment_data[$row['payment_type']]['payment_amount'] += $payment_amount;
				}
				
				$total_sale_balance-=$payment_amount;
			}
		}
				
		$data['sales_by_payments'] = $payment_data;		
		
		$this->db->select('categories.name as category, categories.id as category_id, sum('.$this->db->dbprefix('sales_items').'.total) as total', false);
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id','left');
		$this->db->join('items', 'sales_items.item_id = items.item_id', 'left');
		$this->db->join('categories', 'categories.id = items.category_id','left');
		$this->db->where('sales.suspended < 2');
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->where_in('sales.location_id', $location_ids);
		$this->sale_time_where();
		$this->db->where('sales.deleted', 0);
		$this->db->where(''.$this->db->dbprefix('sales_items').'.total > 0');
		$this->db->group_start();
		$this->db->where('items.name !=', lang('common_discount'));
		$this->db->or_where('items.name IS NULL');
		$this->db->group_end();
		
		
		$this->db->group_by('category_id');
		//$this->db->order_by('category');
		$qry1=$this->db->get_compiled_select();
		
		
		$this->db->select('categories.name as category, categories.id as category_id, sum('.$this->db->dbprefix('sales_item_kits').'.total) as total', false);
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id','left');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id', 'left');
		$this->db->join('categories', 'categories.id = item_kits.category_id','left');
		$this->db->where('sales.suspended < 2');
		$this->db->where('sales.deleted', 0);
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->where_in('sales.location_id', $location_ids);
		$this->sale_time_where();
		$this->db->where(''.$this->db->dbprefix('sales_item_kits').'.total > 0');
		$this->db->group_start();
		$this->db->where('item_kits.name !=', lang('common_discount'));
		$this->db->or_where('item_kits.name IS NULL');
		$this->db->group_end();
		
		
		$this->db->group_by('category_id');
		//$this->db->order_by('category');
		
		$qry2=$this->db->get_compiled_select();
		
		
		$query = $this->db->query("select category, category_id,sum(total) as total from (".$qry1." UNION ".$qry2. ") as alias  group by category_id order by category");
		
	
		$res=$query->result_array();
		
		$data['sales_by_category'] = $res;
		
		foreach($data['sales_by_category'] as $sales_by_category)
		{
			$total+=$sales_by_category['total'];
		}
		
		
		$returns_total = array();
		$this->db->select('sale_id, sum(total) as total', false);
		$this->db->from('sales');
		$this->db->group_by('sale_id');
			
		foreach($this->db->get()->result_array() as $return_total_row)
		{
			$returns_total[$return_total_row['sale_id']] = to_currency_no_money($return_total_row['total']);
		}

		$this->db->select('sales_payments.sale_id, sales_payments.payment_type, payment_amount', false);
		$this->db->from('sales_payments');
		$this->db->join('sales', 'sales.sale_id=sales_payments.sale_id');
		$this->db->where('sales.deleted', 0);
		$this->db->where('date(payment_date) BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']));
		
		//We only want returns, we don't want positive transactions
		$this->db->where('payment_amount < 0');
		$this->db->where('sales.suspended < 2');
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->where_in('sales.location_id', $location_ids);
		$this->sale_time_where();
		
		
		$this->db->order_by('sale_id, payment_date, payment_type');
		$sales_payments = $this->db->get()->result_array();
		
		$payments_by_sale = array();
		
		foreach($sales_payments as $row)
		{
        	$payments_by_sale[$row['sale_id']][] = $row;
		}
		
		$payment_data = array();
		
		foreach($payments_by_sale as $sale_id => $payment_rows)
		{
			if(isset($returns_total[$sale_id])){
				$total_sale_balance = $returns_total[$sale_id];
			}
			
			foreach($payment_rows as $row)
			{
				$payment_amount = $row['payment_amount'] <= $total_sale_balance ? $row['payment_amount'] : $total_sale_balance;
				
				if (!isset($payment_data[$row['payment_type']]))
				{
					$payment_data[$row['payment_type']] = array('payment_type' => $row['payment_type'], 'payment_amount' => 0 );
				}
				
				if ($total_sale_balance != 0)
				{
					$payment_data[$row['payment_type']]['payment_amount'] += $payment_amount;
				}
				
				$total_sale_balance-=$payment_amount;
			}
		}
				
		$data['returns_by_payments'] = $payment_data;		
		
		
		$this->db->select('categories.name as category,  sum('.$this->db->dbprefix('sales_items').'.total) as total', false);
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id','left');
		$this->db->join('items', 'sales_items.item_id = items.item_id', 'left');
		$this->db->join('categories', 'categories.id = items.category_id','left');
		$this->db->where('sales.suspended < 2');
		$this->sale_time_where();
		
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->where_in('sales.location_id', $location_ids);
		$this->db->where(''.$this->db->dbprefix('sales_items').'.total < 0');
		$this->db->group_start();
		$this->db->where('items.name !=', lang('common_discount'));
		$this->db->or_where('items.name IS NULL');
		$this->db->group_end();
		
		
		$this->db->group_by('category');
		//$this->db->order_by('category');
		$qry3=$this->db->get_compiled_select();
		
		
		$this->db->select('categories.name as category, sum('.$this->db->dbprefix('sales_item_kits').'.total) as total', false);
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id','left');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id', 'left');
		$this->db->join('categories', 'categories.id = item_kits.category_id','left');
		$this->db->where('sales.suspended < 2');
		$this->db->where('sales.deleted', 0);
		
		$this->sale_time_where();
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->where_in('sales.location_id', $location_ids);
		$this->db->where(''.$this->db->dbprefix('sales_item_kits').'.total < 0');
		$this->db->group_start();
		$this->db->where('item_kits.name !=', lang('common_discount'));
		$this->db->or_where('item_kits.name IS NULL');
		$this->db->group_end();
		
		$this->db->group_by('category');
		//$this->db->order_by('category');
		
		$qry4=$this->db->get_compiled_select();
		
			$query2 = $this->db->query("select category,sum(total) as total from (".$qry3." UNION ".$qry4. ") as alias  group by category order by category");
		
	
		$res2=$query2->result_array();
		
		
		
		$data['returns_by_category'] = $res2;
		
		foreach($data['returns_by_category'] as $returns_by_category)
		{
			$total+=$returns_by_category['total'];
		}
		
		
	
		$this->db->select('categories.name as category, sum('.$this->db->dbprefix('receivings_items').'.total) as total', false);
		$this->db->from('receivings_items');
		$this->db->join('receivings', 'receivings.receiving_id = receivings_items.receiving_id','left');
		$this->db->join('items', 'receivings_items.item_id = items.item_id', 'left');
		$this->db->join('categories', 'categories.id = items.category_id','left');
		
		$this->db->where('receivings.suspended < 2');
		$this->db->where('receivings.deleted', 0);
		
		$this->receiving_time_where();
		
		$this->db->where($this->db->dbprefix('receivings').'.deleted', 0);
		$this->db->where_in('receivings.location_id', $location_ids);
		$this->db->group_by('category');
		$this->db->order_by('category');

		$data['receivings_by_category'] = $this->db->get()->result_array();
		
		
		$this->db->select('SUM('.$this->db->dbprefix('sales_items').'.item_unit_price * '.$this->db->dbprefix('sales_items').'.quantity_purchased * ( '.$this->db->dbprefix('sales_items').'.discount_percent /100 )) as discount');
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id','left');
		$this->db->join('items', 'sales_items.item_id = items.item_id', 'left');
		$this->db->where('discount_percent > 0');
		$this->db->where('sales.suspended < 2');
		$this->db->where('sales.deleted', 0);
		
		$this->sale_time_where();
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->where_in('sales.location_id', $location_ids);
		
		$qry5=$this->db->get_compiled_select();
		
		$this->db->select('SUM('.$this->db->dbprefix('sales_item_kits').'.item_kit_unit_price * '.$this->db->dbprefix('sales_item_kits').'.quantity_purchased * ( '.$this->db->dbprefix('sales_item_kits').'.discount_percent /100 )) as discount');
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id','left');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id', 'left');
		$this->db->where('discount_percent > 0');
		$this->db->where('sales.suspended < 2');
		$this->db->where('sales.deleted', 0);
		$this->sale_time_where();
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->where_in('sales.location_id', $location_ids);
		
		$qry6=$this->db->get_compiled_select();
		
		$query3 = $this->db->query($qry5." UNION ".$qry6);
		$res3=$query3->result_array();
		
		$discountt_total=0;
		
		foreach($res3 as $discountt_items)
		{
			
			//print_r($commission_items);
			$discountt_total+=$discountt_items['discount'];
			
		}	
		
		
		$discount_array=array();
		$discount_array['discount']=$discountt_total;
		
		$data['discount_total'] = $discount_array;
		
		
		$this->db->select('SUM('.$this->db->dbprefix('sales_items').'.item_unit_price * '.$this->db->dbprefix('sales_items').'.quantity_purchased) as discount');
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id','left');
		$this->db->join('items', 'sales_items.item_id = items.item_id');
		$this->db->where('items.name', lang('common_discount'));
		$this->db->where('sales.suspended < 2');
		$this->db->where('sales.deleted', 0);
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
			$this->db->where_in('sales.location_id', $location_ids);
		$this->sale_time_where();
		
		$qry7=$this->db->get_compiled_select();
		
		$this->db->select('SUM('.$this->db->dbprefix('sales_item_kits').'.item_kit_unit_price * '.$this->db->dbprefix('sales_item_kits').'.quantity_purchased) as discount');
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id','left');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id', 'left');
		$this->db->where('item_kits.name', lang('common_discount'));
		$this->db->where('sales.suspended < 2');
		$this->db->where('sales.deleted', 0);
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
			$this->db->where_in('sales.location_id', $location_ids);
		$this->sale_time_where();

		$qry8=$this->db->get_compiled_select();
		
		$query4 = $this->db->query($qry7." UNION ".$qry8);
		
			
		if ($query4->num_rows() > 0)
		{
			$query_result = $query4->result();
			$discount = $query_result[0]->discount;
			
			$data['discount_total']['discount']+= -$discount;
		}
		
		$total-=$data['discount_total']['discount'];
		
				
		$this->db->select('sum(tax) as tax', false);
		$this->db->from('sales');
		
		$this->db->where('sales.suspended < 2');
		$this->db->where('deleted', 0);
		$this->db->where_in('sales.location_id', $location_ids);
		$this->sale_time_where();
		$data['taxes'] = $this->db->get()->row_array();
		
		$total-=$data['taxes']['tax'];
		$data['total'] = $total;
				
      $this->db->select('sum(expense_amount) as expense_amount', false);
		$this->db->from('expenses');
		$this->db->where('expenses.deleted', 0);
		$this->db->join('locations', 'locations.location_id = expenses.location_id');
		$this->db->where_in('expenses.location_id', $location_ids);
      $this->db->where('date(expense_date) BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']));
		$expenses_row = $this->db->get()->row_array();		
      $data['expense_amount'] = $expenses_row ['expense_amount'];
		
		
		$this->db->select('sum('.$this->db->dbprefix('sales_items').'.profit) as profit, SUM('.$this->db->dbprefix('sales_items').'.commission) as commission', false);
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id');
		
		$this->db->where('deleted', 0);
		$this->db->where('sales.suspended < 2');
			$this->db->where_in('sales.location_id', $location_ids);
		$this->sale_time_where();
		
		$qry9=$this->db->get_compiled_select();
		
		$this->db->select('sum('.$this->db->dbprefix('sales_item_kits').'.profit) as profit, SUM('.$this->db->dbprefix('sales_item_kits').'.commission) as commission', false);
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id');
		$this->db->where('deleted', 0);
		$this->db->where('sales.suspended < 2');
			$this->db->where_in('sales.location_id', $location_ids);
		$this->sale_time_where();
		
		
		$qry10=$this->db->get_compiled_select();
		
		$query4 = $this->db->query($qry9." UNION ".$qry10);
		$res4=$query4->result_array();
		
		$profit_row = $res4;
		
		$data['commissions'] = $res4;
		$data['profits'] = $res4;
		//print_r($data['commissions']); exit;
		
		$profit_total=0;
		$commission_total = 0;
		foreach($data['commissions'] as $commission_items)
		{
			
			//print_r($commission_items);
			$commission_total+=$commission_items['commission'];
			
		}	
		foreach($data['profits'] as $profit_items)
		{
			
			//print_r($commission_items);
			$profit_total+=$profit_items['profit'];
			
		}	
		$data['commission'] = $commission_total;
		
		$data['profit'] = $profit_total - $data['expense_amount'] - $commission_total;;
       
		return $data;
	}
	
	public function getSummaryData()
	{
		return array();
	}
}
?>