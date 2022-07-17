<?php
require_once ("Report.php");
class Inventory_expire_summary extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		
		$columns = array();
		
		$location_count = $this->Location->count_all();
	
		if ($location_count > 1)
		{
			$columns[] = array('data'=>lang('common_location'), 'align'=> 'left');
		}
		
		$columns[] = array('data'=>lang('reports_item_name'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_expire_date'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_quantity_expiring'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_category'), 'align'=> 'right');
		$columns[] = array('data'=>lang('common_item_number'), 'align'=> 'right');
		$columns[] = array('data'=>lang('common_product_id'), 'align'=> 'right');
		$columns[] = array('data'=>lang('common_size'), 'align'=> 'right');
		if (!$this->config->item('hide_item_descriptions_in_reports') || (isset($this->params['export_excel']) && $this->params['export_excel']))
		{
			$columns[] = array('data'=>lang('reports_description'), 'align'=> 'right');
		}
		
		if($this->has_cost_price_permission)
		{
			$columns[] = array('data'=>lang('common_cost_price'), 'align'=> 'right');
		}

		$columns[] = array('data'=>lang('common_unit_price'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_count'), 'align'=> 'left');
		$columns[] = array('data'=>lang('reports_reorder_level'), 'align'=> 'left');
		
		return $columns;
	}
	
	
	public function getInputData()
	{
	
		$input_params = array();

		if ($this->settings['display'] == 'tabular')
		{
			$input_data = Report::get_simple_date_ranges_expire();
			
			$input_params = array(
				array('view' => 'date_range', 'with_time' => FALSE),
				array('view' => 'excel_export'),
				array('view' => 'locations', 'can_view_inventory_at_all_locations' => $this->Employee->has_module_action_permission('reports','view_inventory_at_all_locations', $this->Employee->get_logged_in_employee_info()->person_id)),
				array('view' => 'submit'),
			);
		}
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}
	
	function getOutputData()
	{
		$this->setupDefaultPagination();
		$this->load->model('Category');
		$tabular_data = array();
		$report_data = $this->getData();
		$location_count = $this->Location->count_all();
		
		foreach($report_data as $row)
		{
			$data_row = array();
			
		
			if ($location_count > 1)
			{
				$data_row[] = array('data'=>$row['location_name'], 'align' => 'left');
			}
			
			$data_row[] = array('data'=>$row['name'].($row['unit_name'] ? ' - '.$row['unit_name'] : ''), 'align' => 'left');
			$data_row[] = array('data'=>date(get_date_format(), strtotime($row['expire_date'])), 'align' => 'left');
			$data_row[] = array('data'=>to_quantity($row['quantity_expiring']), 'align'=> 'left');
			$data_row[] = array('data'=>$this->Category->get_full_path($row['category_id']), 'align'=> 'left');
			$data_row[] = array('data'=>$row['item_number'], 'align'=> 'left');
			$data_row[] = array('data'=>$row['product_id'], 'align'=> 'left');
			$data_row[] = array('data'=>$row['size'], 'align'=> 'left');
			
			if (!$this->config->item('hide_item_descriptions_in_reports') || (isset($this->params['export_excel']) && $this->params['export_excel']))
			{
				$data_row[] = array('data'=>$row['description'], 'align'=> 'left');
			}
			if($this->has_cost_price_permission)
			{
				$data_row[] = array('data'=>to_currency($row['cost_price']), 'align'=> 'right');
			}
			$data_row[] = array('data'=>to_currency($row['unit_price']), 'align'=> 'right');
			$data_row[] = array('data'=>to_quantity($row['quantity']), 'align'=> 'left');
			$data_row[] = array('data'=>to_quantity($row['reorder_level']), 'align'=> 'left');
			
			$tabular_data[] = $data_row;				
			
		}

		$data = array(
			'view' => 'tabular',
			"title" => lang('reports_expired_inventory_report'),
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
		$location_ids_string = implode(',',$location_ids);
		
		$this->db->select('locations.name as location_name, items.name, items_quantity_units.unit_name,SUM(quantity_purchased) as quantity_expiring,items.size,receivings_items.expire_date, categories.id as category_id,categories.name as category, company_name, item_number, product_id, 
		'.$this->db->dbprefix('receivings_items').'.item_unit_price as cost_price, 
		IFNULL('.$this->db->dbprefix('location_items').'.unit_price, '.$this->db->dbprefix('items').'.unit_price) as unit_price,
		SUM(quantity) as quantity, 
		IFNULL('.$this->db->dbprefix('location_items').'.reorder_level, '.$this->db->dbprefix('items').'.reorder_level) as reorder_level, 
		items.description', FALSE);
		$this->db->from('items');
		$this->db->join('receivings_items', 'receivings_items.item_id = items.item_id');
		$this->db->join('items_quantity_units','receivings_items.items_quantity_units_id = items_quantity_units.id','left');
		$this->db->join('receivings', 'receivings_items.receiving_id = receivings.receiving_id');
		$this->db->join('suppliers', 'items.supplier_id = suppliers.person_id', 'left outer');
		$this->db->join('categories', 'items.category_id = categories.id', 'left outer');
		$this->db->join('locations', 'locations.location_id = receivings.location_id');
		$this->db->join('location_items', 'location_items.item_id = items.item_id and location_items.location_id IN ('.$location_ids_string.')', 'left');

		$this->db->where('items.deleted', 0);
		$this->db->where('items.system_item',0);
		
		$this->db->where_in('receivings.location_id', $location_ids);
			
		$this->db->where('receivings_items.expire_date >=', $this->params['start_date']);
		$this->db->where('receivings_items.expire_date <=', $this->params['end_date']);

		$this->db->group_by('receivings_items.receiving_id,receivings_items.item_id,receivings_items.line');
		$this->db->order_by('receivings_items.expire_date');
		
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
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		
		$this->db->select('locations.name as location_name, items.name, SUM(quantity_purchased) as quantity_expiring,items.size,receivings_items.expire_date, categories.id as category_id,categories.name as category, company_name, item_number, product_id, 
		'.$this->db->dbprefix('receivings_items').'.item_unit_price as cost_price, 
		IFNULL('.$this->db->dbprefix('location_items').'.unit_price, '.$this->db->dbprefix('items').'.unit_price) as unit_price,
		SUM(quantity) as quantity, 
		IFNULL('.$this->db->dbprefix('location_items').'.reorder_level, '.$this->db->dbprefix('items').'.reorder_level) as reorder_level, 
		items.description', FALSE);
		$this->db->from('items');
		$this->db->join('receivings_items', 'receivings_items.item_id = items.item_id');
		$this->db->join('receivings', 'receivings_items.receiving_id = receivings.receiving_id');
		$this->db->join('suppliers', 'items.supplier_id = suppliers.person_id', 'left outer');
		$this->db->join('categories', 'items.category_id = categories.id', 'left outer');
		$this->db->join('locations', 'locations.location_id = receivings.location_id');
		$this->db->join('location_items', 'location_items.item_id = items.item_id and location_items.location_id IN ('.$location_ids_string.')', 'left');

		$this->db->where('items.deleted', 0);
		$this->db->where('items.system_item',0);
		
		$this->db->where_in('receivings.location_id', $location_ids);
			
		$this->db->where('receivings_items.expire_date >=', $this->params['start_date']);
		$this->db->where('receivings_items.expire_date <=', $this->params['end_date']);

		$this->db->group_by('receivings_items.receiving_id,receivings_items.item_id,receivings_items.line');
		$this->db->order_by('receivings_items.expire_date');
		
		return $this->db->get()->num_rows();
	}
	
	public function getSummaryData()
	{
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);

		$this->db->select('sum(quantity_purchased) as total_items_in_inventory, sum(IFNULL('.$this->db->dbprefix('receivings_items').'.item_unit_price, '.$this->db->dbprefix('items').'.cost_price) * quantity_purchased) as inventory_total,
		sum(IFNULL('.$this->db->dbprefix('location_items').'.unit_price, '.$this->db->dbprefix('items').'.unit_price) * quantity_purchased) as inventory_sale_total', FALSE);
		$this->db->from('items');
		$this->db->join('receivings_items', 'receivings_items.item_id = items.item_id');
		$this->db->join('receivings', 'receivings_items.receiving_id = receivings.receiving_id');
		$this->db->join('location_items', 'location_items.item_id = items.item_id and location_items.location_id IN ('.$location_ids_string.')', 'left');
		$this->db->join('suppliers', 'items.supplier_id = suppliers.person_id', 'left outer');
		$this->db->where('items.deleted', 0);
		$this->db->where('items.system_item',0);

		$this->db->where('receivings_items.expire_date >=', $this->params['start_date']);
		$this->db->where('receivings_items.expire_date <=', $this->params['end_date']);
		$this->db->where_in('receivings.location_id', $location_ids);
		

		return $this->db->get()->row_array();
	}
}
?>