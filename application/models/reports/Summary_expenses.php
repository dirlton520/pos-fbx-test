<?php
require_once ("Report.php");
class Summary_expenses extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
			
		return array(
		array('data'=>lang('common_category'), 'align'=> 'left')	
			, array('data'=>lang('common_tax'), 'align'=> 'left')
			, array('data'=>lang('common_amount'), 'align'=> 'left')
		);
	}
	
	public function getInputData()
	{
		
		$input_params = array();

		if ($this->settings['display'] == 'tabular')
		{
			$input_data = Report::get_common_report_input_data(FALSE);
			
			$input_params = array(
				array('view' => 'date_range', 'with_time' => FALSE),
				array('view' => 'excel_export'),
				array('view' => 'locations'),
				array('view' => 'submit'),
			);
		}
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}
	
	public function getOutputData()
	{
		$this->setupDefaultPagination();
		$this->load->model('Category');
		$tabular_data = array();
		$report_data = $this->getData();
		foreach($report_data as $row)
		{
			$tabular_data[] = array(
			array('data'=>$this->Expense_category->get_full_path($row['category_id']), 'align'=> 'left'), 
			array('data'=>  to_currency($row['expense_tax']), 'align'=> 'left'), 
			array('data'=>  to_currency($row['expense_amount']), 'align'=> 'left'), 
		);
		}
		$data = array(
		"view" => 'tabular',
		"title" => lang('reports_expenses_summary_report'),
		"subtitle" => date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date'])),
		"headers" => $this->getDataColumns(),
		"data" => $tabular_data,
		"summary_data" => $this->getSummaryData(),
		"export_excel" => $this->params['export_excel'],
		"pagination" => $this->pagination->create_links(),
		);
       
		return $data;
	}
	
	
	public function getData()
	{		
		$location_ids = self::get_selected_location_ids();
		
		$this->db->select('expenses_categories.id as category_id,expenses_categories.name as category, SUM(expense_amount) as expense_amount,SUM(expense_tax) as expense_tax', false);
		$this->db->from('expenses');
		$this->db->join('expenses_categories', 'expenses_categories.id = expenses.category_id','left');
		$this->db->where_in('expenses.location_id', $location_ids);
		$this->db->where('expenses.deleted', 0);
		$this->db->group_by('expenses_categories.id');
		if (isset($this->params['start_date']) && isset($this->params['end_date']))
		{
 		  $this->db->where($this->db->dbprefix('expenses').'.expense_date BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']));
		}
		$this->db->order_by('expenses.id');
		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			if (isset($this->params['offset']))
			{
				$this->db->offset($this->params['offset']);
			}
		}
		return $this->db->get()->result_array();		
	}
	
	public function getSummaryData()
	{
		$location_ids = self::get_selected_location_ids();
		$this->db->select('SUM(expense_amount) as total_expenses,SUM(expense_tax) as total_taxes', false);
		$this->db->from('expenses');
		$this->db->where('deleted', 0);
		if (isset($this->params['start_date']) && isset($this->params['end_date']))
		{
 		  $this->db->where($this->db->dbprefix('expenses').'.expense_date BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']));
		}
		$this->db->where_in('expenses.location_id', $location_ids);
		return $this->db->get()->row_array();		
	}
	
	function getTotalRows()
	{
		$location_ids = self::get_selected_location_ids();
		$this->db->select('expenses_categories.id as category_id,expenses_categories.name as category, SUM(expense_amount) as expense_amount,SUM(expense_tax) as expense_tax', false);
		$this->db->from('expenses');
		$this->db->join('expenses_categories', 'expenses_categories.id = expenses.category_id','left');
		$this->db->where_in('expenses.location_id', $location_ids);
		$this->db->where('expenses.deleted', 0);
		$this->db->group_by('expenses_categories.id');
		if (isset($this->params['start_date']) && isset($this->params['end_date']))
		{
 		  $this->db->where($this->db->dbprefix('expenses').'.expense_date BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']));
		}
		
		return $this->db->count_all_results();
	}
	
}
?>