<?php
require_once ("Report.php");
class Detailed_transfers extends Report
{
	function __construct()
	{
		$this->lang->load('receivings');
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		$return = array('summary' => array(
		array('data'=>lang('reports_receiving_id'), 'align'=>'left'), 
		array('data'=>lang('receivings_transfer_from'), 'align'=> 'left'),
		array('data'=>lang('receivings_transfer_to'), 'align'=> 'left'),
		array('data'=>lang('reports_date'), 'align'=>'left'), 
		array('data'=>lang('reports_items_ordered'), 'align'=>'left'),
		array('data'=>lang('common_qty_received'), 'align'=>'left'), 
		array('data'=>lang('reports_received_by'), 'align'=>'left'), 
		array('data'=>lang('reports_supplied_by'), 'align'=>'left'),  
		array('data'=>lang('reports_subtotal'), 'align'=>'right'), 
		array('data'=>lang('reports_total'), 'align'=>'right'),  
		array('data'=>lang('common_tax'), 'align'=>'right'), 
		array('data'=>lang('reports_payment_type'), 'align'=>'left'), 
		array('data'=>lang('reports_comments'), 'align'=>'left')),
		'details' => $this->get_details_data_column_recv(),
		);		
		
		return $return;
	}
	
	function get_details_data_column_recv()
	{
		$return = array(array('data'=>lang('common_item_id'), 'align'=>'left'),array('data'=>lang('reports_name'), 'align'=>'left'),array('data'=>lang('common_product_id'), 'align'=> 'left'), array('data'=>lang('reports_category'), 'align'=>'left'),array('data'=>lang('common_serial_number'), 'align'=>'left'),array('data'=>lang('common_size'), 'align'=>'left'), 		array('data'=>lang('reports_items_ordered'), 'align'=>'left'),array('data'=>lang('common_qty_received'), 'align'=>'left'), array('data'=>lang('reports_subtotal'), 'align'=>'right'), array('data'=>lang('reports_total'), 'align'=>'right'),  		array('data'=>lang('common_tax'), 'align'=>'right'), array('data'=>lang('common_discount'), 'align'=>'left'));
		
	  for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
		{
			$this->load->model('Item');
			$custom_field = $this->Item->get_custom_field($k);
			if($custom_field !== FALSE)
			{
				$return[] = array('data'=>$custom_field, 'align'=> 'right');
			}
		}
		
		return $return;
	}
	
	
	public function getInputData()
	{
		$input_data = Report::get_common_report_input_data(TRUE);
		
		
		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array();
			
			$input_params[] = array('view' => 'date_range', 'with_time' => TRUE);
			
			$locations = array();
			
			foreach($this->Location->get_all()->result() as $location)
			{
				$locations[$location->location_id] = $location->name;
			}
			
			$input_params[] = array('view' => 'locations','label' => lang('receivings_transfer_to'));
			$input_params[] = array('view' => 'excel_export');
			$input_params[] = array('view' => 'submit');
		}
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}
	
	public function receiving_time_where($sql_requrn=false)
	{
		static $location_ids;
		
		if (!$location_ids)
		{
			$location_ids = implode(',',Report::get_selected_location_ids());
		}
		
		$where = 'receiving_time BETWEEN "'.$this->params['start_date'].'" and "'.$this->params['end_date'].'"'.(($this->config->item('hide_store_account_payments_in_reports') ) ? ' and '.$this->db->dbprefix('receivings').'.store_account_payment=0' : '');
		//Added for detailed_suspended_report, we don't need this for other reports as we are always going to have start + end date
		if (isset($this->settings['force_suspended']) && $this->settings['force_suspended'])
		{
			$where .=' and suspended != 0';				
		}
		elseif ($this->config->item('hide_suspended_recv_in_reports'))
		{
			$where .=' and suspended = 0';
		}
		
		$this->db->where($where);
		
	}
	
	
	function getOutputData()
	{
		$this->load->model('Category');
		$this->setupDefaultPagination();
		
		$headers = $this->getDataColumns();
		$report_data = $this->getData();
		$export_excel = $this->params['export_excel'];
		$start_date = $this->params['start_date'];
		$end_date = $this->params['end_date'];
		$summary_data = array();
		$details_data = array();
		$location_count = $this->Location->count_all();
		
		
		foreach(isset($export_excel) == 1 && isset($report_data['summary']) ? $report_data['summary']:$report_data as $key=>$row)
		{
			
			$transfer_info = '';
			if ($row['transfer_to_location_id'])
			{
				$this->lang->load('receivings');
				$transfer_info=' <strong style="color: red;">'.lang('receivings_transfer').'</strong>';
				
				if ($row['suspended'])
				{
					$transfer_info.=' '.anchor('receivings/switch_location_and_unsuspend/'.$row['location_id'].'/'.$row['receiving_id'], lang('reports_complete_pending_transfer'));
				}
				
			}
			
			$summary_data[$key] = array( array('data'=>anchor('receivings/receipt/'.$row['receiving_id'], '<i class="ion-printer"></i>', array('target' => '_blank')).' '.anchor('receivings/edit/'.$row['receiving_id'], '<i class="ion-document-text"></i>', array('target' => '_blank')).' '.anchor('receivings/edit/'.$row['receiving_id'], lang('common_edit').' '.$row['receiving_id'], array('target' => '_blank')).' ['.anchor('items/generate_barcodes_from_recv/'.$row['receiving_id'], lang('common_barcode_sheet'), array('target' => '_blank', 'class' => 'generate_barcodes_from_recv')).' / '.anchor('items/generate_barcodes_labels_from_recv/'.$row['receiving_id'], lang('common_barcode_labels'), 
			array('target' => '_blank')).' / '.anchor('reports/export_recv/'.$row['receiving_id'], lang('common_excel_export'), 
			array('target' => '_blank')).']'.$transfer_info, 'align'=> 'left', 'detail_id' => $row['receiving_id'] ), 
			array('data'=>$row['transfer_from'], 'align'=> 'left'),
			array('data'=>$row['transfer_to'], 'align'=> 'left'),
			array('data'=>date(get_date_format(), strtotime($row['receiving_date'])), 'align'=> 'left'), 
			array('data'=>to_quantity($row['items_purchased']*-1), 'align'=> 'left'),
			array('data'=>to_quantity($row['items_received']*-1), 'align'=> 'left'), 
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
					$myrow = array(
					array('data'=>$drow['item_id'], 'align'=> 'left'),
					array('data'=>$drow['name'], 'align'=> 'left'),
					array('data'=>$drow['product_id'], 'align'=> 'left'), 
					array('data'=>$this->Category->get_full_path($drow['category_id']), 'align'=> 'left'), 
					array('data'=>$drow['serialnumber'], 'align'=> 'left'), 
					array('data'=>$drow['size'], 'align'=> 'left'), 
					array('data'=>to_quantity($drow['quantity_purchased']*-1), 'align'=> 'left'),
					array('data'=>to_quantity($drow['quantity_purchased']*-1), 'align'=> 'left'), 
					array('data'=>to_currency($drow['subtotal']), 'align'=> 'right'), 
					array('data'=>to_currency($drow['total']), 'align'=> 'right'),
					array('data'=>to_currency($drow['tax']), 'align'=> 'right'), 
					array('data'=>$drow['discount_percent'].'%', 'align'=> 'left'));
					
				  for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
					{
						$custom_field = $this->Item->get_custom_field($k);
						if($custom_field !== FALSE)
						{
							if ($this->Item->get_custom_field($k,'type') == 'checkbox')
							{
								$format_function = 'boolean_as_string';
							}
							elseif($this->Item->get_custom_field($k,'type') == 'date')
							{
								$format_function = 'date_as_display_date';				
							}
							elseif($this->Item->get_custom_field($k,'type') == 'email')
							{
								$format_function = 'strsame';					
							}
							elseif($this->Item->get_custom_field($k,'type') == 'url')
							{
								$format_function = 'strsame';					
							}
							elseif($this->Item->get_custom_field($k,'type') == 'phone')
							{
								$format_function = 'strsame';					
							}
							elseif($this->Item->get_custom_field($k,'type') == 'image')
							{
								$this->load->helper('url');
								$format_function = 'file_id_to_image_thumb';					
							}
							elseif($this->Item->get_custom_field($k,'type') == 'file')
							{
								$this->load->helper('url');
								$format_function = 'file_id_to_download_link';					
							}
							else
							{
								$format_function = 'strsame';
							}
					
							$myrow[] = array('data'=>$format_function($drow["custom_field_${k}_value"]), 'align'=>'right');
						}
					}
					$details_data[$key][] = $myrow;
				}
			}
		}

		$data = array(
			"view" => 'tabular_details_lazy_load',
			"title" =>lang('reports_detailed_receivings_report'),
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
	
	
	function get_report_details_recv($ids, $export_excel=0)
	{

		$this->db->select('items.item_id,items.custom_field_1_value,items.custom_field_2_value,items.custom_field_3_value,items.custom_field_4_value,items.custom_field_5_value,items.custom_field_6_value,items.custom_field_7_value,items.custom_field_8_value,items.custom_field_9_value,items.custom_field_10_value,receivings_items.serialnumber,receivings_items.receiving_id, items.category_id, items.item_number, items.product_id , items.name, categories.name as category, quantity_purchased,quantity_received, serialnumber, items.description, subtotal, total, tax, profit, discount_percent, items.size as size, items.unit_price as current_selling_price, suppliers.company_name as supplier_name, suppliers.person_id as supplier_id', false);
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
				$details_data_row[] = array('data'=>$drow['item_id'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['name'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['product_id'], 'align'=>'left');
				$details_data_row[] = array('data'=>$this->Category->get_full_path($drow['category_id']), 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['serialnumber'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['size'], 'align'=>'left');
				$details_data_row[] = array('data'=>to_quantity($drow['quantity_purchased']*-1), 'align'=>'left');
				$details_data_row[] = array('data'=>to_quantity($drow['quantity_received']*-1), 'align'=> 'left');
				$details_data_row[] = array('data'=>to_currency($drow['subtotal']), 'align'=>'right');
				$details_data_row[] = array('data'=>to_currency($drow['total']), 'align'=>'right');
				$details_data_row[] = array('data'=>to_currency($drow['tax']), 'align'=>'right');
				
				
				$details_data_row[] = array('data'=>$drow['discount_percent'].'%', 'align'=> 'left');
				
			  for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
				{
					$custom_field = $this->Item->get_custom_field($k);
					if($custom_field !== FALSE)
					{
						if ($this->Item->get_custom_field($k,'type') == 'checkbox')
						{
							$format_function = 'boolean_as_string';
						}
						elseif($this->Item->get_custom_field($k,'type') == 'date')
						{
							$format_function = 'date_as_display_date';				
						}
						elseif($this->Item->get_custom_field($k,'type') == 'email')
						{
							$format_function = 'strsame';					
						}
						elseif($this->Item->get_custom_field($k,'type') == 'url')
						{
							$format_function = 'strsame';					
						}
						elseif($this->Item->get_custom_field($k,'type') == 'phone')
						{
							$format_function = 'strsame';					
						}
						elseif($this->Item->get_custom_field($k,'type') == 'image')
						{
							$this->load->helper('url');
							$format_function = 'file_id_to_image_thumb';					
						}
						elseif($this->Item->get_custom_field($k,'type') == 'file')
						{
							$this->load->helper('url');
							$format_function = 'file_id_to_download_link';					
						}
						else
						{
							$format_function = 'strsame';
						}
					
						$details_data_row[] = array('data'=>$format_function($drow["custom_field_${k}_value"]), 'align'=>'right');
					}
				}
				
				$details_data[$key][$drow['receiving_id']] = $details_data_row;
			}
		
		$data=array(
		"headers" => $this->getDataColumns(),
		"details_data" => $details_data
		);
		
		return $data;
	
	}
	
	
	public function getData()
	{
		$location_ids = self::get_selected_location_ids();
						
		$this->db->select('receivings.location_id,suspended,receivings.transfer_to_location_id, location_transfer_from.name as transfer_from, location_transfer_to.name as transfer_to,receiving_id, date(receiving_time) as receiving_date, total_quantity_purchased as items_purchased,total_quantity_received as items_received, CONCAT(employee.first_name," ",employee.last_name) as employee_name, CONCAT(supplier.company_name, " (",people.first_name," ",people.last_name, ")") as supplier_name, subtotal, total, tax, sum(profit) as profit, payment_type, comment', false);
		$this->db->from('receivings');
		$this->db->join('locations as location_transfer_from', 'location_transfer_from.location_id = receivings.location_id');
		$this->db->join('locations as location_transfer_to', 'location_transfer_to.location_id = receivings.transfer_to_location_id');
		$this->db->join('people as employee', 'receivings.employee_id = employee.person_id');
		$this->db->join('suppliers as supplier', 'receivings.supplier_id = supplier.person_id', 'left');
		$this->db->join('people as people', 'people.person_id = supplier.person_id', 'left');
		$this->db->where_in('transfer_to_location_id', $location_ids);
		
		$this->receiving_time_where();
		$this->db->where('receivings.deleted', 0);
		$this->db->group_by('receiving_id');
		$this->db->order_by('receiving_time', ($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');

		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			
			$this->db->limit($this->report_limit);
			$this->db->offset(isset($this->params['offset']) ? $this->params['offset'] : 0);
			return $this->db->get()->result_array();
			
		}		
		if (isset($this->params['export_excel']) && $this->params['export_excel'] == 1)
		{
			
			$data=array();
			$data['summary']=array();
			$data['details']=array();
			
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
		
		}
	}
	
	public function getTotalRows()
	{		
		$location_ids = self::get_selected_location_ids();
		
		$this->db->select("COUNT(receiving_id) as receiving_count");
		$this->db->from('receivings');
		$this->db->where_in('transfer_to_location_id', $location_ids);
		
		$this->receiving_time_where();
		$this->db->where('receivings.deleted', 0);
		$ret = $this->db->get()->row_array();
		return $ret['receiving_count'];

	}
	
	public function getSummaryData()
	{
		$location_ids = self::get_selected_location_ids();
		
		$this->db->select('sum(tax) as tax, sum(total) as total', false);
		$this->db->from('receivings');
		$this->db->where_in('transfer_to_location_id', $location_ids);
		
		$this->receiving_time_where();
		$this->db->where('deleted', 0);
		return $this->db->get()->row_array();
	}
	
	function get_report_details($ids, $export_excel=0)
	{
		return $this->get_report_details_recv($ids,$export_excel);
	}
}
?>