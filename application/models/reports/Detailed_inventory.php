<?php
require_once ("Report.php");
class Detailed_inventory extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{				
		$columns = array(array('data'=>lang('common_item_id'), 'align'=> 'left'), array('data'=>lang('reports_date'), 'align' => 'left'), array('data'=>lang('reports_item_name'), 'align' => 'left'), array('data'=>lang('common_customer'), 'align' => 'left'), array('data'=>lang('common_employee'), 'align' => 'left'), array('data'=>lang('common_category'), 'align'=>'left'), array('data'=>lang('common_item_number'), 'align' => 'left'), array('data'=>lang('common_product_id'), 'align' => 'left'),array('data'=>lang('common_size'), 'align'=> 'right'), array('data'=>lang('common_items_in_out_qty'), 'align' => 'left'),array('data'=>lang('common_items_inventory_comments'), 'align' => 'left'));
		
		$location_count = $this->Location->count_all();
		
		if ($location_count > 1)
		{
			array_unshift($columns, array('data'=>lang('common_location'), 'align'=> 'left'));
			
		}
		
		return $columns;
		
	}
	
	public function getInputData()
	{
		$input_data = Report::get_common_report_input_data(TRUE);
				
		$this->load->model('Category');
		$this->load->model('Supplier');
		
		$specific_entity_data['specific_input_name'] = 'item_id';
		$specific_entity_data['specific_input_label'] = lang('common_item');
		$specific_entity_data['search_suggestion_url'] = site_url('reports/item_search');
		$specific_entity_data['view'] = 'specific_entity';
				
		
		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array();
			$input_params[] = array('view' => 'date_range', 'with_time' => TRUE);
			$input_params[] = $specific_entity_data;
			$input_params[] = array('view' => 'checkbox','checkbox_label' => lang('reports_show_manual_adjustments_only'), 'checkbox_name' => 'show_manual_adjustments_only');
			$input_params[] = array('view' => 'locations', 'can_view_inventory_at_all_locations' => $this->Employee->has_module_action_permission('reports','view_inventory_at_all_locations', $this->Employee->get_logged_in_employee_info()->person_id));
			$input_params[] = array('view' => 'excel_export');
			$input_params[] = array('view' => 'submit');
		}
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	 	 
	}
	
	public function getOutputData()
	{
		$this->load->model('Category');
		$this->setupDefaultPagination();
		$tabular_data = array();
		$report_data = $this->getData();
		$location_count = $this->Location->count_all();
		
		foreach($report_data as $row)
		{
			$row['trans_comment'] = preg_replace('/'.$this->config->item('sale_prefix').' ([0-9]+)/', anchor('sales/receipt/$1', $row['trans_comment']), $row['trans_comment']);
			
			$tabular_data_row = array(
				array('data'=>$row['item_id'], 'align'=>'left'),
				array('data'=>date(get_date_format().'-'.get_time_format(), strtotime($row['trans_date'])), 'align'=>'left'),
				array('data'=>$row['name'], 'align'=>'left'),
				array('data'=>$row['customer'], 'align'=>'left'),
				array('data'=>$row['employee'], 'align'=>'left'),
				array('data'=>$this->Category->get_full_path($row['category_id']), 'align'=>'left'),
				array('data'=>$row['item_number'], 'align'=>'left'),
				array('data'=>$row['product_id'], 'align'=>'left'),
				array('data'=>$row['size'], 'align'=>'left'),
				array('data'=>to_quantity($row['trans_inventory']), 'align'=>'left'),
				array('data'=>$row['trans_comment'], 'align'=>'left'),
			); 
			
		
			if ($location_count > 1)
			{
				array_unshift($tabular_data_row, array('data'=>$row['location_name'], 'align'=>'left'));
			}
			
			$tabular_data[] = $tabular_data_row;
			
		}

		$data = array(
			'view' => 'tabular',
			"title" => lang('reports_detailed_inventory_report'),
			"subtitle" => lang('reports_detailed_inventory_report')." - ".date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date']))." - ".$this->getTotalRows().' '.lang('reports_sales_report_generator_results_found'),
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
		$location_ids_string = implode(',',$location_ids);

		$people = $this->db->dbprefix('people');
		
		$this->db->select('CONCAT(emp.first_name," ",emp.last_name) as employee,CONCAT('.$people.'.first_name," ",'.$people.'.last_name) as customer,locations.name as location_name, item_variation_id, inventory.*, items.*, categories.id as category_id,categories.name as category');
		$this->db->from('inventory');
		$this->db->join('people as emp','emp.person_id = trans_user','left');
		$this->db->join('items', 'items.item_id = inventory.trans_items');
		$this->db->join('sales', 'trans_comment LIKE "'.$this->config->item('sale_prefix').'%" and sales.sale_id = CAST(REPLACE(trans_comment,"'.$this->config->item('sale_prefix').' ","") as signed)','left');
		$this->db->join('people','people.person_id = sales.customer_id','left');
		$this->db->join('locations', 'inventory.location_id = locations.location_id');
		$this->db->join('categories', 'items.category_id = categories.id', 'left outer');
		$this->db->where('trans_date BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']));
		$this->db->where('items.deleted', 0);
		$this->db->where('items.system_item',0);
		$this->db->where('trans_inventory !=', 0);
		$this->db->where_in('inventory.location_id', $location_ids);
		$this->db->order_by('trans_date', ($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');
				
		//Hide POS XXX and RECV XXX
		if (isset($this->params['show_manual_adjustments_only']) && $this->params['show_manual_adjustments_only'])
		{
			$sale_prefix = $this->config->item('sale_prefix');
			$recv_prefix = 'RECV';
			
			$this->db->not_like('trans_comment', $sale_prefix, 'after');
			$this->db->not_like('trans_comment', $recv_prefix, 'after');
			
		}
		
		if ($this->params['item_id'])
		{
			$this->db->where('trans_items', $this->params['item_id']);
		}

		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			
			if (isset($this->params['offset']))
			{
				$this->db->offset($this->params['offset']);
			}
		}
		
		
		$return = $this->db->get()->result_array();
		
		$variation_ids = array();
		
		foreach($return as $row)
		{
			$variation_ids[$row['item_variation_id']] = TRUE;
		}
		
		$variation_ids = array_keys($variation_ids);
		$this->load->model('Item_variations');
		
		$variation_results = $this->Item_variations->get_attributes($variation_ids);
		
		$variation_names = array();
		
		foreach($variation_results as $variation_id => $variation_row)
		{
			$variation_names[$variation_id] = implode(', ',array_column($variation_row,'label'));
		}
		
		
		for($k=0;$k<count($return);$k++)
		{
			if ($return[$k]['item_variation_id'])
			{
				$return[$k]['name'].=': '.$variation_names[$return[$k]['item_variation_id']];
			}
		}
		
		
		return $return;
	}
	
	function getTotalRows()
	{
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);

		$this->db->from('inventory');
		$this->db->where('trans_date BETWEEN "'.$this->params['start_date'].'" and "'.$this->params['end_date'].'"');
		$this->db->where_in('inventory.location_id', $location_ids);
		$this->db->where('trans_inventory !=', 0);
		
		//Hide POS XXX and RECV XXX
		if (isset($this->params['show_manual_adjustments_only']) && $this->params['show_manual_adjustments_only'])
		{
			$sale_prefix = $this->config->item('sale_prefix');
			$recv_prefix = 'RECV';
			
			$this->db->not_like('trans_comment', $sale_prefix, 'after');
			$this->db->not_like('trans_comment', $recv_prefix, 'after');
			
		}
		
		if ($this->params['item_id'])
		{
			$this->db->where('trans_items', $this->params['item_id']);
		}
		
		return $this->db->count_all_results();
	}
	
	public function getSummaryData()
	{
		$location_ids = self::get_selected_location_ids();
		
		$this->db->from('sales');
		$this->db->join('sales_items', 'sales_items.sale_id = sales.sale_id');
		
		if(isset($this->params['item_id']) && $this->params['item_id'])
		{
			$sales_items= $this->db->dbprefix('sales_items');
			$this->db->where('sales_items.item_id',$this->params['item_id']);
		}

		$this->db->select('SUM('.$this->db->dbprefix('sales_items').'.quantity_purchased) as quantity_purchased');
		
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('sales.store_account_payment', 0);
		}
		
		
		$this->sale_time_where();
		$this->db->where('sales.deleted', 0);
		
		$divide_by = days_between_dates($this->params['end_date'],$this->params['start_date']);
		$row = $this->db->get()->row_array();
		
		$return = array(
			'average_quantity' => $divide_by ? round($row['quantity_purchased']/$divide_by,2) : 0
		);
				
		return $return;
	}
}
?>