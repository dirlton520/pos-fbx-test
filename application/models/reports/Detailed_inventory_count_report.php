<?php
require_once ("Report.php");
class Detailed_inventory_count_report extends Report
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
	
		if ($location_count > 1)
		{
			$return['summary'][] = array('data'=>lang('common_location'), 'align'=> 'left');
		}
		$return['summary'][] = array('data'=>lang('reports_count_date'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_status'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_employee'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_number_items_counted'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_amount_over_under_from_actual_on_hand'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_total_difference'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_comments'), 'align'=> 'left');		

		$return['details'] = array();
		$return['details'][] = array('data'=>lang('common_item_id'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('common_item_number'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('common_product_id'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('reports_name'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('reports_category'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('common_size'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('common_count'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('reports_actual_count'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('common_cost_price'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('reports_difference'), 'align'=> 'left');
		$return['details'][] = array('data'=>lang('common_comments'), 'align'=> 'left');
		
		return $return;	
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
		$this->load->model('Category');
		$this->setupDefaultPagination();
		$headers = $this->getDataColumns();
		$report_data = $this->getData();

		$summary_data = array();
		$details_data = array();
		$location_count = $this->Location->count_all();
		
		foreach($report_data['summary'] as $key=>$row)
		{
			$status = '';
			switch($row['status'])
			{
				case 'open':
					$status = lang('common_open');
				break;
	
				case 'closed':
					$status = lang('common_closed');
				break;
			}
			
			$summary_data_row = array(
				array('data'=>date(get_date_format().' '.get_time_format(), strtotime($row['count_date'])), 'align'=>'left'), 
				array('data'=>$status, 'align'=>'left'), 
				array('data'=>$row['employee_name'], 'align'=>'left'), 
				array('data'=>to_quantity($row['items_counted']), 'align'=>'left'), 
				array('data'=>to_quantity($row['difference']), 'align'=>'left'), 
				array('data'=>to_currency($row['cost_price_difference']), 'align'=>'left'), 
				array('data'=>$row['comment'], 'align'=>'left'), 
			);	
		
			if ($location_count > 1)
			{
				array_unshift($summary_data_row, array('data'=>$row['location_name'], 'align'=>'left'));
			}
			
			$summary_data[$key] = $summary_data_row;
			
			foreach($report_data['details'][$key] as $drow)
			{
				$details_data_row = array();
				$details_data_row[] = array('data'=>$drow['item_id'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['item_number'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['product_id'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['name'], 'align'=>'left');
				$details_data_row[] = array('data'=>$this->Category->get_full_path($drow['category_id']), 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['size'], 'align'=>'left');
				$details_data_row[] = array('data'=>to_quantity($drow['count']), 'align'=>'left');
				$details_data_row[] = array('data'=>to_quantity($drow['actual_quantity']), 'align'=>'left');
				$details_data_row[] = array('data'=>to_currency($drow['cost_price']), 'align'=>'left');
				$details_data_row[] = array('data'=>to_currency($drow['cost_price_difference']), 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['comment'], 'align'=>'left');
				$details_data[$key][] = $details_data_row;
			}
			
		}

		$data = array(
			"view" => 'tabular_details',
			"title" =>lang('reports_detailed_count_report'),
			"subtitle" => date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date'])),
			"headers" => $headers,
			"summary_data" => $summary_data,
			"details_data" => $details_data,
			"overall_summary_data" => $this->getSummaryData(),
			"export_excel" => $this->params['export_excel'],
			"pagination" => $this->pagination->create_links(),
		);
		
		return $data;
	}
	
	public function getData()
	{
		$data = array();
		$data['summary'] = array();
		$data['details'] = array();
		
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);

		$this->db->select('locations.name as location_name, inventory_counts.id, inventory_counts.comment, count_date, CONCAT(`first_name`, " ", `last_name`) as employee_name, status, SUM(count) - SUM(actual_quantity) as difference, COUNT(*) as items_counted,SUM(cost_price * count) - SUM(cost_price * actual_quantity) as cost_price_difference', false);
		$this->db->from('inventory_counts');
		$this->db->join('locations', 'inventory_counts.location_id = locations.location_id');
		$this->db->join('inventory_counts_items', 'inventory_counts.id = inventory_counts_items.inventory_counts_id');
		$this->db->join('items', 'inventory_counts_items.item_id = items.item_id');
		$this->db->join('employees', 'employees.person_id = inventory_counts.employee_id');
		$this->db->join('people', 'employees.person_id = people.person_id');
		$this->db->where('count_date BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']. ' 23:59:59').' and inventory_counts.location_id IN('.$location_ids_string.')');
		$this->db->group_by('inventory_counts_id');
		$this->db->order_by('count_date',($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');
		
		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			
			if(isset($this->params['offset']))
			{
				$this->db->offset($this->params['offset']);
			}
		}

		foreach($this->db->get()->result_array() as $summary_row)
		{
			$data['summary'][$summary_row['id']] = $summary_row; 
		}		
		
		$this->db->select('item_variation_id,inventory_counts_items.*, items.*, categories.id as category_id,categories.name as category, (cost_price * count) - (cost_price * actual_quantity) as cost_price_difference', false);
		$this->db->from('inventory_counts_items');
		$this->db->join('inventory_counts', 'inventory_counts.id = inventory_counts_items.inventory_counts_id');
		$this->db->join('items', 'items.item_id = inventory_counts_items.item_id');
		$this->db->join('categories', 'categories.id = items.category_id','left');
		$this->db->where('count_date BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']. ' 23:59:59').' and inventory_counts.location_id IN('.$location_ids_string.')');
		$this->db->order_by('count_date',($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');
		$variation_ids = array();
		
		foreach($this->db->get()->result_array() as $inventory_count_items_row)
		{
			$data['details'][$inventory_count_items_row['inventory_counts_id']][] = $inventory_count_items_row;
			$variation_ids[$inventory_count_items_row['item_variation_id']] = TRUE;
			
		}
		$variation_ids = array_keys($variation_ids);
		$this->load->model('Item_variations');
		$variation_results = $this->Item_variations->get_attributes($variation_ids);
		
		$variation_names = array();
		
		foreach($variation_results as $variation_id => $variation_row)
		{
			$variation_names[$variation_id] = implode(', ',array_column($variation_row,'label'));
		}
		
		foreach($data['details'] as $inventory_count_id=>$inventory_count_items_row)
		{
				for($k=0;$k<count($data['details'][$inventory_count_id]);$k++)
				{
					if ($data['details'][$inventory_count_id][$k]['item_variation_id'])
					{
						$data['details'][$inventory_count_id][$k]['name'].=': '.$variation_names[$data['details'][$inventory_count_id][$k]['item_variation_id']];
					}					
				}
		}
				
		return $data;
	}
	
	public function getSummaryData()
	{
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		
		$this->db->select('COUNT(*) as number_items_counted,SUM(cost_price * count) - SUM(cost_price * actual_quantity) as total_difference', false);
		$this->db->from('inventory_counts');
		$this->db->join('inventory_counts_items', 'inventory_counts.id = inventory_counts_items.inventory_counts_id');
		$this->db->join('items', 'inventory_counts_items.item_id = items.item_id');
		$this->db->where('count_date BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']. ' 23:59:59').' and inventory_counts.location_id IN('.$location_ids_string.')');
		$this->db->order_by('count_date',($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');
		
		return $this->db->get()->row_array();
	}
	
	function getTotalRows()
	{
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		$this->db->from('inventory_counts');
		$this->db->join('inventory_counts_items', 'inventory_counts.id = inventory_counts_items.inventory_counts_id');
		$this->db->join('employees', 'employees.person_id = inventory_counts.employee_id');
		$this->db->join('people', 'employees.person_id = people.person_id');
		$this->db->where('count_date BETWEEN '. $this->db->escape($this->params['start_date']). ' and '. $this->db->escape($this->params['end_date']. ' 23:59:59').' and inventory_counts.location_id IN('.$location_ids_string.')');
		$this->db->group_by('inventory_counts_id');
				
		return $this->db->get()->num_rows();
	}
	
}
?>