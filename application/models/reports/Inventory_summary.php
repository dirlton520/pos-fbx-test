<?php
require_once ("Report.php");
class Inventory_summary extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getInputData()
	{
		$input_data = Report::get_common_report_input_data(TRUE);
				
		$this->load->model('Category');
		$this->load->model('Supplier');
		
		$supplier_entity_data = array();
		$supplier_entity_data['specific_input_name'] = 'supplier';
		$supplier_entity_data['specific_input_label'] = lang('reports_supplier');
		$supplier_entity_data['view'] = 'specific_entity';

		$suppliers = array();
		
		$suppliers[] = lang('common_all');
		foreach($this->Supplier->get_all()->result() as $supplier)
		{
			$suppliers[$supplier->person_id] = $supplier->company_name. ' ('.$supplier->first_name .' '.$supplier->last_name.')';
		}
		
		$supplier_entity_data['specific_input_data'] = $suppliers;
		
		$category_entity_data = array();
		$category_entity_data['specific_input_name'] = 'category_id';
		$category_entity_data['specific_input_label'] = lang('reports_category');
		$category_entity_data['view'] = 'specific_entity';
		
		$categories = array();
		$categories[] =lang('common_all');
		
		$categories_phppos= $this->Category->sort_categories_and_sub_categories($this->Category->get_all_categories_and_sub_categories());
		
		foreach($categories_phppos as $key=>$value)
		{
			$name = $this->config->item('show_full_category_path') ? str_repeat('&nbsp;&nbsp;', $value['depth']).$this->Category->get_full_path($key) : str_repeat('&nbsp;&nbsp;', $value['depth']).$value['name'];
			$categories[$key] = $name;
		}
		
		$category_entity_data['specific_input_data'] = $categories;
		
		$specific_entity_data['specific_input_name'] = 'customer_id';
		$specific_entity_data['specific_input_label'] = lang('reports_customer');
		$specific_entity_data['search_suggestion_url'] = site_url('reports/customer_search/1');
		$specific_entity_data['view'] = 'specific_entity';
		
		
		if ($this->settings['display'] == 'tabular')
		{
			$input_params = array();
			$input_params[] = $supplier_entity_data;
			$input_params[] = $category_entity_data;
			$input_params[] = array('view' => 'dropdown','dropdown_label' =>lang('common_inventory'),'dropdown_name' => 'inventory','dropdown_options' =>array('all' => lang('common_all'), 'in_stock' => lang('reports_in_stock'), 'out_of_stock' => lang('reports_out_of_stock')),'dropdown_selected_value' => '');
			$input_params[] = array('view' => 'checkbox','checkbox_label' =>lang('reports_show_pending_only') ,'checkbox_name' => 'show_only_pending');
			$input_params[] = array('view' => 'checkbox','checkbox_label' =>lang('reports_show_negative_inventory_only') ,'checkbox_name' => 'show_negative_inventory_only');
			$input_params[] = array('view' => 'checkbox','checkbox_label' => lang('reports_list_each_location_separately'), 'checkbox_name' => 'list_each_location_separately');			
			$input_params[] = array('view' => 'locations', 'can_view_inventory_at_all_locations' => $this->Employee->has_module_action_permission('reports','view_inventory_at_all_locations', $this->Employee->get_logged_in_employee_info()->person_id));
			$input_params[] = array('view' => 'text','label' => lang('common_item_name'),'name' => 'item_name','default' => '');
			$input_params[] = array('view' => 'excel_export');
			$input_params[] = array('view' => 'submit');
		}

		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		//echo print_r($input_data);exit;
		return $input_data;
	 	 
	
	    
	}
	
	public function getOutputData()
	{
				
		$this->lang->load('error');
		$this->load->model('Category');
		// Note: Moved to getData after the get
		// $this->setupDefaultPagination();
		
		$summary_data = array();
		$variation_quantity_summary_data = array();
		$report_data = $this->getData();

		// echo '<pre>';print_r($report_data);exit;
		$details_data = array();
		$details_quantity_data = array(); 
		foreach ($report_data['details'] as $drow)
		{
			$details_data_row = array();

			$details_data_row[] = array('data'=>$drow['variation_id'], 'align' => 'left');			
			$details_data_row[] = array('data'=>$drow['name'], 'align' => 'left');
			$details_data_row[] = array('data'=>$drow['item_number'], 'align'=> 'left');
			$details_data_row[] = array('data'=>to_quantity($drow['quantity']), 'align'=> 'left');
			// Added: 2022-03-09 Milc cost_price and unit_price DATA columns
			$details_data_row[] = array('data'=>to_currency($drow['cost_price']), 'align'=> 'left');
      $details_data_row[] = array('data'=>to_currency($drow['unit_price']), 'align'=> 'left');

			$details_data_row[] = array('data'=>to_quantity($drow['pending_inventory']), 'align'=> 'left');
			$details_data_row[] = array('data'=>to_quantity($drow['reorder_level']), 'align'=> 'left');
			$details_data_row[] = array('data'=>to_quantity($drow['replenish_level']), 'align'=> 'left');
			
			if ($drow['replenish_level'])
			{
				$details_data_row[] = array('data'=>to_quantity($drow['replenish_level'] - $drow['quantity']), 'align'=> 'left');
			}
			else
			{
				$details_data_row[] = array('data'=>lang('error_unknown'), 'align'=> 'left');
			}
			$details_data[$drow['item_id'].'|'.$drow['location_id']][] = $details_data_row;
			
			$details_quantity_data[$drow['item_id'].'|'.$drow['location_id']][] = array(
				'quantity' => $drow['quantity'],
				'pending_inventory' => $drow['pending_inventory'],
				'reorder_level' => $drow['reorder_level'],
				'replenish_level' => $drow['replenish_level'],
			);
		}
		
		foreach(array_keys($details_data) as $item_location_id)
		{
			list($item_id,$location_id) = explode('|',$item_location_id);
			$item_quantity = 0;
			$item_pending_inventory = 0;
			$item_reorder_level = 0;
			$item_replenish_level = 0;
			
			for($k=0;$k<count($details_data[$item_id.'|'.$location_id]);$k++)
			{
				$item_quantity+=$details_quantity_data[$item_id.'|'.$location_id][$k]['quantity'];
				$item_pending_inventory+=$details_quantity_data[$item_id.'|'.$location_id][$k]['pending_inventory'];
				$item_reorder_level+=$details_quantity_data[$item_id.'|'.$location_id][$k]['reorder_level'];
				$item_replenish_level+=$details_quantity_data[$item_id.'|'.$location_id][$k]['replenish_level'];
			}
			
			$variation_quantity_summary_data[$item_id.'|'.$location_id] = array(
				'quantity' => $item_quantity,
				'pending_inventory' => $item_pending_inventory,
				'reorder_level' => $item_reorder_level,
				'replenish_level' => $item_replenish_level,
			);
			
		}
		
		foreach($report_data['summary'] as $row)
		{
			$data_row = array();
			if (isset($this->params['list_each_location_separately']) && $this->params['list_each_location_separately'])
			{
				$data_row[] = array('data'=>$row['location_name'], 'align'=>'left');					
			}

			$data_row[] = array('data'=>$row['item_id'], 'align' => 'left');			
			$data_row[] = array('data'=>$row['name'], 'align' => 'left');
			$data_row[] = array('data'=>$this->Category->get_full_path($row['category_id']), 'align'=> 'left');
			$data_row[] = array('data'=>$row['company_name'], 'align'=> 'left');
			$data_row[] = array('data'=>$row['item_number'], 'align'=> 'left');
			$data_row[] = array('data'=>$row['product_id'], 'align'=> 'left');
			if (!$this->config->item('hide_item_descriptions_in_reports') || (isset($this->params['export_excel']) && $this->params['export_excel']))
			{
				$data_row[] = array('data'=>$row['description'], 'align'=> 'left');
			}
			
			$data_row[] = array('data'=>$row['size'], 'align'=> 'left');
			if($this->has_cost_price_permission)
			{
				$data_row[] = array('data'=>to_currency($row['cost_price']), 'align'=> 'right');
			}
			$data_row[] = array('data'=>to_currency($row['unit_price']), 'align'=> 'right');
			$qty = isset($variation_quantity_summary_data[$row['item_id']]['quantity']) ? $variation_quantity_summary_data[$row['item_id']]['quantity'] : $row['quantity'];
			$data_row[] = array('data'=>to_quantity($qty), 'align'=> 'left');
			$data_row[] = array('data'=>to_currency($row['cost_price']*$qty), 'align'=> 'right');
			$data_row[] = array('data'=>to_currency($row['unit_price']*$qty), 'align'=> 'right');
			
			$data_row[] = array('data'=>to_quantity(isset($variation_quantity_summary_data[$row['item_id']]['pending_inventory']) ? $variation_quantity_summary_data[$row['item_id']]['pending_inventory'] : $row['pending_inventory']), 'align'=> 'left');
			//$data_row[] = array('data'=>to_quantity(isset($variation_quantity_summary_data[$row['item_id']]['reorder_level']) ? $variation_quantity_summary_data[$row['item_id']]['reorder_level'] : $row['reorder_level']), 'align'=> 'left');
			$data_row[] = array('data'=>to_quantity(isset($variation_quantity_summary_data[$row['item_id']]['reorder_level']) ? $row['reorder_level'] : $row['reorder_level']), 'align'=> 'left');

			$data_row[] = array('data'=>to_quantity(isset($variation_quantity_summary_data[$row['item_id']]['replenish_level']) ? $variation_quantity_summary_data[$row['item_id']]['replenish_level'] : $row['replenish_level']), 'align'=> 'left');
			
			$quantity = isset($variation_quantity_summary_data[$row['item_id']]['quantity']) ? $variation_quantity_summary_data[$row['item_id']]['quantity'] : $row['quantity'];
			$replenish_level = isset($variation_quantity_summary_data[$row['item_id']]['replenish_level']) ? $variation_quantity_summary_data[$row['item_id']]['replenish_level'] : $row['replenish_level'];

			if ($replenish_level && ($replenish_level - $quantity) > 0)
			{
				$data_row[] = array('data'=>to_quantity($replenish_level - $quantity), 'align'=> 'right');				
			}
			else
			{
				$data_row[] = array('data'=>lang('error_unknown'), 'align'=> 'right');				
			}
			$summary_data[$row['item_id'].'|'.$row['location_id']] = $data_row;
			
		}
		

		$data = array(
			"view" =>'tabular_details',
			"title" => lang('reports_inventory_summary_report'),
			"subtitle" => '',
			"headers" => $this->getDataColumns(),
			"summary_data" => $summary_data,
			"overall_summary_data" => $this->getSummaryData(),
			"export_excel" => $this->params['export_excel'],
			"pagination" => $this->pagination->create_links(),
		);
		isset($details_data) && !empty($details_data) ? $data["details_data"]=$details_data: '' ;

		return $data;
	}
	
	
	public function getDataColumns()
	{
		
		$columns = array();
		
		if (isset($this->params['list_each_location_separately']) && $this->params['list_each_location_separately'])
		{
			$columns['summary'][] = array('data'=>lang('common_location'), 'align'=> 'left');			
		}
		
		$columns['summary'][] = array('data'=>lang('common_item_id'), 'align'=> 'left');
		$columns['summary'][] = array('data'=>lang('reports_item_name'), 'align'=> 'left');
		$columns['summary'][] = array('data'=>lang('common_category'), 'align'=> 'right');
		$columns['summary'][] = array('data'=>lang('common_supplier'), 'align'=> 'right');
		$columns['summary'][] = array('data'=>lang('common_item_number'), 'align'=> 'right');
		$columns['summary'][] = array('data'=>lang('common_product_id'), 'align'=> 'right');
		if (!$this->config->item('hide_item_descriptions_in_reports') || (isset($this->params['export_excel']) && $this->params['export_excel']))
		{
			$columns['summary'][] = array('data'=>lang('reports_description'), 'align'=> 'right');
		}
		
		$columns['summary'][] = array('data'=>lang('common_size'), 'align'=> 'right');

		if($this->has_cost_price_permission)
		{
			$columns['summary'][] = array('data'=>lang('common_cost_price'), 'align'=> 'right');
		}

		$columns['summary'][] = array('data'=>lang('common_unit_price'), 'align'=> 'left');
		$columns['summary'][] = array('data'=>lang('common_count'), 'align'=> 'left');
		$columns['summary'][] = array('data'=>lang('reports_inventory_total'), 'align'=> 'left');
		$columns['summary'][] = array('data'=>lang('reports_inventory_sale_total'), 'align'=> 'left');
		$columns['summary'][] = array('data'=>lang('reports_pending_inventory'), 'align'=> 'left');
		$columns['summary'][] = array('data'=>lang('reports_reorder_level'), 'align'=> 'left');
		$columns['summary'][] = array('data'=>lang('common_replenish_level'), 'align'=> 'left');
		$columns['summary'][] = array('data'=>lang('reports_order_amount'), 'align'=> 'left');

		$columns['details'][] = array('data'=>lang('common_item_id').'/'.lang('common_variation_id'), 'align'=> 'left');
		$columns['details'][] = array('data'=>lang('reports_item_name').'/'.lang('common_variation'), 'align'=> 'left');
		$columns['details'][] = array('data'=>lang('common_item_number'), 'align'=> 'right');
		$columns['details'][] = array('data'=>lang('common_count'), 'align'=> 'left');
		// Added: 2022-03-09 Milc cost_price and unit_price HEADER columns
		$columns['details'][] = array('data'=>lang('common_cost_price'), 'align'=> 'left');
		$columns['details'][] = array('data'=>lang('common_unit_price'), 'align'=> 'left');

		$columns['details'][] = array('data'=>lang('reports_pending_inventory'), 'align'=> 'left');
		$columns['details'][] = array('data'=>lang('reports_reorder_level'), 'align'=> 'left');
		$columns['details'][] = array('data'=>lang('common_replenish_level'), 'align'=> 'left');
		$columns['details'][] = array('data'=>lang('reports_order_amount'), 'align'=> 'left');
		
		return $columns;
	}
	
	public function getData()
	{
		
		$query = $this->dataQuery();
		
		//If we are exporting NOT exporting to excel make sure to use offset and limit
    	$limit = '';
		if ((isset($this->params['export_excel']) && !$this->params['export_excel']) && !isset($this->params['show_only_pending']))
		{
			// $this->db->limit($this->report_limit);
			$limit .= ' LIMIT ' . $this->report_limit;
			if (isset($this->params['offset']))
			{
				$limit .= ' OFFSET ' . $this->params['offset'];
			}
		}
		$query = $query . $limit;
		$inventory_result = $this->db->query($query)->result_array();
		$this->report_count = $this->count_last_query_results();

    	$this->setupDefaultPagination();

		// echo '<pre>';print_r($this->db->last_query());exit; //milc

		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		$item_ids = array();

		// Get Suspended Items
		$this->db->select('item_id, quantity_purchased - quantity_received as pending_inventory', false);
		$this->db->from('receivings_items');
		$this->db->join('receivings', 'receivings.receiving_id = receivings_items.receiving_id');
		$this->db->where('receivings.deleted', 0);
		$this->db->where('receivings.suspended', 1);
		$this->db->where_in('location_id', $location_ids);

		$pending_inventory_result = $this->db->get()->result_array();

    // echo '<pre>';print_r($this->db->last_query());exit; //milc

		for($k=0;$k<count($inventory_result);$k++)
		{
			$inventory_result[$k]['pending_inventory'] = 0;
			$item_ids[] = $inventory_result[$k]['item_id'];
		}

		for($k=0;$k<count($pending_inventory_result);$k++)
		{
			$item_id = $pending_inventory_result[$k]['item_id'];
			$pending_inventory = $pending_inventory_result[$k]['pending_inventory'];

			for($i=0;$i<count($inventory_result);$i++)
			{
				if ($inventory_result[$i]['item_id'] == $item_id)
				{
					$inventory_result[$i]['pending_inventory'] += $pending_inventory;
					break;
				}
			}
		}

		if (isset($this->params['show_only_pending']))
		{
			foreach($inventory_result as $key=>$value)
			{
				if($value['pending_inventory'] <= 0)
				{
					unset($inventory_result[$key]);
				}
			}

			//Fix any missing holes...not really needed but looks better
			$inventory_result = array_values($inventory_result);
		}


		if ($this->params['category_id'])
		{
			if ($this->config->item('include_child_categories_when_searching_or_reporting'))
			{
				$category_ids = $this->Category->get_category_id_and_children_category_ids_for_category_id($this->params['category_id']);
			}
			else
			{
				$category_ids = array($this->params['category_id']);
			}
		}
	    
				
					if (isset($this->params['list_each_location_separately']) && $this->params['list_each_location_separately'])
					{
						$group_by_var = '`phppos_item_variations`.`id`,phppos_location_item_variations.location_id ';
					}
					else
					{
						$group_by_var = '`phppos_item_variations`.`id` ';
					}
				
	        $item_ids_string = implode(',',$item_ids);
	        $items_in = (!empty($item_ids))?'AND ( `phppos_items`.`item_id` IN('.$item_ids_string.') ) ':'';

    // Variations
    // TODO: Milc: Made change to next Q, adding: WHERE X.location_id is not null
		    $query = 'SELECT X.*,Z.name
                    FROM
                    (
                      SELECT
                        phppos_item_variations.id as variation_id, phppos_location_item_variations.location_id as location_id, phppos_items.item_id,
                        phppos_categories.id as category_id, phppos_categories.name as category, company_name,
                        phppos_item_variations.item_number, size, product_id,
                        COALESCE(phppos_location_item_variations.cost_price, phppos_item_variations.cost_price, phppos_items.cost_price, 0) as cost_price,
                        COALESCE(phppos_location_item_variations.unit_price, phppos_item_variations.unit_price, phppos_items.unit_price, 0) as unit_price,
                        -- SUM( DISTINCT(phppos_location_item_variations.quantity)) as quantity,
                        -- Next is correct; Milan 2020-05-14
                        SUM(COALESCE(phppos_location_item_variations.quantity, 0)) AS quantity,
                        COALESCE(phppos_location_item_variations.reorder_level, phppos_item_variations.reorder_level, phppos_items.reorder_level) as reorder_level,
                        COALESCE(phppos_location_item_variations.replenish_level, phppos_item_variations.replenish_level, phppos_items.replenish_level) as replenish_level, description

                        -- Next is correct; Milan 2021-01-16 - Note, the chain is build starting with phppos_location_item_variations, and not phppos_item_variations
                        FROM phppos_location_item_variations
                        join phppos_item_variations on phppos_item_variations.id = phppos_location_item_variations.item_variation_id
                        join phppos_locations ON phppos_location_item_variations.location_id = phppos_locations.location_id

                        JOIN phppos_items ON phppos_items.item_id=phppos_item_variations.item_id
                        LEFT OUTER JOIN phppos_suppliers ON phppos_items.supplier_id = phppos_suppliers.person_id
                        LEFT OUTER JOIN phppos_categories ON phppos_items.category_id = phppos_categories.id

                      WHERE phppos_item_variations.deleted = 0
                        '.$items_in.'
                        and phppos_location_item_variations.location_id IN ('.$location_ids_string.')
                      GROUP BY '.$group_by_var.'
                    ) X
                    
                    LEFT JOIN (
                      SELECT
                        `phppos_item_variations`.`id`
                        ,GROUP_CONCAT(DISTINCT phppos_attributes.name, ": ", phppos_attribute_values.name SEPARATOR ", ") as name
                      FROM `phppos_item_variations`
                      JOIN `phppos_item_variation_attribute_values` ON `phppos_item_variations`.`id` = `phppos_item_variation_attribute_values`.`item_variation_id`
                      JOIN `phppos_attribute_values` ON `phppos_attribute_values`.`id` = `phppos_item_variation_attribute_values`.`attribute_value_id`
                      JOIN `phppos_attributes` ON `phppos_attributes`.`id` = `phppos_attribute_values`.`attribute_id`
                      WHERE `phppos_item_variations`.`deleted` = 0
                      GROUP BY `phppos_item_variations`.`id`
                    ) Z ON X.variation_id=Z.ID
                    WHERE X.location_id is not null';
                    $inventory_result_variations = $this->db->query($query)->result_array();
    // echo '<pre>';print_r($query);exit; //milc
	    
		// Get Suspended Items Again for variations
		$this->db->select('item_id, item_variation_id, quantity_purchased - quantity_received as pending_inventory', false);
		$this->db->from('receivings_items');
		$this->db->join('receivings', 'receivings.receiving_id = receivings_items.receiving_id');
		$this->db->where('receivings.deleted', 0);
		$this->db->where('receivings.suspended', 1);
		$this->db->where_in('location_id', $location_ids);

		$pending_inventory_result = $this->db->get()->result_array();
    //echo '<pre>';print_r($this->db->last_query());exit; //milc

		for($k=0;$k<count($inventory_result_variations);$k++)
		{
			$inventory_result_variations[$k]['pending_inventory'] = 0;
		}

		for($k=0;$k<count($pending_inventory_result);$k++)
		{
			$item_id = $pending_inventory_result[$k]['item_id'];
			$variation_id = $pending_inventory_result[$k]['item_variation_id'];
			$pending_inventory = $pending_inventory_result[$k]['pending_inventory'];

			for($i=0;$i<count($inventory_result_variations);$i++)
			{
				if ($inventory_result_variations[$i]['item_id'] == $item_id && $inventory_result_variations[$i]['variation_id'] == $variation_id)
				{
					$inventory_result_variations[$i]['pending_inventory'] += $pending_inventory;
					break;
				}
			}
		}

		if (isset($this->params['show_only_pending']))
		{
			foreach($inventory_result_variations as $key=>$value)
			{
				if($value['pending_inventory'] <= 0)
				{
					unset($inventory_result_variations[$key]);
				}
			}

			//Fix any missing holes...not really needed but looks better
			$inventory_result_variations = array_values($inventory_result_variations);
		}
		
		
		//echo '<pre>';print_r($inventory_result);exit; //milc
		//echo '<pre>';print_r($inventory_result_variations);exit; //milc
		return array('summary' => $inventory_result, 'details' => $inventory_result_variations);
		
	}

	// TODO: $this->count_last_query_results
	function getTotalRows()
	{
		return $this->report_count;
	}

	// dataQuery, redesigned to properly account for items/variations entity schema design
	// milc, 2021-01-14
	// TODO: Cleanup codeigniter old commented code
	private function dataQuery()
	{
		$location_id = '';
		$location_ids = self::get_selected_location_ids();
		// $location_ids_string = implode(',',$location_ids);
    if (!empty($location_ids))
    {
      // location_id
      $location_id = 'and COALESCE(q.location_id, v.location_id, null) in ('.implode(',', $location_ids).')';
    }

		$category_id = '';
		if ($this->params['category_id'])
		{
			if ($this->config->item('include_child_categories_when_searching_or_reporting'))
			{
				$category_ids = $this->Category->get_category_id_and_children_category_ids_for_category_id($this->params['category_id']);
			}
			else
			{
				$category_ids = array($this->params['category_id']);
			}
      if (!empty($category_ids))
      {
				$category_id = 'and phppos_categories.id in ('.implode(',', $category_ids).')';
      }
		}

    $group_by = '';
    //-- group by phppos_items.item_id
    //-- group by phppos_items.item_id, v.item_variation_id, location_id
		if (isset($this->params['list_each_location_separately']) && $this->params['list_each_location_separately'])
		{
			// $this->db->group_by('items.item_id,item_variations.id,locations.location_id');
			$group_by = 'group by phppos_items.item_id, location_id';
		}
		else
		{
			// $this->db->group_by('items.item_id');
			$group_by = 'group by phppos_items.item_id';
		}

    $supplier = '';
		if ($this->params['supplier'])
		{
			// $this->db->where('suppliers.person_id', $this->params['supplier']);
		  $supplier = 'and phppos_suppliers.person_id = ' . $this->db->escape($this->params['supplier']);
		}

    $item_name = '';
		if (isset($this->params['item_name']) && $this->params['item_name'])
		{
			// $this->db->like('items.name',$this->params['item_name'],'both');
		  // -- and phppos_items.name like '%605PY- YOUTH ELASTIC BOTTOM BASEBALL PANT%'
		  $item_name = 'and phppos_items.name like "%'.$this->db->escape_like_str($this->params['item_name']).'%"';
		}

    $having = '';
    if (isset($this->params['inventory']))
    {
		  if ($this->params['inventory'] == 'in_stock')
		  {
  			// $this->db->having($sum_query.' > 0');
  			$having = 'HAVING quantity > 0';
  		}

      if ($this->params['inventory'] == 'out_of_stock')
      {
        // $this->db->having($sum_query.' <= 0');
        $having = 'HAVING quantity <= 0';
      }
    }

		if (isset($this->params['show_negative_inventory_only']) && $this->params['show_negative_inventory_only'])
		{
			// $this->db->having($sum_query.' < 0');
			$having = 'HAVING quantity < 0';
		}

	  $query = '
      select SQL_CALC_FOUND_ROWS 1 as _h
        , COALESCE(q.location_name, v.location_name, null) as location_name
        , COALESCE(q.location_id, v.location_id, null) as location_id
        , phppos_items.item_id, phppos_items.name
        , phppos_categories.id as category_id
        , phppos_categories.name as category
        , company_name, phppos_items.item_number as item_number, size, product_id
        -- Added: Milc 2022-03-09 Changed to WEIGHTED AVG prices (cost_price and unit_price)
        , SUM(COALESCE(q_cost_price, v_cost_price, phppos_items.cost_price, 0) * COALESCE(q.quantity, variation_quantity, 0)) / SUM(COALESCE(q.quantity, variation_quantity, 0)) as cost_price
        , SUM(COALESCE(q_unit_price, v_unit_price, phppos_items.unit_price, 0) * COALESCE(q.quantity, variation_quantity, 0)) / SUM(COALESCE(q.quantity, variation_quantity, 0)) as unit_price
        -- Old formula: Takes first price
        -- , COALESCE(q.quantity, variation_quantity, 0)) as cost_price
        -- , COALESCE(q_unit_price, v_unit_price, phppos_items.unit_price, 0) as unit_price
        , SUM(COALESCE(q.quantity, variation_quantity, 0)) as quantity
        , COALESCE(q_reorder_level, v_reorder_level, phppos_items.reorder_level) as reorder_level
        , COALESCE(q_replenish_level, v_replenish_level, phppos_items.replenish_level) as replenish_level
        , description
      from phppos_items

      left join (
        select phppos_location_items.item_id, phppos_location_items.location_id
            ,GROUP_CONCAT(DISTINCT phppos_locations.name SEPARATOR ", ") as location_name
            ,sum(quantity) quantity
          ,coalesce(phppos_location_items.cost_price, phppos_items.cost_price, null) q_cost_price
          ,coalesce(phppos_location_items.unit_price, phppos_items.unit_price, null) q_unit_price
            ,coalesce(phppos_location_items.reorder_level, phppos_items.reorder_level, null) q_reorder_level
            ,coalesce(phppos_location_items.replenish_level, phppos_items.replenish_level, null) q_replenish_level
        from phppos_location_items
        join phppos_items on phppos_items.item_id = phppos_location_items.item_id
        join phppos_locations ON phppos_location_items.location_id = phppos_locations.location_id
        where phppos_location_items.item_id not in (select item_id from phppos_item_variations)
          group by item_id, location_id
      ) q on phppos_items.item_id=q.item_id

      left JOIN (
        SELECT phppos_item_variations.item_id, item_variation_id, phppos_location_item_variations.location_id
          ,GROUP_CONCAT(DISTINCT phppos_locations.name SEPARATOR ", ") as location_name
          ,sum(phppos_location_item_variations.quantity) variation_quantity
          ,coalesce(phppos_location_item_variations.cost_price, phppos_item_variations.cost_price, null) v_cost_price
          ,coalesce(phppos_location_item_variations.unit_price, phppos_item_variations.unit_price, null) v_unit_price
          ,coalesce(phppos_location_item_variations.reorder_level, phppos_item_variations.reorder_level, null) v_reorder_level
          ,coalesce(phppos_location_item_variations.replenish_level, phppos_item_variations.replenish_level, null) v_replenish_level
        FROM phppos_location_item_variations
        join phppos_item_variations on phppos_item_variations.id = phppos_location_item_variations.item_variation_id

        join phppos_locations ON phppos_location_item_variations.location_id = phppos_locations.location_id
        WHERE 1 = 1
          and phppos_item_variations.deleted = 0
        GROUP BY item_id, item_variation_id, location_id
      ) v on phppos_items.item_id=v.item_id
      LEFT JOIN phppos_suppliers ON phppos_items.supplier_id = phppos_suppliers.person_id
      LEFT JOIN phppos_categories ON phppos_items.category_id = phppos_categories.id
      WHERE 1=1
        and phppos_items.deleted = 0
        and phppos_items.system_item = 0
        '.$item_name.'
        '.$supplier.'
        '.$location_id.'
        '.$category_id.'
      '.$group_by.'
      '.$having.'
	  ';

    // TODO: Cleanup debug lines
    // echo print_r($result);
    // echo '<pre>';print_r($query);
    // echo '<pre>';print_r($this->db->last_query());exit; // milc
    return $query;

	} // dataQuery


	public function getSummaryData()
	{
		if (isset($this->params['show_only_pending']) && $this->params['show_only_pending'])
		{
			return array();
		}

		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		$location_ids_string = implode(',',$location_ids);
		if ($this->params['category_id'])
		{
			if ($this->config->item('include_child_categories_when_searching_or_reporting'))
			{	
				$category_ids = $this->Category->get_category_id_and_children_category_ids_for_category_id($this->params['category_id']);			
			}
			else
			{
				$category_ids = array($this->params['category_id']);
			}
		}		
		
		$location_item_variations_quantity_col = $this->db->dbprefix('location_item_variations').'.quantity';
		$location_items_quantity_col = $this->db->dbprefix('location_items').'.quantity';
		
		$full_sum_query = 'COALESCE(SUM('.$location_item_variations_quantity_col.'),SUM('.$location_items_quantity_col.'),0)';
		$quantity_query = 'COALESCE('.$location_item_variations_quantity_col.','.$location_items_quantity_col.',0)';			

    $suplier = '';
		if ($this->params['supplier'])
		{
			$suplier = ' and supplier_id in ('.$this->params['supplier'].')';
		}

    $category_id = '';
		if ($this->params['category_id'])
		{
		    $category_ids_string = implode(',',$category_ids);
		    $category_id = ' and phppos_items.category_id in ('.$category_ids_string.')';
		}
		
		$in_stock = '';

		if ($this->params['inventory'] == 'in_stock')
		{
			$in_stock = ' and ifnull(quantity, 0) > 0';
		}

		$out_stock = '';
		if ($this->params['inventory'] == 'out_of_stock')
		{
			$out_stock = ' and ifnull(quantity, 0) <= 0';
		}

		$items_where = '';
		if (isset($this->params['item_name']) && $this->params['item_name'])
		{
				$items_where = ' and phppos_items.name LIKE "%'.$this->db->escape_like_str($this->params['item_name']).'%" ';
		}

    $query = 'select
            sum(total_items_in_inventory) total_items_in_inventory,
            sum(inventory_total) inventory_total,
            sum(inventory_sale_total) inventory_sale_total,
            sum(inventory_total) / sum(total_items_in_inventory) as weighted_cost
            from (
              select
                phppos_items.item_id, coalesce(q.location_id, v.location_id, null) location_id,
                ifnull(q.quantity,0) + ifnull(v.variation_quantity,0) as total_items_in_inventory,
                      coalesce(q.q_cost_price, v.v_cost_price, phppos_items.cost_price, 0) as cost_price,
                (ifnull(q.quantity,0) + ifnull(v.variation_quantity,0)) * coalesce(v.v_cost_price, q.q_cost_price, phppos_items.cost_price, 0) as inventory_total,
                (ifnull(q.quantity,0) + ifnull(v.variation_quantity,0)) * coalesce(v.v_unit, q.q_unit, phppos_items.unit_price, 0) as inventory_sale_total,
                q_unit,
                v_unit,
                q_cost_price,
                v_cost_price

              FROM phppos_items

              left join (
                select phppos_location_items.item_id, phppos_location_items.location_id, sum(quantity) quantity
                  ,coalesce(phppos_location_items.cost_price, phppos_items.cost_price) q_cost_price 
                  ,coalesce(phppos_location_items.unit_price, phppos_items.unit_price) q_unit
                from phppos_location_items
                        join phppos_items on phppos_items.item_id = phppos_location_items.item_id
                where phppos_location_items.item_id not in (select item_id from phppos_item_variations)
                  and phppos_location_items.`location_id` IN ('.$location_ids_string.') '.$in_stock.' '.$out_stock.'
                group by item_id, location_id
              ) q on phppos_items.item_id=q.item_id -- and phppos_location_items.location_id = q.location_id

              left JOIN (
                SELECT item_id, item_variation_id, location_id
                  ,sum(quantity) variation_quantity
                  ,coalesce(phppos_location_item_variations.cost_price, phppos_item_variations.cost_price) v_cost_price
                  ,coalesce(phppos_location_item_variations.unit_price, phppos_item_variations.unit_price) v_unit
                FROM phppos_location_item_variations
                join phppos_item_variations on phppos_item_variations.id=phppos_location_item_variations.item_variation_id
                WHERE 1 = 1
                        and phppos_item_variations.deleted = 0
                        and `phppos_location_item_variations`.`location_id` IN ('.$location_ids_string.') '.$in_stock.' '.$out_stock.'
                        GROUP BY item_id, item_variation_id, location_id
               ) v on phppos_items.item_id=v.item_id
               where
                 phppos_items.deleted = 0
					       and is_service != 1
                 '.$suplier.' '.$category_id.' '.$items_where.'
             ) x';
                    
    $result = $this->db->query($query)->row_array();
    // TODO: Cleanup debug lines
    // echo print_r($result);
    // echo '<pre>';print_r($query);
    // echo '<pre>';print_r($this->db->last_query());exit; // milc
		return $result;
	}
}
?>