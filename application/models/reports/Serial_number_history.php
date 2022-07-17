<?php
require_once ("Report.php");
class Serial_number_history extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getInputData()
	{
		$input_data = Report::get_common_report_input_data(TRUE);
		
		$input_params = array();

		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array(
				array('view' => 'text','default' => '','name' => 'serial_number','label' =>lang('common_serial_number')),
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
		$tabular_data = array();
		$report_data = $this->getData();
		
		foreach($report_data as $row)
		{
			if ($row['type'] == 'sale')
			{
				$url = anchor(site_url('sales/receipt/'.$row['id']),($this->config->item('sale_prefix') ? $this->config->item('sale_prefix') : 'POS').' '.$row['id'],array('target' => '_blank'));
			}
			elseif ($row['type'] == 'receiving')
			{
				$url = anchor(site_url('receivings/receipt/'.$row['id']),'RECV '.$row['id'],array('target' => '_blank'));				
			}
			$tabular_data[] = array(
				array('data'=>date(get_date_format().' '.get_time_format(),strtotime($row['action_date'])),'align' => 'left'),
				array('data'=>lang('common_'.$row['type']), 'align' => 'center'),
				array('data'=>$url, 'align' => 'center'),
			);
		}
 		$data = array(
			'view' => 'tabular',
			"title" => lang('reports_serial_number_history'),
			"subtitle" => '',
			"headers" => $this->getDataColumns(),
			"data" => $tabular_data,
			"summary_data" => array(),
			"export_excel" => $this->params['export_excel'],
			"pagination" => ''
		);
		
	return $data;
		
	}
	
	
	public function getDataColumns()
	{
		$columns = array();
		
		$columns[] = array('data'=>lang('common_date'), 'align'=> 'center');
		$columns[] = array('data'=>lang('common_type'), 'align'=> 'center');
		$columns[] = array('data'=>lang('common_id'), 'align'=> 'center');

		
		return $columns;		
	}
		
	public function getData()
	{		
		$serial_number = $this->db->escape($this->params['serial_number']);
		$query = "SELECT 'receiving' as type, receiving_id as id,receiving_time as action_date FROM phppos_receivings INNER JOIN phppos_receivings_items USING (receiving_id) WHERE serialnumber = $serial_number UNION ALL SELECT 'sale' as type,sale_id as id,sale_time as action_date FROM phppos_sales INNER JOIN phppos_sales_items USING (sale_id) WHERE phppos_sales_items.serialnumber=$serial_number ORDER BY action_date";
		$result = $this->db->query($query);
		
		return $result->result_array();
	}

	public function getTotalRows()
	{
		return 1000000;
	}
	public function getSummaryData()
	{
		return array();
	}
}

?>