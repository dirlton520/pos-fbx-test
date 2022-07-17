<?php
require_once ("Report.php");
class Summary_new_customers extends Report
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Tier');
		
	}
	
	public function getInputData()
	{
		
		$input_params = array();
			
		if ($this->settings['display'] == 'tabular')
		{
			$input_data = Report::get_common_report_input_data(TRUE);
			
			$input_params = array(
				array('view' => 'date_range', 'with_time' => TRUE),
				array('view' => 'date_range', 'with_time' => TRUE, 'compare_to' => TRUE),
				array('view' => 'excel_export'),
				array('view' => 'submit'),
			);
		}
		
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}
	
	function getOutputData()
	{
		$do_compare = isset($this->params['compare_to']) && $this->params['compare_to'];		
		$subtitle = date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date'])).($do_compare  ? ' '. lang('reports_compare_to'). ' '. date(get_date_format(), strtotime($this->params['start_date_compare'])) .'-'.date(get_date_format(), strtotime($this->params['end_date_compare'])) : '');

		$report_data = $this->getData();
		
		if ($this->settings['display'] == 'tabular')
		{				
			$tabular_data = array();
			
			if ($do_compare)
			{
				$report_data_compare_model = new Summary_new_customers();
				$report_data_compare_model->report_key = $this->report_key;
				$report_data_compare_model->setSettings($this->settings);
				$report_data_compare_model->setParams(array_merge($this->params,array('start_date'=>$this->params['start_date_compare'], 'end_date'=>$this->params['end_date_compare'])));

				$report_data_compare = $report_data_compare_model->getData();
			}

			$index = 0;
			foreach($report_data as $row)
			{
				$data_row = array();
				if ($do_compare)
				{
					if (isset($report_data_compare[$index]))
					{
						$row_compare = $report_data_compare[$index];
					}
					else
					{
						$row_compare = FALSE;
					}
				}
			
				$data_row[] = array('data'=>to_quantity($row['new_customers']).($do_compare && $row_compare ? ' / <span class="compare '.($row_compare['new_customers'] >= $row['new_customers'] ? ($row['new_customers'] == $row_compare['new_customers'] ?  '' : 'compare_better') : 'compare_worse').'">'.to_quantity($row_compare['new_customers']) .'</span>':''), 'align'=>'center');
			
				$tabular_data[] = $data_row;
			
				$index++;
			}
		

			
	 		$data = array(
				'view' => 'tabular',
				"title" => lang('reports_new_customers_report'),
				"subtitle" => $subtitle,
				"headers" => $this->getDataColumns(),
				"data" => $tabular_data,
				"summary_data" => $this->getSummaryData(),
				"export_excel" => $this->params['export_excel'],
				"pagination" => ''
			);
			
		}
		
		return $data;
	}
	
	public function getDataColumns()
	{
		$columns = array();
		
		$columns[] = array('data'=>lang('reports_new_customers'), 'align'=> 'center');

		
		return $columns;		
	}
	
	public function getData()
	{		
		$location_ids = self::get_selected_location_ids();
		$this->db->select('count(*) as new_customers', false);
		$this->db->from('customers');
		$this->db->join('people', 'people.person_id = customers.person_id');
		$where = 'create_date BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']);
		$this->db->where($where);
		return $this->db->get()->result_array();
	}
	
	
	function getTotalRows()
	{		
		return 1;
	}
	
	public function getSummaryData()
	{
		return array();
	}

}
?>