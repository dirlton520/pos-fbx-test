<?php
require_once("Report.php");
class Layaway_statements extends Report
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
		$specific_entity_data['specific_input_name'] = 'customer_id';
		$specific_entity_data['specific_input_label'] = lang('reports_customer');
		$specific_entity_data['search_suggestion_url'] = site_url('reports/customer_search/1');
		$specific_entity_data['view'] = 'specific_entity';


		if ($this->settings['display'] == 'tabular') {
			$input_params = array();

			$input_params[] = $specific_entity_data;
			$input_params[] = array('view' => 'date_range', 'with_time' => FALSE, 'end_date_end_of_day' => FALSE);
			$input_params[] = array('view' => 'checkbox', 'checkbox_label' => lang('reports_hide_items'), 'checkbox_name' => 'hide_items');
			$input_params[] = array('view' => 'checkbox', 'checkbox_label' => lang('reports_hide_paid'), 'checkbox_name' => 'hide_paid');
			$input_params[] = array('view' => 'locations');
			$input_params[] = array('view' => 'submit');
		}

		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}

	public function getOutputData()
	{
		$this->load->model('Sale');
		$this->load->model('Customer');
		$this->load->model('Category');

		$this->setupDefaultPagination();
		$report_data = $this->getData();
		$location_count = count(Report::get_selected_location_ids());

		$total_amount_due = 0;

		$data = array(
			'total_amount_due' => $this->getSummaryData(),
			"view" => 'layaway_statements',
			"title" => lang('reports_layaway_statements'),
			"subtitle" => date(get_date_format(), strtotime($this->params['start_date'])) . '-' . date(get_date_format(), strtotime($this->params['end_date'])),
			'location_count' => $location_count,
			'report_data' => $report_data,
			'hide_items' => isset($this->params['hide_items']) ? TRUE : FALSE,
			"pagination" => $this->pagination->create_links(),
			'date_column' => 'sale_time'
		);

		return $data;
	}

	public function getData()
	{
		$this->load->model('Customer');
		$this->load->model('Sale');
		$return = array();

		$customer_ids_for_report = array();
		$customer_id = $this->params['customer_id'];
		$suspended_types = array(1);

		if (!$customer_id) {
			$this->db->select('person_id');
			$this->db->from('customers');
			//$this->db->where('balance !=', 0);
			$this->db->where('deleted', 0);
			$this->db->limit($this->report_limit);

			if (isset($this->params['offset'])) {
				$this->db->offset($this->params['offset']);
			}
			$result = $this->db->get()->result_array();

			foreach ($result as $row) {
				$customer_ids_for_report[] = $row['person_id'];
			}
		} else {
			$this->db->select('person_id');
			$this->db->from('customers');
			$this->db->where('person_id', $customer_id);
			$this->db->where('deleted', 0);

			$result = $this->db->get()->row_array();

			if (!empty($result)) {
				$customer_ids_for_report[] = $result['person_id'];
			}
		}

		foreach ($customer_ids_for_report as $customer_id) {
			$result = $this->Sale->get_all_suspended($suspended_types, $customer_id, $this->params);

			if (!empty($result)) {
				$return[] = array('customer_info' => $this->Customer->get_info($customer_id), 'layaway_transactions' => $result);
			}
		}
		return $return;
	}

	public function getTotalRows()
	{
		$customer_id = $this->params['customer_id'];

		if (!$customer_id) {
			$this->db->distinct();
			$this->db->select('store_accounts.customer_id');
			$this->db->from('store_accounts');
			$this->db->join('sales', 'sales.sale_id = store_accounts.sale_id');
			$this->db->where('balance !=', 0);
		} else {
			$this->db->distinct();
			$this->db->select('store_accounts.customer_id');
			$this->db->from('store_accounts');
			$this->db->join('sales', 'sales.sale_id = store_accounts.sale_id');
			$this->db->where('store_accounts.customer_id', $customer_id);
		}

		return $this->db->get()->num_rows();
	}

	//This gets total amount due
	public function getSummaryData()
	{
		$total_amount_due = 0;

		$this->load->model('Customer');
		$this->load->model('Sale');
		$return = array();

		$customer_ids_for_report = array();
		$customer_id = $this->params['customer_id'];
		$suspended_types = array(1);

		if (!$customer_id) {
			$this->db->select('person_id');
			$this->db->from('customers');
			//$this->db->where('balance !=', 0);
			$this->db->where('deleted', 0);
			$this->db->limit($this->report_limit);

			if (isset($this->params['offset'])) {
				$this->db->offset($this->params['offset']);
			}
			$result = $this->db->get()->result_array();

			foreach ($result as $row) {
				$customer_ids_for_report[] = $row['person_id'];
			}
		} else {
			$this->db->select('person_id');
			$this->db->from('customers');
			$this->db->where('person_id', $customer_id);
			$this->db->where('deleted', 0);

			$result = $this->db->get()->row_array();

			if (!empty($result)) {
				$customer_ids_for_report[] = $result['person_id'];
			}
		}

		foreach ($customer_ids_for_report as $customer_id) {
			$result = $this->Sale->get_all_suspended($suspended_types, $customer_id, $this->params);

			if (!empty($result)) {
				$return[] = array('layaway_transactions' => $result);
			}
		}

		foreach($return as $data) 
		{
			$amount_due = 0;
			foreach($data['layaway_transactions'] as $transaction)
			{
		
				$amount_due = $transaction['amount_due'];
			}

			$total_amount_due+=$amount_due;
		}

		return $total_amount_due;
	}
}
