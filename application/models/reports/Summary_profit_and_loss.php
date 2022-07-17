<?php
require_once ("Report.php");
class Summary_profit_and_loss extends Report
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
			"view" => 'profit_and_loss_summary',
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
		
		$this->db->select(' sum('.$this->db->dbprefix('sales').'.total) as total', false);
		$this->db->from('sales');
		
		$this->db->where('sales.deleted',0);
		$this->db->where('sales.suspended < 2');
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		
		$this->db->where(''.$this->db->dbprefix('sales').'.total > 0');
		
		$this->db->where_in('sales.location_id', $location_ids);
		$this->sale_time_where();
		
		$qry1=$this->db->get_compiled_select();
		
		
		$query = $this->db->query($qry1);
		$res5=$query->result_array();
		
		$data['sales_by_category'] = $res5;
		
		$sales_total = 0;
		foreach($data['sales_by_category'] as $sales_by_category)
		{
			$sales_total+=$sales_by_category['total'];
		}	
		$total+=$sales_total;
		$data['sales_total'] = $sales_total;

		$this->db->select('categories.name as category, sum('.$this->db->dbprefix('sales_items').'.total) as total', false);
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id','left');
		$this->db->join('items', 'sales_items.item_id = items.item_id', 'left');
		$this->db->join('categories', 'categories.id = items.category_id','left');
		$this->db->where('sales.suspended < 2');
		$this->db->where('sales.deleted',0);
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		
		$this->db->where(''.$this->db->dbprefix('sales_items').'.total < 0');
		$this->db->group_start();
		$this->db->where('items.name !=', lang('common_discount'));
		$this->db->or_where('items.name IS NULL');
		$this->db->where_in('sales.location_id', $location_ids);
		$this->db->group_end();
		$this->sale_time_where();
		
		$qry3=$this->db->get_compiled_select();
		
		$this->db->select('categories.name as category, sum('.$this->db->dbprefix('sales_item_kits').'.total) as total', false);
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id','left');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id', 'left');
		$this->db->join('categories', 'categories.id = item_kits.category_id','left');
		$this->db->where('sales.suspended < 2');
		$this->db->where('sales.deleted',0);
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->where(''.$this->db->dbprefix('sales_item_kits').'.total < 0');
		$this->db->group_start();
		$this->db->where('item_kits.name !=', lang('common_discount'));
		$this->db->or_where('item_kits.name IS NULL');
		$this->db->where_in('sales.location_id', $location_ids);
		$this->db->group_end();
		$this->sale_time_where();
		
		
		$this->db->group_by('category');
		//$this->db->order_by('category');
		
		$qry4=$this->db->get_compiled_select();
		
		$query1 = $this->db->query($qry3." UNION ".$qry4. "order by category desc");
		$res1=$query1->result_array();
	
		$data['returns_by_category'] = $res1;
		
		
		
		$returns_total = 0;
		foreach($data['returns_by_category'] as $returns_by_category)
		{
			$returns_total+=$returns_by_category['total'];
		}

		$total+=$returns_total;
		$data['returns_total'] = $returns_total;
		
		
		$this->db->select('categories.name as category, sum('.$this->db->dbprefix('receivings_items').'.total) as total', false);
		$this->db->from('receivings_items');
		
		$this->db->join('receivings', 'receivings.receiving_id = receivings_items.receiving_id','left');
		$this->db->join('items', 'receivings_items.item_id = items.item_id', 'left');
		$this->db->join('categories', 'categories.id = items.category_id','left');
		
		$this->db->where('receivings.suspended < 2');
		$this->db->where('receivings.deleted',0);
		$this->db->where($this->db->dbprefix('receivings').'.deleted', 0);
			$this->db->where_in('receivings.location_id', $location_ids);
		$this->receiving_time_where();
		$receivings_row = $this->db->get()->row_array();
		$data['receivings_total'] = $receivings_row['total'];
		
		
		$this->db->select('SUM('.$this->db->dbprefix('sales_items').'.item_unit_price * '.$this->db->dbprefix('sales_items').'.quantity_purchased * ( '.$this->db->dbprefix('sales_items').'.discount_percent /100 )) as discount');
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id','left');
		$this->db->join('items', 'sales_items.item_id = items.item_id', 'left');
		$this->db->where('discount_percent > 0');
		$this->db->where('sales.suspended < 2');
		$this->db->where('sales.deleted',0);
		
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
			$this->db->where_in('sales.location_id', $location_ids);
		$this->sale_time_where();
		
		$qry5=$this->db->get_compiled_select();
		
		$this->db->select('SUM('.$this->db->dbprefix('sales_item_kits').'.item_kit_unit_price * '.$this->db->dbprefix('sales_item_kits').'.quantity_purchased * ( '.$this->db->dbprefix('sales_item_kits').'.discount_percent /100 )) as discount');
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id','left');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id', 'left');
		$this->db->where('discount_percent > 0');
		$this->db->where('sales.suspended < 2');
		$this->db->where('sales.deleted',0);
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
			$this->db->where_in('sales.location_id', $location_ids);
		$this->sale_time_where();
		
		$qry6=$this->db->get_compiled_select();
		
		$query2 = $this->db->query($qry5." UNION ".$qry6);
		$res2=$query2->result_array();
		
		$discountt_total=0;
		foreach($res2 as $discountt_items)
		{			
			$discountt_total+=$discountt_items['discount'];
			
		}					
		$data['discount_total'] = $discountt_total;
		
		$this->db->select('SUM('.$this->db->dbprefix('sales_items').'.item_unit_price * '.$this->db->dbprefix('sales_items').'.quantity_purchased) as discount');
		$this->db->from('sales_items');
		$this->db->join('sales', 'sales.sale_id = sales_items.sale_id','left');
		$this->db->join('items', 'sales_items.item_id = items.item_id');
		$this->db->where('items.name', lang('common_discount'));
		$this->db->where('sales.suspended < 2');
		$this->db->where('sales.deleted',0);
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
		$this->db->where_in('sales.location_id', $location_ids);
		$this->sale_time_where();
		
		$qry7=$this->db->get_compiled_select();
		
		$this->db->select('SUM('.$this->db->dbprefix('sales_item_kits').'.item_kit_unit_price * '.$this->db->dbprefix('sales_item_kits').'.quantity_purchased) as discount');
		$this->db->from('sales_item_kits');
		$this->db->join('sales', 'sales.sale_id = sales_item_kits.sale_id','left');
		$this->db->where('sales.deleted',0);
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id', 'left');
		$this->db->where('item_kits.name', lang('common_discount'));
		$this->db->where('sales.suspended < 2');
		$this->db->where($this->db->dbprefix('sales').'.deleted', 0);
			$this->db->where_in('sales.location_id', $location_ids);
		$this->sale_time_where();

		$qry8=$this->db->get_compiled_select();
		
		$query3 = $this->db->query($qry7." UNION ".$qry8);		
			
		if ($query3->num_rows() > 0)
		{
			$query_result = $query3->result();

			$discount = $query_result[0]->discount;
			
			$data['discount_total']+= -$discount;
		}
		
		$total-=$data['discount_total'];
		
		$this->db->select('sum(tax) as tax', false);
		$this->db->from('sales');
		$this->db->where('sales.deleted',0);
		$this->db->where('sales.suspended < 2');
		$this->db->where('deleted', 0);
		$this->db->where_in('sales.location_id', $location_ids);
		$this->sale_time_where();
		$tax_row = $this->db->get()->row_array();
		$data['taxes_total'] = $tax_row['tax'];
		
		$total-=$tax_row['tax'];
		$data['total'] = $total;
		
		$this->db->select('sum(expense_amount) as expense_amount', false);
		$this->db->from('expenses');
		$this->db->join('locations', 'locations.location_id = expenses.location_id');
		$this->db->where_in('expenses.location_id', $location_ids);
		
		$this->db->where('expenses.deleted', 0);
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
		
		$profit_total=0;
		$commission_total = 0;
		foreach($data['commissions'] as $commission_items)
		{
			
			$commission_total+=$commission_items['commission'];
			
		}	
		foreach($data['profits'] as $profit_items)
		{
			
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