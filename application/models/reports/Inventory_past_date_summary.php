<?php
require_once ("Report.php");
class Inventory_past_date_summary extends Report
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
			$name = str_repeat('&nbsp;&nbsp;', $value['depth']).$value['name'];
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
			$input_params[] = array('view' => 'date');			
			$input_params[] = $supplier_entity_data;
			$input_params[] = $category_entity_data;
			$input_params[] = array('view' => 'dropdown','dropdown_label' =>lang('common_inventory'),'dropdown_name' => 'inventory','dropdown_options' =>array('all' => lang('common_all'), 'in_stock' => lang('reports_in_stock'), 'out_of_stock' => lang('reports_out_of_stock')),'dropdown_selected_value' => '');
			$input_params[] = array('view' => 'checkbox','checkbox_label' =>lang('reports_show_deleted_items') ,'checkbox_name' => 'show_deleted');
			$input_params[] = array('view' => 'checkbox','checkbox_label' =>lang('reports_show_negative_inventory_only') ,'checkbox_name' => 'show_negative_inventory_only');
			$input_params[] = array('view' => 'locations', 'can_view_inventory_at_all_locations' => $this->Employee->has_module_action_permission('reports','view_inventory_at_all_locations', $this->Employee->get_logged_in_employee_info()->person_id));
			$input_params[] = array('view' => 'text','label' => lang('common_item_name'),'name' => 'item_name','default' => '');
			$input_params[] = array('view' => 'excel_export');
			$input_params[] = array('view' => 'submit');
		}
	
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	 	 
	}
	
	
	
	
	public function getOutputData()
	{
				
		$this->lang->load('error');
		$this->load->model('Category');
		// Note: Moved to getData after the get
		// $this->setupDefaultPagination();
		
		$date = $this->params['date'];
		$date_as_time = strtotime($date);
		$inventory_start_as_time = strtotime($this->config->item('past_inventory_date'));
		
		if ($date_as_time < $inventory_start_as_time)
		{
			
			return array(
				"view" => 'summary',
				"title" => lang('common_error'),
				"subtitle" => lang('reports_cannot_see_inventory_this_far_in_past'),
				"data" => array(),
				"summary_data" => array(),
				"headers" => array(),
				
			);

		}
		elseif($date == date('Y-m-d'))
		{
			return array(
				"view" => 'summary',
				"title" => lang('common_error'),
				"subtitle" => lang('reports_date_must_be_in_past'),
				"data" => array(),
				"summary_data" => array(),
				"headers" => array(),
				
			);
		}
		
		
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
			// Added: 2022-03-10 Milc cost_price and unit_price DATA columns
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
			$details_data[$drow['item_id']][] = $details_data_row;
			
			$details_quantity_data[$drow['item_id']][] = array(
				'quantity' => $drow['quantity'],
				'pending_inventory' => $drow['pending_inventory'],
				'reorder_level' => $drow['reorder_level'],
				'replenish_level' => $drow['replenish_level'],
			);
		}
		
		foreach(array_keys($details_data) as $item_id)
		{
			$item_quantity = 0;
			$item_pending_inventory = 0;
			$item_reorder_level = 0;
			$item_replenish_level = 0;
			
			for($k=0;$k<count($details_data[$item_id]);$k++)
			{
				$item_quantity+=$details_quantity_data[$item_id][$k]['quantity'];
				$item_pending_inventory+=$details_quantity_data[$item_id][$k]['pending_inventory'];
				$item_reorder_level+=$details_quantity_data[$item_id][$k]['reorder_level'];
				$item_replenish_level+=$details_quantity_data[$item_id][$k]['replenish_level'];
			}
			
			$variation_quantity_summary_data[$item_id] = array(
				'quantity' => $item_quantity,
				'pending_inventory' => $item_pending_inventory,
				'reorder_level' => $item_reorder_level,
				'replenish_level' => $item_replenish_level,
			);
			
		}
		foreach($report_data['summary'] as $row)
		{
			$data_row = array();

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
			$data_row[] = array('data'=>$row['location'], 'align'=> 'left');
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
			$summary_data[$row['item_id']] = $data_row;
			
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
		$columns['summary'][] = array('data'=>lang('common_location'), 'align'=> 'right');

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
		// Added: 2022-03-10 Milc cost_price and unit_price HEADER columns
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
		
		$this->dataQuery();
		
		//If we are exporting NOT exporting to excel make sure to use offset and limit
		if (isset($this->params['export_excel']) && !$this->params['export_excel'])
		{
			$this->db->limit($this->report_limit);
			if (isset($this->params['offset']))
			{
				$this->db->offset($this->params['offset']);
			}
		}
		
		$location_ids = self::get_selected_location_ids();
		$location_ids_string = implode(',',$location_ids);
		

		$inventory_result = $this->db->get()->result_array();

    // echo '<pre>';print_r($this->db->last_query());exit; // milc

    // TODO: Cleanup comments
    // Moved here, right after the main query call so the
    //    $this->getTotalRows(); and
    //    $this->count_last_query_results();
    //    Returns correct count
    $this->setupDefaultPagination();

		// echo '<pre>';print_r($this->db->last_query());exit; //milc

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
	    
	        $item_ids_string = implode(',',$item_ids);
	        $items_in = (!empty($item_ids))?'AND ( `phppos_items`.`item_id` IN('.$item_ids_string.') ) ':'';

    // Variations
        $date = $this->params['date'];
		    $query = 'SELECT X.*,Z.name
                    FROM
                    (
                    SELECT
                        phppos_item_variations.id as variation_id, phppos_location_item_variations.location_id as location_id, phppos_items.item_id,
                        phppos_categories.id as category_id, phppos_categories.name as category, location, company_name,
                        phppos_item_variations.item_number, size, product_id,

                        COALESCE(phppos_location_item_variations.cost_price, phppos_item_variations.cost_price, phppos_location_items.cost_price, phppos_items.cost_price, 0) as cost_price,
                        COALESCE(phppos_location_item_variations.unit_price, phppos_item_variations.unit_price, phppos_location_items.unit_price, phppos_items.unit_price, 0) as unit_price,

                        -- SUM( DISTINCT(phppos_location_item_variations.quantity)) as quantity,
                        SUM(COALESCE(inv.trans_current_quantity, 0)) AS quantity,

                        COALESCE(phppos_location_item_variations.reorder_level, phppos_item_variations.reorder_level, phppos_location_items.reorder_level, phppos_items.reorder_level) as reorder_level,
                        COALESCE(phppos_location_item_variations.replenish_level, phppos_item_variations.replenish_level, phppos_location_items.replenish_level, phppos_items.replenish_level) as replenish_level,
                        description
                    
                    FROM phppos_inventory inv
                      -- Item by Variations
                      JOIN  `phppos_item_variations` ON `phppos_item_variations`.`item_id` = inv.trans_items
                        AND `phppos_item_variations`.`id` = inv.item_variation_id
                      LEFT JOIN `phppos_location_item_variations` ON `phppos_location_item_variations`.`item_variation_id` = `phppos_item_variations`.`id`
                        AND `phppos_location_item_variations`.`location_id` = inv.location_id

                      -- Items by Location
                      JOIN `phppos_items` ON `phppos_items`.`item_id` =  inv.`trans_items`
                      LEFT JOIN `phppos_location_items` ON `phppos_location_items`.`item_id` = `phppos_items`.`item_id`
                        AND `phppos_location_items`.`location_id` = inv.location_id

                      LEFT OUTER JOIN `phppos_suppliers` ON `phppos_items`.`supplier_id` = `phppos_suppliers`.`person_id`
                      LEFT OUTER JOIN `phppos_categories` ON `phppos_items`.`category_id` = `phppos_categories`.`id`

                    WHERE
                        inv.trans_id = (
                          SELECT MAX(inv1.trans_id)
                          FROM
                            phppos_inventory inv1
                          WHERE
                            inv1.trans_items = inv.trans_items
                            AND (inv1.item_variation_id = phppos_item_variations.id OR `phppos_item_variations`.`id` IS NULL)
                            AND inv1.location_id = inv.location_id
                            AND inv1.trans_date < \''. $date . ' 23:59:59\'
                        )
                        AND inv.`location_id` IN ('.$location_ids_string.')
                        AND `phppos_item_variations`.`deleted` = 0
                        '.$items_in.'
                    GROUP BY `phppos_item_variations`.`id`
                        ) X
                    
                    LEFT JOIN (
                        SELECT `phppos_item_variations`.`id` ,
                        GROUP_CONCAT(DISTINCT phppos_attributes.name, ": ", phppos_attribute_values.name SEPARATOR ", ") as name

                        FROM `phppos_item_variations` JOIN `phppos_item_variation_attribute_values` ON `phppos_item_variations`.`id` = `phppos_item_variation_attribute_values`.`item_variation_id` JOIN `phppos_attribute_values` ON `phppos_attribute_values`.`id` = `phppos_item_variation_attribute_values`.`attribute_value_id` JOIN `phppos_attributes` ON `phppos_attributes`.`id` = `phppos_attribute_values`.`attribute_id`
                        WHERE `phppos_item_variations`.`deleted` = 0
                        GROUP BY `phppos_item_variations`.`id`
                            ) Z 
                            ON X.variation_id=Z.ID';
                    $inventory_result_variations = $this->db->query($query)->result_array();
    // echo '<pre>';print_r($this->db->last_query());exit; //milc
	    
		// Get Suspended Items Again for variations
		$this->db->select('item_id, item_variation_id, quantity_purchased - quantity_received as pending_inventory', false);
		$this->db->from('receivings_items');
		$this->db->join('receivings', 'receivings.receiving_id = receivings_items.receiving_id');
		$this->db->where('receivings.deleted', 0);
		$this->db->where('receivings.suspended', 1);
		$this->db->where_in('location_id', $location_ids);

		$pending_inventory_result = $this->db->get()->result_array();
    // echo '<pre>';print_r($this->db->last_query());exit; //milc

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
		
		
		// echo '<pre>';print_r($inventory_result);exit; //milc
		// echo '<pre>';print_r($inventory_result_variations);exit; //milc
		return array('summary' => $inventory_result, 'details' => $inventory_result_variations);
		
	}

	function getTotalRows()
	{
	  // TODO: Call from correct class (object)
		$count_all_results = $this->count_last_query_results();
		// echo '<pre>';print_r('All results: '. $count_all_results);exit; //milc
		return $count_all_results;
	}
	
	private function dataQuery()
	{
		$date = $this->params['date'];
		$location_ids = self::get_selected_location_ids();
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

		$quantity_query = 'COALESCE('.$location_item_variations_quantity_col.','.$location_items_quantity_col.', 0)';

    // Build inner join query as subquery:
    $this->db->select('trans_items');
    $this->db->select('item_variation_id');
    $this->db->select('location_id');
    $this->db->select('MAX(trans_id) as trans_id');
    $this->db->from('inventory');
    $this->db->where('trans_date <', $date . ' 23:59:59');
    $this->db->group_by('trans_items, item_variation_id, location_id');
    $this->db->order_by('trans_items, item_variation_id, location_id');
		$join_subquery = $this->db->get_compiled_select();
    $this->db->reset_query();

		$this->db->protect_identifiers('SQL_CALC_FOUND_ROWS');

		$this->db->select('/*+ SEMIJOIN(@subq MATERIALIZATION) */ SQL_CALC_FOUND_ROWS 1 as _h', FALSE);

		$this->db->select('location_items.location_id');
		$this->db->select('items.item_id');
		$this->db->select('items.name');
		$this->db->select('categories.id as category_id');
		$this->db->select('categories.name as category');
		$this->db->select('location_items.location');
		$this->db->select('suppliers.company_name');
		$this->db->select('items.item_number');
		$this->db->select('items.size');
		$this->db->select('items.product_id');

    // Added: Milc 2022-03-10 Changed to WEIGHTED AVG prices (cost_price and unit_price)
    $this->db->select('SUM(COALESCE('.$this->db->dbprefix('location_item_variations').'.cost_price, '
                                     .$this->db->dbprefix('item_variations').'.cost_price, '
                                     .$this->db->dbprefix('location_items').'.cost_price, '
                                     .$this->db->dbprefix('items').'.cost_price, 0) * COALESCE(inv.trans_current_quantity, 0)) / SUM(COALESCE(inv.trans_current_quantity, 0)) as cost_price', FALSE);

    $this->db->select('SUM(COALESCE('.$this->db->dbprefix('location_item_variations').'.unit_price, '
                                     .$this->db->dbprefix('item_variations').'.unit_price, '
                                     .$this->db->dbprefix('location_items').'.unit_price, '
                                     .$this->db->dbprefix('items').'.unit_price, 0) * COALESCE(inv.trans_current_quantity, 0)) / SUM(COALESCE(inv.trans_current_quantity, 0)) as unit_price', FALSE);

    /* Original code
    $this->db->select('COALESCE('.$this->db->dbprefix('location_item_variations').'.cost_price, '
                                 .$this->db->dbprefix('item_variations').'.cost_price, '
                                 .$this->db->dbprefix('location_items').'.cost_price, '
                                 .$this->db->dbprefix('items').'.cost_price, 0) as cost_price', FALSE);

    $this->db->select('COALESCE('.$this->db->dbprefix('location_item_variations').'.unit_price, '
                                 .$this->db->dbprefix('item_variations').'.unit_price, '
                                 .$this->db->dbprefix('location_items').'.unit_price, '
                                 .$this->db->dbprefix('items').'.unit_price, 0) as unit_price', FALSE);
    */

    $sum_query = 'SUM(COALESCE(inv.trans_current_quantity, 0))';
		$this->db->select($sum_query.' as quantity', FALSE);

    $this->db->select('COALESCE('.$this->db->dbprefix('location_item_variations').'.reorder_level,'
                                 .$this->db->dbprefix('item_variations').'.reorder_level,'
                                 .$this->db->dbprefix('location_items').'.reorder_level, '
                                 .$this->db->dbprefix('items').'.reorder_level) as reorder_level', FALSE);
    $this->db->select('COALESCE('.$this->db->dbprefix('location_item_variations').'.replenish_level, '
                                 .$this->db->dbprefix('item_variations').'.replenish_level, '
                                 .$this->db->dbprefix('location_items').'.replenish_level, '
                                 .$this->db->dbprefix('items').'.replenish_level) as replenish_level', FALSE);

    $this->db->select('description', FALSE);

		$this->db->from('inventory inv');

    // Items by Location
		$this->db->join('items', 'items.item_id=inv.trans_items', 'left');
		$this->db->join('location_items', 'location_items.item_id = items.item_id and location_items.location_id = inv.location_id', 'left');

		// Item by Variations
		$this->db->join('item_variations', 'items.item_id=item_variations.item_id and item_variations.id = inv.item_variation_id and item_variations.deleted = 0', 'left');
		$this->db->join('location_item_variations', 'location_item_variations.item_variation_id = item_variations.id and location_item_variations.location_id = inv.location_id', 'left');

		$this->db->join('suppliers', 'items.supplier_id = suppliers.person_id', 'left outer');
		$this->db->join('categories', 'items.category_id = categories.id', 'left outer');

    // inner join with subquery
		$this->db->join("($join_subquery) inv1", 'inv1.trans_id = inv.trans_id AND inv1.trans_items = inv.trans_items AND (inv1.item_variation_id = '.
		  $this->db->dbprefix('item_variations').'.id OR '.
		  $this->db->dbprefix('item_variations').'.id IS NULL) AND inv1.location_id = inv.location_id', 'inner');
    /*
    // Old implementation using WHERE to determine last trans_items
    $past_date_where = '(SELECT MAX(inv1.trans_id)
			FROM
				phppos_inventory inv1
			WHERE
				inv1.trans_items = inv.trans_items
				AND (inv1.item_variation_id = '.$this->db->dbprefix('item_variations').'.id OR '.$this->db->dbprefix('item_variations').'.id IS NULL)
				AND inv1.location_id = inv.location_id
        AND inv1.trans_date < \''. $date . ' 23:59:59\'
        )';
		$this->db->where('inv.trans_id', $past_date_where, FALSE);
    */

    $this->db->where_in('inv.location_id', $location_ids, FALSE);

		$this->db->where('items.system_item',0);

		// To get relevant results we need to group by item_id and location_id
		$this->db->group_by('items.item_id');
		// TODO: Decide this next line:
		// $this->db->group_by('location_items.location_id');

		if (!isset($this->params['show_deleted']) || !$this->params['show_deleted'])
		{
		  $this->db->where('items.deleted', 0);
		}

		if ($this->params['supplier'])
		{
			// $this->db->where('suppliers.person_id', $this->params['supplier']);
			$this->db->where('supplier_id', $this->params['supplier']);
		}

		if ($this->params['category_id'])
		{
			// $this->db->where_in('categories.id', $category_ids);
			$this->db->where_in('category_id', $category_ids);
		}
		
		if (isset($this->params['item_name']) && $this->params['item_name'])
		{
			$this->db->like('items.name',$this->params['item_name'],'both');
		}

		if ($this->params['inventory'] == 'in_stock')
		{
			$this->db->having($sum_query.' > 0');
		}

		if ($this->params['inventory'] == 'out_of_stock')
		{
			$this->db->having($sum_query.' <= 0');
		}
		
		if (isset($this->params['show_negative_inventory_only']) && $this->params['show_negative_inventory_only'])
		{
			$this->db->having($sum_query.' < 0');
		}

		$this->db->where('is_service !=', 1);
		
	} // private function dataQuery()
	
	/*
	  New Query -  Inventory Summary at Past Date
	  Note: Due to the bug in CodeIgniter query builder with incorrectly prefixing nested tables the query has been
	  written via string rather than query builder.
	*/
	public function getSummaryData()
	{		
		$date = $this->params['date'];
		
		$location_ids = self::get_selected_location_ids();
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
		
		$location_item_variations_quantity_col = 'trans_current_quantity';
		$location_items_quantity_col = 'trans_current_quantity';
		
		$full_sum_query = 'SUM(COALESCE('.$location_item_variations_quantity_col.', '.$location_items_quantity_col.', 0))';
		$quantity_query = 'COALESCE('.$location_item_variations_quantity_col.', '.$location_items_quantity_col.', 0)';

    $location_where = '';
    if (count($location_ids))
    {
      if (count($location_ids) == 1)
      {
        $location_where = 'AND inv.`location_id` = '.$location_ids[0];
      }
      else
      {
        $location_where = 'AND inv.`location_id` IN ('. $location_ids_string .')';
      }
    }
    $deleted_where = '';
		if (!isset($this->params['show_deleted']) || !$this->params['show_deleted'])
		{
				$deleted_where = 'AND `phppos_items`.`deleted` = 0';
		}

    $supplier_where = '';
		if ($this->params['supplier'])
		{
			// $this->db->where('suppliers.person_id', $this->params['supplier']);
			// $supplier_where = 'AND `phppos_suppliers`.`person_id` = ' . $this->params['supplier'] . '';
			$supplier_where = 'AND `phppos_items`.`supplier_id` = ' . $this->params['supplier'] . '';
		}

    $category_where = '';
		if ($this->params['category_id'])
		{
			// $this->db->where_in('categories.id', $category_ids);
			$category_ids_string = implode(',',$category_ids);
			if (count($category_ids) == 1)
			{
			  // $category_where = 'AND `phppos_categories`.`id` IN ('.$category_ids_string.')';
			  $category_where = 'AND `phppos_items`.`category_id` = ' . $category_ids[0];
		  }
		  else
		  {
		    $category_where = 'AND `phppos_items`.`category_id` IN ('.$category_ids_string.')';
		  }
		}

    $inventory_where = '';
		if ($this->params['inventory'] == 'in_stock')
		{
			// $this->db->where($quantity_query.' > 0');
			$inventory_where = 'AND '.$quantity_query.' > 0';
		}

		if ($this->params['inventory'] == 'out_of_stock')
		{
			// $this->db->where($quantity_query.' <= 0');
			$inventory_where = 'AND '.$quantity_query.' <= 0';
		}

    $query = 'SELECT /*+ SEMIJOIN(@subq MATERIALIZATION) */
    SUM(COALESCE(inv.trans_current_quantity, inv.trans_current_quantity, 0)) AS total_items_in_inventory
    ,SUM(COALESCE(phppos_location_item_variations.cost_price, phppos_item_variations.cost_price, phppos_location_items.cost_price, phppos_items.cost_price, 0) * (COALESCE(trans_current_quantity, trans_current_quantity, 0))) AS inventory_total
    ,SUM(COALESCE(phppos_location_item_variations.unit_price, phppos_item_variations.unit_price, phppos_location_items.unit_price, phppos_items.unit_price, 0) * (COALESCE(trans_current_quantity, trans_current_quantity, 0))) AS inventory_sale_total
    ,SUM(COALESCE(phppos_location_item_variations.cost_price, phppos_item_variations.cost_price, phppos_location_items.cost_price, phppos_items.cost_price, 0) * (COALESCE(trans_current_quantity, trans_current_quantity, 0))) / SUM(COALESCE(trans_current_quantity, trans_current_quantity, 0)) AS weighted_cost

FROM `phppos_inventory` `inv`
	JOIN `phppos_items` on `phppos_items`.`item_id` = inv.`trans_items`
	LEFT JOIN `phppos_item_variations` ON `phppos_items`.`item_id` = `phppos_item_variations`.`item_id` AND `phppos_item_variations`.`deleted` = 0
	LEFT JOIN `phppos_location_item_variations` ON `phppos_location_item_variations`.`item_variation_id` = `phppos_item_variations`.`id`
		AND `phppos_location_item_variations`.`location_id` = inv.location_id
	LEFT JOIN `phppos_location_items` ON `phppos_location_items`.`item_id` = `phppos_items`.`item_id`
		AND `phppos_location_items`.`location_id` = inv.location_id

	INNER JOIN (
		SELECT inv1.trans_items, inv1.item_variation_id, inv1.location_id, MAX(inv1.trans_id) as trans_id
			FROM phppos_inventory inv1
			WHERE inv1.trans_date < \''. $date . ' 23:59:59\'
		GROUP BY inv1.trans_items, inv1.item_variation_id, inv1.location_id
		ORDER BY inv1.trans_items, inv1.item_variation_id, inv1.location_id
	) inv1 on inv1.trans_id = inv.trans_id
	  AND inv1.trans_items = inv.trans_items
		AND (inv1.item_variation_id = phppos_item_variations.id OR phppos_item_variations.id IS NULL)
		AND inv1.location_id = inv.location_id

	WHERE 1=1
--		AND inv.trans_id = (
--		SELECT MAX(inv1.trans_id)
--			FROM
--				phppos_inventory inv1
--			WHERE
--             inv1.trans_items = inv.trans_items
--				AND (inv1.item_variation_id = phppos_item_variations.id OR `phppos_item_variations`.`id` IS NULL)
--				AND inv1.`location_id` = inv.location_id
--        AND inv1.trans_date < \''. $date . ' 23:59:59\'
--        )
		AND `phppos_items`.`is_service` != 1
		AND `phppos_items`.`system_item` = 0
    '.$location_where.'
    '.$deleted_where.'
    '.$supplier_where.'
    '.$category_where.'
    '.$inventory_where.'
    ';

		$result = $this->db->query($query)->row_array();
		// echo '<pre>';print_r($this->db->last_query());exit; // milc
		return $result;
	}
	
}
?>