<?php
require_once ("Report.php");
class Store_account_outstanding_supplier extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{		
		
		$return  = array(
			array('data'=>lang('reports_receiving_id'), 'align'=>'left'), 
			array('data'=>lang('reports_supplier'), 'align'=>'left'),
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
		$specific_entity_data['specific_input_name'] = 'supplier_id';
		$specific_entity_data['specific_input_label'] = lang('reports_supplier');
		$specific_entity_data['search_suggestion_url'] = site_url('reports/supplier_search/1');
		$specific_entity_data['view'] = 'specific_entity';
		
		
		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array();
			
			$input_params[] = $specific_entity_data;
			$input_params[] = array('view' => 'checkbox','checkbox_label' =>lang('reports_show_paid_receivings'),'checkbox_name' => 'show_paid');
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
		$supplier_id = $this->params['supplier_id'];
		$show_paid = isset($this->params['show_paid']) && $this->params['show_paid'] ? 1 : 0;
		$offset = isset($this->params['offset']) ? $this->params['offset'] : 0;
		$export_excel = isset($this->params['export_excel']) &&  $this->params['export_excel'] ? 1 : 0;
		$location_count = count($this->Location->get_all()->result_array());
		
		foreach($report_data as $row)
		{			
			
			if ($row['paid'])
			{
				$mark_paid_unpaid=anchor('reports/supplier_store_account_outstanding_mark_as_unpaid/?receiving_id='.$row['receiving_id'], lang('reports_mark_as_unpaid'),"onclick='return do_link_confirm(".json_encode(lang('reports_confirm_mark_as_unpaid')).", this)' class='btn btn-success'");
			}
			else
			{
				$mark_paid_unpaid=anchor('reports/supplier_store_account_outstanding_mark_as_paid/?receiving_id='.$row['receiving_id'], lang('reports_mark_as_paid'),"onclick='return do_link_confirm(".json_encode(lang('reports_confirm_mark_as_paid')).", this)' class='btn btn-danger'");
			}
			
			
			$tab_row = array(
				array('data'=>anchor('receivings/receipt/'.$row['receiving_id'], ($this->config->item('receiving_prefix') ? $this->config->item('receiving_prefix') : 'POS').' '.$row['receiving_id'], array('target' => '_blank')), 'align'=> 'left'),
				array('data'=>$row['supplier_name'].(isset($row['account_number']) && $row['account_number'] ? ' ('.$row['account_number'].')' : ''), 'align'=> 'left'),
				array('data'=>date(get_date_format().' '.get_time_format(), strtotime($row['receiving_time'])), 'align'=> 'left'),
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

		$mark_all_paid=anchor('reports/supplier_store_account_outstanding_mark_all_as_paid/?supplier_id='.$supplier_id, lang('reports_mark_all_as_paid'), 
		"onclick='return do_link_confirm(".json_encode(lang('reports_confirm_all_mark_as_paid')).", this)'");

		$data = array(
			"view" => "tabular",
			"title" => lang('reports_outstanding_receivings_report'),
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
		$this->db->select('locations.name as location,CONCAT('.$this->db->dbprefix('suppliers').'.company_name, " "," (",supplier_person_info.first_name," ",supplier_person_info.last_name,")") as supplier_name, suppliers.account_number, receivings.receiving_id, receiving_time,SUM(transaction_amount) as payment_amount,receivings.comment', false);
		$this->db->from('supplier_store_accounts');
		$this->db->join('receivings','receivings.receiving_id = supplier_store_accounts.receiving_id');
		$this->db->join('locations', 'receivings.location_id = locations.location_id', 'left');
		$this->db->join('receivings_payments', 'receivings.receiving_id = receivings_payments.receiving_id');
		$this->db->join('people as supplier_person_info', 'receivings.supplier_id = supplier_person_info.person_id');
		$this->db->join('suppliers', 'receivings.supplier_id = suppliers.person_id');
		$store_account_in_all_languages = get_all_language_values_for_key('common_store_account','common');
		$this->db->where_in('receivings_payments.payment_type', $store_account_in_all_languages);
		$this->db->group_by('receivings.receiving_id');
		
		if ($this->params['supplier_id'])
		{
			$this->db->where('supplier_store_accounts.supplier_id',$this->params['supplier_id']);
		}
		if (!isset($this->params['show_paid']) || !$this->params['show_paid'])
		{
			$this->db->where($this->db->dbprefix('supplier_store_accounts').'.receiving_id NOT IN (SELECT '.$this->db->dbprefix('supplier_store_accounts_paid_receivings').'.receiving_id FROM '.$this->db->dbprefix('supplier_store_accounts_paid_receivings').' WHERE partial_payment_amount=0 and '.$this->db->dbprefix('supplier_store_accounts_paid_receivings').'.receiving_id is NOT NULL)');
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
			$this->db->from('supplier_store_accounts_paid_receivings');
			$this->db->where('receiving_id', $return[$k]['receiving_id']);
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
		$this->db->from('supplier_store_accounts');
		$this->db->join('receivings','receivings.receiving_id = supplier_store_accounts.receiving_id');
		$this->db->join('receivings_payments', 'receivings.receiving_id = receivings_payments.receiving_id');
		$store_account_in_all_languages = get_all_language_values_for_key('common_store_account','common');
		$this->db->where_in('receivings_payments.payment_type', $store_account_in_all_languages);
		
		if ($this->params['supplier_id'])
		{
			$this->db->where('supplier_store_accounts.supplier_id',$this->params['supplier_id']);
		}
		
		if (!isset($this->params['show_paid']) || !$this->params['show_paid'])
		{
			$this->db->where($this->db->dbprefix('supplier_store_accounts').'.receiving_id NOT IN (SELECT '.$this->db->dbprefix('supplier_store_accounts_paid_receivings').'.receiving_id FROM '.$this->db->dbprefix('supplier_store_accounts_paid_receivings').' WHERE partial_payment_amount=0 and '.$this->db->dbprefix('supplier_store_accounts_paid_receivings').'.receiving_id is NOT NULL)');
		}
		return $this->db->get()->row_array();		
	}
	
	function getTotalRows()
	{
		$this->db->select('CONCAT(supplier_person_info.first_name," ",supplier_person_info.last_name) as supplier_name, suppliers.account_number, receivings.receiving_id, receiving_time,SUM(transaction_amount) as payment_amount,receivings.comment', false);
		$this->db->from('supplier_store_accounts');
		$this->db->join('receivings','receivings.receiving_id = supplier_store_accounts.receiving_id');
		$this->db->join('receivings_payments', 'receivings.receiving_id = receivings_payments.receiving_id');
		$this->db->join('people as supplier_person_info', 'receivings.supplier_id = supplier_person_info.person_id');
		$this->db->join('suppliers', 'receivings.supplier_id = suppliers.person_id');
		$store_account_in_all_languages = get_all_language_values_for_key('common_store_account','common');
		$this->db->where_in('receivings_payments.payment_type', $store_account_in_all_languages);
		$this->db->group_by('receivings.receiving_id');
		
		if ($this->params['supplier_id'])
		{
			$this->db->where('supplier_store_accounts.supplier_id',$this->params['supplier_id']);
		}
		if (!isset($this->params['show_paid']) || !$this->params['show_paid'])
		{
			$this->db->where($this->db->dbprefix('supplier_store_accounts').'.receiving_id NOT IN (SELECT '.$this->db->dbprefix('supplier_store_accounts_paid_receivings').'.receiving_id FROM '.$this->db->dbprefix('supplier_store_accounts_paid_receivings').' WHERE partial_payment_amount=0 and '.$this->db->dbprefix('supplier_store_accounts_paid_receivings').'.receiving_id is NOT NULL)');
		}
		return $this->db->count_all_results();
	}
	
}
?>