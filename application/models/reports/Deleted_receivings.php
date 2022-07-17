<?php
require_once ("Report.php");
class Deleted_receivings extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		$return = array('summary' => array(array('data'=>lang('reports_receiving_id'), 'align'=>'left'), array('data'=>lang('common_location'), 'align'=> 'left'),array('data'=>lang('reports_date'), 'align'=>'left'), array('data'=>lang('reports_items_ordered'), 'align'=>'left'),array('data'=>lang('common_qty_received'), 'align'=>'left'), array('data'=>lang('reports_received_by'), 'align'=>'left'), array('data'=>lang('reports_supplied_by'), 'align'=>'left'),  array('data'=>lang('reports_subtotal'), 'align'=>'right'), array('data'=>lang('reports_total'), 'align'=>'right'),  array('data'=>lang('common_tax'), 'align'=>'right'), array('data'=>lang('reports_payment_type'), 'align'=>'left'), array('data'=>lang('reports_comments'), 'align'=>'left')),
					'details' => $this->get_details_data_column_recv());		
				
		
		return $return;
	}
	
	public function getInputData()
	{
		$input_data = Report::get_common_report_input_data(TRUE);
		
		$input_params = array();

		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array(
				array('view' => 'date_range', 'with_time' => TRUE),
				array('view' => 'dropdown','dropdown_label' =>lang('reports_receiving_type'),'dropdown_name' => 'receiving_type','dropdown_options' =>array('all' => lang('reports_all'), 'receiving' => lang('common_receiving'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all'),
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
		$this->load->model('Receiving');
		$this->load->model('Category');
		$export_excel = $this->params['export_excel'];
		$start_date = $this->params['start_date'];
		$end_date = $this->params['end_date'];
		$headers = $this->getDataColumns();
		$report_data = $this->getData();
		
		$summary_data = array();
		$details_data = array();

		$location_count = $this->Location->count_all();
		
		foreach(isset($export_excel) == 1 && isset($report_data['summary']) ? $report_data['summary']:$report_data as $key=>$row)
		{
			$summary_data[$key] = array(array('data'=>anchor('receivings/receipt/'.$row['receiving_id'], '<i class="ion-printer"></i>', array('target' => '_blank')).' '.anchor('receivings/edit/'.$row['receiving_id'], '<i class="ion-document-text"></i>', array('target' => '_blank')).' '.anchor('receivings/edit/'.$row['receiving_id'], lang('common_edit').' '.$row['receiving_id'], array('target' => '_blank')).' ['.anchor('items/generate_barcodes_from_recv/'.$row['receiving_id'], lang('common_barcode_sheet'), array('target' => '_blank')).' / '.anchor('items/generate_barcodes_labels_from_recv/'.$row['receiving_id'], lang('common_barcode_labels'), 
			array('target' => '_blank')).' / '.anchor('reports/export_recv/'.$row['receiving_id'], lang('common_excel_export'), 
			array('target' => '_blank')).']', 'align'=> 'left','detail_id' => $row['receiving_id']), 
			array('data'=>$row['location_name'], 'align'=> 'left'),
			array('data'=>date(get_date_format(), strtotime($row['receiving_date'])), 'align'=> 'left'), 
			array('data'=>to_quantity($row['items_purchased']), 'align'=> 'left'),
			array('data'=>to_quantity($row['items_received']), 'align'=> 'left'), 
			array('data'=>$row['employee_name'], 'align'=> 'left'), 
			array('data'=>$row['supplier_name'], 'align'=> 'left'), 
			array('data'=>to_currency($row['subtotal']), 'align'=> 'right'), 
			array('data'=>to_currency($row['total']), 'align'=> 'right'),
			array('data'=>to_currency($row['tax']), 'align'=> 'right'), 
			array('data'=>$row['payment_type'], 'align'=> 'left'), 
			array('data'=>$row['comment'], 'align'=> 'left'));
			
			
			if($export_excel == 1)
			{
				foreach($report_data['details'][$key] as $drow)
				{
					$details_data[$key][] =	array(array('data'=>$drow['name'], 'align'=> 'left'),
					array('data'=>$drow['product_id'], 'align'=> 'left'), 
					array('data'=>$this->Category->get_full_path($drow['category_id']), 'align'=> 'left'), 
					array('data'=>$drow['size'], 'align'=> 'left'), 
					array('data'=>to_quantity($drow['quantity_purchased']), 'align'=> 'left'),
					array('data'=>to_quantity($drow['quantity_received']), 'align'=> 'left'),
					array('data'=>to_currency($drow['subtotal']), 'align'=> 'right'), 
					array('data'=>to_currency($drow['total']), 'align'=> 'right'),
					array('data'=>to_currency($drow['tax']), 'align'=> 'right'), 
					array('data'=>$drow['discount_percent'].'%', 'align'=> 'left'));
				}
			}
		}
		
		$data = array(
			"view" => 'tabular_details_lazy_load',
			"title" =>lang('reports_deleted_recv_reports'),
			"subtitle" => date(get_date_format(), strtotime($start_date)) .'-'.date(get_date_format(), strtotime($end_date)),
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
	
	public function getData()
	{
		$data = array();
		$data['summary'] = array();
		$data['details'] = array();
	
		$this->db->select('locations.name as location_name, receiving_id, date(receiving_time) as receiving_date, total_quantity_purchased as items_purchased,total_quantity_received as items_received, CONCAT(employee.first_name," ",employee.last_name) as employee_name, CONCAT(supplier.company_name, " (",people.first_name," ",people.last_name, ")") as supplier_name, subtotal, total, tax, profit, payment_type, comment', false);
		$this->db->from('receivings');
		$this->db->join('locations', 'locations.location_id = receivings.location_id');
		$this->db->join('people as employee', 'receivings.employee_id = employee.person_id');
		$this->db->join('suppliers as supplier', 'receivings.supplier_id = supplier.person_id', 'left');
		$this->db->join('people as people', 'people.person_id = supplier.person_id', 'left');
		if ($this->params['receiving_type'] == 'receiving')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['receiving_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
		}
		
		$this->receiving_time_where();
		$this->db->where('receivings.deleted', 1);
		$this->db->order_by('receiving_time', ($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');

		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			$this->db->offset(isset($this->params['offset']) ? $this->params['offset'] : 0);
			return $this->db->get()->result_array();
			exit;
		}		
		
		foreach($this->db->get()->result_array() as $receiving_summary_row)
		{
			$data['summary'][$receiving_summary_row['receiving_id']] = $receiving_summary_row; 
		}
		
		$receiving_ids = array();
		
		foreach($data['summary'] as $receiving_row)
		{
			$receiving_ids[] = $receiving_row['receiving_id'];
		}

		$result = $this->get_report_details($receiving_ids,1);

		foreach($result as $receiving_item_row)
		{
			$data['details'][$receiving_item_row['receiving_id']][] = $receiving_item_row;
		}

		return $data;
		exit;
	}
	
	public function getTotalRows()
	{		
		
		$this->db->from('receivings');
		if ($this->params['receiving_type'] == 'receiving')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['receiving_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
		}

		$this->receiving_time_where();
		$this->db->where('deleted', 1);
		return $this->db->count_all_results();

	}
	
	public function getSummaryData()
	{
		$this->db->select('sum(tax) as tax, sum(total) as total', false);
		$this->db->from('receivings');
		if ($this->params['receiving_type'] == 'receiving')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['receiving_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
		}
				
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('store_account_payment', 0);
		}
		
		$this->receiving_time_where();
		
		$this->db->where('deleted', 1);
		return $this->db->get()->row_array();
	}
	function get_report_details($ids, $export_excel=0)
	{
		return $this->get_report_details_recv($ids,$export_excel);
	}
}

?>