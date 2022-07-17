<?php
require_once ("Report.php");
class Specific_supplier_receiving extends Report
{
	function __construct()
	{		
		parent::__construct();
	}
	
	public function getDataColumns()
	{

		$return = array();
		
		$return['summary'] = array();
		$location_count = $this->Location->count_all();
		
		$return['summary'][] = array('data'=>lang('reports_receiving_id'), 'align'=> 'left');
		
		if ($location_count > 1)
		{
			$return['summary'][] = array('data'=>lang('common_location'), 'align'=> 'left');
		}
		
		
		$return['summary'][] = array('data'=>lang('reports_date'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_items_ordered'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_qty_received'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('reports_total'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('common_tax'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('reports_payment_type'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('reports_comments'), 'align'=> 'right');

		$return['details'] = array();
		$return['details'][] = array('data'=>lang('common_item_number'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('common_product_id'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('reports_name'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('reports_category'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('reports_items_ordered'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('common_qty_received'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
		$return['details'][] = array('data'=>lang('reports_total'), 'align'=> 'right');
		$return['details'][] = array('data'=>lang('common_tax'), 'align'=> 'right');
		$return['details'][] = array('data'=>lang('common_discount'), 'align'=> 'right');
		
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
			
			$input_params[] = array('view' => 'date_range', 'with_time' => TRUE);
			$input_params[] = $specific_entity_data;
			$input_params[] = array('view' => 'dropdown','dropdown_label' =>lang('reports_receiving_type'),'dropdown_name' => 'receiving_type','dropdown_options' =>array('all' => lang('reports_all'), 'receiving' => lang('common_receiving'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all');
			$input_params[] = array('view' => 'excel_export');
			$input_params[] = array('view' => 'locations');
			$input_params[] = array('view' => 'submit');
		}
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}
	
	public function getOutputData()
	{
		$this->load->model('Receiving');
		$this->load->model('Supplier');
		$this->load->model('Category');
		
		$this->setupDefaultPagination();
		$headers = $this->getDataColumns();
		$report_data = $this->getData();

		$summary_data = array();
		$details_data = array();
		$location_count = $this->Location->count_all();

		foreach(isset($this->params['export_excel']) == 1 && isset($report_data['summary']) ? $report_data['summary']:$report_data as $key=>$row)
		{			
			$summary_data_row = array();
		
			$summary_data_row[] = array('data'=>anchor('receivings/edit/'.$row['receiving_id'], lang('common_edit').' '.$row['receiving_id'], array('target' => '_blank')).' ['.anchor('items/generate_barcodes_from_recv/'.$row['receiving_id'], lang('common_barcode_sheet'), array('target' => '_blank')).' / '.anchor('items/generate_barcodes_labels_from_recv/'.$row['receiving_id'], lang('common_barcode_labels'), array('target' => '_blank')).']'.'<br />'.anchor('receivings/clone_receiving/'.$row['receiving_id'], lang('common_clone'), 
			array('target' => '_blank','class'=>'hidden-print')), 'align'=> 'left', 'detail_id' => $row['receiving_id']);
			
			if ($location_count > 1)
			{
				$summary_data_row[] = array('data'=>$row['location_name'], 'align' => 'left');
			}
			
			$summary_data_row[] = array('data'=>date(get_date_format().'-'.get_time_format(), strtotime($row['receiving_time'])), 'align'=> 'left');
			$summary_data_row[] = array('data'=>to_quantity($row['items_purchased']), 'align'=> 'left');
			$summary_data_row[] = array('data'=>to_quantity($row['items_received']), 'align'=> 'left');
			$summary_data_row[] = array('data'=>to_currency($row['subtotal']), 'align'=> 'right');
			$summary_data_row[] = array('data'=>to_currency($row['total']), 'align'=> 'right');
			$summary_data_row[] = array('data'=>to_currency($row['tax']), 'align'=> 'right');
		
			$summary_data_row[] = array('data'=>$row['payment_type'], 'align'=>'right');
			$summary_data_row[] = array('data'=>$row['comment'], 'align'=>'right');
			$summary_data[$key] = $summary_data_row;
			
			if($this->params['export_excel'] == 1)
				
			{
				foreach($report_data['details'][$key] as $drow)
				{
					$details_data[$key][] = array(
					array('data'=>$drow['name'], 'align'=> 'left'),
					array('data'=>$drow['product_id'], 'align'=> 'left'), 
					array('data'=>$this->Category->get_full_path($drow['category_id']), 'align'=> 'left'), 
					array('data'=>$drow['size'], 'align'=> 'left'), 
					array('data'=>to_quantity($drow['quantity_purchased']), 'align'=> 'left'),
					array('data'=>to_quantity($drow['quantity_purchased']), 'align'=> 'left'), 
					array('data'=>to_currency($drow['subtotal']), 'align'=> 'right'), 
					array('data'=>to_currency($drow['total']), 'align'=> 'right'),
					array('data'=>to_currency($drow['tax']), 'align'=> 'right'), 
					array('data'=>$drow['discount_percent'].'%', 'align'=> 'left'));
				}
			
			}
		}

		$supplier_info = $this->Supplier->get_info($this->params['supplier_id']);
		$data = array(
					"view" => 'tabular_details_lazy_load',
					"title" => $supplier_info->first_name .' '. $supplier_info->last_name.' '.lang('reports_recevings_report'),
					"subtitle" => date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date'])),
					"headers" => $this->getDataColumns(),
					"summary_data" => $summary_data,
					"overall_summary_data" => $this->getSummaryData(),
					"export_excel" => $this->params['export_excel'],
					"pagination" => $this->pagination->create_links(),
					"report_model" => get_class($this)
		);
		isset($details_data) && !empty($details_data) ? $data["details_data"]=$details_data: '' ;
		return $data;

	}
	
	public function getData()
	{
		$location_ids = self::get_selected_location_ids();
		$data = array();
		$data['summary'] = array();
		$data['details'] = array();

		$this->db->select('locations.name as location_name, receiving_id, receiving_time, date(receiving_time) as receiving_date, sum(total_quantity_purchased) as items_purchased, sum(total_quantity_received) as items_received, sum(total) as total, sum(subtotal) as subtotal, sum(tax) as tax, payment_type, comment', false);
		$this->db->from('receivings');
		$this->db->join('locations', 'locations.location_id = receivings.location_id');
		
		$this->db->where_in('receivings.location_id', $location_ids);
		$this->db->where('receiving_time BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and receivings.supplier_id='.$this->db->escape($this->params['supplier_id']));
		
		if ($this->params['receiving_type'] == 'receiving')
		{
			$this->db->where('recivings.total_quantity_purchased > 0');
		}
		elseif ($this->params['receiving_type'] == 'returns')
		{
			$this->db->where('receivings.total_quantity_purchased < 0');
		}


		$this->db->where('receivings.deleted', 0);
		$this->db->where('receivings.suspended', 0);
		
		$this->db->group_by('receivings.receiving_id');
		$this->db->order_by('receiving_time', ($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');

		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			if (isset($this->params['offset']))
			{
				$this->db->offset($this->params['offset']);
			}
			return $this->db->get()->result_array();
			exit;
		}		
		
		foreach($this->db->get()->result_array() as $sale_summary_row)
		{
			$data['summary'][$sale_summary_row['receiving_id']] = $sale_summary_row; 
		}
		
		$receiving_ids = array();
		
		foreach($data['summary'] as $sale_row)
		{
			$receiving_ids[] = $sale_row['receiving_id'];
		}
		
			$result = $this->get_report_details($receiving_ids,1);
		
		foreach($result as $sale_item_row)
		{
			$data['details'][$sale_item_row['receiving_id']][] = $sale_item_row;
		}
		return $data;
		exit;
	}
	
	public function getTotalRows()
	{		
		$this->db->select("COUNT(receiving_id) as recv_count");
		$this->db->from('receivings');
		$this->db->where('receiving_time BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and receivings.supplier_id='.$this->db->escape($this->params['supplier_id']));
		
		if ($this->params['receiving_type'] == 'receiving')
		{
			$this->db->where('total_quantity_purchased > 0');
		}
		elseif ($this->params['receiving_type'] == 'returns')
		{
			$this->db->where('total_quantity_purchased < 0');
		}

		
		$this->db->where('receivings.deleted', 0);
		$this->db->where('receivings.suspended', 0);
		
		$ret = $this->db->get()->row_array();
		return $ret['recv_count'];
	}
	
	
	public function getSummaryData()
	{
		$location_ids = self::get_selected_location_ids();
		$this->db->select('sum(total) as total,sum(subtotal) as subtotal, sum(tax) as tax', false);
		$this->db->from('receivings');
		$this->db->where('receiving_time BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']).' and receivings.supplier_id='.$this->db->escape($this->params['supplier_id']));
		$this->db->where_in('receivings.location_id', $location_ids);
		
		if ($this->params['receiving_type'] == 'receiving')
		{
			$this->db->where('receivings.total_quantity_purchased > 0');
		}
		elseif ($this->params['receiving_type'] == 'returns')
		{
			$this->db->where('receivings.total_quantity_purchased < 0');
		}
		
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('receivings.store_account_payment', 0);
		}
		
		$this->db->where('receivings.deleted', 0);
		$this->db->where('receivings.suspended', 0);
		
		$this->db->group_by('receivings.receiving_id');
		
		$return = array(
			'subtotal' => 0,
			'total' => 0,
			'tax' => 0,
		);
		
		foreach($this->db->get()->result_array() as $row)
		{
			$return['subtotal'] += to_currency_no_money($row['subtotal'],2);
			$return['total'] += to_currency_no_money($row['total'],2);
			$return['tax'] += to_currency_no_money($row['tax'],2);
		}
		return $return;
	}
	
	function get_report_details($ids, $export_excel=0)
	{
		$this->db->select('receivings_items.receiving_id, items.category_id, items.item_number, items.product_id, items.name, categories.name as category,quantity_purchased ,quantity_received , serialnumber, receivings_items.description, subtotal, total, tax, profit, discount_percent, items.size as size, items.unit_price as current_selling_price, suppliers.company_name as supplier_name, suppliers.person_id as supplier_id', false);
		$this->db->from('receivings_items');
		$this->db->join('items', 'receivings_items.item_id = items.item_id', 'left');
		$this->db->join('categories', 'categories.id = items.category_id', 'left');
		$this->db->join('suppliers', 'items.supplier_id = suppliers.person_id', 'left');
		
		if (!empty($ids))
		{
			$sale_ids_chunk = array_chunk($ids,25);
			$this->db->group_start();
			foreach($sale_ids_chunk as $sale_ids)
			{
				$this->db->or_where_in('receivings_items.receiving_id', $sale_ids);
			}
			$this->db->group_end();
		}
		else
		{
			$this->db->where('1', '2', FALSE);		
		}		
		$qry=$this->db->get_compiled_select();
		

		$query = $this->db->query($qry);
		
		$res=$query->result_array();
		
		if($export_excel == 1)
		{
			return $res;
			exit;
		}
		$this->load->model('Category');
		$details_data = array();
		foreach($res as $key=>$drow)
			{	
				$details_data_row = array();
				$details_data_row[] = array('data'=>$drow['item_number'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['product_id'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['name'], 'align'=>'left');
				$details_data_row[] = array('data'=>$this->Category->get_full_path($drow['category_id']), 'align'=>'left');
	
				$details_data_row[] = array('data'=>to_quantity($drow['quantity_purchased']), 'align'=>'left');
				$details_data_row[] = array('data'=>to_quantity($drow['quantity_received']), 'align'=>'left');
				$details_data_row[] = array('data'=>to_currency($drow['subtotal']), 'align'=>'right');
				$details_data_row[] = array('data'=>to_currency($drow['total']), 'align'=>'right');
				$details_data_row[] = array('data'=>to_currency($drow['tax']), 'align'=>'right');
				
				$details_data_row[] = array('data'=>$drow['discount_percent'].'%', 'align'=> 'left');
				
				$details_data[$key][$drow['receiving_id']] = $details_data_row;
			}
		
		$data=array(
		"headers" => $this->getDataColumns(),
		"details_data" => $details_data
		);
		
		return $data;
	
	}
}
?>