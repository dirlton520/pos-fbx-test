<?php
require_once ("Report.php");
class Store_account_outstanding extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{		
		
		$return = array(
			array('data'=>lang('common_sale_id'), 'align'=>'left'), 
			array('data'=>lang('common_customer_name'), 'align'=>'left'),
			array('data'=>lang('common_date'), 'align'=> 'left'), 
			array('data'=>lang('common_total_charge_to_account'), 'align'=> 'left'), 
			array('data'=>lang('common_comment'), 'align'=> 'left'),
			array('data'=>lang('reports_mark_as_paid').'/'.lang('reports_mark_as_unpaid'), 'align'=> 'left'),
		);
		
		$location_count = count($this->Location->get_all()->result_array());
		
		if ($location_count > 1)
		{
			array_unshift($return,array('data'=>lang('common_location'), 'align'=> 'left'));
		}
		
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
			
			$input_params[] = $specific_entity_data;
			$input_params[] = array('view' => 'checkbox','checkbox_label' =>lang('reports_show_paid_sales'),'checkbox_name' => 'show_paid');
			$input_params[] = array('view' => 'excel_export');
			$input_params[] = array('view' => 'submit');
		}
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}
	
	public function getOutputData()
	{
		$this->setupDefaultPagination();
		$tabular_data = array();
		$report_data = $this->getData();
		$customer_id = $this->params['customer_id'];
		$show_paid = isset($this->params['show_paid']) && $this->params['show_paid'] ? 1 : 0;
		$offset = isset($this->params['offset']) ? $this->params['offset'] : 0;
		$export_excel = isset($this->params['export_excel']) &&  $this->params['export_excel'] ? 1 : 0;
		$location_count = count($this->Location->get_all()->result_array());
		
		
		foreach($report_data as $row)
		{			
			
			if ($row['paid'])
			{
				$mark_paid_unpaid=anchor('reports/store_account_outstanding_mark_as_unpaid/?sale_id='.$row['sale_id'], lang('reports_mark_as_unpaid'),"onclick='return do_link_confirm(".json_encode(lang('reports_confirm_mark_as_unpaid')).", this)' class='btn btn-success'");
			}
			else
			{
				$mark_paid_unpaid=anchor('reports/store_account_outstanding_mark_as_paid/?sale_id='.$row['sale_id'], lang('reports_mark_as_paid'),"onclick='return do_link_confirm(".json_encode(lang('reports_confirm_mark_as_paid')).", this)' class='btn btn-danger'");
			}
			
			
			$tab_row = array(
				array('data'=>anchor('sales/receipt/'.$row['sale_id'], ($this->config->item('sale_prefix') ? $this->config->item('sale_prefix') : 'POS').' '.$row['sale_id'], array('target' => '_blank')), 'align'=> 'left'),
				array('data'=>$row['customer_name'].(isset($row['account_number']) && $row['account_number'] ? ' ('.$row['account_number'].')' : ''), 'align'=> 'left'),
				array('data'=>date(get_date_format().' '.get_time_format(), strtotime($row['sale_time'])), 'align'=> 'left'),
				array('data'=>to_currency($row['payment_amount']), 'align'=> 'left'),
			 	array('data'=>$row['comment'], 'align'=> 'left'),
				array('data'=>$mark_paid_unpaid, 'align'=> 'center')
			);
			
			if ($location_count > 1)
			{
				array_unshift($tab_row,array('data'=>$row['location'], 'align'=> 'left'));
				
			}
			
			$tabular_data[] = $tab_row;
		}

		$mark_all_paid=anchor('reports/store_account_outstanding_mark_all_as_paid/?customer_id='.$customer_id, lang('reports_mark_all_as_paid'), 
		"onclick='return do_link_confirm(".json_encode(lang('reports_confirm_all_mark_as_paid')).", this)'");

		$data = array(
			"view" => "tabular",
			"title" => lang('reports_outstanding_sales_report'),
			"subtitle" => $mark_all_paid,
			"headers" => $this->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $this->getSummaryData(),
			"export_excel" => $export_excel,
			"pagination" => $this->pagination->create_links(),
		);

		return $data;
	}
	
	public function getData()
	{
		$this->db->select('locations.name as location,CONCAT(customer_person_info.first_name," ",customer_person_info.last_name) as customer_name, customers.account_number, sales.sale_id, sale_time,SUM(transaction_amount) as payment_amount,sales.comment', false);
		$this->db->from('store_accounts');
		$this->db->join('sales','sales.sale_id = store_accounts.sale_id');
		$this->db->join('locations', 'sales.location_id = locations.location_id', 'left');
		$this->db->join('sales_payments', 'sales.sale_id = sales_payments.sale_id');
		$this->db->join('people as customer_person_info', 'sales.customer_id = customer_person_info.person_id');
		$this->db->join('customers', 'sales.customer_id = customers.person_id');
		$store_account_in_all_languages = get_all_language_values_for_key('common_store_account','common');
		$this->db->where_in('sales_payments.payment_type', $store_account_in_all_languages);
		$this->db->group_by('sales.sale_id');
		
		if ($this->params['customer_id'])
		{
			$this->db->where('store_accounts.customer_id',$this->params['customer_id']);
		}
		if (!isset($this->params['show_paid']) || !$this->params['show_paid'])
		{
			$this->db->where($this->db->dbprefix('store_accounts').'.sale_id NOT IN (SELECT '.$this->db->dbprefix('store_accounts_paid_sales').'.sale_id FROM '.$this->db->dbprefix('store_accounts_paid_sales').' WHERE partial_payment_amount=0 and '.$this->db->dbprefix('store_accounts_paid_sales').'.sale_id is NOT NULL)');
		}
		$this->db->order_by('date',($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');


		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			
			if (isset($this->params['offset']))
			{
				$this->db->offset($this->params['offset']);
		
			}
		}
		
		$return = $this->db->get()->result_array();
		
		for($k=0;$k<count($return);$k++)
		{
			$this->db->from('store_accounts_paid_sales');
			$this->db->where('sale_id', $return[$k]['sale_id']);
			$query = $this->db->get();
			$row = $query->row_array();
			$paid = ($query->num_rows()>=1);
			
			if ($paid && $row['partial_payment_amount'] == 0)
			{
				$return[$k]['paid'] = TRUE;
			}
			else
			{
				$return[$k]['paid'] = FALSE;
			}
		}
		
		return $return;
	}
	
	public function getSummaryData()
	{
		$this->db->select('SUM(transaction_amount) as total');
		$this->db->from('store_accounts');
		$this->db->join('sales','sales.sale_id = store_accounts.sale_id');
		$this->db->join('sales_payments', 'sales.sale_id = sales_payments.sale_id');
		$store_account_in_all_languages = get_all_language_values_for_key('common_store_account','common');
		$this->db->where_in('sales_payments.payment_type', $store_account_in_all_languages);
		
		if ($this->params['customer_id'])
		{
			$this->db->where('store_accounts.customer_id',$this->params['customer_id']);
		}
		
		if (!isset($this->params['show_paid']) || !$this->params['show_paid'])
		{
			$this->db->where($this->db->dbprefix('store_accounts').'.sale_id NOT IN (SELECT '.$this->db->dbprefix('store_accounts_paid_sales').'.sale_id FROM '.$this->db->dbprefix('store_accounts_paid_sales').' WHERE partial_payment_amount=0 and '.$this->db->dbprefix('store_accounts_paid_sales').'.sale_id is NOT NULL)');
		}
		return $this->db->get()->row_array();		
	}
	
	function getTotalRows()
	{
		$this->db->select('CONCAT(customer_person_info.first_name," ",customer_person_info.last_name) as customer_name, customers.account_number, sales.sale_id, sale_time,SUM(transaction_amount) as payment_amount,sales.comment', false);
		$this->db->from('store_accounts');
		$this->db->join('sales','sales.sale_id = store_accounts.sale_id');
		$this->db->join('sales_payments', 'sales.sale_id = sales_payments.sale_id');
		$this->db->join('people as customer_person_info', 'sales.customer_id = customer_person_info.person_id');
		$this->db->join('customers', 'sales.customer_id = customers.person_id');
		$store_account_in_all_languages = get_all_language_values_for_key('common_store_account','common');
		$this->db->where_in('sales_payments.payment_type', $store_account_in_all_languages);
		$this->db->group_by('sales.sale_id');
		
		if ($this->params['customer_id'])
		{
			$this->db->where('store_accounts.customer_id',$this->params['customer_id']);
		}
		if (!isset($this->params['show_paid']) || !$this->params['show_paid'])
		{
			$this->db->where($this->db->dbprefix('store_accounts').'.sale_id NOT IN (SELECT '.$this->db->dbprefix('store_accounts_paid_sales').'.sale_id FROM '.$this->db->dbprefix('store_accounts_paid_sales').' WHERE partial_payment_amount=0 and '.$this->db->dbprefix('store_accounts_paid_sales').'.sale_id is NOT NULL)');
		}
		return $this->db->count_all_results();
	}
	
}
?>