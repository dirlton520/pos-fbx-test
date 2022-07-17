<?php
require_once ("Report.php");
class Summary_store_accounts_supplier extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array(array('data'=>lang('reports_supplier'), 'align'=> 'left'), array('data'=>lang('common_balance'), 'align'=> 'right'), array('data'=>lang('common_pay'), 'align'=> 'right'));
	}
	
	public function getInputData()
	{
		return array(
		'input_params' => array(
				array('view' => 'date'),
				array('view' => 'excel_export'),
				array('view' => 'submit'),
			),
			'input_report_title' => lang('reports_report_input')
		);
	}
	
	public function getOutputData()
	{
		$this->setupDefaultPagination();
		
		$tabular_data = array();
		$report_data = $this->getData();
		foreach($report_data as $row)
		{
			$tabular_data[] = array(array('data'=>$row['supplier'], 'align'=> 'left'), array('data'=>to_currency($row['balance']), 'align'=> 'right'), array('data'=>anchor("suppliers/pay_now/".$row['person_id'],lang('common_pay'),array('title'=>lang('common_update'),'class'=>'btn btn-info')), 'align'=> 'right'));
		}

		$data = array(
			"view" => 'tabular',
			"title" => lang('reports_store_account_summary_report'),
			"subtitle" => '',
			"headers" => $this->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => $this->getSummaryData(),
			"export_excel" => $this->params['export_excel'],
			'pagination' => $this->pagination->create_links()
		);

		return $data;
	}
	
	public function getData()
	{
		$date = $this->params['date'];
		$lookup_balance_in_past = $date != date('Y-m-d');
		
		if (!$lookup_balance_in_past)
		{		
			$this->db->select('CONCAT(company_name," (",first_name, " ",last_name, ")") as supplier, balance, suppliers.person_id', false);
			$this->db->from('suppliers');
			$this->db->join('people', 'suppliers.person_id = people.person_id');
			$this->db->where('balance != 0');
			$this->db->where('deleted',0);		
		}
		else
		{
			
			$this->db->select('CONCAT(company_name," (",first_name, " ",last_name, ")") as supplier, outersa.balance as balance, suppliers.person_id', false);
			$this->db->from('supplier_store_accounts as outersa');
			$this->db->join('suppliers', 'suppliers.person_id = outersa.supplier_id');
			$this->db->join('people', 'suppliers.person_id = people.person_id');
			$this->db->where("date = (SELECT MAX(date) FROM ".$this->db->dbprefix('supplier_store_accounts')." as innersa WHERE innersa.supplier_id=outersa.supplier_id and date < '$date 23:59:59' )");	
			$this->db->where('outersa.balance != 0');
			$this->db->where('deleted',0);
			
		}
		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			$this->db->offset(isset($this->params['offset']) ? $this->params['offset'] : 0);
		}
		
		return $this->db->get()->result_array();		
	}
	
	
	public function getTotalRows()
	{
		$this->db->select('CONCAT(company_name," (",first_name, " ",last_name, ")") as supplier, balance, suppliers.person_id', false);
		$this->db->from('suppliers');
		$this->db->join('people', 'suppliers.person_id = people.person_id');
		$this->db->where('balance != 0');
		$this->db->where('deleted',0);
			
		return $this->db->count_all_results();
	}
	
	public function getSummaryData()
	{
		$date = $this->params['date'];
		
		$lookup_balance_in_past = $date != date('Y-m-d');
		
		if (!$lookup_balance_in_past)
		{		
			$this->db->select('SUM(balance) as total', false);
			$this->db->from('suppliers');
			$this->db->where('balance != 0');
			$this->db->where('deleted',0);		
		}
		else
		{
			$this->db->select('SUM(outersa.balance) as total', false);
			$this->db->from('supplier_store_accounts as outersa');
			$this->db->join('suppliers', 'suppliers.person_id = outersa.supplier_id');
			$this->db->join('people', 'suppliers.person_id = people.person_id');
			$this->db->where("date = (SELECT MAX(date) FROM ".$this->db->dbprefix('supplier_store_accounts')." as innersa WHERE innersa.supplier_id=outersa.supplier_id and date < '$date 23:59:59' )");	
			$this->db->where('outersa.balance != 0');
			$this->db->where('deleted',0);
		}		
		return $this->db->get()->row_array();		
	}
}
?>