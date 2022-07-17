<?php
require_once ("Report.php");
class Series_customers extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		
		$return = array();
		
		$return['summary'] = array();
		$return['summary'][] = array('data'=>lang('common_edit'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_delete'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_customer'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_item_name'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_sale_date'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_quantity_remaining'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_expire_date'), 'align'=> 'left');
				

		$return['details'] = array();
		$return['details'][] = array('data'=>lang('common_date'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('common_quantity_used'), 'align'=> 'left');
		
		return $return;
		
	}
	
	public function getInputData()
	{
		$input_data = Report::get_common_report_input_data(FALSE);
		$specific_entity_data = array();
		$specific_entity_data['view']  = 'specific_entity';
		$specific_entity_data['specific_input_name'] = 'customer_id';
		$specific_entity_data['specific_input_label'] = lang('common_customer');
		$customers = array('' => lang('common_all'));

		$this->load->model('Customer');
		foreach($this->Customer->get_all()->result() as $customer)
		{
			$customers[$customer->person_id] = $customer->first_name .' '.$customer->last_name;
		}
		$specific_entity_data['specific_input_data'] = $customers;
		
		$input_params[] = $specific_entity_data;
		$input_params[] = array('view' => 'excel_export');
		$input_params[] = array('view' => 'submit');
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
		
	}
	
	public function getOutputData()
	{
		$this->setupDefaultPagination();
		
		$report_data = $this->getData();

		$tabular_data = array();
		$report_data = $this->getData();

		$summary_data = array();
		foreach($report_data as $row)
		{
			$data_row = array();

			$edit=anchor('reports/view_series/'.$row['id'].'/?'.$_SERVER['QUERY_STRING'], lang('common_edit'));
			
			$delete=anchor('reports/delete_series/'.$row['id'].'?'.$_SERVER['QUERY_STRING'], lang('common_delete'), 
			"onclick='return do_link_confirm(".json_encode(lang('reports_confirm_delete_series')).", this)'");

			$data_row[] = array('data'=>$edit, 'align' => 'left');
			$data_row[] = array('data'=>$delete, 'align' => 'left');
			$data_row[] = array('data'=>$row['first_name'].' '.$row['last_name'], 'align' => 'left');
			$data_row[] = array('data'=>$row['name'], 'align' => 'left');
			$data_row[] = array('data'=>date(get_date_format().' '.get_time_format(), strtotime($row['sale_time'])), 'align' => 'left');
			$data_row[] = array('data'=>to_quantity($row['quantity_remaining']), 'align' => 'left');			
			$data_row[] = array('data'=>date(get_date_format(), strtotime($row['expire_date'])), 'align' => 'left');
			$summary_data[$row['id']] = $data_row;			
		}
		


		foreach($this->getDetailsData() as $drow)
		{
			$details_data_row = array();
			
			$details_data_row[] = array('data'=>date(get_date_format().'-'.get_time_format(), strtotime($drow['date'])), 'align'=>'left');
			$details_data_row[] = array('data'=>to_quantity($drow['quantity_used']), 'align'=>'right');
			$details_data[$drow['series_id']][] = $details_data_row;
			
		}
		

		$export_excel = $this->params['export_excel'];

		$data = array(
			"view" =>'tabular_details',
			"title" =>lang('reports_detailed_series_report'),
			"subtitle" => '',
			"headers" => $this->getDataColumns(),
			"summary_data" => $summary_data,
			"overall_summary_data" => $this->getSummaryData(),
			"export_excel" => $export_excel,
			"pagination" => $this->pagination->create_links(),
			"report_model" => get_class($this),
		);
		isset($details_data) && !empty($details_data) ? $data["details_data"]=$details_data: '' ;

		return $data;
	}
	
	function getDetailsData()
	{
		$this->db->select('customers_series_log.*');
		$this->db->from('customers_series_log');
		$this->db->join('customers_series', 'customers_series.id = customers_series_log.series_id');
		if ($this->params['customer_id'])
		{
			$this->db->where('customers_series.customer_id', $this->params['customer_id']);
		}
		
		$this->db->order_by('date');		
		return $this->db->get()->result_array();
	}
	public function getData()
	{
		$this->db->select('items.name, customers_series.id,people.first_name,people.last_name,sales.sale_time,customers_series.quantity_remaining,customers_series.expire_date');
		$this->db->from('customers_series');
		$this->db->join('items', 'items.item_id = customers_series.item_id');
		$this->db->join('sales', 'sales.sale_id = customers_series.sale_id');
		$this->db->join('people', 'people.person_id = customers_series.customer_id');
		if ($this->params['customer_id'])
		{
			$this->db->where('customers_series.customer_id', $this->params['customer_id']);
		}		
		
		$this->db->order_by('customers_series.expire_date');
		
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
	
	function getTotalRows()
	{
		$this->db->select('customers_series.id,people.first_name,people.last_name,sales.sale_time,customers_series.quantity_remaining,customers_series.expire_date');
		$this->db->from('customers_series');
		$this->db->join('sales', 'sales.sale_id = customers_series.sale_id');
		$this->db->join('people', 'people.person_id = customers_series.customer_id');
		if ($this->params['customer_id'])
		{
			$this->db->where('customers_series.customer_id', $this->params['customer_id']);
		}		
		
		return $this->db->count_all_results();
	}
	
	public function getSummaryData()
	{
		return array();
	}
}
?>