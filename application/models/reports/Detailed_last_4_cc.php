<?php
require_once ("Report.php");
class Detailed_last_4_cc extends Report
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Tier');
	}
	
	public function getInputData()
	{
		$input_data = Report::get_common_report_input_data(TRUE);
		
		$input_params = array();

		if ($this->settings['display'] == 'tabular')
		{
			$tier_entity_data = array();
			$tier_entity_data['specific_input_name'] = 'tier_id';
			$tier_entity_data['specific_input_label'] = lang('common_tier_name');
			$tier_entity_data['view'] = 'specific_entity';
		
			$tiers = array();
			$tiers[''] =lang('common_no_tier_or_tier');
			$tiers['none'] = lang('common_none');
			$tiers['all'] = lang('common_all');
			$tiers_phppos= $this->Tier->get_all()->result_array();
			foreach($tiers_phppos as $value)
			{
				$tiers[$value['id']] = $value['name'];
			}
		
			$tier_entity_data['specific_input_data'] = $tiers;
			
			
			$input_params = array(
				array('view' => 'date_range', 'with_time' => TRUE),
				array('view' => 'dropdown','dropdown_label' =>lang('reports_sale_type'),'dropdown_name' => 'sale_type','dropdown_options' =>array('all' => lang('reports_all'), 'sales' => lang('reports_sales'), 'returns' => lang('reports_returns')),'dropdown_selected_value' => 'all'),
				array('view' => 'text', 'name' => 'last_4_cc','label' => lang('reports_last_4_cc'),'default' => ''),
				array('view' => 'excel_export'),
				array('view' => 'locations'),
				array('view' => 'submit'),
			);
			
			if (count($tiers_phppos))
			{
				array_unshift($input_params,$tier_entity_data);
			}
		}
		
		$input_data['input_report_title'] = lang('reports_report_options');
		$input_data['input_params'] = $input_params;
		return $input_data;
	}
	
	function getOutputData()
	{
		$this->load->model('Sale');			
		$this->load->model('Category');
		
		$this->setupDefaultPagination();
		
		$headers = $this->getDataColumns();
		
		$report_data = $this->getData();
		$tier_count = $this->Tier->count_all();
		
		$location_count = $this->Location->count_all();
		$summary_data = array();
		foreach($this->params['export_excel'] == 1 && isset($report_data['summary']) ? $report_data['summary']:$report_data as $key=>$row)
		{
			$summary_data_row = array();

			$link = site_url('reports/generate/specific_customer?report_type=complex&start_date='.$this->params['start_date'].'&start_date_formatted='.date(get_date_format().' '.get_time_format(), strtotime($this->params['start_date'])).'&end_date='.$this->params['end_date'].'&end_date_formatted='.date(get_date_format().' '.get_time_format(), strtotime($this->params['end_date'])).'&customer_id='.$row['customer_id'].'&sale_type=all&export_excel=0');
			
			$summary_data_row[] = array('data'=>anchor('sales/receipt/'.$row['sale_id'], '<i class="ion-printer"></i>', 
			array('target' => '_blank', 'class'=>'hidden-print')).'<span class="visible-print">'.$row['sale_id'].'</span>'.anchor('sales/edit/'.$row['sale_id'], '<i class="ion-document-text"></i>', 
			array('target' => '_blank')).' '.anchor('sales/edit/'.$row['sale_id'], lang('common_edit').' '.$row['sale_id'], 
			array('target' => '_blank','class'=>'hidden-print')), 'align'=>'left', 'detail_id' => $row['sale_id']);
			
			if ($location_count > 1)
			{
				$summary_data_row[] = array('data'=>$row['location_name'], 'align' => 'left');
			}
			
			$summary_data_row[] = array('data'=>date(get_date_format().'-'.get_time_format(), strtotime($row['sale_time'])), 'align'=>'left');
			$summary_data_row[] = array('data'=>$row['register_name'], 'align'=>'left');
			$summary_data_row[] = array('data'=>to_quantity($row['items_purchased']), 'align'=>'left');
			$summary_data_row[] = array('data'=>$row['employee_name'].($row['sold_by_employee'] && $row['sold_by_employee'] != $row['employee_name'] ? '/'. $row['sold_by_employee']: ''), 'align'=>'left');
			$summary_data_row[] = array('data'=>'<a href="'.$link.'" target="_blank">'.$row['customer_name'].(isset($row['account_number']) && $row['account_number'] ? ' ('.$row['account_number'].')' : '').'</a>', 'align'=>'left');
			$summary_data_row[] = array('data'=>to_currency($row['subtotal']), 'align'=>'right');
			$summary_data_row[] = array('data'=>to_currency($row['total']), 'align'=>'right');
			if ($this->config->item('enable_tips'))
			{
				$summary_data_row[] = array('data'=>to_currency($row['tip']), 'align'=>'right');
			}
			$summary_data_row[] = array('data'=>to_currency($row['tax']), 'align'=>'right');
			
			if($this->has_profit_permission)
			{
				$summary_data_row[] = array('data'=>to_currency($row['profit']), 'align'=>'right');
				$summary_data_row[] = array('data'=>to_currency($row['subtotal'] - $row['profit']), 'align'=>'right');
			}
			
			$summary_data_row[] = array('data'=>$row['payment_type'], 'align'=>'right');
			$summary_data_row[] = array('data'=>$row['comment'], 'align'=>'right');
			$summary_data_row[] = array('data'=>$row['discount_reason'], 'align'=>'right');
			
			if ($tier_count)
			{
				$summary_data_row[] = array('data'=>$row['tier_name'], 'align'=>'right');
			}
			
		  for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
			{
				$custom_field = $this->Sale->get_custom_field($k);
				if($custom_field !== FALSE)
				{
					if ($this->Sale->get_custom_field($k,'type') == 'checkbox')
					{
						$format_function = 'boolean_as_string';
					}
					elseif($this->Sale->get_custom_field($k,'type') == 'date')
					{
						$format_function = 'date_as_display_date';				
					}
					elseif($this->Sale->get_custom_field($k,'type') == 'email')
					{
						$format_function = 'strsame';					
					}
					elseif($this->Sale->get_custom_field($k,'type') == 'url')
					{
						$format_function = 'strsame';					
					}
					elseif($this->Sale->get_custom_field($k,'type') == 'phone')
					{
						$format_function = 'strsame';					
					}
					elseif($this->Sale->get_custom_field($k,'type') == 'image')
					{
						$this->load->helper('url');
						$format_function = 'file_id_to_image_thumb';					
					}
					elseif($this->Sale->get_custom_field($k,'type') == 'file')
					{
						$this->load->helper('url');
						$format_function = 'file_id_to_download_link';					
					}
					else
					{
						$format_function = 'strsame';
					}
					
					$summary_data_row[] = array('data'=>$format_function($row["custom_field_${k}_value"]), 'align'=>'right');					
				}
			}
			$summary_data[$key] = $summary_data_row;
			
			if($this->params['export_excel'] == 1)
			{
				foreach($report_data['details'][$key] as $drow)
				{
					$details_data_row = array();
					
					$details_data_row[] = array('data'=>$drow['item_number'], 'align'=>'left');
					$details_data_row[] = array('data'=>$drow['item_product_id'], 'align'=>'left');
					$details_data_row[] = array('data'=>$drow['item_name'], 'align'=>'left');
					$details_data_row[] = array('data'=>$this->Category->get_full_path($drow['category_id']), 'align'=>'left');
					$details_data_row[] = array('data'=>$drow['size'], 'align'=>'left');
					$details_data_row[] = array('data'=>$drow['supplier_name']. ' ('.$drow['supplier_id'].')', 'align'=>'left');
					$details_data_row[] = array('data'=>$drow['manufacturer'], 'align'=>'left');
					$details_data_row[] = array('data'=>$drow['serialnumber'], 'align'=>'left');
					$details_data_row[] = array('data'=>character_limiter($drow['description'],150), 'align'=>'left');
					$details_data_row[] = array('data'=>to_currency($drow['unit_price']), 'align'=>'left');
					$details_data_row[] = array('data'=>to_quantity($drow['quantity_purchased']), 'align'=>'left');
					$details_data_row[] = array('data'=>to_currency($drow['subtotal']), 'align'=>'right');
					$details_data_row[] = array('data'=>to_currency($drow['total']), 'align'=>'right');
					$details_data_row[] = array('data'=>to_currency($drow['tax']), 'align'=>'right');
					
					if($this->has_profit_permission)
					{
						$details_data_row[] = array('data'=>to_currency($drow['profit']), 'align'=>'right');					
						$details_data_row[] = array('data'=>to_currency($drow['subtotal'] - $drow['profit']), 'align'=>'right');					
					}
					
					$details_data_row[] = array('data'=>$drow['discount_percent'].'%', 'align'=>'left');
					$details_data[$key][] = $details_data_row;
				}
			
			}
		
		}
		
			$data = array(
				'view' => 'tabular_details_lazy_load',
				"title" =>lang('reports_detailed_sales_report'),
				"subtitle" => date(get_date_format(), strtotime($this->params['start_date'])) .'-'.date(get_date_format(), strtotime($this->params['end_date'])),
				"headers" => $this->getDataColumns(),
				"summary_data" => $summary_data,
				"overall_summary_data" => $this->getSummaryData(),
				"export_excel" => $this->params['export_excel'],
				"pagination" => $this->pagination->create_links(),
				"report_model" => get_class($this),
			);
			isset($details_data) && !empty($details_data) ? $data["details_data"]=$details_data: '' ;
				
		return $data;
	}
	
	
	public function getDataColumns()
	{
		$return = array();
		
		$return['summary'] = array();
		$location_count = $this->Location->count_all();
		
		$return['summary'][] = array('data'=>lang('reports_sale_id'), 'align'=> 'left');
		if ($location_count > 1)
		{
			$return['summary'][] = array('data'=>lang('common_location'), 'align'=> 'left');
		}
		$return['summary'][] = array('data'=>lang('reports_date'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_register'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('common_items_purchased'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_sold_by'), 'align'=> 'left');
		$return['summary'][] = array('data'=>lang('reports_sold_to'), 'align'=> 'left');		
		$return['summary'][] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('reports_total'), 'align'=> 'right');
		
		if ($this->config->item('enable_tips'))
		{
			$return['summary'][] = array('data'=>lang('common_tip'), 'align'=> 'right');
		}
		$return['summary'][] = array('data'=>lang('common_tax'), 'align'=> 'right');
				
		if($this->has_profit_permission)
		{
			$return['summary'][] = array('data'=>lang('common_profit'), 'align'=> 'right');
			$return['summary'][] = array('data'=>lang('common_cogs'), 'align'=> 'right');
		}
		$return['summary'][] = array('data'=>lang('reports_payment_type'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('reports_comments'), 'align'=> 'right');
		$return['summary'][] = array('data'=>lang('common_discount_reason'), 'align'=> 'right');
		
		$tier_count = $this->Tier->count_all();
		if ($tier_count)
		{
			$return['summary'][] = array('data'=>lang('common_tier_name'), 'align'=> 'right');
		}
		
	  for($k=1;$k<=NUMBER_OF_PEOPLE_CUSTOM_FIELDS;$k++) 
		{
			$this->load->model('Sale');
			$custom_field = $this->Sale->get_custom_field($k);
			if($custom_field !== FALSE)
			{
				$return['summary'][] = array('data'=>$custom_field, 'align'=> 'right');
			}
		}
				
		
		$return['details'] = $this->get_details_data_columns_sales();
		return $return;
	}
	
	public function getData()
	{		
		$this->db->select('sales.tip as tip,sales.custom_field_1_value,sales.custom_field_2_value,sales.custom_field_3_value,sales.custom_field_4_value,sales.custom_field_5_value,sales.custom_field_6_value,sales.custom_field_7_value,sales.custom_field_8_value,sales.custom_field_9_value,sales.custom_field_10_value,price_tiers.name as tier_name,locations.name as location_name, sale_id, sale_time, date(sale_time) as sale_date, registers.name as register_name, total_quantity_purchased as items_purchased, CONCAT(sold_by_employee.first_name," ",sold_by_employee.last_name) as sold_by_employee, CONCAT(sold_by_employee.first_name," ",sold_by_employee.last_name) as sold_by_employee, CONCAT(employee.first_name," ",employee.last_name) as employee_name, customer.person_id as customer_id, CONCAT(customer.first_name," ",customer.last_name) as customer_name, customer_data.account_number as account_number,subtotal as subtotal, total as total, tax as tax, profit as profit, payment_type, comment, discount_reason', false);
		$this->db->from('sales');
		$this->db->join('locations', 'sales.location_id = locations.location_id');
		$this->db->join('registers', 'sales.register_id = registers.register_id', 'left');
		$this->db->join('price_tiers', 'sales.tier_id = price_tiers.id', 'left');
		$this->db->join('people as employee', 'sales.employee_id = employee.person_id');
		$this->db->join('people as sold_by_employee', 'sales.sold_by_employee_id = sold_by_employee.person_id', 'left');
		$this->db->join('people as customer', 'sales.customer_id = customer.person_id', 'left');
		$this->db->join('customers as customer_data', 'sales.customer_id = customer_data.person_id', 'left');
		
		if ($this->params['last_4_cc'])
		{
			$this->db->where('phppos_sales.sale_id in (SELECT sale_id FROM phppos_sales_payments WHERE truncated_card LIKE "%'.$this->db->escape_like_str($this->params['last_4_cc']).'%")',null,false);	
		}
		
		if (isset($this->params['tier_id']) && $this->params['tier_id'])
		{
			if ($this->params['tier_id'] == 'none')
			{
				$this->db->where('sales.tier_id is NULL');				
			}
			elseif($this->params['tier_id'] == 'all')
			{
				$this->db->where('sales.tier_id is NOT NULL');				
			}
			else
			{
				$this->db->where('sales.tier_id',$this->params['tier_id']);
			}
		}
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('sales.total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('sales.total_quantity_purchased < 0');
		}
		$this->sale_time_where();
		$this->db->where('sales.deleted', 0);
		
		$this->db->order_by('sale_time', ($this->config->item('report_sort_order')) ? $this->config->item('report_sort_order') : 'asc');
		
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
		
		if (isset($this->params['export_excel']) && $this->params['export_excel'] == 1)
		{
			$data=array();
			$data['summary']=array();
			$data['details']=array();
			foreach($this->db->get()->result_array() as $sale_summary_row)
			{
				$data['summary'][$sale_summary_row['sale_id']] = $sale_summary_row; 
			}

			$sale_ids = array();
			
			foreach($data['summary'] as $sale_row)
			{
				$sale_ids[] = $sale_row['sale_id'];
			}

			$result = $this->get_report_details($sale_ids,1);

			foreach($result as $sale_item_row)
			{
				$data['details'][$sale_item_row['sale_id']][] = $sale_item_row;
			}
			
			return $data;
			exit;
		}
		
		
	}

	public function getTotalRows()
	{
		$this->db->from('sales');
		
		if (isset($this->params['tier_id']) && $this->params['tier_id'])
		{
			if ($this->params['tier_id'] == 'none')
			{
				$this->db->where('sales.tier_id is NULL');				
			}
			elseif($this->params['tier_id'] == 'all')
			{
				$this->db->where('sales.tier_id is NOT NULL');				
			}
			else
			{
				$this->db->where('sales.tier_id',$this->params['tier_id']);
			}
		}
		
		
		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('sales.total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('sales.total_quantity_purchased < 0');
		}
		
		$this->sale_time_where();
		$this->db->where('deleted', 0);
		
		return $this->db->count_all_results();
	}
	public function getSummaryData()
	{
		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit', false);
		$this->db->from('sales');
		
		if (isset($this->params['tier_id']) && $this->params['tier_id'])
		{
			if ($this->params['tier_id'] == 'none')
			{
				$this->db->where('sales.tier_id is NULL');				
			}
			elseif($this->params['tier_id'] == 'all')
			{
				$this->db->where('sales.tier_id is NOT NULL');				
			}
			else
			{
				$this->db->where('sales.tier_id',$this->params['tier_id']);
			}
		}

		if ($this->params['last_4_cc'])
		{
			$this->db->where('phppos_sales.sale_id in (SELECT sale_id FROM phppos_sales_payments WHERE truncated_card LIKE "%'.$this->db->escape_like_str($this->params['last_4_cc']).'%")',null,false);	
		}

		if ($this->params['sale_type'] == 'sales')
		{
			$this->db->where('sales.total_quantity_purchased > 0');
		}
		elseif ($this->params['sale_type'] == 'returns')
		{
			$this->db->where('sales.total_quantity_purchased < 0');
		}
		
		if ($this->config->item('hide_store_account_payments_from_report_totals'))
		{
			$this->db->where('sales.store_account_payment', 0);
		}
		
		
		$this->sale_time_where();
		$this->db->where('deleted', 0);
		
		$return = array(
			'subtotal' => 0,
			'total' => 0,
			'tax' => 0,
			'profit' => 0,
			'cogs' => 0,
		);
		
		foreach($this->db->get()->result_array() as $row)
		{
			$return['subtotal'] += to_currency_no_money($row['subtotal'],2);
			$return['total'] += to_currency_no_money($row['total'],2);
			$return['tax'] += to_currency_no_money($row['tax'],2);
			$return['profit'] += to_currency_no_money($row['profit'],2);
			$return['cogs'] += to_currency_no_money($row['subtotal']-$row['profit'],2);
		}
		
		if(!$this->has_profit_permission)
		{
			unset($return['profit']);
			unset($return['cogs']);
		}
		return $return;
	}
	
	
	
	function get_details_data_columns_sales()
	{
		$details = array();
		$details[] = array('data'=>lang('common_item_number'), 'align'=> 'left');
		$details[] = array('data'=>lang('common_product_id'), 'align'=> 'left');
		$details[] = array('data'=>lang('reports_name'), 'align'=> 'left');
		$details[] = array('data'=>lang('reports_category'), 'align'=> 'left');
		$details[] = array('data'=>lang('common_size'), 'align'=> 'left');
		$details[] = array('data'=>lang('common_supplier'), 'align'=> 'left');
		$details[] = array('data'=>lang('common_manufacturer'), 'align'=> 'left');
		$details[] = array('data'=>lang('reports_serial_number'), 'align'=> 'left');
		if (!$this->config->item('hide_item_descriptions_in_reports') || (isset($this->params['export_excel']) && $this->params['export_excel']))
		{
			$details[] = array('data'=>lang('reports_description'), 'align'=> 'left');
		}
		
		$details[] = array('data'=>lang('common_unit_price'), 'align'=> 'left');
		
		$details[] = array('data'=>lang('reports_quantity_purchased'), 'align'=> 'left');
		$details[] = array('data'=>lang('reports_subtotal'), 'align'=> 'right');
		$details[] = array('data'=>lang('reports_total'), 'align'=> 'right');
		$details[] = array('data'=>lang('common_tax'), 'align'=> 'right');
		if($this->has_profit_permission)
		{
			$details[] = array('data'=>lang('common_profit'), 'align'=> 'right');			
			$details[] = array('data'=>lang('common_cogs'), 'align'=> 'right');			
		}
		
		
		if (strpos($this->report_key, 'commission') !== false)
		{
			$details[] = array('data'=>lang('reports_commission'), 'align'=> 'right');			
		}
		
		$details[] = array('data'=>lang('common_discount'), 'align'=> 'right');
		return $details;
	}
	
	function get_report_details($ids, $export_excel=0)
	{
		$this->db->select('manufacturers.name as manufacturer,sales_items.item_unit_price as unit_price,sales_items.item_variation_id, items.item_id, sales_items.sale_id, items.category_id, items.item_number, items.product_id as item_product_id, items.name as item_name, categories.name as category, quantity_purchased, serialnumber, sales_items.description, subtotal, total, tax, profit, commission, discount_percent, items.size as size, items.unit_price as current_selling_price, suppliers.company_name as supplier_name, suppliers.person_id as supplier_id', false);
		$this->db->from('sales_items');
		$this->db->join('items', 'sales_items.item_id = items.item_id', 'left');
		$this->db->join('manufacturers','manufacturers.id=items.manufacturer_id', 'left');
		$this->db->join('categories', 'categories.id = items.category_id', 'left');
		$this->db->join('suppliers', 'items.supplier_id = suppliers.person_id', 'left');
		if (!empty($ids))
		{
			$sale_ids_chunk = array_chunk($ids,25);
			$this->db->group_start();
			foreach($sale_ids_chunk as $sale_ids)
			{
				$this->db->or_where_in('sales_items.sale_id', $sale_ids);
			}
			$this->db->group_end();
		}
		else
		{
			$this->db->where('1', '2', FALSE);		
		}		
		$qry1=$this->db->get_compiled_select();
		
		$this->db->select('manufacturers.name as manufacturer, sales_item_kits.item_kit_unit_price as unit_price, 0 as item_variation_id, item_kits.item_kit_id, sales_item_kits.sale_id,item_kits.category_id, item_kits.item_kit_number as item_number, item_kits.product_id as item_product_id, item_kits.name as item_name, categories.name as category, quantity_purchased, NULL as serialnumber, sales_item_kits.description, subtotal, total, tax, profit, commission, discount_percent, NULL as size, item_kits.unit_price as current_selling_price, NULL as supplier_name, NULL as supplier_id', false);
		$this->db->from('sales_item_kits');
		$this->db->join('item_kits', 'sales_item_kits.item_kit_id = item_kits.item_kit_id', 'left');
		$this->db->join('manufacturers','manufacturers.id=item_kits.manufacturer_id','left');
		$this->db->join('categories', 'categories.id = item_kits.category_id', 'left');
		if (!empty($ids))
		{
			$sale_ids_chunk = array_chunk($ids,25);
			$this->db->group_start();
			foreach($sale_ids_chunk as $sale_ids)
			{
				$this->db->or_where_in('sales_item_kits.sale_id', $sale_ids);
			}
			$this->db->group_end();
		}
		else
		{
			$this->db->where('1', '2', FALSE);		
		}
		
		$qry2=$this->db->get_compiled_select();
		
		$query = $this->db->query($qry1." UNION ALL ".$qry2);
		//echo $this->db->last_query();exit;
		$res=$query->result_array();
		
		if($export_excel == 1)
		{
			return $res;
			exit;
		}
		$this->load->model('Category');
		
		$variation_ids = array();
		foreach($res as $key=>$drow)
		{			
			if (isset($row['variation_id']) && $row['variation_id'])
			{
				$variation_ids[] = $row['variation_id'];
			}
		}
		
		
		$this->load->model('Item_variations');
		$variation_attrs = $this->Item_variations->get_attributes($variation_ids);

		$variation_labels = array();
		
		foreach($variation_attrs as $variation_id => $attrs)
		{
			 $variation_labels[$variation_id] = implode(', ', array_column($attrs,'label'));
		}
		
		$details_data = array();
		foreach($res as $key=>$drow)
			{	
				$details_data_row = array();
				$details_data_row[] = array('data'=>$drow['item_number'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['item_product_id'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['item_name'].(isset($variation_labels[$drow['item_variation_id']]) ? ': '.$variation_labels[$drow['item_variation_id']] : ''), 'align'=>'left');
				$details_data_row[] = array('data'=>$this->Category->get_full_path($drow['category_id']), 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['size'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['supplier_name'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['manufacturer'], 'align'=>'left');
				$details_data_row[] = array('data'=>$drow['serialnumber'], 'align'=>'left');
				if (!$this->config->item('hide_item_descriptions_in_reports') || (isset($this->params['export_excel']) && $this->params['export_excel']))
				{
					$details_data_row[] = array('data'=>character_limiter($drow['description'],150), 'align'=>'left');
				}
				$details_data_row[] = array('data'=>to_currency($drow['unit_price']), 'align'=>'left');
				$details_data_row[] = array('data'=>to_quantity($drow['quantity_purchased']), 'align'=>'left');
				
				$details_data_row[] = array('data'=>to_currency($drow['subtotal']), 'align'=>'right');
				$details_data_row[] = array('data'=>to_currency($drow['total']), 'align'=>'right');
				$details_data_row[] = array('data'=>to_currency($drow['tax']), 'align'=>'right');
				
				if($this->has_profit_permission)
				{
					$details_data_row[] = array('data'=>to_currency($drow['profit']), 'align'=>'right');					
					$details_data_row[] = array('data'=>to_currency($drow['subtotal']-$drow['profit']), 'align'=>'right');					
				}
				
				if (strpos($this->report_key, 'commission') !== false)
				{
					$details_data_row[] = array('data'=>to_currency($drow['commission']), 'align'=>'right');					
				}
				$details_data_row[] = array('data'=>$drow['discount_percent'].'%', 'align'=> 'left');
				
				$details_data[$key][$drow['sale_id']] = $details_data_row;
			}
		
		$data=array(
		"headers" => $this->getDataColumns(),
		"details_data" => $details_data
		);
		
		return $data;
	}
	
}

?>