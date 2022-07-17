<?php
require_once ("Report.php");
class Item_price_history extends Report
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Tier');
		
	}
	
	public function getInputData()
	{
		
		$input_params = array();
		
		$specific_entity_data['specific_input_name'] = 'item_id';
		$specific_entity_data['specific_input_label'] = lang('common_item');
		$specific_entity_data['search_suggestion_url'] = site_url('reports/item_search');
		$specific_entity_data['view'] = 'specific_entity';		
		
		if ($this->settings['display'] == 'tabular')
		{
			$input_data = Report::get_common_report_input_data(TRUE);
			
			$input_params = array(
				array('view' => 'date_range', 'with_time' => TRUE),
				$specific_entity_data,
				array('view' => 'excel_export'),
				array('view' => 'locations'),
				array('view' => 'submit'),
			);
		}				
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}
	
	function getOutputData()
	{
		$location_count = $this->Location->count_all();
		
		$subtitle = date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date']));

		$report_data = $this->getData();
		$summary_data = $this->getSummaryData();
		
		if ($this->settings['display'] == 'tabular')
		{				
			$this->setupDefaultPagination();
			$tabular_data = array();
			
			$index = 0;
			foreach($report_data as $row)
			{
				$data_row = array();
				$data_row[] = array('data'=>date(get_date_format().' '.get_time_format(), strtotime($row['on_date'])), 'align'=>'left');
				$data_row[] = array('data'=>$row['employee'], 'align'=>'left');
				$data_row[] = array('data'=>$row['item_name'], 'align'=>'left');
				$data_row[] = array('data'=>$this->Item_variations->get_variation_name($row['item_variation_id']), 'align'=>'left');
				
				if ($location_count > 1)
				{
					$data_row[] = array('data'=>$row['location_name'], 'align'=>'left');
				}
				$data_row[] = array('data'=>to_currency($row['cost_price']), 'align'=>'right');
				$data_row[] = array('data'=>to_currency($row['unit_price']), 'align'=>'right');
			
				$tabular_data[] = $data_row;
			
				$index++;
			}
					
	 		$data = array(
				'view' => 'tabular',
				"title" => lang('reports_pricing_history'),
				"subtitle" => $subtitle,
				"headers" => $this->getDataColumns(),
				"data" => $tabular_data,
				"summary_data" => $summary_data,
				"export_excel" => $this->params['export_excel'],
				"pagination" => $this->pagination->create_links(),
			);
			
		}
		
		return $data;
	}
	
	public function getDataColumns()
	{
		$location_count = $this->Location->count_all();
		
		$columns = array();
		
		$columns[] = array('data'=>lang('common_date'), 'align'=> 'center');
		$columns[] = array('data'=>lang('common_employee'), 'align'=> 'center');
		$columns[] = array('data'=>lang('common_item_name'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_variation'), 'align'=> 'left');
		
		if ($location_count > 1)
		{
			$columns[] = array('data'=>lang('common_location'), 'align'=> 'left');
		}
		$columns[] = array('data'=>lang('common_cost_price'), 'align'=> 'left');
		$columns[] = array('data'=>lang('common_unit_price'), 'align'=> 'left');
		
		return $columns;		
	}
	
	public function getData()
	{		
		$location_ids = self::get_selected_location_ids();
		
		$this->db->select('employee_person.full_name as employee, locations.name as location_name, items_pricing_history.*,items.name as item_name,item_variations.id as item_variation_id');
		$this->db->from('items_pricing_history');
		$this->db->join('people as employee_person', 'items_pricing_history.employee_id = employee_person.person_id', 'left');
		$this->db->join('locations', 'items_pricing_history.location_id = locations.location_id','left');
		$this->db->join('items','items.item_id=items_pricing_history.item_id');
		$this->db->join('item_variations','items_pricing_history.item_variation_id=item_variations.id','left');
		$this->db->group_start();
		$this->db->where_in('items_pricing_history.location_id', $location_ids);
		$this->db->or_where('items_pricing_history.location_id',NULL);
		$this->db->group_end();
		$this->db->where('on_date BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']));
		$this->db->order_by('on_date', ($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');
		if(isset($this->params['item_id']) && $this->params['item_id'])
		{
			$this->db->where('items_pricing_history.item_id',$this->params['item_id']);
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
		
		return $this->db->get()->result_array();
	}
	
	
	function getTotalRows()
	{
		$location_ids = self::get_selected_location_ids();
		
		$this->db->from('items_pricing_history');
		$this->db->join('items','items.item_id=items_pricing_history.item_id');
		$this->db->join('item_variations','items_pricing_history.item_variation_id=item_variations.id','left');
		$this->db->group_start();
		$this->db->where_in('items_pricing_history.location_id', $location_ids);
		$this->db->or_where('items_pricing_history.location_id',NULL);
		$this->db->group_end();
		$this->db->where('on_date BETWEEN '.$this->db->escape($this->params['start_date']).' and '.$this->db->escape($this->params['end_date']));
				
		if(isset($this->params['item_id']) && $this->params['item_id'])
		{
			$this->db->where('items_pricing_history.item_id',$this->params['item_id']);
		}
		
		return $this->db->count_all_results();
		
	}
	
	public function getSummaryData()
	{
		return array();
	}

}
?>